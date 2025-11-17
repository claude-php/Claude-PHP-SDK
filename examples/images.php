#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

// Read the logo image and encode as base64
$imagePath = __DIR__ . '/logo.png';
if (!file_exists($imagePath)) {
    die("Error: logo.png not found in examples directory\n");
}

$imageData = base64_encode(file_get_contents($imagePath));

$response = $client->messages()->create([
    'max_tokens' => 1024,
    'messages' => [
        [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'text',
                    'text' => 'What is in this image? Describe it briefly.',
                ],
                [
                    'type' => 'image',
                    'source' => [
                        'type' => 'base64',
                        'media_type' => 'image/png',
                        'data' => $imageData,
                    ],
                ],
            ],
        ],
    ],
    'model' => 'claude-sonnet-4-5-20250929',
]);

echo "Response:\n";
foreach ($response->content as $block) {
    if ($block['type'] === 'text') {
        echo $block['text'] . "\n";
    }
}
