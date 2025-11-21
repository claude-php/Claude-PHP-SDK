#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

// List all batches
echo "=== List All Batches ===\n\n";

$client = createClient();

try {
    $limit = isset($argv[1]) ? (int)$argv[1] : 20;
    
    echo "Listing up to {$limit} batches...\n\n";
    
    $response = $client->messages()->batches()->list([
        'limit' => $limit,
    ]);
    
    $batches = $response['data'] ?? [];
    $count = 0;
    
    foreach ($batches as $batch) {
        $count++;
        echo "Batch #{$count}\n";
        echo "  ID: {$batch['id']}\n";
        echo "  Status: {$batch['processing_status']}\n";
        echo "  Created: {$batch['created_at']}\n";
        
        if (isset($batch['ended_at'])) {
            echo "  Ended: {$batch['ended_at']}\n";
        }
        
        echo "  Request counts:\n";
        echo "    Total: " . ($batch['request_counts']['processing'] + 
                              $batch['request_counts']['succeeded'] + 
                              $batch['request_counts']['errored'] + 
                              $batch['request_counts']['canceled'] + 
                              $batch['request_counts']['expired']) . "\n";
        echo "    Succeeded: {$batch['request_counts']['succeeded']}\n";
        echo "    Errored: {$batch['request_counts']['errored']}\n";
        
        if (isset($batch['results_url'])) {
            echo "  Results available: Yes\n";
        }
        
        echo "\n";
    }
    
    if ($count === 0) {
        echo "No batches found.\n";
    } else {
        echo "✓ Listed {$count} batch(es)\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
