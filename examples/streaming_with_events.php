#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;
use ClaudePhp\Lib\Streaming\MessageStream;

// Streaming with event handling - demonstrates all event types
echo "=== Streaming with Event Handling ===\n\n";

$client = createClient();

try {
    $rawStream = $client->messages()->stream([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Write a short poem about coding.',
            ],
        ],
    ]);

    $stream = new MessageStream($rawStream);
    
    // Listen to different event types
    echo "Listening to streaming events...\n\n";
    
    foreach ($stream as $event) {
        $type = $event['type'] ?? 'unknown';
        
        // Handle different event types
        switch ($type) {
            case 'message_start':
                $message = $event['message'] ?? [];
                echo "[Message Start] ID: {$message['id']}, Model: {$message['model']}\n";
                echo "Input tokens: {$message['usage']['input_tokens']}\n\n";
                break;
                
            case 'content_block_start':
                $contentBlock = $event['content_block'] ?? [];
                echo "[Content Block Start] Index: {$event['index']}, Type: {$contentBlock['type']}\n";
                break;
                
            case 'content_block_delta':
                $delta = $event['delta'] ?? [];
                if (($delta['type'] ?? '') === 'text_delta') {
                    echo $delta['text'] ?? '';
                    flush();
                }
                break;
                
            case 'content_block_stop':
                echo "\n[Content Block Stop] Index: {$event['index']}\n";
                break;
                
            case 'message_delta':
                $delta = $event['delta'] ?? [];
                $usage = $event['usage'] ?? [];
                echo "\n[Message Delta] Stop reason: {$delta['stop_reason']}\n";
                echo "Output tokens: {$usage['output_tokens']}\n";
                break;
                
            case 'message_stop':
                echo "[Message Stop]\n";
                break;
                
            case 'ping':
                // Ping events can be ignored or logged
                break;
                
            case 'error':
                $error = $event['error'] ?? [];
                echo "\n[Error] Type: {$error['type']}, Message: {$error['message']}\n";
                break;
        }
    }
    
    echo "\n\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "✓ Event handling streaming completed successfully\n";
