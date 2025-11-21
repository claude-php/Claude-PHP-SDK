#!/usr/bin/env php
<?php
/**
 * Comprehensive Streaming Examples - PHP examples from:
 * https://docs.claude.com/en/docs/build-with-claude/streaming
 * 
 * Demonstrates all streaming patterns including basic streaming, tool use,
 * extended thinking, web search, and error recovery.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;
use ClaudePhp\Lib\Streaming\MessageStream;
use ClaudePhp\Responses\Helpers\MessageContentHelper;
use ClaudePhp\Responses\Helpers\StreamEventHelper;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Comprehensive Streaming Examples ===\n\n";

// Example 1: Basic streaming request
echo "Example 1: Basic Streaming Request\n";
echo "-----------------------------------\n";
echo "Stream responses using server-sent events (SSE)\n\n";

try {
    $rawStream = $client->messages()->stream([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            ['role' => 'user', 'content' => 'Hello']
        ]
    ]);

    $stream = new MessageStream($rawStream);
    
    echo "Streaming response: ";
    foreach ($stream as $event) {
        if (($event['type'] ?? null) === 'content_block_delta') {
            echo $event['delta']['text'] ?? '';
            flush();
        }
    }
    
    $finalMessage = $stream->getFinalMessage();
    echo "\n\nFinal message usage:\n";
    echo "  Input tokens:  {$finalMessage->usage->input_tokens}\n";
    echo "  Output tokens: {$finalMessage->usage->output_tokens}\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: Event types in streaming
echo "Example 2: Understanding Event Types\n";
echo "-------------------------------------\n";
echo "Event flow: message_start → content_blocks → message_delta → message_stop\n\n";

try {
    $rawStream = $client->messages()->stream([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 256,
        'messages' => [
            ['role' => 'user', 'content' => 'Count to 3']
        ]
    ]);

    $stream = new MessageStream($rawStream);
    
    $eventCounts = [];
    
    foreach ($stream as $event) {
        $type = $event['type'] ?? 'unknown';
        $eventCounts[$type] = ($eventCounts[$type] ?? 0) + 1;
        
        switch ($type) {
            case 'message_start':
                echo "→ message_start (contains empty Message object)\n";
                break;
            case 'content_block_start':
                $blockType = $event['content_block']['type'] ?? 'unknown';
                echo "→ content_block_start (type: {$blockType}, index: {$event['index']})\n";
                break;
            case 'content_block_delta':
                if (isset($event['delta']['text'])) {
                    echo "  • content_block_delta: \"{$event['delta']['text']}\"\n";
                }
                break;
            case 'content_block_stop':
                echo "→ content_block_stop (index: {$event['index']})\n";
                break;
            case 'message_delta':
                echo "→ message_delta (stop_reason: {$event['delta']['stop_reason']})\n";
                break;
            case 'message_stop':
                echo "→ message_stop\n";
                break;
        }
    }
    
    echo "\nEvent counts:\n";
    foreach ($eventCounts as $type => $count) {
        echo "  {$type}: {$count}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 3: Streaming with tool use
echo "Example 3: Streaming with Tool Use\n";
echo "-----------------------------------\n";
echo "Receive tool use requests as they stream\n\n";

try {
    $rawStream = $client->messages()->stream([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'tools' => [
            [
                'name' => 'get_weather',
                'description' => 'Get weather for a location',
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
            ['role' => 'user', 'content' => 'What is the weather in Paris?']
        ]
    ]);

    $stream = new MessageStream($rawStream);
    
    foreach ($stream as $event) {
        $type = $event['type'] ?? null;
        
        if ($type === 'content_block_start') {
            $blockType = $event['content_block']['type'] ?? 'unknown';
            if ($blockType === 'tool_use') {
                $toolName = $event['content_block']['name'] ?? 'unknown';
                echo "→ Tool use starting: {$toolName}\n";
            }
        } elseif ($type === 'content_block_delta') {
            if (isset($event['delta']['text'])) {
                echo $event['delta']['text'];
                flush();
            } elseif (isset($event['delta']['partial_json'])) {
                echo $event['delta']['partial_json'];
                flush();
            }
        } elseif ($type === 'content_block_stop') {
            echo "\n";
        }
    }
    
    echo "\nTool use blocks accumulated in final message\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 4: Streaming with extended thinking
echo "Example 4: Streaming with Extended Thinking\n";
echo "--------------------------------------------\n";
echo "Stream thinking blocks with thinking_delta events\n\n";

try {
    $rawStream = $client->messages()->stream([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 2048,
        'thinking' => [
            'type' => 'enabled',
            'budget_tokens' => 1024
        ],
        'messages' => [
            ['role' => 'user', 'content' => 'What is 27 * 453?']
        ]
    ]);

    $stream = new MessageStream($rawStream);
    $inThinking = false;
    
    foreach ($stream as $event) {
        $type = $event['type'] ?? null;
        
        if ($type === 'content_block_start') {
            $blockType = $event['content_block']['type'] ?? 'unknown';
            if ($blockType === 'thinking') {
                echo "Thinking process:\n";
                $inThinking = true;
            } elseif ($blockType === 'text') {
                if ($inThinking) {
                    echo "\n\nFinal answer:\n";
                    $inThinking = false;
                }
            }
        } elseif ($type === 'content_block_delta') {
            if (isset($event['delta']['thinking'])) {
                echo $event['delta']['thinking'];
                flush();
            } elseif (isset($event['delta']['text'])) {
                echo $event['delta']['text'];
                flush();
            } elseif (isset($event['delta']['signature'])) {
                // Signature delta appears just before content_block_stop
                echo " [signature received]";
            }
        }
    }
    
    echo "\n\nNote: thinking_delta and signature_delta events for thinking blocks\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 5: Streaming with web search
echo "Example 5: Streaming with Web Search Tool\n";
echo "------------------------------------------\n";
echo "Stream responses that use the web search tool\n\n";

try {
    $rawStream = $client->messages()->stream([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'tools' => [
            [
                'type' => 'web_search_20250305',
                'name' => 'web_search',
                'max_uses' => 3
            ]
        ],
        'messages' => [
            ['role' => 'user', 'content' => 'What is the weather like in New York City today?']
        ]
    ]);

    $stream = new MessageStream($rawStream);
    
    foreach ($stream as $event) {
        $type = $event['type'] ?? null;
        
        if ($type === 'content_block_start') {
            $blockType = $event['content_block']['type'] ?? 'unknown';
            if ($blockType === 'server_tool_use') {
                echo "\n[Web search initiated]\n";
            } elseif ($blockType === 'web_search_tool_result') {
                echo "\n[Web search results received]\n";
            } elseif ($blockType === 'text') {
                echo "\nResponse: ";
            }
        } elseif ($type === 'content_block_delta') {
            if (isset($event['delta']['text'])) {
                echo $event['delta']['text'];
                flush();
            }
        }
    }
    
    $finalMessage = $stream->getFinalMessage();
    if (isset($finalMessage->usage->server_tool_use)) {
        echo "\n\nWeb search requests made: {$finalMessage->usage->server_tool_use['web_search_requests']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 6: Error handling in streams
echo "Example 6: Error Handling in Streams\n";
echo "-------------------------------------\n";
echo "Handle errors that occur during streaming\n\n";

echo "Error event format:\n";
echo "event: error\n";
echo "data: {\"type\": \"error\", \"error\": {\"type\": \"overloaded_error\", \"message\": \"Overloaded\"}}\n\n";

echo "Best practices:\n";
echo "  • Catch exceptions during stream iteration\n";
echo "  • Handle 'error' event type in stream\n";
echo "  • Implement retry logic for recoverable errors\n";
echo "  • Save partial responses before errors\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "✓ Comprehensive streaming examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Set 'stream' => true to enable streaming\n";
echo "• Event flow: message_start → content_blocks → message_delta → message_stop\n";
echo "• Use MessageStream wrapper for easier handling\n";
echo "• Thinking delta: thinking_delta and signature_delta events\n";
echo "• Tool use: input_json_delta events for tool parameters\n";
echo "• Web search: server_tool_use and web_search_tool_result blocks\n";
echo "• Streaming required when max_tokens > 21,333\n";
echo "• Use SDKs for message accumulation and error handling\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • examples/messages_stream.php - Basic streaming\n";
echo "  • examples/thinking_stream.php - Streaming with extended thinking\n";
echo "  • examples/web_search.php - Web search tool\n";

