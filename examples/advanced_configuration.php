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
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 4096,
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Create a simple command line calculator app using Python',
            ],
        ],
        'tools' => [
            [
                'type' => 'text_editor_20250728',
                'name' => 'str_replace_based_edit_tool',
                'max_characters' => 10000,
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
                    'type' => 'clear_tool_uses_20250919',
                    'trigger' => [
                        'type' => 'input_tokens',
                        'value' => 30000,
                    ],
                    'keep' => [
                        'type' => 'tool_uses',
                        'value' => 3,
                    ],
                    'clear_at_least' => [
                        'type' => 'input_tokens',
                        'value' => 5000,
                    ],
                    'exclude_tools' => ['web_search'],
                ],
            ],
        ],
    ]);

    print_r($response);
} catch (NotFoundError $e) {
    echo "This example requires access to the context-management beta features.\n";
    echo "Your API key does not currently have the required feature flag.\n";
}
