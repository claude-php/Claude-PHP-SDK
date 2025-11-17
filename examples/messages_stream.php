#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;
use ClaudePhp\Lib\Streaming\MessageStream;
use ClaudePhp\Responses\Helpers\MessageContentHelper;
use ClaudePhp\Responses\Helpers\StreamEventHelper;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "Streaming message example:\n";
echo "==========================\n\n";

$rawStream = $client->messages()->stream([
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 800,
    'metadata' => ['run_id' => uniqid('example_', true)],
    'messages' => [
        [
            'role' => 'user',
            'content' => "List one interesting Anthropic fact and emit JSON:\n"
                . "```json\n{\"fact\": \"...\"}\n```",
        ],
    ],
]);

$stream = new MessageStream($rawStream);
$contentBlockIndex = 0;

foreach ($stream as $event) {
    $type = $event['type'] ?? 'unknown';

    switch ($type) {
        case 'message_start':
            $message = $event['message'] ?? [];
            $model = $message['model'] ?? 'unknown';
            $inputTokens = $message['usage']['input_tokens'] ?? 0;
            echo "[message_start] model={$model}, input_tokens={$inputTokens}\n";
            break;

        case 'content_block_start':
            $contentBlockIndex = (int) ($event['index'] ?? $contentBlockIndex);
            $blockType = $event['content_block']['type'] ?? 'text';
            echo "\n[content_block_start #{$contentBlockIndex}] type={$blockType}\n";
            break;

        case 'content_block_delta':
            if (StreamEventHelper::isTextDelta($event)) {
                echo StreamEventHelper::textDelta($event) ?? '';
            } elseif (StreamEventHelper::isInputJsonDelta($event)) {
                echo StreamEventHelper::inputJsonDelta($event) ?? '';
            }
            flush();
            break;

        case 'content_block_stop':
            echo "\n[content_block_stop]\n";
            break;

        case 'message_delta':
            $usage = $event['usage'] ?? [];
            if (isset($usage['output_tokens'])) {
                echo "\n[message_delta] output_tokens={$usage['output_tokens']}\n";
            }
            break;

        case 'message_stop':
            $stopReason = $event['stop_reason'] ?? 'unknown';
            echo "[message_stop] reason={$stopReason}\n";
            break;
    }
}

$finalMessage = $stream->getFinalMessage();
$text = MessageContentHelper::text($finalMessage);

echo "\nFinal text content:\n--------------------\n{$text}\n";
echo "\nStream complete! Accumulated text length: " . strlen($stream->textStream()) . " chars\n";
