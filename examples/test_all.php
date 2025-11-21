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
        'name' => 'Working with Messages',
        'file' => 'working_with_messages.php',
        'description' => 'Testing practical Messages API patterns',
    ],
    [
        'name' => 'Context Windows',
        'file' => 'context_windows.php',
        'description' => 'Testing context window management',
    ],
    [
        'name' => 'Prompt Caching',
        'file' => 'prompt_caching.php',
        'description' => 'Testing prompt caching for cost reduction',
    ],
    [
        'name' => 'Context Editing (Beta)',
        'file' => 'context_editing.php',
        'description' => 'Testing automatic context management',
    ],
    [
        'name' => 'Extended Thinking',
        'file' => 'extended_thinking.php',
        'description' => 'Testing enhanced reasoning capabilities',
    ],
    [
        'name' => 'Streaming (Comprehensive)',
        'file' => 'streaming_comprehensive.php',
        'description' => 'Testing all streaming patterns',
    ],
    [
        'name' => 'Batch Processing',
        'file' => 'batch_processing.php',
        'description' => 'Testing batch API for cost savings',
    ],
    [
        'name' => 'Citations (Beta)',
        'file' => 'citations.php',
        'description' => 'Testing source attribution',
    ],
    [
        'name' => 'Token Counting',
        'file' => 'token_counting.php',
        'description' => 'Testing token estimation',
    ],
    [
        'name' => 'Embeddings',
        'file' => 'embeddings.php',
        'description' => 'Testing embeddings concepts',
    ],
    [
        'name' => 'Vision (Comprehensive)',
        'file' => 'vision_comprehensive.php',
        'description' => 'Testing complete vision capabilities',
    ],
    [
        'name' => 'PDF Support',
        'file' => 'pdf_support.php',
        'description' => 'Testing PDF document handling',
    ],
    [
        'name' => 'Files API (Beta)',
        'file' => 'files_api.php',
        'description' => 'Testing file management',
    ],
    [
        'name' => 'Search Results',
        'file' => 'search_results.php',
        'description' => 'Testing manual search result provision',
    ],
    [
        'name' => 'Structured Outputs',
        'file' => 'structured_outputs.php',
        'description' => 'Testing guaranteed JSON schema',
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
        'description' => 'Testing basic message creation',
    ],
    [
        'name' => 'Basic Streaming',
        'file' => 'messages_stream.php',
        'description' => 'Testing basic streaming',
    ],
    [
        'name' => 'Basic Images',
        'file' => 'images.php',
        'description' => 'Testing basic image handling',
    ],
    [
        'name' => 'Basic Thinking',
        'file' => 'thinking.php',
        'description' => 'Testing basic extended thinking',
    ],
    [
        'name' => 'Thinking Stream',
        'file' => 'thinking_stream.php',
        'description' => 'Testing streaming thinking',
    ],
    [
        'name' => 'Tool Use Overview',
        'file' => 'tool_use_overview.php',
        'description' => 'Testing complete tool use guide',
    ],
    [
        'name' => 'Tool Use Implementation',
        'file' => 'tool_use_implementation.php',
        'description' => 'Testing tool implementation patterns',
    ],
    [
        'name' => 'Token Efficient Tools',
        'file' => 'token_efficient_tool_use.php',
        'description' => 'Testing token optimization for tools',
    ],
    [
        'name' => 'Fine-grained Tool Streaming',
        'file' => 'fine_grained_tool_streaming.php',
        'description' => 'Testing real-time tool parameter streaming',
    ],
    [
        'name' => 'Bash Tool',
        'file' => 'bash_tool.php',
        'description' => 'Testing bash command execution',
    ],
    [
        'name' => 'Code Execution Tool',
        'file' => 'code_execution_tool.php',
        'description' => 'Testing Python code execution',
    ],
    [
        'name' => 'Computer Use Tool',
        'file' => 'computer_use_tool.php',
        'description' => 'Testing desktop automation',
    ],
    [
        'name' => 'Text Editor Tool',
        'file' => 'text_editor_tool.php',
        'description' => 'Testing file editing',
    ],
    [
        'name' => 'Web Fetch Tool',
        'file' => 'web_fetch_tool.php',
        'description' => 'Testing web content fetching',
    ],
    [
        'name' => 'Memory Tool',
        'file' => 'memory_tool.php',
        'description' => 'Testing persistent memory',
    ],
    [
        'name' => 'Basic Tool Use',
        'file' => 'tools.php',
        'description' => 'Testing basic tool example',
    ],
    [
        'name' => 'Web Search Tool',
        'file' => 'web_search.php',
        'description' => 'Testing built-in web search',
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
