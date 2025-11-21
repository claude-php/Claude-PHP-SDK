#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;
use ClaudePhp\Lib\Streaming\MessageStream;

// Streaming with extended thinking - demonstrates thinking_delta and signature_delta
echo "=== Streaming with Extended Thinking ===\n\n";

$client = createClient();

try {
    $rawStream = $client->messages()->stream([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 20000,
        'thinking' => [
            'type' => 'enabled',
            'budget_tokens' => 16000,
        ],
        'messages' => [
            [
                'role' => 'user',
                'content' => 'What is 27 * 453?',
            ],
        ],
    ]);

    $stream = new MessageStream($rawStream);
    $thinkingContent = '';
    $textContent = '';
    
    foreach ($stream as $event) {
        $type = $event['type'] ?? 'unknown';
        
        switch ($type) {
            case 'message_start':
                $message = $event['message'] ?? [];
                echo "[Message Start] Model: {$message['model']}\n\n";
                break;
                
            case 'content_block_start':
                $contentBlock = $event['content_block'] ?? [];
                $blockType = $contentBlock['type'] ?? 'unknown';
                
                if ($blockType === 'thinking') {
                    echo "[Thinking Block Start]\n";
                    echo "--- Claude's Internal Reasoning ---\n";
                } elseif ($blockType === 'text') {
                    echo "\n[Text Block Start]\n";
                    echo "--- Claude's Response ---\n";
                }
                break;
                
            case 'content_block_delta':
                $delta = $event['delta'] ?? [];
                $deltaType = $delta['type'] ?? 'unknown';
                
                if ($deltaType === 'thinking_delta') {
                    // Accumulate and display thinking content
                    $thinking = $delta['thinking'] ?? '';
                    $thinkingContent .= $thinking;
                    echo $thinking;
                    flush();
                } elseif ($deltaType === 'signature_delta') {
                    // Signature for thinking block integrity verification
                    $signature = $delta['signature'] ?? '';
                    echo "\n[Thinking Signature] " . substr($signature, 0, 50) . "...\n";
                } elseif ($deltaType === 'text_delta') {
                    $text = $delta['text'] ?? '';
                    $textContent .= $text;
                    echo $text;
                    flush();
                }
                break;
                
            case 'content_block_stop':
                echo "\n[Content Block Stop]\n";
                break;
                
            case 'message_delta':
                $delta = $event['delta'] ?? [];
                $usage = $event['usage'] ?? [];
                echo "\n[Message Complete] Stop reason: {$delta['stop_reason']}\n";
                echo "Output tokens: {$usage['output_tokens']}\n";
                break;
                
            case 'message_stop':
                echo "[Stream End]\n";
                break;
        }
    }
    
    echo "\n";
    echo "Summary:\n";
    echo "- Thinking content length: " . strlen($thinkingContent) . " characters\n";
    echo "- Text content length: " . strlen($textContent) . " characters\n";
    echo "\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "✓ Extended thinking streaming completed successfully\n";
