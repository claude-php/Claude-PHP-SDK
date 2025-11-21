#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

// Basic batch processing example
echo "=== Basic Batch Processing Example ===\n\n";

$client = createClient();

try {
    // Create a batch with multiple requests
    $requests = [
        [
            'custom_id' => 'request-1',
            'params' => [
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 1024,
                'messages' => [
                    ['role' => 'user', 'content' => 'What is 2+2?'],
                ],
            ],
        ],
        [
            'custom_id' => 'request-2',
            'params' => [
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 1024,
                'messages' => [
                    ['role' => 'user', 'content' => 'What is the capital of France?'],
                ],
            ],
        ],
        [
            'custom_id' => 'request-3',
            'params' => [
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 1024,
                'messages' => [
                    ['role' => 'user', 'content' => 'Write a haiku about coding.'],
                ],
            ],
        ],
    ];

    echo "Creating batch with " . count($requests) . " requests...\n";
    
    $batch = $client->beta()->messages()->batches()->create([
        'requests' => $requests,
    ]);
    
    echo "Batch created successfully!\n";
    echo "Batch ID: {$batch['id']}\n";
    echo "Status: {$batch['processing_status']}\n";
    echo "Created at: {$batch['created_at']}\n";
    echo "Expires at: {$batch['expires_at']}\n";
    echo "\nRequest counts:\n";
    echo "  Processing: {$batch['request_counts']['processing']}\n";
    echo "  Succeeded: {$batch['request_counts']['succeeded']}\n";
    echo "  Errored: {$batch['request_counts']['errored']}\n";
    echo "  Canceled: {$batch['request_counts']['canceled']}\n";
    echo "  Expired: {$batch['request_counts']['expired']}\n";
    
    echo "\n✓ Batch created successfully\n";
    echo "\nBatch ID saved for other examples: {$batch['id']}\n";
    
    // Save batch ID for polling example
    file_put_contents(__DIR__ . '/.last_batch_id', $batch['id']);
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if ($e instanceof \ClaudePhp\Exceptions\APIStatusError) {
        echo "Status code: " . $e->status_code . "\n";
    }
    exit(1);
}
