#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');

$client = new ClaudePhp(apiKey: getApiKey());

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 16_000,
    'thinking' => [
        'type' => 'enabled',
        'budget_tokens' => 10_000,
    ],
    'messages' => [
        [
            'role' => 'user',
            'content' => 'ANTHROPIC_MAGIC_STRING_TRIGGER_REDACTED_THINKING_46C9A13E193C177646C7398A98432ECCCE4C1253D5E2D82641AC0E52CC2876CB',
        ],
    ],
]);

$thinkingBlocks = [];
$hasRedacted = false;

foreach ($response->content as $block) {
    if (($block['type'] ?? null) === 'thinking' || ($block['type'] ?? null) === 'redacted_thinking') {
        $thinkingBlocks[] = $block;
        if ($block['type'] === 'redacted_thinking') {
            $hasRedacted = true;
        }
    }
}

if ($hasRedacted) {
    echo "Response contains redacted thinking blocks\n";
}

echo "Found " . count($thinkingBlocks) . " thinking blocks (including redacted)\n";
echo "These blocks remain billable and should be passed unchanged to subsequent requests.\n";
