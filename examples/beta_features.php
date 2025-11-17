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

// Example 1: Using a single beta feature
echo "1. Using a single beta feature:\n";
echo "   Requesting with betas=['prompt-caching-2024-07-31']\n\n";

try {
    $response = $client->beta()->messages()->create([
        'model' => 'claude-sonnet-4-5-20250929',
        'max_tokens' => 1024,
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Hello! Tell me about beta features.',
            ],
        ],
        'betas' => ['prompt-caching-2024-07-31'],
    ]);

    echo "Response received:\n";
    echo "ID: {$response->id}\n";
    echo "Model: {$response->model}\n";
    echo "Content: " . json_encode($response->content) . "\n\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Example 2: Using multiple beta features
echo "2. Using multiple beta features:\n";
echo "   Requesting with betas=['feature-1', 'feature-2']\n";
echo "   (Note: These are example feature names)\n\n";

try {
    $response = $client->beta()->messages()->create([
        'model' => 'claude-sonnet-4-5-20250929',
        'max_tokens' => 1024,
        'messages' => [
            [
                'role' => 'user',
                'content' => 'What can you tell me?',
            ],
        ],
        'betas' => ['prompt-caching-2024-07-31', 'thinking-2024-11-28'],
    ]);

    echo "Response received successfully.\n\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Example 3: Structured outputs with automatic beta injection
echo "3. Structured outputs (automatically adds structured-outputs beta):\n\n";

try {
    $schema = [
        'type' => 'object',
        'properties' => [
            'name' => ['type' => 'string'],
            'age' => ['type' => 'integer'],
        ],
        'required' => ['name', 'age'],
    ];

    // The parse() method automatically adds the structured-outputs beta
    $result = $client->beta()->messages()->parse([
        'model' => 'claude-sonnet-4-5-20250929',
        'max_tokens' => 1024,
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Extract: John Smith is 30 years old',
            ],
        ],
        'output_format' => $schema,
        // Note: structured-outputs-2025-09-17 beta is automatically added
    ]);

    echo "Parsed result:\n";
    print_r($result);
    echo "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Example 4: Combining custom betas with structured outputs
echo "4. Combining custom betas with structured outputs:\n\n";

try {
    $schema = [
        'type' => 'object',
        'properties' => [
            'summary' => ['type' => 'string'],
        ],
        'required' => ['summary'],
    ];

    $result = $client->beta()->messages()->parse([
        'model' => 'claude-sonnet-4-5-20250929',
        'max_tokens' => 1024,
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Summarize: The quick brown fox jumps over the lazy dog.',
            ],
        ],
        'output_format' => $schema,
        'betas' => ['prompt-caching-2024-07-31'], // Will be merged with structured-outputs
    ]);

    echo "Parsed result with custom beta:\n";
    print_r($result);
    echo "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "=== Examples Complete ===\n";
echo "\nNOTE: The SDK automatically converts the 'betas' parameter to the 'anthropic-beta' HTTP header.\n";
echo "You don't need to manually set headers - just pass the beta feature names in the 'betas' array.\n";
