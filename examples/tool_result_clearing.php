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
                'content' => 'Search for recent developments in AI',
            ],
        ],
        'tools' => [
            [
                'type' => 'web_search_20250305',
                'name' => 'web_search',
            ],
        ],
        'betas' => ['context-management-2025-06-27'],
        'context_management' => [
            'edits' => [
                ['type' => 'clear_tool_uses_20250919'],
            ],
        ],
    ]);

    print_r($response);
} catch (NotFoundError $e) {
    echo "This example requires the context-management beta feature flag.\n";
    echo "Please reach out to Anthropic to enable it on your account.\n";
}
