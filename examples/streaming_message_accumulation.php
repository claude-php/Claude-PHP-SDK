#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;
use ClaudePhp\Lib\Streaming\MessageStream;

// Message accumulation example - demonstrates building the final message from stream
echo "=== Streaming with Message Accumulation ===\n\n";

$client = createClient();

try {
    $rawStream = $client->messages()->stream([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Count from 1 to 5 and explain why you like each number.',
            ],
        ],
    ]);

    $stream = new MessageStream($rawStream);
    
    // Accumulate the message
    $messageId = null;
    $model = null;
    $contentBlocks = [];
    $currentBlockIndex = null;
    $stopReason = null;
    $usage = null;
    
    echo "Streaming response...\n\n";
    
    foreach ($stream as $event) {
        $type = $event['type'] ?? 'unknown';
        
        switch ($type) {
            case 'message_start':
                $message = $event['message'] ?? [];
                $messageId = $message['id'] ?? null;
                $model = $message['model'] ?? null;
                $messageUsage = $message['usage'] ?? [];
                $usage = [
                    'input_tokens' => $messageUsage['input_tokens'] ?? 0,
                    'output_tokens' => 0,
                ];
                break;
                
            case 'content_block_start':
                $currentBlockIndex = $event['index'] ?? 0;
                $contentBlock = $event['content_block'] ?? [];
                $blockType = $contentBlock['type'] ?? 'text';
                
                $contentBlocks[$currentBlockIndex] = [
                    'type' => $blockType,
                    'content' => '',
                ];
                
                if ($blockType === 'tool_use') {
                    $contentBlocks[$currentBlockIndex]['id'] = $contentBlock['id'] ?? '';
                    $contentBlocks[$currentBlockIndex]['name'] = $contentBlock['name'] ?? '';
                    $contentBlocks[$currentBlockIndex]['input'] = '';
                }
                break;
                
            case 'content_block_delta':
                $delta = $event['delta'] ?? [];
                $deltaType = $delta['type'] ?? 'unknown';
                $index = $event['index'] ?? $currentBlockIndex;
                
                if ($deltaType === 'text_delta') {
                    $text = $delta['text'] ?? '';
                    $contentBlocks[$index]['content'] .= $text;
                    echo $text;
                    flush();
                } elseif ($deltaType === 'input_json_delta') {
                    $partialJson = $delta['partial_json'] ?? '';
                    $contentBlocks[$index]['input'] .= $partialJson;
                }
                break;
                
            case 'message_delta':
                $delta = $event['delta'] ?? [];
                $deltaUsage = $event['usage'] ?? [];
                $stopReason = $delta['stop_reason'] ?? null;
                $usage['output_tokens'] = $deltaUsage['output_tokens'] ?? 0;
                break;
                
            case 'message_stop':
                // Stream complete
                break;
        }
    }
    
    echo "\n\n";
    echo "=== Accumulated Message ===\n";
    echo "Message ID: {$messageId}\n";
    echo "Model: {$model}\n";
    echo "Stop Reason: {$stopReason}\n";
    echo "Usage: Input tokens: {$usage['input_tokens']}, Output tokens: {$usage['output_tokens']}\n";
    echo "\nContent Blocks:\n";
    
    foreach ($contentBlocks as $index => $block) {
        echo "Block {$index} ({$block['type']}):\n";
        if ($block['type'] === 'text') {
            echo "  Text: " . substr($block['content'], 0, 100);
            if (strlen($block['content']) > 100) {
                echo "... (+" . (strlen($block['content']) - 100) . " more chars)";
            }
            echo "\n";
        } elseif ($block['type'] === 'tool_use') {
            echo "  Tool: {$block['name']}\n";
            echo "  ID: {$block['id']}\n";
            echo "  Input: {$block['input']}\n";
        }
    }
    
    echo "\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "✓ Message accumulation completed successfully\n";
