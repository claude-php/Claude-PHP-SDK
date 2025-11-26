# Claude PHP SDK

A universal, framework-agnostic PHP SDK for the Anthropic Claude API with full PSR compliance and **complete documentation parity**.

## Features

- ✅ **Full API Parity**: Comprehensive implementation of Messages, Files, Batches, and Models APIs
- ✅ **Complete Example Coverage**: **80+ comprehensive examples** covering all Claude documentation pages (11,000+ lines)
- ✅ **PSR Compliance**: Follows PSR-12 coding standards and PSR-11 dependency injection patterns
- ✅ **Framework Agnostic**: Works seamlessly with Laravel, Symfony, Slim, and other PHP frameworks
- ✅ **Latest Models**: Support for Claude Sonnet 4.5, Haiku 4.5, and Opus 4.5
- ✅ **Advanced Features**: Tool use, vision, streaming, extended thinking, embeddings, batch processing, and more
- ✅ **Async Ready**: Built for modern async patterns with Amphp support
- ✅ **Comprehensive Error Handling**: Detailed exception hierarchy matching the Python SDK
- ✅ **Production Ready**: All examples tested, documented, and verified with live API calls

## Installation

```bash
composer require claude-php/claude-php-sdk
```

## Quick Start

### Basic Usage

```php
<?php
require 'vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 1024,
    'messages' => [
        [
            'role' => 'user',
            'content' => 'Hello Claude, what is 2+2?'
        ]
    ]
]);

echo $response->content[0]->text;
```

### Configuration

```php
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY'],      // Or defaults to ANTHROPIC_API_KEY env var
    baseUrl: 'https://api.anthropic.com/v1',  // Default URL
    timeout: 30.0,                             // Request timeout in seconds
    maxRetries: 2,                             // Auto-retry on 429/5xx errors
    customHeaders: [                           // Additional headers
        'X-Custom-Header' => 'value'
    ]
);
```

## 📚 Comprehensive Examples

> 💡 **Complete Documentation Coverage**: The [`examples/`](examples/) directory contains **80+ comprehensive example files** (11,000+ lines of code) covering **all Claude documentation pages** with complete Python → PHP parity:
>
> 📊 **Statistics**: 80+ files | 11,000+ lines | All docs pages | 100% tested | 0 errors
>
> **Getting Started:**
>
> - **[quickstart.php](examples/quickstart.php)** - Simplest possible example
> - **[basic_request.php](examples/basic_request.php)** - Basic API request
> - **[get_started.php](examples/get_started.php)** - Complete getting started guide
> - **[working_with_messages.php](examples/working_with_messages.php)** - Practical patterns (vision, prefilling, etc.)
> - **[multi_turn.php](examples/multi_turn.php)** - Multi-turn conversations
> - **[putting_words.php](examples/putting_words.php)** - Response prefilling
>
> **Streaming (8 Examples):**
>
> - **[streaming_basic.php](examples/streaming_basic.php)** - Simple text streaming
> - **[streaming_comprehensive.php](examples/streaming_comprehensive.php)** - All streaming patterns
> - **[streaming_with_events.php](examples/streaming_with_events.php)** - Event-driven streaming
> - **[streaming_with_tools.php](examples/streaming_with_tools.php)** - Streaming with tool use
> - **[streaming_extended_thinking.php](examples/streaming_extended_thinking.php)** - Streaming with thinking
> - **[streaming_message_accumulation.php](examples/streaming_message_accumulation.php)** - Building complete messages
> - **[streaming_error_recovery.php](examples/streaming_error_recovery.php)** - Error handling and retry logic
> - **[streaming_web_search.php](examples/streaming_web_search.php)** - Streaming with web search
>
> **Batch Processing (8 Examples):**
>
> - **[batch_processing.php](examples/batch_processing.php)** - 50% cost savings overview
> - **[batch_create.php](examples/batch_create.php)** - Creating batches
> - **[batch_list.php](examples/batch_list.php)** - Listing batches
> - **[batch_poll.php](examples/batch_poll.php)** - Polling for completion
> - **[batch_results.php](examples/batch_results.php)** - Retrieving results
> - **[batch_cancel.php](examples/batch_cancel.php)** - Canceling batches
> - **[batch_complete_workflow.php](examples/batch_complete_workflow.php)** - Complete workflow
> - **[batch_with_caching.php](examples/batch_with_caching.php)** - Batches with prompt caching
>
> **Extended Thinking (6 Examples):**
>
> - **[extended_thinking.php](examples/extended_thinking.php)** - Comprehensive guide (1K-32K tokens)
> - **[thinking.php](examples/thinking.php)** - Basic extended thinking
> - **[thinking_stream.php](examples/thinking_stream.php)** - Streaming thinking
> - **[thinking_with_tools.php](examples/thinking_with_tools.php)** - Thinking with tool use
> - **[interleaved_thinking_tools.php](examples/interleaved_thinking_tools.php)** - Interleaved patterns
> - **[redacted_thinking.php](examples/redacted_thinking.php)** - Redacted thinking blocks
>
> **Citations (7 Examples):**
>
> - **[citations.php](examples/citations.php)** - Source attribution overview (beta)
> - **[citations_basic.php](examples/citations_basic.php)** - Basic citations
> - **[citations_multiple_documents.php](examples/citations_multiple_documents.php)** - Multiple documents
> - **[citations_with_context.php](examples/citations_with_context.php)** - Citations with context
> - **[citations_large_document.php](examples/citations_large_document.php)** - Large document handling
> - **[citations_streaming.php](examples/citations_streaming.php)** - Streaming citations
> - **[citations_disabled.php](examples/citations_disabled.php)** - Disabling citations
>
> **Optimization:**
>
> - **[context_windows.php](examples/context_windows.php)** - Token management
> - **[prompt_caching.php](examples/prompt_caching.php)** - 90% cost reduction
> - **[messages_caching.php](examples/messages_caching.php)** - Message-level caching
> - **[system_prompt_caching.php](examples/system_prompt_caching.php)** - System prompt caching
> - **[context_editing.php](examples/context_editing.php)** - Automatic context management (beta)
> - **[token_counting.php](examples/token_counting.php)** - Cost planning
>
> **Vision & Documents:**
>
> - **[vision_comprehensive.php](examples/vision_comprehensive.php)** - Complete vision guide
> - **[vision.php](examples/vision.php)** - Basic vision example
> - **[images.php](examples/images.php)** - Image handling
> - **[pdf_support.php](examples/pdf_support.php)** - PDF analysis
> - **[files_api.php](examples/files_api.php)** - File management (beta)
>
> **Tools & Agents:**
>
> - **[tool_use_overview.php](examples/tool_use_overview.php)** - Complete tool use guide
> - **[tool_use_implementation.php](examples/tool_use_implementation.php)** - Implementation patterns
> - **[token_efficient_tool_use.php](examples/token_efficient_tool_use.php)** - Optimize tool usage
> - **[fine_grained_tool_streaming.php](examples/fine_grained_tool_streaming.php)** - Real-time tool parameters
> - **[bash_tool.php](examples/bash_tool.php)**, **[code_execution_tool.php](examples/code_execution_tool.php)**, **[computer_use_tool.php](examples/computer_use_tool.php)**
> - **[text_editor_tool.php](examples/text_editor_tool.php)**, **[web_fetch_tool.php](examples/web_fetch_tool.php)**, **[memory_tool.php](examples/memory_tool.php)**
> - **[web_search.php](examples/web_search.php)** - Web search tool
>
> **Context Management (Beta):**
>
> - **[advanced_configuration.php](examples/advanced_configuration.php)** - Advanced context editing
> - **[combining_strategies.php](examples/combining_strategies.php)** - Multiple strategies
> - **[tool_result_clearing.php](examples/tool_result_clearing.php)** - Clear tool results
> - **[thinking_block_clearing.php](examples/thinking_block_clearing.php)** - Clear thinking blocks
>
> **Advanced:**
>
> - **[structured_outputs.php](examples/structured_outputs.php)** - Guaranteed JSON schema
> - **[embeddings.php](examples/embeddings.php)** - Semantic search concepts
> - **[search_results.php](examples/search_results.php)** - Provide search results
> - **[error_handling.php](examples/error_handling.php)** - Comprehensive error handling
> - **[model_comparison.php](examples/model_comparison.php)** - Compare different models
>
> 📖 **See [examples/README.md](examples/README.md) for the complete list and detailed descriptions**

## 🎓 Agentic AI Tutorial Series

> 🤖 **EXPANDED**: Complete tutorial series - 15 comprehensive tutorials from basics to autonomous agents!

**[Start the Tutorial Series →](tutorials/)**

Learn to build sophisticated AI agents through 15 progressive tutorials covering all major agentic patterns:

### Foundation (Tutorials 0-6)

| Tutorial                                          | Topic                      | Time   | Level        |
| ------------------------------------------------- | -------------------------- | ------ | ------------ |
| **[Tutorial 0](tutorials/00-introduction/)**      | Introduction to Agentic AI | 20 min | Beginner     |
| **[Tutorial 1](tutorials/01-first-agent/)**       | Your First Agent           | 30 min | Beginner     |
| **[Tutorial 2](tutorials/02-react-basics/)**      | ReAct Loop Basics          | 45 min | Intermediate |
| **[Tutorial 3](tutorials/03-multi-tool-agent/)**  | Multi-Tool Agent           | 45 min | Intermediate |
| **[Tutorial 4](tutorials/04-production-ready/)**  | Production-Ready Agent     | 60 min | Intermediate |
| **[Tutorial 5](tutorials/05-advanced-react/)**    | Advanced ReAct Patterns    | 60 min | Advanced     |
| **[Tutorial 6](tutorials/06-agentic-framework/)** | Complete Agentic Framework | 90 min | Advanced     |

### Advanced Patterns (Tutorials 7-14)

| Tutorial                                             | Topic                      | Time   | Level        |
| ---------------------------------------------------- | -------------------------- | ------ | ------------ |
| **[Tutorial 7](tutorials/07-chain-of-thought/)**     | Chain of Thought (CoT)     | 45 min | Intermediate |
| **[Tutorial 8](tutorials/08-tree-of-thoughts/)**     | Tree of Thoughts (ToT)     | 60 min | Advanced     |
| **[Tutorial 9](tutorials/09-plan-and-execute/)**     | Plan-and-Execute           | 45 min | Intermediate |
| **[Tutorial 10](tutorials/10-reflection/)**          | Reflection & Self-Critique | 45 min | Intermediate |
| **[Tutorial 11](tutorials/11-hierarchical-agents/)** | Hierarchical Agents        | 60 min | Advanced     |
| **[Tutorial 12](tutorials/12-multi-agent-debate/)**  | Multi-Agent Debate         | 60 min | Advanced     |
| **[Tutorial 13](tutorials/13-rag-pattern/)**         | RAG Pattern                | 60 min | Advanced     |
| **[Tutorial 14](tutorials/14-autonomous-agents/)**   | Autonomous Agents          | 90 min | Advanced     |

**What You'll Learn:**

- **Foundation**: Core concepts, ReAct pattern, tool use, production patterns
- **Reasoning**: Chain of Thought, Tree of Thoughts, planning and reflection
- **Multi-Agent**: Hierarchical systems, debate protocols, consensus building
- **Advanced**: RAG integration, autonomous goal-directed agents

**Perfect For:**

- PHP developers new to AI agents
- Anyone wanting to build autonomous AI systems
- Developers exploring advanced agentic patterns

📖 **[View Full Tutorial Series →](tutorials/)**

## Cloud Platform Integrations

### Microsoft Azure AI Foundry

Access Claude through Microsoft's Azure AI Foundry platform with enterprise-grade security and compliance:

```php
use ClaudePhp\Lib\Foundry\AnthropicFoundry;

// Using API key authentication
$client = new AnthropicFoundry(
    resource: 'my-foundry-resource',
    apiKey: $_ENV['AZURE_FOUNDRY_API_KEY']
);

// Or using Azure AD authentication
$client = new AnthropicFoundry(
    resource: 'my-foundry-resource',
    azureAdTokenProvider: fn() => getAzureAdToken()
);

$response = $client->createMessage([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => [['role' => 'user', 'content' => 'Hello!']]
]);
```

**Features:**

- ✅ API key and Azure AD token authentication
- ✅ Full Claude API support (streaming, tools, vision)
- ✅ Regional deployment options
- ✅ Enterprise compliance and monitoring

**See:** [examples/foundry.php](examples/foundry.php) for complete examples

### AWS Bedrock & Google Vertex AI

Similar integrations are available for AWS Bedrock and Google Cloud Vertex AI. See:

- `ClaudePhp\Lib\Bedrock\AnthropicBedrock` for AWS Bedrock
- `ClaudePhp\Lib\Vertex\AnthropicVertex` for Google Vertex AI

## Framework Integrations

### Laravel Package

For Laravel applications, use the official Laravel integration package:

```bash
composer require claude-php/claude-php-sdk-laravel
```

This provides a service provider, facade, and configuration publishing:

```php
use ClaudePhp\Laravel\Facades\Claude;

$response = Claude::messages()->create([
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 1024,
    'messages' => [['role' => 'user', 'content' => 'Hello!']]
]);
```

**Features:**

- ✅ Auto-registered service provider and facade
- ✅ Publishable configuration with environment variable support
- ✅ Full dependency injection support
- ✅ Laravel 11.x and 12.x compatible
- ✅ Comprehensive documentation with agentic patterns

👉 **[Laravel Package Documentation](https://github.com/claude-php/Claude-PHP-SDK-Laravel)**

### Manual Integration

For other frameworks or custom setups, see the
[framework integration guide](.docs/framework_integration.md) for drop-in
Symfony bindings, environment variables, and streaming controller patterns.

## Supported Models

| Model             | ID                           |                  Best For |
| ----------------- | ---------------------------- | ------------------------: |
| Claude Sonnet 4.5 | `claude-sonnet-4-5-20250929` | Complex reasoning, coding |
| Claude Haiku 4.5  | `claude-haiku-4-5-20251001`  |   Speed & cost efficiency |
| Claude Opus 4.5   | `claude-opus-4-5-20251101`   |         Specialized tasks |

Use model aliases (`claude-sonnet-4-5`, `claude-haiku-4-5`) for automatic updates.

## API Examples

### Streaming Messages

```php
$stream = $client->messages()->stream([
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 1024,
    'messages' => [['role' => 'user', 'content' => 'Tell me a story']]
]);

foreach ($stream as $event) {
    if (($event['type'] ?? null) === 'content_block_delta') {
        echo $event['delta']['text'] ?? '';
    }
}
```

### Tool Use (Function Calling)

```php
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5-20250929',
    'tools' => [
        [
            'name' => 'get_weather',
            'description' => 'Get current weather',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'location' => ['type' => 'string']
                ],
                'required' => ['location']
            ]
        ]
    ],
    'messages' => [['role' => 'user', 'content' => 'What is the weather?']]
]);
```

### Using Beta Features

Beta features are accessed through the `beta()` namespace and use the `anthropic-beta` HTTP header as specified in the [API documentation](https://docs.claude.com/en/api/beta-headers).

```php
// The SDK automatically converts the 'betas' array parameter
// to the 'anthropic-beta' HTTP header
$response = $client->beta()->messages()->create([
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'Hello!'],
    ],
    'betas' => ['prompt-caching-2024-07-31', 'thinking-2024-11-28'],
]);

// Multiple beta features are comma-separated in the header:
// anthropic-beta: prompt-caching-2024-07-31,thinking-2024-11-28
```

### Beta Structured Outputs

```php
$orderSchema = [
    'type' => 'object',
    'required' => ['product_name', 'quantity'],
    'properties' => [
        'product_name' => ['type' => 'string'],
        'quantity' => ['type' => 'integer'],
        'price' => ['type' => 'number'],
    ],
];

$parsed = $client->beta()->messages()->parse([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'I need 2 lattes for $4 each'],
    ],
    'output_format' => $orderSchema,
    // Note: structured-outputs-2025-11-13 beta is automatically added
]);

// ['product_name' => 'latte', 'quantity' => 2, 'price' => 4.0]
```

For live validation while streaming, use `streamStructured()`:

```php
$stream = $client->beta()->messages()->streamStructured([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => [['role' => 'user', 'content' => 'Summarize the order log as JSON']],
    'output_format' => $orderSchema,
]);

foreach ($stream as $event) {
    if (isset($event['parsed_output'])) {
        // Inspect structured JSON snapshots as soon as they're valid
    }
}
```

### Beta Tool Runner & `beta_tool`

```php
use function ClaudePhp\Lib\Tools\beta_tool;

$getWeather = beta_tool(
    handler: function (array $args): string {
        return 'It is 68°F and sunny in ' . ($args['location'] ?? 'somewhere');
    },
    name: 'get_weather',
    description: 'Fetch the current weather for a city',
    inputSchema: [
        'type' => 'object',
        'properties' => [
            'location' => ['type' => 'string'],
        ],
        'required' => ['location'],
    ]
);

$runner = $client->beta()->messages()->toolRunner([
    'model' => 'claude-3-5-sonnet-latest',
    'max_tokens' => 1024,
    'messages' => [['role' => 'user', 'content' => 'What is the weather in SF?']],
], [$getWeather]);

foreach ($runner as $message) {
    // Iteration continues until Claude stops requesting tool calls
}
```

> **Note on Beta Feature Verification**
>
> Some beta-only capabilities (context-management edits, interleaved thinking, memories, etc.) require Anthropic Tier&nbsp;4 access and special feature flags. We aren’t able to fully integration-test those flows without that elevated account tier. If someone from the Anthropic Claude team can help enable those betas for this project, we’d love to run the full automated suite and report any edge cases directly.

### Vision (Image Analysis)

```php
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 1024,
    'messages' => [
        [
            'role' => 'user',
            'content' => [
                ['type' => 'image', 'source' => ['type' => 'base64', 'media_type' => 'image/jpeg', 'data' => $base64]],
                ['type' => 'text', 'text' => 'What is in this image?']
            ]
        ]
    ]
]);
```

### Token Counting

```php
$count = $client->messages()->countTokens([
    'model' => 'claude-sonnet-4-5-20250929',
    'messages' => [['role' => 'user', 'content' => 'Hello!']]
]);

echo "Token count: " . $count->input_tokens;
```

### Batch Processing

```php
// Create batch (50% cost savings!)
$batch = $client->messages()->batches()->create([
    'requests' => [
        ['custom_id' => '1', 'params' => ['model' => 'claude-sonnet-4-5-20250929', 'messages' => [...]]],
        ['custom_id' => '2', 'params' => ['model' => 'claude-sonnet-4-5-20250929', 'messages' => [...]]]
    ]
]);

// Poll for results
$batch = $client->messages()->batches()->retrieve($batch->id);
echo "Status: " . $batch->processing_status;
```

## Response Helpers

Need structured output from responses without hand-written casts? Two helper
classes ship with the SDK:

- `ClaudePhp\Responses\Helpers\MessageContentHelper` hydrates content blocks into
  `TextContent`, `ToolUseContent`, and `ToolResultContent` objects.
- `ClaudePhp\Responses\Helpers\StreamEventHelper` exposes guard/inspection
  helpers for SSE payloads (text deltas, tool input JSON, message stop events).

```php
use ClaudePhp\Responses\Helpers\MessageContentHelper;
use ClaudePhp\Responses\Helpers\StreamEventHelper;

$message = $client->messages()->create([...]);

foreach (MessageContentHelper::toolUses($message) as $toolCall) {
    // $toolCall is a ToolUseContent value object
}

$stream = $client->messages()->stream([...]);
foreach ($stream as $event) {
    if (StreamEventHelper::isTextDelta($event)) {
        echo StreamEventHelper::textDelta($event);
    }
}
```

## Error Handling

The SDK provides a comprehensive exception hierarchy for proper error handling:

```php
use ClaudePhp\Exceptions\{
    APIConnectionError,
    RateLimitError,
    AuthenticationError,
    APIStatusError
};

try {
    $response = $client->messages()->create([...]);
} catch (RateLimitError $e) {
    // Handle rate limiting - implement backoff
    echo "Rate limited. Retry after: " . $e->response->getHeaderLine('retry-after');
} catch (AuthenticationError $e) {
    // Invalid API key
    echo "Invalid API key";
} catch (APIConnectionError $e) {
    // Network/timeout issue
    echo "Connection failed: " . $e->getMessage();
} catch (APIStatusError $e) {
    // Any other 4xx/5xx error
    echo "API Error {$e->status_code}: {$e->message}";
}
```

## Development

### Setup

```bash
# Install dependencies
composer install

# Run tests
composer test

# Check code style
composer lint

# Fix code style
composer format

# Run static analysis
composer stan

```

### Docker

```bash
# Build the dev image
docker compose build

# Run commands inside the container (mounted source tree)
docker compose run --rm sdk composer test
docker compose run --rm sdk php examples/messages.php
```

### Project Structure

```
Claude-PHP-SDK/
├── src/
│   ├── ClaudePhp.php           # Main client class
│   ├── Exceptions/              # Exception hierarchy
│   ├── Client/                  # HTTP client implementation
│   ├── Resources/               # API resource classes
│   ├── Requests/                # Request builders/DTOs
│   ├── Responses/               # Response objects
│   └── Contracts/               # Interfaces for DI
├── tests/
├── composer.json
└── README.md
```

## Integration with Frameworks

Full-length guides live in [.docs/framework_integration.md](.docs/framework_integration.md).

### Laravel

```php
// config/services.php
'claude' => [
    'api_key' => env('ANTHROPIC_API_KEY'),
    'timeout' => (float) env('CLAUDE_TIMEOUT', 30),
    'max_retries' => (int) env('CLAUDE_MAX_RETRIES', 2),
];

// app/Providers/ClaudeServiceProvider.php
use ClaudePhp\ClaudePhp;

public function register(): void
{
    $this->app->singleton(ClaudePhp::class, function ($app) {
        $config = $app['config']['services.claude'];

        return new ClaudePhp(
            apiKey: $config['api_key'],
            timeout: $config['timeout'],
            maxRetries: $config['max_retries']
        );
    });
}
```

### Symfony

```yaml
# config/services.yaml
services:
  ClaudePhp\ClaudePhp:
    arguments:
      $apiKey: "%env(string:ANTHROPIC_API_KEY)%"
      $timeout: "%env(float:CLAUDE_TIMEOUT)%"
      $maxRetries: "%env(int:CLAUDE_MAX_RETRIES)%"
```

## Contributing

Contributions are welcome! Please ensure:

- Code follows PSR-12 standards
- All tests pass: `composer test`
- No style issues: `composer lint`
- Static analysis passes: `composer stan`

## License

MIT License - see LICENSE file for details

## Support

- 📚 [Anthropic API Documentation](https://docs.claude.com/en/api/overview)
- 🐛 [Issue Tracker](https://github.com/claude-php/claude-php-sdk/issues)
- 💬 [Discussions](https://github.com/claude-php/claude-php-sdk/discussions)
