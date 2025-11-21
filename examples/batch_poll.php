#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

// Polling for batch completion
echo "=== Batch Polling Example ===\n\n";

$client = createClient();

// Get batch ID from command line or last created batch
$batchId = $argv[1] ?? null;
if (!$batchId && file_exists(__DIR__ . '/.last_batch_id')) {
    $batchId = trim(file_get_contents(__DIR__ . '/.last_batch_id'));
}

if (!$batchId) {
    echo "Usage: php batch_poll.php <batch_id>\n";
    echo "Or create a batch first using batch_create.php\n";
    exit(1);
}

echo "Polling batch: {$batchId}\n\n";

try {
    $pollCount = 0;
    $maxPolls = 60; // Maximum 60 polls (10 minutes with 10 second intervals)
    
    while ($pollCount < $maxPolls) {
        $batch = $client->beta()->messages()->batches()->retrieve($batchId);
        
        echo "Poll #{$pollCount}: Status = {$batch['processing_status']}\n";
        echo "  Processing: {$batch['request_counts']['processing']}\n";
        echo "  Succeeded: {$batch['request_counts']['succeeded']}\n";
        echo "  Errored: {$batch['request_counts']['errored']}\n";
        echo "  Canceled: {$batch['request_counts']['canceled']}\n";
        echo "  Expired: {$batch['request_counts']['expired']}\n";
        
        if ($batch['processing_status'] === 'ended') {
            echo "\n✓ Batch processing completed!\n";
            echo "\nFinal counts:\n";
            echo "  Succeeded: {$batch['request_counts']['succeeded']}\n";
            echo "  Errored: {$batch['request_counts']['errored']}\n";
            echo "  Canceled: {$batch['request_counts']['canceled']}\n";
            echo "  Expired: {$batch['request_counts']['expired']}\n";
            
            if (isset($batch['results_url'])) {
                echo "\nResults URL: {$batch['results_url']}\n";
                echo "Use batch_results.php to retrieve results\n";
            }
            
            break;
        }
        
        if ($batch['processing_status'] === 'canceling') {
            echo "\n⚠ Batch is being canceled...\n";
        }
        
        $pollCount++;
        
        if ($pollCount < $maxPolls) {
            echo "\nWaiting 10 seconds before next poll...\n\n";
            sleep(10);
        }
    }
    
    if ($pollCount >= $maxPolls) {
        echo "\n⚠ Reached maximum poll attempts. Batch may still be processing.\n";
        echo "Check status manually or try polling again later.\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
