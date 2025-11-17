#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

$response = $client->messages()->create([
    'max_tokens' => 1024,
    'messages' => [
        [
            'role' => 'user',
            'content' => 'Hello!',
        ],
    ],
    'model' => 'claude-sonnet-4-5-20250929',
]);

echo "First response:\n";
print_r($response);
echo "\n\n";

// Follow-up conversation with previous response
$response2 = $client->messages()->create([
    'max_tokens' => 1024,
    'messages' => [
        [
            'role' => 'user',
            'content' => 'Hello!',
        ],
        [
            'role' => $response->role,
            'content' => $response->content,
        ],
        [
            'role' => 'user',
            'content' => 'How are you?',
        ],
    ],
    'model' => 'claude-sonnet-4-5-20250929',
]);

echo "Second response:\n";
print_r($response2);
