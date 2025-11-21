# Tutorial 4: Production-Ready Agent

**Time: 60 minutes** | **Difficulty: Intermediate**

You've built agents that work in ideal conditions. But production systems need to handle errors, failures, retries, and persistent state. In this tutorial, we'll transform your agent into a robust, production-grade system.

## 🎯 Learning Objectives

By the end of this tutorial, you'll be able to:

- Implement comprehensive error handling
- Add retry logic with exponential backoff
- Handle tool execution failures gracefully
- Integrate server-side tools (Web Search)
- Implement logging and monitoring
- Build graceful degradation strategies
- Test error scenarios

## 🏗️ What We're Building

We'll enhance our multi-tool agent with:

1. **Error Handling** - Catch and handle all failure modes
2. **Retry Logic** - Automatically retry transient failures
3. **Server-Side Tools** - Integrate tools like Web Search
4. **Logging** - Track agent behavior and issues
5. **Graceful Degradation** - Continue working when tools fail

## 🚨 Production Failure Modes

### Common Failures

1. **API Errors**

   - Rate limiting (429)
   - Temporary outages (503)
   - Authentication issues (401)
   - Timeout errors

2. **Tool Execution Errors**

   - Invalid input
   - External API failures
   - Calculation errors
   - Resource unavailable

3. **Agent Errors**

   - Infinite loops
   - Context window overflow
   - Malformed responses
   - Unexpected stop reasons

4. **State Management**
   - Lost conversation history
   - Memory inconsistencies
   - Token limit exceeded

## 🛡️ Error Handling Strategy

### 1. Catch All Exceptions

```php
try {
    $response = $client->messages()->create([...]);
} catch (\ClaudePhp\Exceptions\RateLimitError $e) {
    // Handle rate limiting
    $retryAfter = $e->response->getHeaderLine('retry-after');
    sleep($retryAfter);
    // Retry...
} catch (\ClaudePhp\Exceptions\APIConnectionError $e) {
    // Network/timeout issue
    log_error("Connection failed: " . $e->getMessage());
    // Retry with backoff...
} catch (\ClaudePhp\Exceptions\AuthenticationError $e) {
    // Invalid API key - don't retry
    log_error("Authentication failed");
    throw $e;
} catch (\ClaudePhp\Exceptions\APIStatusError $e) {
    // Other API errors
    log_error("API error {$e->status_code}: {$e->message}");
    // Retry if 5xx, fail if 4xx
} catch (Exception $e) {
    // Unexpected error
    log_error("Unexpected: " . $e->getMessage());
    throw $e;
}
```

### 2. Retry with Exponential Backoff

```php
function retryWithBackoff(callable $fn, $maxAttempts = 3) {
    $attempt = 0;
    $delay = 1000; // Start with 1 second

    while ($attempt < $maxAttempts) {
        try {
            return $fn();
        } catch (\ClaudePhp\Exceptions\RateLimitError $e) {
            $attempt++;
            if ($attempt >= $maxAttempts) throw $e;

            $retryAfter = $e->response->getHeaderLine('retry-after');
            $waitTime = $retryAfter ?: ($delay / 1000);

            echo "Rate limited. Waiting {$waitTime}s...\n";
            sleep($waitTime);
            $delay *= 2; // Exponential backoff
        } catch (\ClaudePhp\Exceptions\APIConnectionError $e) {
            $attempt++;
            if ($attempt >= $maxAttempts) throw $e;

            echo "Connection error. Retrying in {$delay}ms...\n";
            usleep($delay * 1000);
            $delay *= 2;
        }
    }
}
```

### 3. Tool Error Handling

```php
function executeToolSafely($toolName, $input) {
    try {
        $result = executeTool($toolName, $input);
        return [
            'success' => true,
            'content' => $result
        ];
    } catch (Exception $e) {
        log_error("Tool {$toolName} failed: " . $e->getMessage());
        return [
            'success' => false,
            'content' => "Error: " . $e->getMessage(),
            'is_error' => true
        ];
    }
}

// Use in ReAct loop
foreach ($response->content as $block) {
    if ($block['type'] === 'tool_use') {
        $result = executeToolSafely($block['name'], $block['input']);

        $toolResults[] = [
            'type' => 'tool_result',
            'tool_use_id' => $block['id'],
            'content' => $result['content'],
            'is_error' => !$result['success']
        ];
    }
}
```

## 🌐 Server-Side Tools

### Using the Web Search Tool

The Web Search Tool gives Claude access to real-time information from the web. Unlike custom tools, web search is executed server-side by Claude automatically!

```php
$webSearchTool = [
    'type' => 'web_search_20250305',
    'name' => 'web_search',
    'max_uses' => 3  // Limit searches per request
];

// Add to your tools
$tools = [$calculator, $weather, $webSearchTool];
```

**Note:** Web search must be enabled in your organization's Claude Console.

### Web Search Features

Claude can:

- **Search the web** for current information
- **Cite sources** automatically from search results
- **Answer questions** beyond its knowledge cutoff
- **Find real-time data** like weather, prices, or news

### Example Usage

```php
// User: "What is the current version of PHP?"
// Claude will:
// - Decide to search based on the query
// - Execute web search automatically (server-side)
// - Provide answer with cited sources

// User: "What are the latest developments in quantum computing?"
// Claude will:
// - Perform web search
// - Synthesize information from multiple sources
// - Include citations in the response
```

## 📊 Logging and Monitoring

### Structured Logging

```php
class AgentLogger {
    public function logIteration($iteration, $response) {
        $log = [
            'timestamp' => date('Y-m-d H:i:s'),
            'iteration' => $iteration,
            'stop_reason' => $response->stop_reason,
            'tokens' => [
                'input' => $response->usage->input_tokens,
                'output' => $response->usage->output_tokens
            ],
            'tools_used' => $this->extractToolsUsed($response)
        ];

        file_put_contents(
            'agent.log',
            json_encode($log) . "\n",
            FILE_APPEND
        );
    }

    private function extractToolsUsed($response) {
        $tools = [];
        foreach ($response->content as $block) {
            if ($block['type'] === 'tool_use') {
                $tools[] = $block['name'];
            }
        }
        return $tools;
    }
}
```

### Monitoring Metrics

Track these metrics:

1. **Success Rate**: % of tasks completed successfully
2. **Average Iterations**: How many loops per task
3. **Token Usage**: Total tokens consumed
4. **Error Rate**: % of requests that fail
5. **Tool Usage**: Which tools are used most
6. **Latency**: Time per request

## 🔄 Graceful Degradation

When tools fail, continue working with reduced functionality:

```php
function executeToolWithFallback($toolName, $input) {
    try {
        return executeTool($toolName, $input);
    } catch (Exception $e) {
        // Log the failure
        log_error("Tool {$toolName} failed, using fallback");

        // Return a fallback response
        return "Tool temporarily unavailable. Please try again later.";
    }
}

// Or disable failed tools
if ($toolFailureCount > 3) {
    echo "Disabling unreliable tool: {$toolName}\n";
    $tools = array_filter($tools, fn($t) => $t['name'] !== $toolName);
}
```

## 🧪 Testing Error Scenarios

### Test Cases

```php
// 1. Test rate limiting
function testRateLimiting() {
    // Make many requests quickly
    // Should trigger rate limit and retry
}

// 2. Test tool failure
function testToolFailure() {
    // Force a tool to fail
    // Agent should handle gracefully
}

// 3. Test max iterations
function testMaxIterations() {
    // Give a task that requires many steps
    // Should stop at limit without crashing
}

// 4. Test invalid input
function testInvalidInput() {
    // Provide malformed data
    // Should validate and reject cleanly
}

// 5. Test memory persistence
function testWebSearch() {
    // Test search functionality
    // Verify citations included
}
```

## ✅ Production Checklist

Before deploying your agent:

- [ ] All errors caught and handled
- [ ] Retry logic implemented for transient failures
- [ ] Tool execution wrapped in try-catch
- [ ] Logging configured and tested
- [ ] Server-side tools configured (if needed)
- [ ] Iteration limits set appropriately
- [ ] Token usage monitored
- [ ] Graceful degradation strategies in place
- [ ] Error scenarios tested
- [ ] Documentation for operators

## 🎯 Best Practices

### 1. Always Set Timeouts

```php
$client = new ClaudePhp(
    apiKey: $apiKey,
    timeout: 30.0,  // 30 seconds
    maxRetries: 2
);
```

### 2. Validate Tool Input

```php
function validateCalculatorInput($expression) {
    // Only allow safe characters
    if (!preg_match('/^[0-9+\-*\/().\s]+$/', $expression)) {
        throw new Exception("Invalid expression");
    }
    return true;
}
```

### 3. Rate Limit Your Own Requests

```php
class RateLimiter {
    private $lastRequest = 0;
    private $minInterval = 100; // ms

    public function throttle() {
        $now = microtime(true) * 1000;
        $elapsed = $now - $this->lastRequest;

        if ($elapsed < $this->minInterval) {
            usleep(($this->minInterval - $elapsed) * 1000);
        }

        $this->lastRequest = microtime(true) * 1000;
    }
}
```

### 4. Circuit Breaker Pattern

```php
class CircuitBreaker {
    private $failures = 0;
    private $threshold = 5;
    private $timeout = 60; // seconds
    private $openedAt = null;

    public function call(callable $fn) {
        if ($this->isOpen()) {
            throw new Exception("Circuit breaker is open");
        }

        try {
            $result = $fn();
            $this->recordSuccess();
            return $result;
        } catch (Exception $e) {
            $this->recordFailure();
            throw $e;
        }
    }

    private function isOpen() {
        if ($this->openedAt === null) return false;

        if (time() - $this->openedAt > $this->timeout) {
            $this->openedAt = null;
            $this->failures = 0;
            return false;
        }

        return true;
    }

    private function recordFailure() {
        $this->failures++;
        if ($this->failures >= $this->threshold) {
            $this->openedAt = time();
        }
    }

    private function recordSuccess() {
        $this->failures = 0;
        $this->openedAt = null;
    }
}
```

## ✅ Checkpoint

Before moving on, make sure you understand:

- [ ] Common failure modes in production
- [ ] How to catch and handle different exception types
- [ ] Retry logic with exponential backoff
- [ ] Tool error handling with is_error flag
- [ ] Using server-side tools like Web Search
- [ ] Logging and monitoring strategies
- [ ] Graceful degradation patterns

## 🚀 Next Steps

Your agent is now production-ready! But we can make it even smarter with planning, reflection, and extended thinking.

**[Tutorial 5: Advanced ReAct →](../05-advanced-react/)**

Learn advanced agentic patterns with planning and reflection!

## 💻 Try It Yourself

Run the complete working example:

```bash
php tutorials/04-production-ready/production_agent.php
```

The script demonstrates:

- ✅ Comprehensive error handling
- ✅ Retry logic with backoff
- ✅ Tool error handling
- ✅ Server-side tool integration
- ✅ Logging and monitoring
- ✅ Error scenario testing
- ✅ Graceful degradation

## 📚 Further Reading

- [SDK Example: error_handling.php](../../examples/error_handling.php)
- [SDK Example: web_search.php](../../examples/web_search.php)
- [Claude Docs: Error Handling](https://docs.anthropic.com/en/api/errors)
- [Claude Docs: Web Search Tool](https://docs.anthropic.com/en/docs/agents-and-tools/tool-use/web-search-tool)
