#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');

$client = new ClaudePhp(apiKey: getApiKey());

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1,
    'messages' => [
        [
            'role' => 'user',
            'content' => 'What is latin for Ant? (A) Apoidea, (B) Rhopalocera, (C) Formicidae',
        ],
        [
            'role' => 'assistant',
            'content' => 'The answer is (',
        ],
    ],
]);

print_r($response);
