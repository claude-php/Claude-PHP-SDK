#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;
use ClaudePhp\Lib\Streaming\MessageStream;

// Streaming with web search - demonstrates server_tool_use and web_search_tool_result
echo "=== Streaming with Web Search ===\n\n";

$client = createClient();

try {
    $rawStream = $client->messages()->stream([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'tools' => [
            [
                'type' => 'web_search_20250305',
                'name' => 'web_search',
                'max_uses' => 5,
            ],
        ],
        'messages' => [
            [
                'role' => 'user',
                'content' => 'What is the weather like in New York City today?',
            ],
        ],
    ]);

    $stream = new MessageStream($rawStream);
    $searchQuery = '';
    
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
                
                if ($blockType === 'server_tool_use') {
                    echo "\n[Web Search Tool Use Start]\n";
                    echo "Tool: {$contentBlock['name']}\n";
                    echo "Tool ID: {$contentBlock['id']}\n";
                    $searchQuery = '';
                } elseif ($blockType === 'web_search_tool_result') {
                    echo "\n[Web Search Results Received]\n";
                    echo "Tool Use ID: {$contentBlock['tool_use_id']}\n";
                    if (isset($contentBlock['content']) && is_array($contentBlock['content'])) {
                        echo "Number of results: " . count($contentBlock['content']) . "\n";
                        // Show first result
                        if (count($contentBlock['content']) > 0) {
                            $firstResult = $contentBlock['content'][0];
                            if (isset($firstResult['title'])) {
                                echo "First result: {$firstResult['title']}\n";
                            }
                            if (isset($firstResult['url'])) {
                                echo "URL: {$firstResult['url']}\n";
                            }
                        }
                    }
                } elseif ($blockType === 'text') {
                    echo "\n[Claude's Response]\n";
                }
                break;
                
            case 'content_block_delta':
                $delta = $event['delta'] ?? [];
                $deltaType = $delta['type'] ?? 'unknown';
                
                if ($deltaType === 'input_json_delta') {
                    // Accumulate search query
                    $partialJson = $delta['partial_json'] ?? '';
                    $searchQuery .= $partialJson;
                    echo $partialJson;
                    flush();
                } elseif ($deltaType === 'text_delta') {
                    echo $delta['text'] ?? '';
                    flush();
                }
                break;
                
            case 'content_block_stop':
                if ($searchQuery) {
                    echo "\n[Search Query Complete]: {$searchQuery}\n";
                    $searchQuery = '';
                }
                break;
                
            case 'message_delta':
                $delta = $event['delta'] ?? [];
                $usage = $event['usage'] ?? [];
                echo "\n\n[Message Complete] Stop reason: {$delta['stop_reason']}\n";
                if (isset($usage['server_tool_use'])) {
                    echo "Server tool use stats: " . json_encode($usage['server_tool_use']) . "\n";
                }
                break;
                
            case 'message_stop':
                echo "[Stream End]\n";
                break;
        }
    }
    
    echo "\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "✓ Web search streaming completed successfully\n";
