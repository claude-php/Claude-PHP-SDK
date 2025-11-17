# Framework Integration Guides

The SDK is framework agnostic, but wiring it into a framework's container makes
testing and dependency management much easier. Below are reference
implementations for the two most common PHP frameworks.

## Laravel

### 1. Configure environment values

```dotenv
ANTHROPIC_API_KEY=sk-ant-...
CLAUDE_TIMEOUT=30
CLAUDE_MAX_RETRIES=2
```

You can optionally expose these through `config/services.php`:

```php
// config/services.php
return [
    // ...
    'claude' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'timeout' => (float) env('CLAUDE_TIMEOUT', 30),
        'max_retries' => (int) env('CLAUDE_MAX_RETRIES', 2),
    ],
];
```

### 2. Bind the SDK in a service provider

```php
<?php

namespace App\Providers;

use ClaudePhp\ClaudePhp;
use Illuminate\Support\ServiceProvider;

class ClaudeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ClaudePhp::class, function ($app): ClaudePhp {
            $config = $app['config']->get('services.claude');

            return new ClaudePhp(
                apiKey: $config['api_key'],
                timeout: $config['timeout'],
                maxRetries: $config['max_retries']
            );
        });
    }
}
```

Register it in `config/app.php` and the client will now resolve anywhere via
type-hinting.

### 3. Use the client inside controllers/jobs

```php
use ClaudePhp\ClaudePhp;

class ComposeReplyController
{
    public function __invoke(Request $request, ClaudePhp $claude): JsonResponse
    {
        $reply = $claude->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 512,
            'messages' => [
                ['role' => 'user', 'content' => $request->input('prompt')],
            ],
        ]);

        return response()->json([
            'text' => implode('', array_column($reply->content, 'text')),
        ]);
    }
}
```

For streaming responses, inject the same `$claude` instance in queued jobs and
wrap the iterator in `ClaudePhp\Lib\Streaming\MessageStream`.

## Symfony

### 1. Declare environment variables

```dotenv
###> Claude PHP SDK ###
ANTHROPIC_API_KEY=sk-ant-...
CLAUDE_TIMEOUT=30
CLAUDE_MAX_RETRIES=2
###< Claude PHP SDK ###
```

### 2. Register the service

```yaml
# config/services.yaml
services:
    ClaudePhp\ClaudePhp:
        arguments:
            $apiKey: '%env(string:ANTHROPIC_API_KEY)%'
            $timeout: '%env(float:CLAUDE_TIMEOUT)%'
            $maxRetries: '%env(int:CLAUDE_MAX_RETRIES)%'
        public: true
```

You can also alias it to a custom interface or make it private and inject it
via constructor autowiring in your services.

### 3. Consume it from controllers or Messenger handlers

```php
use ClaudePhp\ClaudePhp;

final class KnowledgeBaseController extends AbstractController
{
    public function __construct(private readonly ClaudePhp $claude) {}

    #[Route('/summaries', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $response = $this->claude->messages()->create([
            'model' => 'claude-opus-4-1-20250805',
            'max_tokens' => 800,
            'messages' => [
                ['role' => 'user', 'content' => $request->request->getString('prompt')],
            ],
        ]);

        return $this->json([
            'text' => implode('', array_column($response->content, 'text')),
            'usage' => $response->usage,
        ]);
    }
}
```

For streaming controllers, wrap the iterator returned by
`$claude->messages()->stream()` inside a Symfony `StreamedResponse` and forward
events as Server-Sent Events (SSE) to the browser, yielding deltas as they
arrive.
