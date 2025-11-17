#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 3200,
    'thinking' => [
        'type' => 'enabled',
        'budget_tokens' => 1600,
    ],
    'messages' => [
        [
            'role' => 'user',
            'content' => 'Create a haiku about Anthropic.',
        ],
    ],
]);

echo "Extended Thinking Example:\n";
echo "==========================\n\n";

foreach ($response->content as $block) {
    if ($block['type'] === 'thinking') {
        echo "Thinking: " . $block['thinking'] . "\n\n";
    } elseif ($block['type'] === 'text') {
        echo "Text: " . $block['text'] . "\n";
    }
}
