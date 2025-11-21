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

$systemPrompt = [
    [
        'type' => 'text',
        'text' => 'You are an AI assistant that is tasked with literary analysis. Analyze the following text carefully.',
    ],
    [
        'type' => 'text',
        'text' => $largeText,
        'cache_control' => ['type' => 'ephemeral'],
    ],
];

$messages = [
    [
        'role' => 'user',
        'content' => 'Analyze the tone of this passage.',
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
    'system' => $systemPrompt,
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
    'system' => $systemPrompt,
    'messages' => $messages,
]);

print_r($response2->usage);

echo "\nThird request - different thinking parameters (cache miss for messages)\n";
$response3 = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 20_000,
    'thinking' => [
        'type' => 'enabled',
        'budget_tokens' => 8000,
    ],
    'system' => $systemPrompt,
    'messages' => $messages,
]);

print_r($response3->usage);
