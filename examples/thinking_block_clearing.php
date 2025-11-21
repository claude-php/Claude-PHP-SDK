#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;
use ClaudePhp\Exceptions\NotFoundError;

loadEnv(__DIR__ . '/../.env');

$client = new ClaudePhp(apiKey: getApiKey());

try {
    $response = $client->beta()->messages()->create([
        'model' => 'claude-sonnet-4-5-20250929',
        'max_tokens' => 1024,
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Walk through your reasoning to design a small todo CLI app.',
            ],
        ],
        'thinking' => [
            'type' => 'enabled',
            'budget_tokens' => 10_000,
        ],
        'betas' => ['context-management-2025-06-27'],
        'context_management' => [
            'edits' => [
                [
                    'type' => 'clear_thinking_20251015',
                    'keep' => [
                        'type' => 'thinking_turns',
                        'value' => 2,
                    ],
                ],
            ],
        ],
    ]);

    print_r($response);
} catch (NotFoundError $e) {
    echo "This example requires the context-management beta feature flag.\n";
    echo "Please contact Anthropic to enable it for your key.\n";
}
