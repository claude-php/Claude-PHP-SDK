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
                'content' => 'Design a knowledge management workflow using CLI tools.',
            ],
        ],
        'thinking' => [
            'type' => 'enabled',
            'budget_tokens' => 10_000,
        ],
        'tools' => [
            [
                'type' => 'text_editor_20250728',
                'name' => 'editor',
                'max_characters' => 8_000,
            ],
            [
                'type' => 'web_search_20250305',
                'name' => 'web_search',
                'max_uses' => 3,
            ],
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
                [
                    'type' => 'clear_tool_uses_20250919',
                    'trigger' => [
                        'type' => 'input_tokens',
                        'value' => 50_000,
                    ],
                    'keep' => [
                        'type' => 'tool_uses',
                        'value' => 5,
                    ],
                ],
            ],
        ],
    ]);

    print_r($response);
} catch (NotFoundError $e) {
    echo "This example requires the context-management beta feature flag.\n";
    echo "Please contact Anthropic to enable it on your API key.\n";
}
