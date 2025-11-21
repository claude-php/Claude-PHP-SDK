#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');

$client = new ClaudePhp(apiKey: getApiKey());

$stream = $client->messages()->stream([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 16_000,
    'thinking' => [
        'type' => 'enabled',
        'budget_tokens' => 10_000,
    ],
    'messages' => [
        [
            'role' => 'user',
            'content' => 'What is 27 * 453?',
        ],
    ],
]);

$thinkingStarted = false;
$responseStarted = false;

foreach ($stream as $event) {
    $type = $event['type'] ?? '';

    if ($type === 'content_block_start') {
        $blockType = $event['content_block']['type'] ?? 'unknown';
        echo "\nStarting {$blockType} block...\n";
        $thinkingStarted = false;
        $responseStarted = false;
    } elseif ($type === 'content_block_delta') {
        $deltaType = $event['delta']['type'] ?? '';
        if ($deltaType === 'thinking_delta') {
            if (!$thinkingStarted) {
                echo "Thinking: ";
                $thinkingStarted = true;
            }
            echo $event['delta']['thinking'] ?? '';
            flush();
        } elseif ($deltaType === 'text_delta') {
            if (!$responseStarted) {
                echo "Response: ";
                $responseStarted = true;
            }
            echo $event['delta']['text'] ?? '';
            flush();
        }
    } elseif ($type === 'content_block_stop') {
        echo "\nBlock complete.\n";
    } elseif ($type === 'message_stop') {
        echo "\n[message_stop]\n";
    }
}
