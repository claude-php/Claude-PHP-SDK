# Tutorial 16: New Features in v0.5.2

**Time:** 60 minutes | **Difficulty:** Intermediate  
**Prerequisites:** Tutorials 1-3 (Basic agent and tool use)

## 🎯 Overview

This tutorial covers the powerful new features introduced in v0.5.2, which achieves complete parity with Python SDK v0.76.0. You'll learn about server-side tools, flexible authentication, and enhanced streaming capabilities.

## 📚 What You'll Learn

- **Server-Side Tools**: How to use tools executed by Claude's API
- **Authentication Flexibility**: Alternative auth methods for enterprise scenarios
- **Stream Management**: Proper resource cleanup and management
- **Binary Streaming**: Advanced binary data handling

## 🔧 New Features Overview

### 1. Server-Side Tools Support

Server-side tools are executed by Claude's API, not your application:

```php
// No handler needed for server-side tools!
$runner = $client->beta()->messages()->toolRunner([
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 4096,
    'messages' => [
        ['role' => 'user', 'content' => 'Execute: print("Hello from server!")'],
    ],
], []); // Empty tools array - server tools work automatically

foreach ($runner as $message) {
    // Claude uses server-side code execution
    // Your code doesn't execute anything locally
    echo $message->content[0]['text'] ?? '';
}
```

**Key Points:**
- Server-side tools have type `server_tool_use` (vs. `tool_use` for client-side)
- No local handler function required
- Claude's API executes the tool securely
- Common examples: `code_execution`, `bash_20250124`

### 2. Authentication Flexibility

Support for multiple authentication methods beyond API keys:

```php
// OAuth2 / Bearer Token
$client = new ClaudePhp(
    apiKey: null,
    customHeaders: ['Authorization' => 'Bearer your-oauth-token']
);

// Custom x-api-key (for proxies)
$client = new ClaudePhp(
    apiKey: null,
    customHeaders: ['x-api-key' => 'your-proxy-key']
);

// Azure AD / Enterprise SSO
$azureToken = getAzureAdToken();
$client = new ClaudePhp(
    apiKey: null,
    customHeaders: [
        'Authorization' => "Bearer {$azureToken}",
        'X-Tenant-ID' => 'your-tenant-id',
    ]
);
```

**Use Cases:**
- API gateways with centralized key management
- OAuth2 service accounts
- Enterprise SSO integration
- Multi-tenant applications

### 3. Enhanced Stream Management

Automatic resource cleanup for streaming responses:

```php
$stream = $client->messages()->stream([
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 1024,
    'messages' => [['role' => 'user', 'content' => 'Tell me a story']],
]);

foreach ($stream as $event) {
    echo $event['delta']['text'] ?? '';
}

// Stream automatically closes when done or when variable goes out of scope
// No manual cleanup needed!
```

**Features:**
- Automatic cleanup via `__destruct()`
- Idempotent `close()` method
- Guaranteed resource freeing
- Works with early loop breaks

### 4. Binary Request Streaming

Send binary data with streaming responses:

```php
$transport = $client->getHttpTransport();

$binaryData = file_get_contents('image.png');

$response = $transport->postStreamBinary(
    '/custom-endpoint',
    $binaryData,
    'image/png'
);

// Process streaming response
foreach ($response as $chunk) {
    // Handle real-time processing results
}
```

## 🧪 Practical Example: Mixed Tools Agent

Let's build an agent that uses both client-side and server-side tools:

```php
<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../helpers.php';

use ClaudePhp\ClaudePhp;
use function ClaudePhp\Lib\Tools\beta_tool;

loadEnv(__DIR__ . '/../../.env');
$client = new ClaudePhp(apiKey: getApiKey());

// Define client-side tools (executed locally)
$weatherTool = beta_tool(
    handler: function(array $args): string {
        // Simulated API call
        $location = $args['location'] ?? 'Unknown';
        return "Weather in {$location}: Sunny, 72°F";
    },
    name: 'get_weather',
    description: 'Get current weather for a location',
    inputSchema: [
        'type' => 'object',
        'properties' => [
            'location' => ['type' => 'string', 'description' => 'City name'],
        ],
        'required' => ['location'],
    ]
);

$databaseTool = beta_tool(
    handler: function(array $args): string {
        // Simulated database query
        $query = $args['query'] ?? '';
        return "Database results for: {$query}\nFound 5 records";
    },
    name: 'query_database',
    description: 'Query the application database',
    inputSchema: [
        'type' => 'object',
        'properties' => [
            'query' => ['type' => 'string', 'description' => 'SQL query'],
        ],
        'required' => ['query'],
    ]
);

// Run agent - it will automatically handle both types
$runner = $client->beta()->messages()->toolRunner([
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 4096,
    'messages' => [
        [
            'role' => 'user',
            'content' => 'What is the weather in San Francisco? Also, write and execute Python code to calculate 15 * 23.',
        ],
    ],
], [$weatherTool, $databaseTool]); // Only client-side tools

echo "🤖 Agent Response:\n";
echo str_repeat("=", 80) . "\n\n";

foreach ($runner as $message) {
    echo "Message ID: {$message->id}\n";
    echo "Stop Reason: {$message->stop_reason}\n\n";
    
    foreach ($message->content as $block) {
        if (($block['type'] ?? '') === 'text') {
            echo "📝 Text: {$block['text']}\n\n";
        } elseif (($block['type'] ?? '') === 'tool_use') {
            echo "🔧 Client Tool Used: {$block['name']}\n";
            echo "   Input: " . json_encode($block['input']) . "\n\n";
        } elseif (($block['type'] ?? '') === 'server_tool_use') {
            echo "🖥️  Server Tool Used: {$block['name']}\n";
            echo "   Input: " . json_encode($block['input']) . "\n";
            echo "   (Executed by Claude's API, not locally)\n\n";
        }
    }
    
    echo str_repeat("-", 80) . "\n\n";
}

echo "✅ Agent completed!\n";
```

## 🔍 Understanding Tool Types

### Client-Side Tools

**Block Type:** `tool_use`

```json
{
  "type": "tool_use",
  "id": "toolu_01ABC123",
  "name": "get_weather",
  "input": {"location": "San Francisco"}
}
```

**Characteristics:**
- Executed by your PHP application
- You provide the handler function
- Results sent back to Claude
- Examples: API calls, database queries, file operations

### Server-Side Tools

**Block Type:** `server_tool_use`

```json
{
  "type": "server_tool_use",
  "id": "toolu_server_001",
  "name": "code_execution",
  "input": {
    "language": "python",
    "code": "print('Hello')"
  }
}
```

**Characteristics:**
- Executed by Claude's API
- No handler function needed
- Sandboxed, secure execution
- Examples: `code_execution`, `bash_20250124`

## 🏗️ Tool Runner Behavior

The tool runners automatically handle both types:

```php
// BetaToolRunner, ToolRunner, and StreamingToolRunner
// all support mixed tool types

foreach ($toolUses as $toolUse) {
    $type = $toolUse['type'];
    
    if ($type === 'server_tool_use') {
        // Skip - API handles execution
        continue;
    }
    
    if ($type === 'tool_use') {
        // Execute locally
        $result = $handler($toolUse['input']);
        // Return result to Claude
    }
}
```

## 🔐 Authentication Patterns

### Pattern 1: API Gateway Integration

```php
// Your API gateway handles authentication
$client = new ClaudePhp(
    apiKey: null,
    baseUrl: 'https://your-gateway.com/claude',
    customHeaders: [
        'X-Gateway-Key' => 'your-gateway-key',
        'X-User-ID' => 'user-123',
    ]
);
```

### Pattern 2: Dynamic Token Refresh

```php
class TokenRefreshClient {
    private ClaudePhp $client;
    private string $token;
    private int $expiresAt;
    
    public function getClient(): ClaudePhp {
        if (time() >= $this->expiresAt) {
            $this->refreshToken();
        }
        
        return new ClaudePhp(
            apiKey: null,
            customHeaders: [
                'Authorization' => "Bearer {$this->token}",
            ]
        );
    }
    
    private function refreshToken(): void {
        // Refresh OAuth2 token
        $response = $this->oauth2Client->refreshToken();
        $this->token = $response['access_token'];
        $this->expiresAt = time() + $response['expires_in'];
    }
}
```

### Pattern 3: Environment-Based Auth

```php
$isProduction = getenv('APP_ENV') === 'production';

$client = new ClaudePhp(
    apiKey: $isProduction ? getenv('ANTHROPIC_API_KEY') : null,
    customHeaders: $isProduction ? [] : [
        // Dev proxy with debugging
        'x-api-key' => getenv('DEV_PROXY_KEY'),
        'X-Debug-Mode' => 'true',
    ]
);
```

## 📊 Stream Resource Management

### Best Practices

```php
// ✅ Good: Automatic cleanup
function processStream(ClaudePhp $client) {
    $stream = $client->messages()->stream([...]);
    
    foreach ($stream as $event) {
        processEvent($event);
    }
    
    // Stream auto-closes when function exits
}

// ✅ Good: Explicit cleanup for early exit
function processStreamWithBreak(ClaudePhp $client) {
    $stream = $client->messages()->stream([...]);
    
    foreach ($stream as $event) {
        if (shouldStop($event)) {
            $stream->close(); // Explicit close
            break;
        }
    }
}

// ✅ Good: Try-finally for guaranteed cleanup
function processStreamSafe(ClaudePhp $client) {
    $stream = $client->messages()->stream([...]);
    
    try {
        foreach ($stream as $event) {
            processEvent($event);
        }
    } finally {
        $stream->close(); // Always closes
    }
}
```

## 🎯 Key Takeaways

1. **Server-Side Tools**
   - No local execution required
   - Automatic handling in tool runners
   - Identified by `server_tool_use` type
   - Perfect for code execution and bash commands

2. **Authentication Flexibility**
   - Multiple auth methods supported
   - Great for enterprise integration
   - Works with proxies and gateways
   - Enables OAuth2 and SSO

3. **Stream Management**
   - Automatic cleanup via destructors
   - Idempotent close operations
   - Guaranteed resource freeing
   - Works with early loop breaks

4. **Tool Runners**
   - Handle mixed tool types seamlessly
   - No code changes needed
   - Backward compatible
   - Server tools "just work"

## 🚀 Next Steps

1. **Experiment** with server-side tools in your agents
2. **Implement** custom authentication for your use case
3. **Review** streaming patterns in your codebase
4. **Test** mixed tool scenarios

## 📖 Related Resources

- [Server-Side Tools Example](../../examples/server_side_tools.php)
- [Authentication Example](../../examples/authentication_flexibility.php)
- [CHANGELOG v0.5.2](../../CHANGELOG.md)
- [Python SDK v0.76.0 Release](https://github.com/anthropics/anthropic-sdk-python/releases/tag/v0.76.0)

## 💡 Tips

- Server-side tools don't count against your infrastructure load
- Use custom auth headers for centralized key management
- Stream cleanup is automatic but explicit close is still good practice
- Mix tool types freely - the runner handles it

---

**Previous:** [Tutorial 15 - Context Management](../15-context-management/)  
**Next:** [Examples Directory](../../examples/)

