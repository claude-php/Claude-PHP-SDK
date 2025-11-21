#!/usr/bin/env php
<?php
/**
 * Batch Processing - PHP examples from:
 * https://docs.claude.com/en/docs/build-with-claude/batch-processing
 * 
 * Process multiple requests asynchronously with 50% cost savings.
 * Results available within 24 hours.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Batch Processing - 50% Cost Savings ===\n\n";

// Example 1: Creating a batch
echo "Example 1: Creating a Message Batch\n";
echo "------------------------------------\n";
echo "Process multiple requests with 50% cost savings\n";
echo "Results available within 24 hours\n\n";

try {
    $requests = [
        [
            'custom_id' => 'request-1',
            'params' => [
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 1024,
                'messages' => [
                    ['role' => 'user', 'content' => 'What is the capital of France?']
                ]
            ]
        ],
        [
            'custom_id' => 'request-2',
            'params' => [
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 1024,
                'messages' => [
                    ['role' => 'user', 'content' => 'What is 2+2?']
                ]
            ]
        ],
        [
            'custom_id' => 'request-3',
            'params' => [
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 1024,
                'messages' => [
                    ['role' => 'user', 'content' => 'Write a haiku about coding']
                ]
            ]
        ]
    ];
    
    echo "Creating batch with " . count($requests) . " requests...\n";
    
    $batch = $client->messages()->batches()->create([
        'requests' => $requests
    ]);
    
    echo "✓ Batch created successfully!\n";
    echo "  Batch ID: {$batch->id}\n";
    echo "  Status: {$batch->processing_status}\n";
    echo "  Request counts:\n";
    echo "    Total: {$batch->request_counts->total}\n";
    echo "    Processing: {$batch->request_counts->processing}\n";
    echo "    Succeeded: {$batch->request_counts->succeeded}\n";
    echo "    Errored: {$batch->request_counts->errored}\n";
    echo "    Canceled: {$batch->request_counts->canceled}\n";
    echo "    Expired: {$batch->request_counts->expired}\n\n";
    
    echo "Use this batch_id to retrieve results later:\n";
    echo "  \$results = \$client->messages()->batches()->retrieve('{$batch->id}');\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: Listing batches
echo "Example 2: Listing Batches\n";
echo "--------------------------\n";

try {
    $batches = $client->messages()->batches()->list([
        'limit' => 5
    ]);
    
    echo "Recent batches:\n";
    foreach ($batches->data as $batch) {
        echo "  • ID: {$batch->id}\n";
        echo "    Status: {$batch->processing_status}\n";
        echo "    Requests: {$batch->request_counts->total} total, ";
        echo "{$batch->request_counts->succeeded} succeeded\n";
        echo "    Created: {$batch->created_at}\n\n";
    }
} catch (Exception $e) {
    echo "Note: List batches to see your batch processing history\n";
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 3: Retrieving batch results
echo "Example 3: Retrieving Batch Results\n";
echo "------------------------------------\n";
echo "Check status and download results when processing is complete\n\n";

echo "Step 1: Retrieve batch status\n";
echo "```php\n";
echo "\$batch = \$client->messages()->batches()->retrieve('batch_01Ab...');\n";
echo "echo \$batch->processing_status; // 'in_progress', 'ended', etc.\n";
echo "```\n\n";

echo "Step 2: Get results URL when complete\n";
echo "```php\n";
echo "if (\$batch->processing_status === 'ended') {\n";
echo "    \$resultsUrl = \$batch->results_url;\n";
echo "    // Download and process results\n";
echo "}\n";
echo "```\n\n";

echo "Processing statuses:\n";
echo "  • in_progress - Batch is being processed\n";
echo "  • canceling - Cancellation requested\n";
echo "  • ended - Processing complete (check results)\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 4: Canceling a batch
echo "Example 4: Canceling a Batch\n";
echo "-----------------------------\n";
echo "Cancel in-progress batches if needed\n\n";

echo "```php\n";
echo "\$result = \$client->messages()->batches()->cancel('batch_01Ab...');\n";
echo "echo \$result->processing_status; // 'canceling'\n";
echo "```\n\n";

echo "Note: Only batches in 'in_progress' status can be canceled\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "✓ Batch processing examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• 50% cost savings compared to standard API\n";
echo "• Results available within 24 hours\n";
echo "• Create batches with \$client->messages()->batches()->create()\n";
echo "• Each request needs a unique custom_id\n";
echo "• Poll with retrieve() to check status\n";
echo "• Download results from results_url when status is 'ended'\n";
echo "• Can cancel in-progress batches\n";
echo "• Ideal for: Analysis, evaluation, classification at scale\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • See tests/Integration/BatchResultsTest.php for complete batch workflow\n";

