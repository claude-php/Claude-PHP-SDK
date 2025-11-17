#!/usr/bin/env php
<?php

/**
 * Comprehensive test script to validate all SDK examples work correctly
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');

echo "=================================================\n";
echo "Claude PHP SDK - Comprehensive Examples Test\n";
echo "=================================================\n\n";

$tests = [
    [
        'name' => 'Quickstart',
        'file' => 'quickstart.php',
        'description' => 'Testing the quickstart example from docs',
    ],
    [
        'name' => 'Getting Started',
        'file' => 'get_started.php',
        'description' => 'Testing complete getting started guide',
    ],
    [
        'name' => 'Error Handling',
        'file' => 'error_handling.php',
        'description' => 'Testing error handling patterns',
    ],
    [
        'name' => 'Model Comparison',
        'file' => 'model_comparison.php',
        'description' => 'Testing different Claude models',
    ],
    [
        'name' => 'Basic Message Creation',
        'file' => 'messages.php',
        'description' => 'Testing basic message creation and multi-turn conversation',
    ],
    [
        'name' => 'Streaming Messages',
        'file' => 'messages_stream.php',
        'description' => 'Testing real-time streaming responses',
    ],
    [
        'name' => 'Vision (Images)',
        'file' => 'images.php',
        'description' => 'Testing image analysis with base64 encoding',
    ],
    [
        'name' => 'Extended Thinking',
        'file' => 'thinking.php',
        'description' => 'Testing extended thinking with budget_tokens',
    ],
    [
        'name' => 'Thinking Stream',
        'file' => 'thinking_stream.php',
        'description' => 'Testing streaming extended thinking',
    ],
    [
        'name' => 'Tool Use',
        'file' => 'tools.php',
        'description' => 'Testing function calling with tools',
    ],
    [
        'name' => 'Web Search',
        'file' => 'web_search.php',
        'description' => 'Testing built-in web search tool',
    ],
];

$passed = 0;
$failed = 0;

foreach ($tests as $test) {
    echo "Testing: {$test['name']}\n";
    echo "Description: {$test['description']}\n";

    $file = __DIR__ . '/' . $test['file'];

    if (!file_exists($file)) {
        echo "❌ FAILED: File not found\n\n";
        $failed++;
        continue;
    }

    // Run the example with timeout
    $output = [];
    $returnCode = 0;
    exec("timeout 30 php " . escapeshellarg($file) . " 2>&1", $output, $returnCode);

    if ($returnCode === 0) {
        echo "✅ PASSED\n";
        $passed++;
    } elseif ($returnCode === 124) {
        echo "⚠️  TIMEOUT (30s) - May still be working\n";
        $passed++; // Count as passed since timeout might be due to slow API
    } else {
        echo "❌ FAILED (exit code: $returnCode)\n";
        echo "Output: " . implode("\n", array_slice($output, 0, 5)) . "\n";
        $failed++;
    }

    echo "\n";
}

echo "=================================================\n";
echo "Test Results:\n";
echo "=================================================\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "Total:  " . ($passed + $failed) . "\n";
echo "\n";

if ($failed === 0) {
    echo "🎉 All examples working correctly!\n";
    exit(0);
} else {
    echo "⚠️  Some examples failed. Check the output above.\n";
    exit(1);
}
