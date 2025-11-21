#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

// Retrieve batch results
echo "=== Retrieve Batch Results ===\n\n";

$client = createClient();

// Get batch ID from command line or last created batch
$batchId = $argv[1] ?? null;
if (!$batchId && file_exists(__DIR__ . '/.last_batch_id')) {
    $batchId = trim(file_get_contents(__DIR__ . '/.last_batch_id'));
}

if (!$batchId) {
    echo "Usage: php batch_results.php <batch_id>\n";
    echo "Or create a batch first using batch_create.php\n";
    exit(1);
}

echo "Retrieving results for batch: {$batchId}\n\n";

try {
    // First, check if batch is complete
    $batch = $client->beta()->messages()->batches()->retrieve($batchId);
    
    echo "Batch status: {$batch['processing_status']}\n";
    
    if ($batch['processing_status'] !== 'ended') {
        echo "\n⚠ Batch has not finished processing yet.\n";
        echo "Current status: {$batch['processing_status']}\n";
        echo "Use batch_poll.php to wait for completion.\n";
        exit(0);
    }
    
    if (!isset($batch['results_url'])) {
        echo "\n⚠ No results URL available for this batch.\n";
        exit(0);
    }
    
    echo "Results URL: {$batch['results_url']}\n";
    echo "\nFetching results...\n\n";
    
    // Retrieve results
    $results = $client->beta()->messages()->batches()->results($batchId);
    
    $successCount = 0;
    $errorCount = 0;
    $canceledCount = 0;
    $expiredCount = 0;
    
    foreach ($results as $result) {
        $customId = $result['custom_id'] ?? 'unknown';
        $resultType = $result['result']['type'] ?? 'unknown';
        
        echo "Result for '{$customId}':\n";
        echo "  Type: {$resultType}\n";
        
        switch ($resultType) {
            case 'succeeded':
                $successCount++;
                $message = $result['result']['message'] ?? [];
                $content = $message['content'] ?? [];
                
                if (!empty($content) && isset($content[0]['text'])) {
                    $text = $content[0]['text'];
                    $preview = strlen($text) > 100 ? substr($text, 0, 100) . '...' : $text;
                    echo "  Response: {$preview}\n";
                }
                
                if (isset($message['usage'])) {
                    $usage = $message['usage'];
                    echo "  Tokens: {$usage['input_tokens']} in, {$usage['output_tokens']} out\n";
                }
                break;
                
            case 'errored':
                $errorCount++;
                $error = $result['result']['error'] ?? [];
                echo "  Error type: " . ($error['type'] ?? 'unknown') . "\n";
                echo "  Error message: " . ($error['message'] ?? 'No message') . "\n";
                break;
                
            case 'canceled':
                $canceledCount++;
                echo "  Request was canceled\n";
                break;
                
            case 'expired':
                $expiredCount++;
                echo "  Request expired\n";
                break;
        }
        
        echo "\n";
    }
    
    echo "=== Summary ===\n";
    echo "Succeeded: {$successCount}\n";
    echo "Errored: {$errorCount}\n";
    echo "Canceled: {$canceledCount}\n";
    echo "Expired: {$expiredCount}\n";
    echo "\n✓ Results retrieved successfully\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
