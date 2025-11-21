# Claude in Microsoft Azure AI Foundry

This guide shows how to use the Claude PHP SDK with Microsoft's Azure AI Foundry platform.

## Overview

Azure AI Foundry provides enterprise-grade infrastructure for accessing Claude models with:

- **Enterprise Security**: Azure AD authentication and compliance
- **Regional Deployment**: Deploy Claude in your preferred Azure region
- **Enterprise Billing**: Consolidated Azure billing and cost management
- **Monitoring**: Azure monitoring and logging integration
- **Compliance**: Meet enterprise security and compliance requirements

## Installation

The Foundry integration is included in the Claude PHP SDK:

```bash
composer require claude-php/claude-php-sdk
```

## Authentication

Azure AI Foundry supports two authentication methods:

### 1. API Key Authentication

The simplest method for getting started:

```php
use ClaudePhp\Lib\Foundry\AnthropicFoundry;

$client = new AnthropicFoundry(
    resource: 'my-foundry-resource',
    apiKey: $_ENV['AZURE_FOUNDRY_API_KEY']
);

$message = $client->createMessage([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'Hello!']
    ]
]);

echo $message->content[0]->text;
```

### 2. Azure AD Token Authentication

For enterprise scenarios requiring Azure AD:

```php
use ClaudePhp\Lib\Foundry\AnthropicFoundry;

// Token provider function
function getAzureAdToken(): string {
    // Use Azure Identity SDK (install via: composer require microsoft/azure-identity)
    // $credential = new \Azure\Identity\DefaultAzureCredential();
    // $token = $credential->getToken(['https://ai.azure.com/.default']);
    // return $token->getToken();
    
    // Or implement your own token acquisition logic
    return $_ENV['AZURE_AD_TOKEN'];
}

$client = new AnthropicFoundry(
    resource: 'my-foundry-resource',
    azureAdTokenProvider: fn() => getAzureAdToken()
);

$message = $client->createMessage([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'Hello!']
    ]
]);
```

## Setup Steps

### 1. Create an Azure AI Foundry Resource

1. Go to the [Azure Portal](https://portal.azure.com)
2. Create a new Azure AI Foundry resource
3. Note your resource name (e.g., "my-foundry-resource")

### 2. Get Your API Key

1. Navigate to your Foundry resource in the Azure portal
2. Go to "Keys and Endpoint"
3. Copy one of the API keys

### 3. Configure Your Application

```bash
# .env file
AZURE_FOUNDRY_RESOURCE=my-foundry-resource
AZURE_FOUNDRY_API_KEY=your-api-key-here
```

## Usage Examples

### Basic Message Creation

```php
$client = new AnthropicFoundry(
    resource: $_ENV['AZURE_FOUNDRY_RESOURCE'],
    apiKey: $_ENV['AZURE_FOUNDRY_API_KEY']
);

$response = $client->createMessage([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'Explain quantum computing']
    ]
]);

echo $response->content[0]->text;
```

### Streaming Messages

```php
foreach ($client->createMessageStream([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'Write a story']
    ]
]) as $event) {
    if (($event['type'] ?? null) === 'content_block_delta') {
        echo $event['delta']['text'] ?? '';
    }
}
```

### Tool Use

```php
$response = $client->createMessage([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'tools' => [
        [
            'name' => 'get_weather',
            'description' => 'Get the current weather',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'location' => ['type' => 'string']
                ],
                'required' => ['location']
            ]
        ]
    ],
    'messages' => [
        ['role' => 'user', 'content' => 'What is the weather in Seattle?']
    ]
]);

// Check for tool use in the response
foreach ($response->content as $block) {
    if ($block->type === 'tool_use') {
        echo "Tool: {$block->name}\n";
        echo "Arguments: " . json_encode($block->input) . "\n";
    }
}
```

### Vision Support

```php
$imageData = base64_encode(file_get_contents('image.jpg'));

$response = $client->createMessage([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => [
        [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'image',
                    'source' => [
                        'type' => 'base64',
                        'media_type' => 'image/jpeg',
                        'data' => $imageData
                    ]
                ],
                [
                    'type' => 'text',
                    'text' => 'What do you see in this image?'
                ]
            ]
        ]
    ]
]);
```

### Multi-turn Conversations

```php
$messages = [
    ['role' => 'user', 'content' => 'My name is Alice.']
];

$response = $client->createMessage([
    'model' => 'claude-haiku-4-5',
    'max_tokens' => 512,
    'messages' => $messages
]);

// Add assistant response to history
$messages[] = [
    'role' => 'assistant',
    'content' => $response->content[0]->text
];

// Continue conversation
$messages[] = [
    'role' => 'user',
    'content' => 'What is my name?'
];

$response = $client->createMessage([
    'model' => 'claude-haiku-4-5',
    'max_tokens' => 512,
    'messages' => $messages
]);
```

### Token Counting

```php
$count = $client->countTokens([
    'model' => 'claude-sonnet-4-5',
    'messages' => [
        ['role' => 'user', 'content' => 'Hello, world!']
    ]
]);

echo "Input tokens: {$count->input_tokens}\n";
```

## Async Operations

For asynchronous operations using Amphp:

```php
use Amp\Loop;
use ClaudePhp\Lib\Foundry\AsyncAnthropicFoundry;

Loop::run(function () {
    $client = new AsyncAnthropicFoundry(
        resource: $_ENV['AZURE_FOUNDRY_RESOURCE'],
        apiKey: $_ENV['AZURE_FOUNDRY_API_KEY']
    );

    $message = yield $client->createMessage([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            ['role' => 'user', 'content' => 'Hello!']
        ]
    ]);

    echo $message->content[0]->text;
});
```

## Available Models

All Claude models are available through Foundry:

| Model             | ID                           | Best For                  |
| ----------------- | ---------------------------- | ------------------------- |
| Claude Sonnet 4.5 | `claude-sonnet-4-5`          | Complex reasoning, coding |
| Claude Haiku 4.5  | `claude-haiku-4-5`           | Speed & cost efficiency   |
| Claude Opus 4.1   | `claude-opus-4-1`            | Specialized tasks         |

You can also use specific version IDs:
- `claude-sonnet-4-5-20250929`
- `claude-haiku-4-5-20251001`
- `claude-opus-4-1-20250805`

## Error Handling

```php
use ClaudePhp\Exceptions\APIConnectionError;
use ClaudePhp\Exceptions\APIStatusError;

try {
    $response = $client->createMessage([...]);
} catch (APIConnectionError $e) {
    echo "Connection error: {$e->getMessage()}\n";
} catch (APIStatusError $e) {
    echo "API error {$e->status_code}: {$e->message}\n";
} catch (\Exception $e) {
    echo "General error: {$e->getMessage()}\n";
}
```

## Configuration Options

```php
$client = new AnthropicFoundry(
    resource: 'my-resource',              // Required: Foundry resource name
    apiKey: 'your-api-key',               // Optional: API key (or use azureAdTokenProvider)
    azureAdTokenProvider: fn() => $token, // Optional: Token provider callable
    timeout: 30.0,                        // Optional: Request timeout in seconds
    customHeaders: [                      // Optional: Additional headers
        'X-Custom-Header' => 'value'
    ]
);
```

## Differences from Direct API

The Foundry integration provides the same API as the direct Claude API, with these differences:

1. **Base URL**: Uses `https://{resource}.api.foundry.azure.ai` instead of `https://api.anthropic.com`
2. **Authentication**: Supports both API keys and Azure AD tokens
3. **Infrastructure**: Runs on Azure infrastructure in your chosen region
4. **Billing**: Billed through your Azure account

All Claude features work identically:
- ✅ Streaming
- ✅ Tool use
- ✅ Vision
- ✅ Extended thinking
- ✅ Prompt caching
- ✅ Batch processing (if available)

## Complete Example

See [examples/foundry.php](../examples/foundry.php) for a comprehensive example with:
- API key authentication
- Azure AD authentication
- Streaming
- Tool use
- Token counting
- Multi-turn conversations
- Vision support
- Error handling

## Resources

- [Azure AI Foundry Documentation](https://aka.ms/foundry/claude/docs)
- [Claude in Microsoft Foundry Guide](https://docs.claude.com/en/docs/build-with-claude/claude-in-microsoft-foundry)
- [Azure Portal](https://portal.azure.com)
- [Claude API Documentation](https://docs.claude.com)

## Support

For Foundry-specific issues:
- [Azure Support](https://azure.microsoft.com/support)
- [Azure AI Foundry Documentation](https://aka.ms/foundry/docs)

For Claude SDK issues:
- [GitHub Issues](https://github.com/claude-php/claude-php-sdk/issues)
- [SDK Documentation](../README.md)

