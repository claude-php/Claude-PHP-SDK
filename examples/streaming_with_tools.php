#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;
use ClaudePhp\Lib\Streaming\MessageStream;

// Streaming with tool use - demonstrates tool_use content blocks and input_json_delta
echo "=== Streaming with Tool Use ===\n\n";

$client = createClient();

$tools = [
    [
        'name' => 'get_weather',
        'description' => 'Get the current weather in a given location',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'location' => [
                    'type' => 'string',
                    'description' => 'The city and state, e.g. San Francisco, CA',
                ],
                'unit' => [
                    'type' => 'string',
                    'enum' => ['celsius', 'fahrenheit'],
                    'description' => 'The unit of temperature, either "celsius" or "fahrenheit"',
                ],
            ],
            'required' => ['location'],
        ],
    ],
];

try {
    $rawStream = $client->messages()->stream([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'tools' => $tools,
        'tool_choice' => ['type' => 'any'],
        'messages' => [
            [
                'role' => 'user',
                'content' => 'What is the weather like in San Francisco?',
            ],
        ],
    ]);

    $stream = new MessageStream($rawStream);
    $toolInputJson = '';
    $currentContentBlock = null;
    
    foreach ($stream as $event) {
        $type = $event['type'] ?? 'unknown';
        
        switch ($type) {
            case 'message_start':
                $message = $event['message'] ?? [];
                echo "[Message Start] Model: {$message['model']}\n\n";
                break;
                
            case 'content_block_start':
                $currentContentBlock = $event['content_block'] ?? [];
                $blockType = $currentContentBlock['type'] ?? 'unknown';
                echo "[Content Block {$event['index']} Start] Type: {$blockType}\n";
                
                if ($blockType === 'tool_use') {
                    echo "Tool: {$currentContentBlock['name']}\n";
                    echo "Tool ID: {$currentContentBlock['id']}\n";
                    $toolInputJson = '';
                }
                break;
                
            case 'content_block_delta':
                $delta = $event['delta'] ?? [];
                $deltaType = $delta['type'] ?? 'unknown';
                
                if ($deltaType === 'text_delta') {
                    echo $delta['text'] ?? '';
                    flush();
                } elseif ($deltaType === 'input_json_delta') {
                    // Accumulate tool input JSON
                    $partialJson = $delta['partial_json'] ?? '';
                    $toolInputJson .= $partialJson;
                    echo $partialJson;
                    flush();
                }
                break;
                
            case 'content_block_stop':
                if ($currentContentBlock && ($currentContentBlock['type'] ?? '') === 'tool_use' && $toolInputJson) {
                    echo "\n[Tool Input Complete]\n";
                    $parsedInput = json_decode($toolInputJson, true);
                    echo "Parsed tool input: " . json_encode($parsedInput, JSON_PRETTY_PRINT) . "\n";
                }
                echo "\n";
                break;
                
            case 'message_delta':
                $delta = $event['delta'] ?? [];
                echo "\n[Message Complete] Stop reason: {$delta['stop_reason']}\n";
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

echo "✓ Tool use streaming completed successfully\n";
