#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');

$client = new ClaudePhp(apiKey: getApiKey());

$weatherTool = [
    'name' => 'get_weather',
    'description' => 'Get current weather for a location',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'location' => ['type' => 'string'],
        ],
        'required' => ['location'],
    ],
];

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 16_000,
    'thinking' => [
        'type' => 'enabled',
        'budget_tokens' => 10_000,
    ],
    'tools' => [$weatherTool],
    'messages' => [
        [
            'role' => 'user',
            'content' => "What's the weather in Paris?",
        ],
    ],
]);

foreach ($response->content as $block) {
    $type = $block['type'] ?? '';
    if ($type === 'thinking') {
        echo "\nThinking summary:\n";
        echo trim($block['thinking'] ?? '') . PHP_EOL;
    } elseif ($type === 'tool_use') {
        echo "\nTool request:\n";
        echo "Tool: {$block['name']} (ID: {$block['id']})\n";
        echo 'Input: ' . json_encode($block['input'], JSON_PRETTY_PRINT) . PHP_EOL;
    } elseif ($type === 'text') {
        echo "\nResponse text:\n";
        echo trim($block['text'] ?? '') . PHP_EOL;
    }
}
