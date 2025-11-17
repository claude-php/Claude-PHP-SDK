#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;
use ClaudePhp\Responses\Helpers\StreamEventHelper;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

$stream = $client->messages()->stream([
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 3200,
    'thinking' => [
        'type' => 'enabled',
        'budget_tokens' => 1600,
    ],
    'messages' => [
        [
            'role' => 'user',
            'content' => 'Create a haiku about Anthropic.',
        ],
    ],
]);

$thinking = 'not-started';

foreach ($stream as $event) {
    if (($event['type'] ?? null) === 'content_block_delta') {
        if (isset($event['delta']['thinking'])) {
            if ($thinking === 'not-started') {
                echo "Thinking:\n---------\n";
                $thinking = 'started';
            }
            echo $event['delta']['thinking'];
            flush();
        } elseif (StreamEventHelper::isTextDelta($event)) {
            if ($thinking !== 'finished') {
                echo "\n\nText:\n-----\n";
                $thinking = 'finished';
            }
            echo StreamEventHelper::textDelta($event);
            flush();
        }
    }

    if (StreamEventHelper::isMessageStop($event)) {
        echo "\n\n[message_stop]\n";
    }
}

echo "\n";
