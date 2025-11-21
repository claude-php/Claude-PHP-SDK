#!/usr/bin/env php
<?php
/**
 * Getting Started Examples - PHP versions of the Python examples from:
 * https://docs.claude.com/en/docs/get-started
 * 
 * This file demonstrates the basic usage patterns for the Claude PHP SDK.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

// Load environment variables and create client
loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Claude PHP SDK - Getting Started Examples ===\n\n";

// Example 1: Simple Web Search Assistant (from the docs homepage)
echo "Example 1: Simple Web Search Assistant\n";
echo "----------------------------------------\n";
try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1000,
        'messages' => [
            [
                'role' => 'user',
                'content' => 'What should I search for to find the latest developments in renewable energy?'
            ]
        ]
    ]);

    echo "Response:\n";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo $block['text'] . "\n";
        }
    }
    echo "\nUsage: {$response->usage->input_tokens} input tokens, {$response->usage->output_tokens} output tokens\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: Basic Hello World
echo "Example 2: Basic Hello World\n";
echo "-----------------------------\n";
try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Hello, Claude!'
            ]
        ]
    ]);

    echo "Claude's response: ";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo $block['text'] . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 3: Multiple Conversational Turns
echo "Example 3: Multiple Conversational Turns\n";
echo "-----------------------------------------\n";
try {
    $messages = [
        [
            'role' => 'user',
            'content' => 'Hello, Claude'
        ],
        [
            'role' => 'assistant',
            'content' => 'Hello! How can I assist you today?'
        ],
        [
            'role' => 'user',
            'content' => 'Can you explain what LLMs are in one sentence?'
        ]
    ];

    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => $messages
    ]);

    echo "Conversation:\n";
    echo "User: Hello, Claude\n";
    echo "Assistant: Hello! How can I assist you today?\n";
    echo "User: Can you explain what LLMs are in one sentence?\n";
    echo "Assistant: ";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo $block['text'] . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 4: System Prompt
echo "Example 4: Using System Prompts\n";
echo "--------------------------------\n";
try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'system' => 'You are a helpful AI assistant specializing in renewable energy.',
        'messages' => [
            [
                'role' => 'user',
                'content' => 'What are the three main types of renewable energy?'
            ]
        ]
    ]);

    echo "Response with system prompt:\n";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo $block['text'] . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 5: Prefilling Claude's Response
echo "Example 5: Prefilling Claude's Response\n";
echo "----------------------------------------\n";
try {
    $messages = [
        [
            'role' => 'user',
            'content' => 'What is the capital of France?'
        ],
        [
            'role' => 'assistant',
            'content' => 'The capital of France is'
        ]
    ];

    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 100,
        'messages' => $messages
    ]);

    echo "Prefilled response: The capital of France is";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo $block['text'] . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 6: Temperature and Response Control
echo "Example 6: Temperature and Response Control\n";
echo "--------------------------------------------\n";
try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 200,
        'temperature' => 0.0, // More deterministic
        'messages' => [
            [
                'role' => 'user',
                'content' => 'List three primary colors.'
            ]
        ]
    ]);

    echo "Response with temperature=0.0 (deterministic):\n";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo $block['text'] . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "✓ All examples completed successfully!\n";
echo "\nNext steps:\n";
echo "- Check out examples/messages_stream.php for streaming responses\n";
echo "- See examples/tools.php for function calling examples\n";
echo "- Explore examples/images.php for vision capabilities\n";
echo "- Try examples/thinking.php for extended reasoning\n";

