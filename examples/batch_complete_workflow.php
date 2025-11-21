#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

// Complete batch workflow example
echo "=== Complete Batch Processing Workflow ===\n\n";

$client = createClient();

try {
    // Step 1: Create a batch
    echo "Step 1: Creating batch...\n";
    
    $requests = [
        [
            'custom_id' => 'translate-hello',
            'params' => [
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 100,
                'messages' => [
                    ['role' => 'user', 'content' => 'Translate "Hello, how are you?" to Spanish'],
                ],
            ],
        ],
        [
            'custom_id' => 'translate-goodbye',
            'params' => [
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 100,
                'messages' => [
                    ['role' => 'user', 'content' => 'Translate "Goodbye, see you later!" to French'],
                ],
            ],
        ],
        [
            'custom_id' => 'translate-thanks',
            'params' => [
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 100,
                'messages' => [
                    ['role' => 'user', 'content' => 'Translate "Thank you very much" to German'],
                ],
            ],
        ],
    ];
    
    $batch = $client->beta()->messages()->batches()->create([
        'requests' => $requests,
    ]);
    
    $batchId = $batch['id'];
    echo "✓ Batch created: {$batchId}\n\n";
    
    // Step 2: Poll for completion
    echo "Step 2: Polling for completion...\n";
    
    $maxAttempts = 60;
    $attempt = 0;
    $pollInterval = 5; // seconds
    
    while ($attempt < $maxAttempts) {
        $batch = $client->beta()->messages()->batches()->retrieve($batchId);
        
        $total = $batch['request_counts']['processing'] + 
                 $batch['request_counts']['succeeded'] + 
                 $batch['request_counts']['errored'];
        
        echo "  Attempt {$attempt}: " .
             "Processing={$batch['request_counts']['processing']}, " .
             "Succeeded={$batch['request_counts']['succeeded']}, " .
             "Errored={$batch['request_counts']['errored']}\n";
        
        if ($batch['processing_status'] === 'ended') {
            echo "✓ Batch processing completed!\n\n";
            break;
        }
        
        $attempt++;
        if ($attempt < $maxAttempts) {
            sleep($pollInterval);
        }
    }
    
    if ($batch['processing_status'] !== 'ended') {
        echo "⚠ Batch still processing after {$maxAttempts} attempts\n";
        echo "You can check status later with: php batch_poll.php {$batchId}\n";
        exit(0);
    }
    
    // Step 3: Retrieve and process results
    echo "Step 3: Retrieving results...\n";
    
    $results = $client->beta()->messages()->batches()->results($batchId);
    
    $translations = [];
    
    foreach ($results as $result) {
        $customId = $result['custom_id'];
        $resultType = $result['result']['type'];
        
        if ($resultType === 'succeeded') {
            $message = $result['result']['message'];
            $text = $message['content'][0]['text'] ?? 'No response';
            $translations[$customId] = $text;
            echo "  ✓ {$customId}: {$text}\n";
        } else {
            echo "  ✗ {$customId}: Failed ({$resultType})\n";
        }
    }
    
    echo "\n";
    
    // Step 4: Summary
    echo "Step 4: Summary\n";
    echo "  Total requests: " . count($requests) . "\n";
    echo "  Successful: {$batch['request_counts']['succeeded']}\n";
    echo "  Failed: {$batch['request_counts']['errored']}\n";
    echo "  Processing time: ~" . ($attempt * $pollInterval) . " seconds\n";
    echo "  Cost savings: 50% off standard API pricing\n";
    
    echo "\n✓ Complete workflow finished successfully!\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if ($e instanceof \ClaudePhp\Exceptions\APIStatusError) {
        echo "Status code: " . $e->status_code . "\n";
    }
    exit(1);
}
