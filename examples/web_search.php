#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "Web Search Example:\n";
echo "===================\n\n";

// Create a message with web search enabled
$message = $client->messages()->create([
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 1024,
    'messages' => [
        [
            'role' => 'user',
            'content' => 'What is the weather in New York?',
        ],
    ],
    'tools' => [
        [
            'name' => 'web_search',
            'type' => 'web_search_20250305',
        ],
    ],
]);

// Print the response content
echo "Response content:\n";
foreach ($message->content as $block) {
    if ($block['type'] === 'text') {
        echo $block['text'] . "\n";
    }
}

// Print usage information
echo "\nUsage statistics:\n";
echo "Input tokens: {$message->usage->input_tokens}\n";
echo "Output tokens: {$message->usage->output_tokens}\n";

if ($message->usage->server_tool_use) {
    echo "Web search requests: {$message->usage->server_tool_use['web_search_requests']}\n";
}
