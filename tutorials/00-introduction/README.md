# Tutorial 0: Introduction to Claude PHP SDK

**Time: 45 minutes** | **Difficulty: Beginner**

Welcome to the first tutorial in our series on building AI agents! This tutorial is your comprehensive introduction to the Claude PHP SDK. We'll start with installation and basic usage, explore all the core features, and then introduce you to agentic AI concepts.

By the end, you'll have a solid foundation in both the SDK and the agent patterns that power the rest of this tutorial series.

## 🎯 Learning Objectives

By the end of this tutorial, you'll understand:

- How to install and configure the Claude PHP SDK
- Core SDK configuration options (API keys, timeouts, retries)
- How to make basic API requests to Claude
- The different Claude models and when to use each
- How to work with messages and conversation history
- How to handle responses and extract information
- Introduction to streaming for real-time responses
- Error handling patterns for robust applications
- The difference between chatbots and AI agents
- The ReAct (Reason-Act-Observe) pattern
- How tool use enables agent capabilities
- When to use agents vs simple API calls

---

# Part 1: Claude PHP SDK Fundamentals

## 📦 Installation & Setup

### System Requirements

Before getting started, ensure you have:

- **PHP 8.1 or higher** installed
- **Composer** for dependency management
- **Anthropic API Key** ([Get one here](https://console.anthropic.com/))

### Installation

Install the Claude PHP SDK using Composer:

```bash
composer require claude-php/claude-php-sdk
```

### Environment Setup

Create a `.env` file in your project root:

```bash
ANTHROPIC_API_KEY=sk-ant-your-api-key-here
```

> **💡 Tip:** Never commit your `.env` file to version control. Add it to `.gitignore` to keep your API key secure.

Load the environment in your PHP code:

```php
<?php
require 'vendor/autoload.php';

// Load environment variables
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$name, $value] = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);
```

> **Note:** The SDK also provides a helper in `/examples/helpers.php` with `loadEnv()` and `getApiKey()` functions for convenience.

## ⚙️ Client Configuration

The Claude PHP SDK provides flexible configuration options to customize the client behavior:

### Basic Configuration

```php
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']  // API key (required)
);
```

### Advanced Configuration

```php
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY'],       // API key
    baseUrl: 'https://api.anthropic.com/v1',   // Default: Anthropic API
    timeout: 30.0,                              // Request timeout in seconds
    maxRetries: 2,                              // Auto-retry on failures
    customHeaders: [                            // Additional headers
        'X-Custom-Header' => 'value'
    ]
);
```

### Configuration Options Explained

| Option          | Type   | Default                        | Purpose                      |
| --------------- | ------ | ------------------------------ | ---------------------------- |
| `apiKey`        | string | `ANTHROPIC_API_KEY` env var    | Your API authentication key  |
| `baseUrl`       | string | `https://api.anthropic.com/v1` | API endpoint URL             |
| `timeout`       | float  | `30.0`                         | Request timeout in seconds   |
| `maxRetries`    | int    | `2`                            | Auto-retry on 429/5xx errors |
| `customHeaders` | array  | `[]`                           | Additional HTTP headers      |

> **Best Practice:** Keep default values for most use cases. Only adjust `timeout` for very long responses or `maxRetries` for unreliable networks.

## 🚀 Basic API Usage

### Your First Request

Here's the simplest way to make a request to Claude:

```php
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: $_ENV['ANTHROPIC_API_KEY']);

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => [
        [
            'role' => 'user',
            'content' => 'Hello, Claude! What can you tell me about PHP?'
        ]
    ]
]);

// Extract the response text
foreach ($response->content as $block) {
    if ($block['type'] === 'text') {
        echo $block['text'];
    }
}
```

> **💡 Quick Tip:** The `content` array can contain multiple blocks. Always check the `type` field before accessing block-specific properties.

### Required Parameters

Every request to the Messages API requires these parameters:

| Parameter    | Type    | Description                                        |
| ------------ | ------- | -------------------------------------------------- |
| `model`      | string  | The model to use (e.g., 'claude-sonnet-4-5')       |
| `max_tokens` | integer | Maximum tokens in the response (1-4096)            |
| `messages`   | array   | Array of message objects with 'role' and 'content' |

> **Important:** `max_tokens` is always required. If the response is cut off, you'll get `stop_reason: 'max_tokens'`. Increase the limit if needed.

### Response Structure

All API responses have this structure:

```php
$response->id              // Unique request ID
$response->type            // Always "message"
$response->model           // Model used
$response->content         // Array of content blocks
$response->stop_reason     // Why Claude stopped (see later)
$response->usage           // Token usage statistics
    ->input_tokens         // Tokens in prompt
    ->output_tokens        // Tokens in response
```

## 🤖 Available Models

Claude has multiple models optimized for different use cases:

### Claude Sonnet 4.5 (Latest & Balanced)

```php
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5-20250929',  // Or use alias: 'claude-sonnet-4-5'
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'Explain machine learning']
    ]
]);
```

**Best for:**

- General-purpose tasks
- Balanced performance and cost
- Most use cases
- Complex reasoning

**Trade-offs:**

- Medium speed
- Medium cost
- Highest capability

### Claude Haiku 4.5 (Fast & Cost-Effective)

```php
$response = $client->messages()->create([
    'model' => 'claude-haiku-4-5-20251001',  // Or use alias: 'claude-haiku-4-5'
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'What is 5+3?']
    ]
]);
```

**Best for:**

- High-volume tasks
- Speed-critical applications
- Cost-sensitive workloads
- Simple questions

**Trade-offs:**

- Fastest response time
- Lowest cost
- Lower capability on complex tasks

### Claude Opus 4.5 (Premium)

```php
$response = $client->messages()->create([
    'model' => 'claude-opus-4-5-20251101',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'Your message']
    ]
]);
```

**Best for:**

- Specialized scientific/technical tasks
- When you need maximum accuracy
- Research applications

### Model Selection Guide

| Task Type             | Recommended Model | Reason                      |
| --------------------- | ----------------- | --------------------------- |
| General Q&A           | Sonnet 4.5        | Best balance                |
| Quick answers         | Haiku 4.5         | Fast & cheap                |
| Complex reasoning     | Sonnet 4.5        | Most capable                |
| Math problems         | Haiku 4.5         | Fast enough, cost-effective |
| Writing/summarization | Sonnet 4.5        | Best quality                |
| Batch processing      | Haiku 4.5         | Cost savings                |

> **Model Aliases:** You can use short aliases like `claude-sonnet-4-5` instead of full model IDs. Aliases automatically point to the latest version of that model tier.

## 💬 Message Patterns

### Single-Turn Conversations

The simplest pattern: ask a question, get an answer.

```php
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'What is the capital of France?']
    ]
]);

echo $response->content[0]['text'];  // Output: Paris
```

### Multi-Turn Conversations

Build conversations by including the full message history:

```php
$messages = [
    ['role' => 'user', 'content' => 'Hello, Claude'],
    ['role' => 'assistant', 'content' => 'Hello! How can I help you today?'],
    ['role' => 'user', 'content' => 'Can you explain LLMs?']
];

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => $messages
]);

// To continue the conversation, add the assistant's response
$messages[] = ['role' => 'assistant', 'content' => $response->content[0]['text']];
$messages[] = ['role' => 'user', 'content' => 'Can you give me an example?'];

// Make another request with the updated history
$response2 = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => $messages
]);
```

**Important:** The Messages API is stateless. You must provide the complete conversation history with each request. Claude doesn't remember previous conversations.

> **Performance Tip:** Use [Prompt Caching](../../examples/prompt_caching.php) for long conversation histories to reduce costs by up to 90%.

### Using System Prompts

System prompts guide Claude's behavior and personality:

```php
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'system' => 'You are a helpful physics teacher. Explain concepts clearly.',
    'messages' => [
        ['role' => 'user', 'content' => 'What is quantum entanglement?']
    ]
]);
```

System prompts are useful for:

- Setting a specific personality or tone
- Providing context for the task
- Establishing rules for how to respond
- Improving response consistency

### Response Prefilling

Guide Claude's response by prefilling the beginning:

```php
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 100,
    'messages' => [
        ['role' => 'user', 'content' => 'List 3 programming languages:'],
        ['role' => 'assistant', 'content' => '1. Python\n2. ']  // Prefilled start
    ]
]);

// Claude will complete from where you left off
// Output might be: "JavaScript\n3. Go"
```

Prefilling helps with:

- Formatting responses in a specific way
- Getting single-word answers (combine with low max_tokens)
- Constraining output to specific formats

> **Use Case:** Prefilling is especially useful for multiple-choice questions, structured output, or when you need responses in a specific format.

## 📊 Response Handling

### Accessing Response Content

Responses can contain multiple content blocks. Always check the type:

```php
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'Hello!']
    ]
]);

// Responses are arrays of content blocks
foreach ($response->content as $block) {
    if ($block['type'] === 'text') {
        echo "Text: " . $block['text'];
    }
    // Later we'll see other types like 'tool_use' for agents
}
```

### Checking Stop Reasons

The `stop_reason` tells you why Claude stopped generating:

```php
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'Tell me a story']
    ]
]);

if ($response->stop_reason === 'end_turn') {
    echo "Claude finished naturally";
} elseif ($response->stop_reason === 'max_tokens') {
    echo "Response was cut off - increase max_tokens";
}
```

Possible stop reasons:

- `end_turn`: Claude finished naturally
- `max_tokens`: Response hit max_tokens limit
- `tool_use`: Claude wants to call a tool (agents - covered later)

### Token Usage & Cost Planning

Every response includes token usage information:

```php
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'What is AI?']
    ]
]);

$inputTokens = $response->usage->input_tokens;
$outputTokens = $response->usage->output_tokens;

echo "Input tokens: {$inputTokens}\n";
echo "Output tokens: {$outputTokens}\n";
echo "Total tokens: " . ($inputTokens + $outputTokens) . "\n";
```

Tokens represent small units of text. Token usage directly affects API costs, so it's important to monitor and optimize.

> **Cost Estimation:** Roughly 1 token ≈ 4 characters (or 0.75 words). Use the [token counting](../../examples/token_counting.php) example to estimate costs before running large batches.

## 🔄 Streaming Responses

### When to Use Streaming

Streaming is useful for:

- Displaying responses as they're generated (better UX)
- Long responses (users see progress)
- Real-time applications (chatbots, assistants)

### Basic Streaming Example

```php
use ClaudePhp\Lib\Streaming\MessageStream;

$rawStream = $client->messages()->stream([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'Tell me a short story']
    ]
]);

$stream = new MessageStream($rawStream);

// Display text as it arrives
echo $stream->textStream();
```

### Streaming vs Non-Streaming Trade-offs

| Aspect        | Streaming           | Non-Streaming   |
| ------------- | ------------------- | --------------- |
| Latency       | Lower (progressive) | Higher (wait)   |
| UX            | Better (real-time)  | Basic           |
| Complexity    | More code           | Simpler         |
| Response time | First token fast    | All at once     |
| Accumulation  | Need to build       | Complete object |

> **When to Stream:** Use streaming for user-facing applications where real-time feedback improves UX. Use non-streaming for batch processing or when you need the complete response immediately.

For now, focus on non-streaming. We'll explore streaming in depth in later examples ([streaming_basic.php](../../examples/streaming_basic.php), [streaming_comprehensive.php](../../examples/streaming_comprehensive.php)).

## ❌ Error Handling

The SDK provides specific exceptions for different error types:

### Exception Hierarchy

```
AnthropicException (base)
├── AuthenticationError       (invalid API key)
├── RateLimitError           (too many requests)
├── APIConnectionError       (network issues)
├── APITimeoutError          (request timeout)
└── APIError                 (other API errors)
```

> **Error Handling Strategy:** Always catch specific exceptions first (most specific to least specific) to handle different errors appropriately.

### Basic Error Handling

```php
use ClaudePhp\Exceptions\{
    AnthropicException,
    AuthenticationError,
    RateLimitError,
    APIConnectionError
};

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            ['role' => 'user', 'content' => 'Hello!']
        ]
    ]);
} catch (AuthenticationError $e) {
    echo "API key is invalid. Check ANTHROPIC_API_KEY";
} catch (RateLimitError $e) {
    echo "Rate limited. Retry after: " . $e->response->getHeaderLine('retry-after');
    // Implement exponential backoff
} catch (APIConnectionError $e) {
    echo "Network error: " . $e->getMessage();
} catch (AnthropicException $e) {
    echo "Unexpected error: " . $e->getMessage();
}
```

### Production Error Handling Pattern

```php
function makeRequestWithRetry($client, $params, $maxRetries = 3) {
    $attempt = 0;
    while ($attempt < $maxRetries) {
        try {
            return $client->messages()->create($params);
        } catch (RateLimitError $e) {
            $attempt++;
            if ($attempt >= $maxRetries) throw $e;

            // Exponential backoff
            sleep(2 ** $attempt);
        } catch (APITimeoutError $e) {
            $attempt++;
            if ($attempt >= $maxRetries) throw $e;

            // Retry on timeout
            sleep(1);
        }
    }
}
```

## 🎚️ Advanced Parameters

### Temperature (Randomness)

Controls response randomness (0.0 = deterministic, 1.0 = creative):

```php
// Deterministic responses (best for facts)
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'temperature' => 0.0,  // Always the same answer
    'messages' => [
        ['role' => 'user', 'content' => 'What is 2+2?']
    ]
]);

// Creative responses (best for writing)
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'temperature' => 1.0,  // Different each time
    'messages' => [
        ['role' => 'user', 'content' => 'Write a poem about coding']
    ]
]);
```

**When to adjust:**

- Factual Q&A → Use 0.0-0.3
- Creative writing → Use 0.7-1.0
- Default (1.0) → Good for most tasks

> **Note:** Temperature 1.0 is the default. Lower temperatures make responses more focused and deterministic, which is useful for tasks requiring consistency.

---

# Part 2: Introduction to Agentic AI

Now that you understand the basics of the Claude PHP SDK, let's explore what makes AI systems "agentic" and why agents are more powerful than simple chatbots.

## 🎯 What is an AI Agent?

### Chatbot vs Agent

Think of the difference like this:

**🤖 Chatbot** (Traditional LLM Use):

- You ask a question → It responds
- Single turn interaction
- Relies only on its training data
- Cannot take actions or gather new information
- Passive responder

**🧠 AI Agent** (Agentic System):

- You give a goal → It figures out how to achieve it
- Multi-turn autonomous operation
- Can use tools to gather information or take actions
- Makes decisions about next steps
- Active problem solver

### Example Comparison

**Chatbot Interaction:**

```
You: "What's the weather in San Francisco?"
Bot: "I don't have access to real-time weather data.
      I was last trained in [date]..."
```

**Agent Interaction:**

```
You: "What's the weather in San Francisco?"
Agent: [Thinks: I need current weather data]
        [Acts: Calls weather API for San Francisco]
        [Observes: API returns 68°F, sunny]
        [Responds: "It's currently 68°F and sunny in San Francisco"]
```

## 🔄 The ReAct Pattern

ReAct (Reason-Act-Observe) is the fundamental pattern that powers agentic behavior. It's a loop that continues until the task is complete:

```
┌─────────────────────────────────────────┐
│  1. REASON (Think)                      │
│     "What do I need to do next?"        │
│     "What information do I need?"       │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│  2. ACT (Execute)                       │
│     "Call a tool to get information"    │
│     "Perform an action"                 │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│  3. OBSERVE (Analyze)                   │
│     "What did the tool return?"         │
│     "Do I have enough information?"     │
└──────────────┬──────────────────────────┘
               │
               ▼
        ┌──────────────┐
        │  Complete?   │
        └──────┬───────┘
               │
        ┌──────┴──────┐
        │             │
       No            Yes
        │             │
        │             ▼
        │      ┌──────────────┐
        │      │  Respond to  │
        │      │     User     │
        │      └──────────────┘
        │
        └──────> (Loop back to REASON)
```

### Real-World Example

Let's see how the ReAct loop works for a complex task:

**Task**: "Book me a flight to New York tomorrow"

```
Iteration 1:
  REASON:  "I need to know the user's departure city and preferred time"
  ACT:     Ask user for details
  OBSERVE: User provides "San Francisco, morning flight"

Iteration 2:
  REASON:  "Now I can search for flights"
  ACT:     Call flight search API
  OBSERVE: Found 3 morning flights

Iteration 3:
  REASON:  "I should present options and get confirmation"
  ACT:     Show flight options to user
  OBSERVE: User selects 7:30 AM flight

Iteration 4:
  REASON:  "Now I can book the selected flight"
  ACT:     Call booking API
  OBSERVE: Booking confirmed

COMPLETE: "Your flight is booked! Confirmation #ABC123"
```

Notice how the agent autonomously decided what information to gather, which APIs to call, and when the task was complete. This is the essence of agentic behavior.

## 🛠️ How Tool Use Enables Agents

Tools are **functions that Claude can call** to:

1. **Get Information**: Weather, stock prices, database queries
2. **Take Actions**: Send emails, book appointments, make purchases
3. **Compute**: Math calculations, data analysis, code execution
4. **Interact**: Web search, API calls, file operations

### Anatomy of a Tool

A tool definition tells Claude:

```php
[
    'name' => 'get_weather',              // What to call it
    'description' => 'Get current weather  // What it does
                      for a location',
    'input_schema' => [                   // What parameters it needs
        'type' => 'object',
        'properties' => [
            'location' => [
                'type' => 'string',
                'description' => 'City name'
            ]
        ],
        'required' => ['location']
    ]
]
```

### Tool Use Flow

```
1. You provide tools to Claude
   ↓
2. Claude decides if/when to use them
   ↓
3. Claude requests tool execution with parameters
   ↓
4. Your code executes the tool
   ↓
5. You return results to Claude
   ↓
6. Claude uses results to formulate response
```

> **Key Insight:** You define what tools are available, but Claude decides when and how to use them. This autonomy is what makes the system "agentic."

## 🤔 When to Use Agents

### ✅ Good Use Cases for Agents

- **Research Tasks**: "Find the top 5 ML papers on agent architectures"
- **Multi-step Workflows**: "Analyze this dataset and create a report"
- **Dynamic Problem Solving**: "Debug why the API is returning errors"
- **Information Gathering**: "Compare prices across 3 vendors"
- **Task Automation**: "Summarize my emails and draft responses"

### ❌ When NOT to Use Agents

- **Simple Q&A**: "What is Python?" → Direct response is fine
- **Static Content**: "Translate this text" → No tools needed
- **Real-time Chat**: High latency from multiple iterations
- **Deterministic Tasks**: "Calculate 2+2" → Tool call overhead unnecessary
- **Cost-Sensitive**: Agents use more tokens (more iterations)

### Decision Matrix

| Scenario                 | Simple API Call | Agent |
| ------------------------ | --------------- | ----- |
| User asks for definition | ✅              | ❌    |
| User needs current data  | ❌              | ✅    |
| Multi-step reasoning     | ❌              | ✅    |
| Needs to take action     | ❌              | ✅    |
| Simple calculation       | ✅              | ❌    |
| Complex workflow         | ❌              | ✅    |

## 🎯 Key Concepts

### 1. Autonomy

Agents make decisions about **what** to do and **when** to do it. You don't script every step; you give them capabilities and a goal.

### 2. Tool Use

Tools extend Claude's capabilities beyond its training data. They're the "hands and eyes" of your agent.

### 3. State Management

Agents maintain conversation history across turns. Each iteration builds on previous observations.

### 4. Stop Conditions

Agents need to know when they're done. This could be:

- Task completed successfully
- Maximum iterations reached
- Error encountered
- User explicitly stops

### 5. Iteration Limits

Always set max iterations to prevent infinite loops:

```php
$maxIterations = 10;  // Safety limit
```

## 📊 Costs and Considerations

### Token Usage

Agents use more tokens because:

- Multiple API calls (each iteration)
- Tool definitions in every request
- Growing conversation history
- Special system prompts for tool use

**Example**: A simple question might use 500 tokens, while an agent task could use 5,000+ tokens.

### Latency

Each iteration adds ~1-3 seconds. A 5-iteration agent task takes 5-15 seconds.

### Reliability

More complexity = more failure points:

- Tools can fail
- APIs can timeout
- Agent might get stuck
- Need robust error handling

## 💡 Best Practices

1. **Start Simple**: Begin with one tool, add complexity gradually
2. **Clear Tool Descriptions**: Help Claude choose the right tool
3. **Validate Input**: Check tool parameters before execution
4. **Handle Errors Gracefully**: Tools will fail; plan for it
5. **Limit Iterations**: Prevent runaway loops
6. **Log Everything**: Debug agents by reviewing their reasoning
7. **Test Edge Cases**: What if tools return errors? Empty results?

## 🔍 Debugging Agents

When debugging, look at:

1. **Tool Selection**: Did Claude pick the right tool?
2. **Parameters**: Are the inputs correct?
3. **Tool Results**: What data came back?
4. **Stop Reason**: Why did it stop? (`tool_use`, `end_turn`, `max_tokens`)
5. **Iteration Count**: Did it hit the limit?
6. **Token Usage**: Are you approaching limits?

## 🎓 Types of Agents (Preview)

We'll explore these in later tutorials:

### Simple ReAct Agent (Tutorial 1)

Your first working agent with a calculator tool

### Multi-Tool Agent (Tutorial 2)

Chooses from multiple tools intelligently

### Production Agent (Tutorial 3)

Error handling, retries, memory

### Advanced ReAct (Tutorial 4)

Planning, reflection, extended thinking

### Agentic Framework (Tutorial 5)

Task decomposition, parallel execution, orchestration

## 🎓 SDK vs Agents: Quick Reference

Before moving on, here's a quick reference to help you decide when to use SDK basics vs agents:

| Scenario                          | SDK Approach                          | Agent Approach                            |
| --------------------------------- | ------------------------------------- | ----------------------------------------- |
| "What is PHP?"                    | ✅ Simple API call with system prompt | ❌ Overkill                               |
| "What's the weather in Paris?"    | ❌ Can't access real-time data        | ✅ Agent with weather tool                |
| "Translate this text"             | ✅ Simple API call                    | ❌ No tools needed                        |
| "Summarize and email this report" | ❌ Multiple steps needed              | ✅ Agent with tools                       |
| "Calculate 123 × 456"             | ✅ Claude can do this mentally        | ⚠️ Agent with calculator is more accurate |
| "Research and compare 3 products" | ❌ Needs external data                | ✅ Agent with search tools                |

**Rule of Thumb:** If the task requires external data, multiple steps, or conditional logic, use an agent. Otherwise, a simple SDK call is sufficient.

## ✅ Checkpoint

Before moving on, make sure you understand:

**SDK Fundamentals:**

- [ ] How to install and configure the Claude PHP SDK
- [ ] The required parameters for making API requests
- [ ] How to access response content and metadata
- [ ] The different Claude models and when to use each
- [ ] How to handle conversation history
- [ ] When to use system prompts and response prefilling
- [ ] Basic streaming vs non-streaming trade-offs
- [ ] Error handling patterns and exception types
- [ ] How to monitor token usage

**Agentic AI Concepts:**

- [ ] Difference between chatbots and agents
- [ ] What the ReAct pattern is
- [ ] How tools enable agent capabilities
- [ ] When to use agents vs simple API calls
- [ ] Why iteration limits are important

## 🚀 Next Steps

Ready to build your first agent? Continue to:

**[Tutorial 1: Your First Agent →](../01-first-agent/)**

You'll build a working agent with a calculator tool and see the ReAct loop in action!

## 📚 Further Reading

**SDK Documentation:**

- [Anthropic API Documentation](https://docs.claude.com/)
- [SDK Examples: basic_request.php](../../examples/basic_request.php)
- [SDK Examples: get_started.php](../../examples/get_started.php)
- [SDK Examples: error_handling.php](../../examples/error_handling.php)

**Agent Concepts:**

- [ReAct Paper (Yao et al., 2022)](https://arxiv.org/abs/2210.03629)
- [Claude Tool Use Documentation](https://docs.claude.com/en/docs/agents-and-tools/tool-use/overview)
- [SDK Examples: tool_use_overview.php](../../examples/tool_use_overview.php)

## 💻 Try It Yourself

Run the companion code examples:

```bash
# See all the concepts in action
php tutorials/00-introduction/concepts.php
```

This will demonstrate both SDK basics and agentic concepts with working code examples!
