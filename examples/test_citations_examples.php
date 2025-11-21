#!/usr/bin/env php
<?php

/**
 * Test runner for all citation examples
 * 
 * This script runs all citation examples and reports their status.
 */

$examples = [
    'citations_basic.php',
    'citations_multiple_documents.php',
    'citations_with_context.php',
    'citations_disabled.php',
    'citations_large_document.php',
    'citations_streaming.php',
];

$baseDir = __DIR__;
$results = [];

echo "=== Testing Citation Examples ===\n\n";

foreach ($examples as $example) {
    $filePath = $baseDir . '/' . $example;
    
    if (!file_exists($filePath)) {
        echo "✗ {$example}: File not found\n";
        $results[$example] = false;
        continue;
    }
    
    echo "Testing {$example}... ";
    
    // Run the example and capture output
    $output = [];
    $returnCode = 0;
    exec("php " . escapeshellarg($filePath) . " 2>&1", $output, $returnCode);
    
    if ($returnCode === 0) {
        echo "✓ Success\n";
        $results[$example] = true;
    } else {
        echo "✗ Failed (exit code: {$returnCode})\n";
        echo "  Output: " . implode("\n  ", array_slice($output, 0, 5)) . "\n";
        $results[$example] = false;
    }
}

echo "\n=== Summary ===\n";
$passed = count(array_filter($results));
$total = count($results);
echo "Passed: {$passed}/{$total}\n";

if ($passed === $total) {
    echo "✓ All tests passed!\n";
    exit(0);
} else {
    echo "✗ Some tests failed\n";
    exit(1);
}


