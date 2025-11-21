#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

// Cancel a batch
echo "=== Cancel Batch Example ===\n\n";

$client = createClient();

// Get batch ID from command line or last created batch
$batchId = $argv[1] ?? null;
if (!$batchId && file_exists(__DIR__ . '/.last_batch_id')) {
    $batchId = trim(file_get_contents(__DIR__ . '/.last_batch_id'));
}

if (!$batchId) {
    echo "Usage: php batch_cancel.php <batch_id>\n";
    echo "Or create a batch first using batch_create.php\n";
    exit(1);
}

echo "Canceling batch: {$batchId}\n\n";

try {
    // First, check current status
    $batch = $client->beta()->messages()->batches()->retrieve($batchId);
    
    echo "Current batch status: {$batch['processing_status']}\n";
    
    if ($batch['processing_status'] === 'ended') {
        echo "\n⚠ Batch has already ended. Cannot cancel.\n";
        exit(0);
    }
    
    if ($batch['processing_status'] === 'canceling') {
        echo "\n⚠ Batch is already being canceled.\n";
        exit(0);
    }
    
    // Cancel the batch
    echo "\nCanceling batch...\n";
    $canceledBatch = $client->beta()->messages()->batches()->cancel($batchId);
    
    echo "\n✓ Cancel request sent successfully\n";
    echo "Status: {$canceledBatch['processing_status']}\n";
    echo "Cancel initiated at: {$canceledBatch['cancel_initiated_at']}\n";
    
    echo "\nNote: Batch cancellation is asynchronous.\n";
    echo "Use batch_poll.php to track cancellation progress.\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
