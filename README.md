# Claude PHP SDK

The unofficial PHP client for the [Anthropic Claude API](https://docs.anthropic.com/en/api/overview). Requires PHP 8.1+, Composer, and an [Anthropic API key](https://console.anthropic.com/).

```bash
composer require claude-php/claude-php-sdk
```

---

## Quick start

```php
<?php
require 'vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use ClaudePhp\Types\ModelParam;

$client = new ClaudePhp(apiKey: $_ENV['ANTHROPIC_API_KEY']);

$message = $client->messages()->create([
    'model'      => ModelParam::MODEL_CLAUDE_SONNET_4_5,
    'max_tokens' => 1024,
    'messages'   => [
        ['role' => 'user', 'content' => 'Explain quantum entanglement in one paragraph.'],
    ],
]);

echo $message->content[0]['text'];
```

The SDK reads `ANTHROPIC_API_KEY` from the environment automatically if you don't pass `apiKey` explicitly.

---

## Sending messages

### Streaming

```php
$stream = $client->messages()->stream([
    'model'      => ModelParam::MODEL_CLAUDE_SONNET_4_5,
    'max_tokens' => 1024,
    'messages'   => [['role' => 'user', 'content' => 'Write a haiku about PHP.']],
]);

foreach ($stream as $event) {
    if ($event['type'] === 'content_block_delta') {
        echo $event['delta']['text'] ?? '';
    }
}
```

For helper methods over raw events, wrap the stream in `MessageStream`:

```php
use ClaudePhp\Lib\Streaming\MessageStream;
use ClaudePhp\Responses\Helpers\StreamEventHelper;

$stream = new MessageStream($client->messages()->stream([...]));

foreach ($stream as $event) {
    if (StreamEventHelper::isTextDelta($event)) {
        echo StreamEventHelper::textDelta($event);
    }
}

$final = $stream->getFinalMessage();
```

See [examples/streaming_comprehensive.php](examples/streaming_comprehensive.php) for all event types.

### Multi-turn conversations

```php
$messages = [
    ['role' => 'user',      'content' => 'My name is Alice.'],
    ['role' => 'assistant', 'content' => 'Nice to meet you, Alice!'],
    ['role' => 'user',      'content' => 'What is my name?'],
];

$response = $client->messages()->create([
    'model'    => ModelParam::MODEL_CLAUDE_SONNET_4_5,
    'messages' => $messages,
]);
```

---

## Tool use

Define tools with a name, description, and JSON Schema input definition. Claude calls them; your code runs them.

```php
$response = $client->messages()->create([
    'model'      => ModelParam::MODEL_CLAUDE_SONNET_4_5,
    'max_tokens' => 1024,
    'tools'      => [
        [
            'name'         => 'get_weather',
            'description'  => 'Get current weather for a city.',
            'input_schema' => [
                'type'       => 'object',
                'properties' => ['city' => ['type' => 'string']],
                'required'   => ['city'],
            ],
        ],
    ],
    'messages'   => [['role' => 'user', 'content' => 'What is the weather in Tokyo?']],
]);

// When stop_reason === 'tool_use', execute the tool and send the result back
```

For automatic tool loop execution, use the built-in tool runner:

```php
use function ClaudePhp\Lib\Tools\beta_tool;

$weather = beta_tool(
    handler:     fn($args) => "Sunny and 24°C in {$args['city']}",
    name:        'get_weather',
    description: 'Get current weather for a city.',
    inputSchema: [
        'type' => 'object', 'properties' => ['city' => ['type' => 'string']], 'required' => ['city'],
    ],
);

$result = $client->beta()->messages()->toolRunner([
    'model'    => ModelParam::MODEL_CLAUDE_SONNET_4_5,
    'messages' => [['role' => 'user', 'content' => 'Weather in Tokyo?']],
], [$weather]);
```

See [examples/tool_use_overview.php](examples/tool_use_overview.php) for the full workflow.

---

## Extended thinking

Claude can reason step-by-step before answering. Three modes are available:

| Mode       | Behaviour                           | When to use                         |
| ---------- | ----------------------------------- | ----------------------------------- |
| `disabled` | No internal reasoning               | Fast responses, simple prompts      |
| `enabled`  | Always reasons; set `budget_tokens` | Deep analysis, guaranteed reasoning |
| `adaptive` | Model decides based on the prompt   | Mixed workloads, cost-conscious     |

```php
// Adaptive — recommended for claude-opus-4-6
$response = $client->messages()->create([
    'model'      => ModelParam::MODEL_CLAUDE_OPUS_4_6,
    'max_tokens' => 8192,
    'thinking'   => ['type' => 'adaptive'],
    'messages'   => [['role' => 'user', 'content' => 'Design a fault-tolerant distributed cache.']],
]);

foreach ($response->content as $block) {
    if ($block['type'] === 'thinking') {
        echo "Reasoning: " . $block['thinking'] . "\n";
    } elseif ($block['type'] === 'text') {
        echo $block['text'];
    }
}
```

See [examples/extended_thinking.php](examples/extended_thinking.php) and [examples/adaptive_thinking.php](examples/adaptive_thinking.php).

---

## Vision and documents

Pass images as base64 or by URL. Pass PDFs or files via the Files API.

```php
$response = $client->messages()->create([
    'model'    => ModelParam::MODEL_CLAUDE_SONNET_4_5,
    'messages' => [
        [
            'role'    => 'user',
            'content' => [
                [
                    'type'   => 'image',
                    'source' => ['type' => 'base64', 'media_type' => 'image/jpeg', 'data' => $base64Data],
                ],
                ['type' => 'text', 'text' => 'What is in this image?'],
            ],
        ],
    ],
]);
```

See [examples/vision_comprehensive.php](examples/vision_comprehensive.php) · [examples/pdf_support.php](examples/pdf_support.php) · [examples/files_api.php](examples/files_api.php).

---

## Batch processing

Process large numbers of requests at 50% of the standard cost, asynchronously.

```php
$batch = $client->messages()->batches()->create([
    'requests' => [
        ['custom_id' => 'req-1', 'params' => ['model' => ModelParam::MODEL_CLAUDE_SONNET_4_5, 'max_tokens' => 256, 'messages' => [...]]],
        ['custom_id' => 'req-2', 'params' => ['model' => ModelParam::MODEL_CLAUDE_SONNET_4_5, 'max_tokens' => 256, 'messages' => [...]]],
    ],
]);

// Poll until done
while ($batch->processing_status !== 'ended') {
    sleep(5);
    $batch = $client->messages()->batches()->retrieve($batch->id);
}

// Iterate JSONL results
foreach ($client->messages()->batches()->results($batch->id) as $result) {
    echo $result['custom_id'] . ': ' . $result['result']['message']['content'][0]['text'] . "\n";
}
```

See [examples/batch_processing.php](examples/batch_processing.php) for the full workflow.

---

## Structured outputs

Get guaranteed JSON that matches a schema, without prompt engineering:

```php
$schema = [
    'type'       => 'object',
    'required'   => ['name', 'price', 'quantity'],
    'properties' => [
        'name'     => ['type' => 'string'],
        'price'    => ['type' => 'number'],
        'quantity' => ['type' => 'integer'],
    ],
];

$parsed = $client->beta()->messages()->parse([
    'model'         => ModelParam::MODEL_CLAUDE_SONNET_4_5,
    'max_tokens'    => 256,
    'messages'      => [['role' => 'user', 'content' => 'I bought 3 coffees at $4.50 each.']],
    'output_format' => $schema,
]);
// ['name' => 'coffee', 'price' => 4.5, 'quantity' => 3]
```

See [examples/structured_outputs.php](examples/structured_outputs.php).

---

## Server-side tools

These tools are executed by Anthropic's infrastructure — no handler code needed on your side.

### Code execution

Claude writes and runs sandboxed code, returning stdout, stderr, and generated files.

```php
$response = $client->messages()->create([
    'model'    => ModelParam::MODEL_CLAUDE_SONNET_4_5,
    'max_tokens' => 2048,
    'tools'    => [['name' => 'code_execution', 'type' => 'code_execution_20250825']],
    'messages' => [['role' => 'user', 'content' => 'Compute the first 10 Fibonacci numbers.']],
]);
```

Use `code_execution_20260120` (beta) for REPL-state persistence across multiple tool calls.

### Memory tool

Persistent file-based storage that survives across conversations.

```php
$response = $client->messages()->create([
    'model'    => ModelParam::MODEL_CLAUDE_SONNET_4_5,
    'tools'    => [['name' => 'memory', 'type' => 'memory_20250818']],
    'messages' => [['role' => 'user', 'content' => 'Remember: I prefer PHP 8.3 and UTC timezone.']],
]);
```

Supports `view`, `create`, `str_replace`, `insert`, `delete`, and `rename` commands.

### Web fetch

Retrieve live URL content with domain restrictions and token caps.

```php
$response = $client->messages()->create([
    'model'   => ModelParam::MODEL_CLAUDE_SONNET_4_5,
    'tools'   => [
        [
            'name'            => 'web_fetch',
            'type'            => 'web_fetch_20250910',
            'allowed_domains' => ['docs.anthropic.com'],
            'max_uses'        => 3,
        ],
    ],
    'messages' => [['role' => 'user', 'content' => 'Summarize https://docs.anthropic.com/']],
]);
```

See [examples/code_execution.php](examples/code_execution.php) · [examples/memory_tool.php](examples/memory_tool.php) · [examples/web_fetch.php](examples/web_fetch.php).

---

## Models

```php
use ClaudePhp\Types\ModelParam;
```

| Constant                         | Model ID                     | Best for                              |
| -------------------------------- | ---------------------------- | ------------------------------------- |
| `MODEL_CLAUDE_OPUS_4_6`          | `claude-opus-4-6`            | Frontier reasoning, adaptive thinking |
| `MODEL_CLAUDE_SONNET_4_6`        | `claude-sonnet-4-6`          | Balanced capability and speed         |
| `MODEL_CLAUDE_SONNET_4_5`        | `claude-sonnet-4-5-20250929` | Coding, complex reasoning             |
| `MODEL_CLAUDE_HAIKU_4_5`         | `claude-haiku-4-5-20251001`  | Speed and cost efficiency             |
| `MODEL_CLAUDE_3_7_SONNET_LATEST` | `claude-3-7-sonnet-latest`   | Hybrid reasoning                      |
| `MODEL_CLAUDE_3_5_HAIKU_LATEST`  | `claude-3-5-haiku-latest`    | Ultra-fast responses                  |

Use `*_LATEST` aliases for automatic updates, or dated IDs (e.g. `MODEL_CLAUDE_SONNET_4_5`) for pinned deployments. See `src/Types/ModelParam.php` for the full list.

---

## Client configuration

```php
$client = new ClaudePhp(
    apiKey:        $_ENV['ANTHROPIC_API_KEY'], // defaults to ANTHROPIC_API_KEY env var
    baseUrl:       'https://api.anthropic.com/v1',
    timeout:       30.0,    // seconds
    maxRetries:    2,        // auto-retries on 429 / 5xx
    customHeaders: ['X-My-Header' => 'value'],
);
```

### Alternative authentication

```php
// OAuth2 / Bearer token (e.g. enterprise SSO, proxies)
$client = new ClaudePhp(apiKey: null, customHeaders: [
    'Authorization' => 'Bearer your-token',
]);

// Custom x-api-key (e.g. API gateway with key management)
$client = new ClaudePhp(apiKey: null, customHeaders: [
    'x-api-key' => 'your-gateway-key',
]);
```

See [examples/authentication_flexibility.php](examples/authentication_flexibility.php).

---

## Error handling

```php
use ClaudePhp\Exceptions\{AuthenticationError, RateLimitError, APIConnectionError, APIStatusError};

try {
    $response = $client->messages()->create([...]);
} catch (AuthenticationError $e) {
    // Invalid or missing API key
} catch (RateLimitError $e) {
    $retryAfter = $e->response->getHeaderLine('retry-after');
    // Back off and retry
} catch (APIConnectionError $e) {
    // Network or timeout failure
} catch (APIStatusError $e) {
    echo "HTTP {$e->status_code}: {$e->message}";
}
```

Retries on 429 and 5xx responses happen automatically (configurable via `maxRetries`). See [examples/error_handling.php](examples/error_handling.php).

---

## Framework integration

### Laravel

Install the first-party package for service provider, facade, and config publishing:

```bash
composer require claude-php/claude-php-sdk-laravel
```

```php
use ClaudePhp\Laravel\Facades\Claude;

$response = Claude::messages()->create([...]);
```

[Laravel Package →](https://github.com/claude-php/Claude-PHP-SDK-Laravel)

For manual registration without the package:

```php
// app/Providers/ClaudeServiceProvider.php
$this->app->singleton(ClaudePhp::class, fn() => new ClaudePhp(
    apiKey:     config('services.claude.api_key'),
    maxRetries: config('services.claude.max_retries', 2),
));
```

### Symfony

```yaml
# config/services.yaml
services:
  ClaudePhp\ClaudePhp:
    arguments:
      $apiKey: "%env(ANTHROPIC_API_KEY)%"
      $maxRetries: 2
```

---

## Cloud platforms

| Platform                   | Class                                    | Example                                      |
| -------------------------- | ---------------------------------------- | -------------------------------------------- |
| Microsoft Azure AI Foundry | `ClaudePhp\Lib\Foundry\AnthropicFoundry` | [examples/foundry.php](examples/foundry.php) |
| AWS Bedrock                | `ClaudePhp\Lib\Bedrock\AnthropicBedrock` | —                                            |
| Google Vertex AI           | `ClaudePhp\Lib\Vertex\AnthropicVertex`   | —                                            |

---

## Examples and tutorials

**85+ runnable examples** organised by topic — streaming, vision, tool use, batching, prompt caching, context management, and more:

```
examples/    — see examples/README.md for the full index
tutorials/   — 17 progressive tutorials from "Hello Claude" to autonomous agents
```

Highlights:

- [examples/quickstart.php](examples/quickstart.php) — 10-line hello world
- [examples/streaming_comprehensive.php](examples/streaming_comprehensive.php) — all streaming patterns
- [examples/tool_use_overview.php](examples/tool_use_overview.php) — full tool use workflow
- [examples/extended_thinking.php](examples/extended_thinking.php) — thinking budgets and modes
- [examples/prompt_caching.php](examples/prompt_caching.php) — 90% cost reduction on repeated context
- [examples/batch_processing.php](examples/batch_processing.php) — 50% cost savings at scale
- [tutorials/README.md](tutorials/README.md) — start the agentic AI tutorial series

---

## Development

```bash
composer install    # install dependencies
composer test       # PHPUnit (351 tests, 1002 assertions)
composer lint       # PSR-12 style check
composer format     # auto-fix style
composer stan       # PHPStan static analysis
```

```
src/
  ClaudePhp.php        main client
  Resources/           Messages, Batches, Models, Files, Beta
  Types/               ModelParam constants, tool param types
  Responses/           response objects and helpers
  Exceptions/          exception hierarchy
  Lib/                 streaming, tool runner, structured outputs
tests/
  Integration/         mocked HTTP end-to-end tests
  Unit/                unit tests per component
examples/              85+ runnable scripts
tutorials/             17 agentic AI tutorials
```

---

## Contributing

1. Fork and clone the repo
2. Run `composer install`
3. Make your changes, add tests
4. Ensure `composer test`, `composer lint`, and `composer stan` all pass
5. Open a pull request

---

## A note to Anthropic

This SDK was built independently by [Dale Hurley](https://github.com/dalehurley) to give the PHP community a first-class Claude integration — the same one the Python and TypeScript communities already enjoy. It tracks the official API closely, ships 85+ examples covering every docs page, and has a full tutorial series for building agentic systems.

If you work at Anthropic and would like to sponsor, officially adopt, or collaborate on this project, I'd love to hear from you. Reach out via [GitHub Discussions](https://github.com/claude-php/claude-php-sdk/discussions) or the email in my profile.

---

## License

MIT — see [LICENSE](LICENSE)

## Support

- [Anthropic API docs](https://docs.anthropic.com/en/api/overview)
- [Issue tracker](https://github.com/claude-php/claude-php-sdk/issues)
- [Discussions](https://github.com/claude-php/claude-php-sdk/discussions)
