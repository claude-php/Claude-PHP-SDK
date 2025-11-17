# Claude PHP SDK

A universal, framework-agnostic PHP SDK for the Anthropic Claude API with full PSR compliance.

## Features

- ✅ **Full API Parity**: Comprehensive implementation of Messages, Files, Batches, and Models APIs
- ✅ **PSR Compliance**: Follows PSR-12 coding standards and PSR-11 dependency injection patterns
- ✅ **Framework Agnostic**: Works seamlessly with Laravel, Symfony, Slim, and other PHP frameworks
- ✅ **Latest Models**: Support for Claude Sonnet 4.5, Haiku 4.5, and Opus 4.1
- ✅ **Advanced Features**: Tool use, vision, streaming, extended thinking, embeddings, and batch processing
- ✅ **Async Ready**: Built for modern async patterns with Amphp support
- ✅ **Comprehensive Error Handling**: Detailed exception hierarchy matching the Python SDK

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

## Framework Integrations

Need a pre-wired client inside your container? See the
[framework integration guide](.docs/framework_integration.md) for drop-in
Laravel and Symfony bindings, environment variables, and streaming controller
patterns.

## Supported Models

| Model | ID | Best For |
|-------|----|---------:|
| Claude Sonnet 4.5 | `claude-sonnet-4-5-20250929` | Complex reasoning, coding |
| Claude Haiku 4.5 | `claude-haiku-4-5-20251001` | Speed & cost efficiency |
| Claude Opus 4.1 | `claude-opus-4-1-20250805` | Specialized tasks |

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
    'model' => 'claude-sonnet-4-5-20250929-structured-outputs',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'I need 2 lattes for $4 each'],
    ],
    'output_format' => $orderSchema,
]);

// ['product_name' => 'latte', 'quantity' => 2, 'price' => 4.0]
```

For live validation while streaming, use `streamStructured()`:

```php
$stream = $client->beta()->messages()->streamStructured([
    'model' => 'claude-sonnet-4-5-20250929-structured-outputs',
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
            $apiKey: '%env(string:ANTHROPIC_API_KEY)%'
            $timeout: '%env(float:CLAUDE_TIMEOUT)%'
            $maxRetries: '%env(int:CLAUDE_MAX_RETRIES)%'
```

## Contributing

Contributions are welcome! Please ensure:
- Code follows PSR-12 standards
- All tests pass: `composer test`
- No style issues: `composer lint`
- Static analysis passes: `composer stan` and `composer psalm`

## License

MIT License - see LICENSE file for details

## Support

- 📚 [Anthropic API Documentation](https://docs.claude.com/en/api/overview)
- 🐛 [Issue Tracker](https://github.com/claude-php/claude-php-sdk/issues)
- 💬 [Discussions](https://github.com/claude-php/claude-php-sdk/discussions)
