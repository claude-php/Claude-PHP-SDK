#!/usr/bin/env php
<?php
/**
 * Quickstart Example - Matches the exact example from:
 * https://docs.claude.com/en/docs/get-started
 * 
 * This is the simplest possible example to get started with Claude.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

// Set up your API key
loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

// Make your first API call
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1000,
    'messages' => [
        [
            'role' => 'user',
            'content' => 'What should I search for to find the latest developments in renewable energy?'
        ]
    ]
]);

// Output the response
echo "Response:\n";
foreach ($response->content as $block) {
    if ($block['type'] === 'text') {
        echo $block['text'] . "\n";
    }
}

// Show usage information
echo "\nUsage:\n";
echo "  Input tokens: {$response->usage->input_tokens}\n";
echo "  Output tokens: {$response->usage->output_tokens}\n";
