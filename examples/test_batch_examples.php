#!/usr/bin/env php
<?php

/**
 * Test runner for batch processing examples
 */

declare(strict_types=1);

echo "===========================================\n";
echo "Testing Batch Processing Examples\n";
echo "===========================================\n\n";

// Test 1: Create batch
echo "Test 1: Creating a batch\n";
echo str_repeat('-', 40) . "\n";
exec("php " . __DIR__ . "/batch_create.php 2>&1", $output, $returnVar);
$createSuccess = $returnVar === 0 && str_contains(implode("\n", $output), '✓');
echo implode("\n", $output) . "\n\n";

if (!$createSuccess) {
    echo "✗ Batch creation failed. Stopping tests.\n";
    exit(1);
}

// Extract batch ID
$batchIdFile = __DIR__ . '/.last_batch_id';
if (!file_exists($batchIdFile)) {
    echo "✗ Batch ID file not found\n";
    exit(1);
}

$batchId = trim(file_get_contents($batchIdFile));
echo "Using batch ID: {$batchId}\n\n";

// Test 2: List batches
echo "Test 2: Listing batches\n";
echo str_repeat('-', 40) . "\n";
$output = [];
exec("php " . __DIR__ . "/batch_list.php 5 2>&1", $output, $returnVar);
$listSuccess = $returnVar === 0;
echo implode("\n", $output) . "\n\n";

// Test 3: Retrieve batch status
echo "Test 3: Retrieving batch status (1 poll)\n";
echo str_repeat('-', 40) . "\n";
$output = [];
exec("php " . __DIR__ . "/batch_poll.php {$batchId} 2>&1 | head -20", $output, $returnVar);
echo implode("\n", $output) . "\n\n";

// Test 4: Check if batch completed quickly (unlikely but possible)
echo "Test 4: Checking for results\n";
echo str_repeat('-', 40) . "\n";
$output = [];
exec("php " . __DIR__ . "/batch_results.php {$batchId} 2>&1", $output, $returnVar);
$resultsOutput = implode("\n", $output);
echo $resultsOutput . "\n\n";

// Test 5: Complete workflow (creates its own batch)
echo "Test 5: Complete workflow example\n";
echo str_repeat('-', 40) . "\n";
$output = [];
exec("php " . __DIR__ . "/batch_complete_workflow.php 2>&1", $output, $returnVar);
$workflowSuccess = $returnVar === 0 && str_contains(implode("\n", $output), '✓');
echo implode("\n", $output) . "\n\n";

// Summary
echo "===========================================\n";
echo "Test Results Summary\n";
echo "===========================================\n\n";

$results = [
    'Batch Creation' => $createSuccess,
    'Batch Listing' => $listSuccess,
    'Complete Workflow' => $workflowSuccess,
];

$passed = 0;
$failed = 0;

foreach ($results as $name => $success) {
    if ($success) {
        echo "✓ {$name}\n";
        $passed++;
    } else {
        echo "✗ {$name}\n";
        $failed++;
    }
}

echo "\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";
echo "\nNote: Batch polling and results tests are informational only.\n";
echo "Batches may take up to 1 hour to complete.\n";

// Cleanup
if (file_exists($batchIdFile)) {
    unlink($batchIdFile);
}

exit($failed > 0 ? 1 : 0);
