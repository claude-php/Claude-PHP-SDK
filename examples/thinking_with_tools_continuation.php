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

echo "Starting conversation...\n";

$firstResponse = $client->messages()->create([
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

$thinkingBlock = null;
$toolUseBlock = null;

foreach ($firstResponse->content as $block) {
    if (($block['type'] ?? null) === 'thinking') {
        $thinkingBlock = $block;
    } elseif (($block['type'] ?? null) === 'tool_use') {
        $toolUseBlock = $block;
    }
}

if (!$thinkingBlock || !$toolUseBlock) {
    throw new RuntimeException('Expected thinking and tool_use blocks in the first response.');
}

echo "Claude requested tool `{$toolUseBlock['name']}` with input: "
    . json_encode($toolUseBlock['input']) . PHP_EOL;

// Pretend we ran the real tool
$weatherData = ['temperature' => 88];
$toolResultText = "Current temperature: {$weatherData['temperature']}°F";

$continuation = $client->messages()->create([
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
        [
            'role' => 'assistant',
            'content' => [$thinkingBlock, $toolUseBlock],
        ],
        [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'tool_result',
                    'tool_use_id' => $toolUseBlock['id'],
                    'content' => [
                        ['type' => 'text', 'text' => $toolResultText],
                    ],
                ],
            ],
        ],
    ],
]);

echo "\nFinal response:\n";
foreach ($continuation->content as $block) {
    if (($block['type'] ?? null) === 'text') {
        echo $block['text'] . PHP_EOL;
    }
}
