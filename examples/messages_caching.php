#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');

$client = new ClaudePhp(apiKey: getApiKey(), timeout: 120.0);

$bookUrl = 'https://www.gutenberg.org/cache/epub/1342/pg1342.txt';
$bookContent = @file_get_contents($bookUrl);

if ($bookContent === false) {
    throw new RuntimeException("Unable to fetch {$bookUrl}");
}

$largeText = substr($bookContent, 0, 5000);

$messages = [
    [
        'role' => 'user',
        'content' => [
            [
                'type' => 'text',
                'text' => $largeText,
                'cache_control' => ['type' => 'ephemeral'],
            ],
            [
                'type' => 'text',
                'text' => 'Analyze the tone of this passage.',
            ],
        ],
    ],
];

$thinking4000 = [
    'type' => 'enabled',
    'budget_tokens' => 4000,
];

echo "First request - establishing cache\n";
$response1 = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 20_000,
    'thinking' => $thinking4000,
    'messages' => $messages,
]);
print_r($response1->usage);

$messages[] = [
    'role' => 'assistant',
    'content' => $response1->content,
];
$messages[] = [
    'role' => 'user',
    'content' => 'Analyze the characters in this passage.',
];

echo "\nSecond request - same thinking parameters (cache hit expected)\n";
$response2 = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 20_000,
    'thinking' => $thinking4000,
    'messages' => $messages,
]);
print_r($response2->usage);

$messages[] = [
    'role' => 'assistant',
    'content' => $response2->content,
];
$messages[] = [
    'role' => 'user',
    'content' => 'Analyze the setting in this passage.',
];

echo "\nThird request - different thinking budget (cache miss expected)\n";
$response3 = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 20_000,
    'thinking' => [
        'type' => 'enabled',
        'budget_tokens' => 8000,
    ],
    'messages' => $messages,
]);
print_r($response3->usage);
