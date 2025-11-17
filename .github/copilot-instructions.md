# Copilot Instructions for Claude PHP SDK

This is a **PHP clone of the Anthropic Python SDK** (https://github.com/anthropics/anthropic-sdk-python), designed as a universal, framework-agnostic PHP SDK following **PSR standards** for easy integration with Laravel, Symfony, Slim, and other frameworks.

## Project Goals

- **API Parity**: Full implementation of latest Claude API endpoints (Messages, Files, Batches, Models APIs)
- **PSR Compliance**: Follow PHP Standards Recommendations (PSR-12 coding style, PSR-11 containers for DI)
- **Dependency Injection Ready**: Support framework-agnostic DI patterns for seamless integration
- **Latest Models & Features**: Support Claude Sonnet 4.5, Haiku 4.5, Opus 4.1, plus extended thinking, streaming, tool use, vision, embeddings
- **Async & Sync Support**: Both synchronous and asynchronous clients via Amphp/PHP async patterns
- **Python SDK Parity**: Mirror error handling, configuration, and patterns from the official Python SDK

## Architecture & Core Components

### Directory Structure (to be created)

```
Claude-PHP-SDK/
├── src/
│   ├── Anthropic.php           # Main client class (factory/entry point)
│   ├── Client/                 # Core HTTP client implementation
│   ├── Messages/               # Messages API (main resource)
│   ├── Files/                  # Files API resource
│   ├── Batches/                # Batch processing API
│   ├── Models/                 # Models API
│   ├── Embeddings/             # Embeddings API
│   ├── Resources/              # Base resource classes
│   ├── Requests/               # Request builders/DTOs
│   ├── Responses/              # Response objects & streaming
│   └── Contracts/              # Interfaces for DI
├── tests/
├── composer.json
└── README.md
```

### Key Architectural Patterns

1. **Client Pattern**: `Anthropic` class acts as main entry point, returns resource instances

   ```php
   $client = new Anthropic(apiKey: $key);
   $response = $client->messages->create([...]);
   ```

2. **Resource-Based API**: Each API endpoint group (Messages, Files, etc.) is a resource class inheriting from `Resource`

3. **Stateless Requests**: Follow Messages API pattern—always send full conversation history, no server-side state

4. **Dependency Injection Ready**:
   - Accept `HttpClientInterface` in constructor (PSR-18)
   - Use constructor injection for configuration
   - Support service container binding for frameworks

### Latest Claude Models (as of Nov 2025)

| Model                 | ID                           | Best For               | Input/Output Tokens        |
| --------------------- | ---------------------------- | ---------------------- | -------------------------- |
| **Claude Sonnet 4.5** | `claude-sonnet-4-5-20250929` | Complex agents, coding | $3/$15 per MTok, 200K ctx  |
| **Claude Haiku 4.5**  | `claude-haiku-4-5-20251001`  | Fast, cost-effective   | $1/$5 per MTok, 200K ctx   |
| **Claude Opus 4.1**   | `claude-opus-4-1-20250805`   | Specialized reasoning  | $15/$75 per MTok, 200K ctx |

**Aliases** (auto-update, prefer versioned IDs in production):

- `claude-sonnet-4-5` → latest Sonnet 4.5
- `claude-haiku-4-5` → latest Haiku 4.5

## API Endpoints to Implement

### Messages API (Core)

- `POST /v1/messages` - Create message (supports streaming)
- `POST /v1/messages/count_tokens` - Token counting

**Key Features**:

- Tool use (function calling)
- Vision (image inputs: base64 or URL)
- Streaming (server-sent events)
- Extended thinking (with `thinking` blocks)
- Batch processing via batch API

### Files API

- `POST /v1/files` - Upload file
- `GET /v1/files` - List files
- `GET /v1/files/{id}` - Get metadata
- `GET /v1/files/{id}/content` - Download
- `DELETE /v1/files/{id}` - Delete

### Batch Processing API

- `POST /v1/messages/batches` - Create batch (50% cost savings)
- `GET /v1/messages/batches` - List batches
- `GET /v1/messages/batches/{id}` - Get batch status
- `GET /v1/messages/batches/{id}/results` - Retrieve results
- `POST /v1/messages/batches/{id}/cancel` - Cancel batch
- `DELETE /v1/messages/batches/{id}` - Delete batch

### Models API

- `GET /v1/models` - List available models
- `GET /v1/models/{id}` - Get model details

### Embeddings API

- `POST /v1/embeddings` - Generate embeddings

## Critical Developer Workflows

### Client Initialization & Configuration

The SDK supports both **synchronous** (`Anthropic`) and **asynchronous** (`AsyncAnthropic`) clients:

```php
// Synchronous client
$client = new Anthropic(
    apiKey: $_ENV['ANTHROPIC_API_KEY'],  // Falls back to env var if not provided
    baseUrl: 'https://api.anthropic.com/v1',  // Override default
    timeout: 30.0,  // Seconds (default: 30)
    maxRetries: 2,  // Default retries for 429/5xx errors
);

// Asynchronous client (Amphp)
$asyncClient = new AsyncAnthropic(
    apiKey: $_ENV['ANTHROPIC_API_KEY'],
    timeout: 30.0,
);

// Async usage pattern
$promise = $asyncClient->messages->create([...]);
$message = \Amp\await($promise);
```

**Configuration Options**:

- `apiKey` (string|null): API key. If null, loads from `ANTHROPIC_API_KEY` env var
- `baseUrl` (string): API base URL, default `https://api.anthropic.com/v1`
- `timeout` (float): Request timeout in seconds, default 30s
- `maxRetries` (int): Auto-retry count for retryable errors (429, 5xx), default 2
- `customHeaders` (array): Additional headers for all requests
- `httpClient` (HttpClientInterface|null): PSR-18 HTTP client, uses sensible default if null

### Building the SDK

```bash
composer install              # Install dependencies
composer test                 # Run tests (PHPUnit)
composer lint                 # Check PSR-12 style (PHP_CodeSniffer)
composer format               # Auto-fix code style
```

### Testing Pattern

- Use **PHPUnit** for unit tests
- Mock `HttpClientInterface` for API calls
- Test streaming responses with mock SSE events
- Test both sync (blocking) and async (Promise) flows
- Test both stateless request/response and streaming flows

### Local Development

1. Set `ANTHROPIC_API_KEY` environment variable
2. Create test messages to verify client works
3. Use `count_tokens` endpoint before calling expensive models
4. Enable logging via environment variables for debugging

## Project-Specific Conventions

### Naming Conventions (PSR-12 + Anthropic SDK)

- **Classes**: StudlyCaps, match file names exactly (`Anthropic.php`, `Messages.php`)
- **Methods**: camelCase (`create()`, `countTokens()`, not `count_tokens`)
- **Parameters**: snake_case in arrays (follows Anthropic API): `max_tokens`, `stop_sequences`
- **Constants**: `UPPER_CASE` (e.g., `DEFAULT_TIMEOUT`)

### Request/Response Pattern

- **Requests**: Use arrays or data objects (DTOs) with type hints
- **Responses**: Return typed objects with properties, not raw arrays
  ```php
  // Returns: Message object with $id, $content, $model, $usage properties
  $response = $client->messages->create($params);
  echo $response->content[0]->text;
  ```

### Streaming Implementation

- Implement via generators or `Iterator` interface
- Yield `ContentBlockDelta`, `MessageDelta` objects as events arrive
- Support server-sent events (SSE) parsing
- Example streaming consumption:
  ```php
  foreach ($client->messages->stream($params) as $event) {
      if ($event instanceof ContentBlockDelta) {
          echo $event->delta->text;
      }
  }
  ```

### Tool Use (Function Calling)

- Define tools as arrays with `name`, `description`, `input_schema`
- Return tool use responses with `id`, `name`, `input` properties
- Support `tool_choice` constraint (`auto`, `any`, `tool`, `none`)
- Example flow:
  ```php
  $tools = [
      [
          'name' => 'get_weather',
          'description' => 'Get current weather for location',
          'input_schema' => [...]
      ]
  ];
  $response = $client->messages->create([
      'model' => 'claude-sonnet-4-5-20250929',
      'tools' => $tools,
      'messages' => [...]
  ]);
  ```

### Extended Thinking

- Pass `thinking` parameter with `type: 'enabled'` and `budget_tokens`
- Response includes `thinking` content blocks
- `max_tokens` must exceed `budget_tokens`
- Supported in Sonnet 4.5, Opus 4.1, Opus 4 models

### Vision (Image Support)

- Accept images as base64 or URL in message content
- Supported formats: `image/jpeg`, `image/png`, `image/gif`, `image/webp`
- Example:
  ```php
  $response = $client->messages->create([
      'messages' => [
          [
              'role' => 'user',
              'content' => [
                  ['type' => 'image', 'source' => ['type' => 'base64', 'media_type' => 'image/jpeg', 'data' => $base64]],
                  ['type' => 'text', 'text' => 'What is in this image?']
              ]
          ]
      ],
      ...
  ]);
  ```

## Error Handling Hierarchy (Mirrored from Python SDK)

All errors inherit from `AnthropicError`. The exception hierarchy follows:

```
AnthropicError (base)
├── APIError (base for all API-related errors)
│   ├── APIConnectionError (network/timeout issues)
│   │   └── APITimeoutError (request timeout)
│   ├── APIResponseValidationError (response validation failed)
│   └── APIStatusError (4xx/5xx HTTP status)
│       ├── BadRequestError (400)
│       ├── AuthenticationError (401)
│       ├── PermissionDeniedError (403)
│       ├── NotFoundError (404)
│       ├── ConflictError (409)
│       ├── RequestTooLargeError (413)
│       ├── UnprocessableEntityError (422)
│       ├── RateLimitError (429)
│       ├── OverloadedError (529)
│       ├── ServiceUnavailableError (503)
│       ├── DeadlineExceededError (504)
│       └── InternalServerError (>=500, others)
```

**Usage Pattern** (match Python SDK):

```php
use Anthropic\Exceptions\{
    APIConnectionError,
    RateLimitError,
    APIStatusError,
    AuthenticationError,
};

try {
    $response = $client->messages->create([...]);
} catch (APIConnectionError $e) {
    // Network error - implement retry logic or fail fast
    echo "The server could not be reached";
} catch (RateLimitError $e) {
    // 429 - implement backoff/retry
    echo "Rate limited - back off and retry";
} catch (AuthenticationError $e) {
    // 401 - invalid API key
    echo "Invalid API key";
} catch (APIStatusError $e) {
    // Any other 4xx/5xx
    echo "API Error: " . $e->status_code . " - " . $e->message;
    // $e->response has full HTTP response
    // $e->body has parsed error body
}
```

**Key Error Properties** (match Python SDK):

- `message` (string): Error message
- `request` (PSR-7 RequestInterface): HTTP request that caused error
- `body` (object|string|null): Parsed API response body
- `response` (PSR-7 ResponseInterface): Full HTTP response (for APIStatusError)
- `status_code` (int): HTTP status code (for APIStatusError only)
- `request_id` (string|null): Request ID from response headers (for debugging)

**Auto-Retry Behavior**:

- Connection errors (network timeout, DNS failure)
- 408 Request Timeout
- 409 Conflict
- 429 Rate Limit
- > = 500 Internal Server errors

Retry with exponential backoff (default: 2 max retries). Override with `maxRetries: 0` to disable.

## Integration Points & External Dependencies

### Framework Integration (Dependency Injection)

- Support **PSR-11** `ContainerInterface` for framework integration
- Allow binding in Laravel service provider:
  ```php
  $this->app->bind('anthropic', fn() => new Anthropic($apiKey));
  ```
- Support Symfony container factory pattern

### External Dependencies (Minimal)

- **PSR-18 HTTP Client**: Use `php-http/client-implementation` (framework provides)
- **PSR-7 HTTP Messages**: `psr/http-message`
- **Optional**: JSON encoding library for streaming (built-in PHP if possible)
- **Test dependencies**: PHPUnit, Mockery

### API Communication Pattern

- Use HTTP `Content-Type: application/json`
- API base URL: `https://api.anthropic.com/v1`
- Include header: `anthropic-version: 2023-06-01` (check latest docs)
- Handle rate limiting: Respect `retry-after` headers
- Timeout default: 30s (configurable)

## Testing & Validation

### When Adding Features

1. Write test that mocks HTTP responses
2. Verify request payload matches Anthropic API spec
3. Test both success and error paths
4. Add streaming test if applicable
5. Run linter: `composer lint`

### Common Patterns to Test

- Stateless message history (send full history each turn)
- Streaming event parsing (fake SSE stream with proper format)
- Token counting before expensive calls
- Batch API polling for results
- Tool use request/response cycle
- Extended thinking block extraction

## References

- **Anthropic API Docs**: https://docs.claude.com/en/api/overview
- **Python SDK**: https://github.com/anthropics/anthropic-sdk-python
- **PSR Standards**: https://www.php-fig.org/psr/
- **PSR-18 (HTTP Client)**: https://www.php-fig.org/psr/psr-18/
- **PSR-11 (Container)**: https://www.php-fig.org/psr/psr-11/
