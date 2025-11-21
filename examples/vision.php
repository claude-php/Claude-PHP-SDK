#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');

$client = new ClaudePhp(apiKey: getApiKey());

$localImage = __DIR__ . '/logo.png';
if (!file_exists($localImage)) {
    throw new RuntimeException("Local image not found at {$localImage}");
}
$imageMediaType = 'image/png';
$imageData = base64_encode(file_get_contents($localImage));
$imageUrl = 'https://upload.wikimedia.org/wikipedia/commons/a/a7/Camponotus_flavomarginatus_ant.jpg';

$base64Response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => [
        [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'image',
                    'source' => [
                        'type' => 'base64',
                        'media_type' => $imageMediaType,
                        'data' => $imageData,
                    ],
                ],
                [
                    'type' => 'text',
                    'text' => 'What is in the above image?',
                ],
            ],
        ],
    ],
]);

echo "Base64 response:\n";
print_r($base64Response);

$urlResponse = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => [
        [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'image',
                    'source' => [
                        'type' => 'url',
                        'url' => $imageUrl,
                    ],
                ],
                [
                    'type' => 'text',
                    'text' => 'What is in the above image?',
                ],
            ],
        ],
    ],
]);

echo "\nURL response:\n";
print_r($urlResponse);
