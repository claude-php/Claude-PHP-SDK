#!/usr/bin/env php
<?php
/**
 * Token Counting - PHP examples from:
 * https://docs.claude.com/en/docs/build-with-claude/token-counting
 * 
 * Estimate token usage before sending requests to plan costs and context usage.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Token Counting - Plan Costs and Context ===\n\n";

// Example 1: Basic token counting
echo "Example 1: Basic Token Counting\n";
echo "--------------------------------\n";
echo "Count tokens before sending request\n\n";

try {
    $tokenCount = $client->messages()->countTokens([
        'model' => 'claude-sonnet-4-5',
        'messages' => [
            ['role' => 'user', 'content' => 'Hello, Claude! How are you today?']
        ]
    ]);
    
    echo "Message: 'Hello, Claude! How are you today?'\n";
    echo "Input tokens: {$tokenCount->input_tokens}\n\n";
    
    // Calculate estimated cost (Sonnet 4.5: $3 per MTok input)
    $estimatedCost = ($tokenCount->input_tokens / 1000000) * 3;
    echo "Estimated input cost: $" . number_format($estimatedCost, 6) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: Token counting with system prompts
echo "Example 2: Token Counting with System Prompts\n";
echo "----------------------------------------------\n\n";

try {
    $systemPrompt = "You are a helpful AI assistant specialized in Python programming. " .
        "Provide clear, concise answers with code examples when appropriate.";
    
    $tokenCount = $client->messages()->countTokens([
        'model' => 'claude-sonnet-4-5',
        'system' => $systemPrompt,
        'messages' => [
            ['role' => 'user', 'content' => 'How do I read a file in Python?']
        ]
    ]);
    
    echo "System prompt + user message\n";
    echo "Total input tokens: {$tokenCount->input_tokens}\n";
    echo "\nBreakdown (approximate):\n";
    echo "  System prompt: ~" . (strlen($systemPrompt) / 4) . " tokens\n";
    echo "  User message: ~" . (strlen('How do I read a file in Python?') / 4) . " tokens\n";
    echo "  Total counted: {$tokenCount->input_tokens} tokens\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 3: Token counting with tools
echo "Example 3: Token Counting with Tools\n";
echo "-------------------------------------\n\n";

try {
    $tools = [
        [
            'name' => 'get_weather',
            'description' => 'Get current weather in a location',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'location' => ['type' => 'string', 'description' => 'City name'],
                    'unit' => ['type' => 'string', 'enum' => ['celsius', 'fahrenheit']]
                ],
                'required' => ['location']
            ]
        ]
    ];
    
    $tokenCount = $client->messages()->countTokens([
        'model' => 'claude-sonnet-4-5',
        'tools' => $tools,
        'messages' => [
            ['role' => 'user', 'content' => 'What is the weather?']
        ]
    ]);
    
    echo "Message with tool definitions\n";
    echo "Input tokens: {$tokenCount->input_tokens}\n";
    echo "\nNote: Tool definitions add tokens to each request\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 4: Manual token estimation
echo "Example 4: Manual Token Estimation\n";
echo "-----------------------------------\n";
echo "Rule of thumb: ~4 characters per token (English text)\n\n";

$texts = [
    'Hello!' => 2,
    'How are you today?' => 5,
    'The quick brown fox jumps over the lazy dog.' => 11,
    'Artificial intelligence and machine learning' => 10
];

echo "Manual estimation examples:\n\n";
foreach ($texts as $text => $actualTokens) {
    $charCount = strlen($text);
    $estimated = (int)($charCount / 4);
    echo "Text: \"{$text}\"\n";
    echo "  Characters: {$charCount}\n";
    echo "  Estimated: ~{$estimated} tokens (using 4 char/token)\n";
    echo "  Actual: ~{$actualTokens} tokens\n\n";
}

echo "Note: Estimation varies by language, formatting, and special characters\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "✓ Token counting examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Use countTokens() to estimate costs before sending\n";
echo "• Counts include: messages, system prompts, tools\n";
echo "• Manual estimation: ~4 characters per token (English)\n";
echo "• Plan context usage to stay within limits\n";
echo "• Essential for cost optimization and context management\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • examples/context_windows.php - Context management\n";
echo "  • examples/prompt_caching.php - Cost optimization\n";
