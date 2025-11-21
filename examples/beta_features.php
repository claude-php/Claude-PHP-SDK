#!/usr/bin/env php
<?php

/**
 * Example: Using Beta Features with the anthropic-beta Header
 *
 * This example demonstrates how to use beta features by passing the `betas` parameter.
 * The SDK automatically converts the `betas` array into the proper `anthropic-beta` HTTP header.
 *
 * According to the API documentation, beta features must be specified in the
 * `anthropic-beta` header as a comma-separated list of feature names.
 *
 * @see https://docs.claude.com/en/api/beta-headers
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Beta Features Example ===\n\n";

// Example 1: Using prompt caching beta feature
echo "1. Using prompt caching beta feature:\n";
echo "   Requesting with betas=['prompt-caching-2024-07-31']\n\n";

try {
    $response = $client->beta()->messages()->create([
        'model' => 'claude-sonnet-4-5-20250929',
        'max_tokens' => 1024,
        'system' => [
            [
                'type' => 'text',
                'text' => 'You are a helpful assistant.',
                'cache_control' => ['type' => 'ephemeral']
            ]
        ],
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Hello! Tell me about prompt caching.',
            ],
        ],
        'betas' => ['prompt-caching-2024-07-31'],
    ]);

    echo "Response received:\n";
    echo "ID: {$response->id}\n";
    echo "Model: {$response->model}\n";
    if (isset($response->usage->cache_creation_input_tokens)) {
        echo "Cache tokens created: {$response->usage->cache_creation_input_tokens}\n";
    }
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo "Content: " . substr($block['text'], 0, 100) . "...\n";
        }
    }
    echo "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Example 2: Using multiple API calls with caching
echo "2. Making multiple requests with prompt caching:\n";
echo "   First request creates cache, second request reads from cache\n\n";

try {
    // First request - creates cache
    $response1 = $client->beta()->messages()->create([
        'model' => 'claude-sonnet-4-5-20250929',
        'max_tokens' => 100,
        'system' => [
            [
                'type' => 'text',
                'text' => 'You are an expert in mathematics. Always show your work.',
                'cache_control' => ['type' => 'ephemeral']
            ]
        ],
        'messages' => [
            ['role' => 'user', 'content' => 'What is 25 * 47?'],
        ],
        'betas' => ['prompt-caching-2024-07-31'],
    ]);

    echo "First request:\n";
    if (isset($response1->usage->cache_creation_input_tokens)) {
        echo "  Cache created: {$response1->usage->cache_creation_input_tokens} tokens\n";
    }

    // Second request - uses cache
    $response2 = $client->beta()->messages()->create([
        'model' => 'claude-sonnet-4-5-20250929',
        'max_tokens' => 100,
        'system' => [
            [
                'type' => 'text',
                'text' => 'You are an expert in mathematics. Always show your work.',
                'cache_control' => ['type' => 'ephemeral']
            ]
        ],
        'messages' => [
            ['role' => 'user', 'content' => 'What is 12 * 34?'],
        ],
        'betas' => ['prompt-caching-2024-07-31'],
    ]);

    echo "Second request:\n";
    if (isset($response2->usage->cache_read_input_tokens)) {
        echo "  Cache read: {$response2->usage->cache_read_input_tokens} tokens (90% cost savings!)\n";
    }
    echo "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Example 3: Available beta features
echo "3. Current available beta features:\n\n";

echo "✓ prompt-caching-2024-07-31\n";
echo "  - Enables prompt caching for 90% cost reduction on repeated content\n";
echo "  - Use cache_control parameter on system prompts\n\n";

echo "✓ Other beta features:\n";
echo "  - Check docs.anthropic.com for current beta features\n";
echo "  - Beta feature names follow pattern: feature-name-YYYY-MM-DD\n";
echo "  - Examples from docs:\n";
echo "    • web-fetch-2025-09-10 (not in header, tool type)\n";
echo "    • context-management-2025-06-27 (for context editing)\n";
echo "    • structured-outputs-2025-11-13 (for guaranteed JSON)\n\n";

echo "Note: Not all features require the anthropic-beta header.\n";
echo "Server-side tools (web_fetch, memory, etc.) use tool 'type' field instead.\n";

// Example 4: How to find current beta features
echo "\n4. Finding and using beta features:\n\n";

echo "Documentation:\n";
echo "  • https://docs.anthropic.com/en/api/beta-headers\n";
echo "  • Check for 'anthropic-beta' header requirements\n\n";

echo "SDK Usage:\n";
echo "  \$client->beta()->messages()->create([\n";
echo "      'model' => 'claude-sonnet-4-5-20250929',\n";
echo "      'max_tokens' => 1024,\n";
echo "      'messages' => [...],\n";
echo "      'betas' => ['feature-name-YYYY-MM-DD'],\n";
echo "  ]);\n\n";

echo "The SDK automatically:\n";
echo "  ✓ Converts 'betas' array to 'anthropic-beta' header\n";
echo "  ✓ Joins multiple features with commas\n";
echo "  ✓ Removes 'betas' from request body\n";
echo "  ✓ Sends header: anthropic-beta: feature1,feature2\n";

echo "=== Examples Complete ===\n";
echo "\nNOTE: The SDK automatically converts the 'betas' parameter to the 'anthropic-beta' HTTP header.\n";
echo "You don't need to manually set headers - just pass the beta feature names in the 'betas' array.\n";
