#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');

$client = new ClaudePhp(apiKey: getApiKey());

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => [
        [
            'role' => 'user',
            'content' => 'Hello, Claude',
        ],
        [
            'role' => 'assistant',
            'content' => 'Hello!',
        ],
        [
            'role' => 'user',
            'content' => 'Can you describe LLMs to me?',
        ],
    ],
]);

print_r($response);
