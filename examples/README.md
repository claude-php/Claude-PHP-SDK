# Claude PHP SDK Examples

This directory contains example scripts demonstrating various features of the Claude PHP SDK.

## Setup

1. Create a `.env` file in the project root with your API key:
```
ANTHROPIC_API_KEY=your-api-key-here
```

2. Run any example:
```bash
php examples/messages.php
```

## Examples

### Basic Usage

- **messages.php** - Basic message creation and multi-turn conversations
- **messages_stream.php** - Streaming message responses in real-time

### Vision

- **images.php** - Send images to Claude using base64 encoding

### Extended Thinking

- **thinking.php** - Use Claude's extended thinking feature for complex reasoning
- **thinking_stream.php** - Stream extended thinking responses

### Tool Use (Function Calling)

- **tools.php** - Basic tool use with manual tool execution
- **web_search.php** - Use Claude's built-in web search tool

### Beta Features

- **beta_features.php** - Using beta features with the anthropic-beta header

### Testing

- **test_connection.php** - Basic connectivity test with debugging output

## Common Patterns

### Loading Environment Variables

All examples use the helper functions in `helpers.php`:

```php
require_once __DIR__ . '/helpers.php';

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());
```

### Handling Responses

```php
$response = $client->messages()->create([...]);

foreach ($response->content as $block) {
    if ($block['type'] === 'text') {
        echo $block['text'];
    }
}
```

### Streaming

```php
use ClaudePhp\Lib\Streaming\MessageStream;
use ClaudePhp\Responses\Helpers\MessageContentHelper;

$rawStream = $client->messages()->stream([...]);
$stream = new MessageStream($rawStream);

foreach ($stream as $event) {
    if ($event['type'] === 'content_block_delta') {
        echo $event['delta']['text'] ?? '';
    }
}

$final = $stream->getFinalMessage();
echo MessageContentHelper::text($final);
```

### Tool Use

```php
// 1. Send request with tools
$message = $client->messages()->create([
    'messages' => [['role' => 'user', 'content' => 'What is the weather?']],
    'tools' => [['name' => 'get_weather', ...]],
]);

// 2. Extract tool use
foreach ($message->content as $block) {
    if ($block['type'] === 'tool_use') {
        $toolUse = $block;
        break;
    }
}

// 3. Execute tool and return result
$result = execute_tool($toolUse['name'], $toolUse['input']);

$response = $client->messages()->create([
    'messages' => [
        $originalMessage,
        ['role' => 'assistant', 'content' => $message->content],
        ['role' => 'user', 'content' => [
            ['type' => 'tool_result', 'tool_use_id' => $toolUse['id'], 'content' => $result]
        ]]
    ],
    'tools' => $tools,
]);
```

## Model Selection

The examples use `claude-sonnet-4-5-20250929` which is the latest Sonnet model. You can also use:

- `claude-sonnet-4-5` - Latest Sonnet (auto-updating alias)
- `claude-haiku-4-5-20251001` - Fast, cost-effective model
- `claude-opus-4-1-20250805` - Most capable model for complex tasks

## Notes

- Extended thinking is only available on Sonnet 4.5, Opus 4, and Opus 4.1
- Web search requires the `web_search_20250305` tool type
- All streaming examples use synchronous streaming - PHP doesn't have native async/await
