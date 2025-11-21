#!/usr/bin/env php
<?php

/**
 * Test runner for all streaming examples
 */

declare(strict_types=1);

$examples = [
    'streaming_basic.php' => 'Basic Streaming',
    'streaming_with_events.php' => 'Event Handling',
    'streaming_with_tools.php' => 'Tool Use',
    'streaming_extended_thinking.php' => 'Extended Thinking',
    'streaming_message_accumulation.php' => 'Message Accumulation',
    'streaming_error_recovery.php' => 'Error Recovery',
    'streaming_web_search.php' => 'Web Search',
];

$results = [];

echo "===========================================\n";
echo "Running Streaming Examples Test Suite\n";
echo "===========================================\n\n";

foreach ($examples as $file => $name) {
    echo "Testing: {$name}\n";
    echo str_repeat('-', 40) . "\n";
    
    $output = [];
    $returnVar = 0;
    
    exec("php " . __DIR__ . "/{$file} 2>&1", $output, $returnVar);
    
    $success = $returnVar === 0 && str_contains(implode("\n", $output), '✓');
    
    $results[$name] = $success;
    
    if ($success) {
        echo "✓ PASSED\n\n";
    } else {
        echo "✗ FAILED\n";
        echo "Output:\n" . implode("\n", $output) . "\n\n";
    }
}

echo "===========================================\n";
echo "Test Results Summary\n";
echo "===========================================\n\n";

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
echo "Total: " . count($results) . " examples\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";

exit($failed > 0 ? 1 : 0);
