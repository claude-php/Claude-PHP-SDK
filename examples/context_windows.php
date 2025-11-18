#!/usr/bin/env php
<?php
/**
 * Context Windows - PHP examples from:
 * https://docs.claude.com/en/docs/build-with-claude/context-windows
 * 
 * Demonstrates context window management, token tracking, and long context features.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Context Windows - Token Management Examples ===\n\n";

// Example 1: Understanding token usage in basic requests
echo "Example 1: Basic Token Usage\n";
echo "-----------------------------\n";
echo "The context window includes all input and output tokens.\n";
echo "Standard context window: 200,000 tokens\n\n";

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            ['role' => 'user', 'content' => 'Explain context windows in one sentence.']
        ]
    ]);

    echo "Response: ";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo $block['text'] . "\n";
        }
    }
    
    echo "\nToken Usage:\n";
    echo "  Input tokens:  {$response->usage->input_tokens}\n";
    echo "  Output tokens: {$response->usage->output_tokens}\n";
    $total = $response->usage->input_tokens + $response->usage->output_tokens;
    echo "  Total used:    {$total}\n";
    echo "  Remaining:     " . (200000 - $total) . " tokens (in 200K window)\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: Multi-turn conversation with token accumulation
echo "Example 2: Multi-turn Token Accumulation\n";
echo "-----------------------------------------\n";
echo "Each turn accumulates tokens. Previous turns are preserved completely.\n\n";

try {
    // First turn
    $messages = [
        ['role' => 'user', 'content' => 'Hi! My name is Alice.']
    ];
    
    $response1 = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => $messages
    ]);
    
    echo "Turn 1:\n";
    echo "  User: Hi! My name is Alice.\n";
    echo "  Assistant: ";
    $assistantResponse1 = '';
    foreach ($response1->content as $block) {
        if ($block['type'] === 'text') {
            $assistantResponse1 = $block['text'];
            echo $assistantResponse1 . "\n";
        }
    }
    echo "  Tokens: {$response1->usage->input_tokens} in, {$response1->usage->output_tokens} out\n\n";
    
    // Second turn - includes full history
    $messages[] = ['role' => 'assistant', 'content' => $response1->content];
    $messages[] = ['role' => 'user', 'content' => 'What is my name?'];
    
    $response2 = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => $messages
    ]);
    
    echo "Turn 2:\n";
    echo "  User: What is my name?\n";
    echo "  Assistant: ";
    foreach ($response2->content as $block) {
        if ($block['type'] === 'text') {
            echo $block['text'] . "\n";
        }
    }
    echo "  Tokens: {$response2->usage->input_tokens} in, {$response2->usage->output_tokens} out\n";
    echo "  Note: Input tokens increased because it includes all previous conversation\n";
    
    $totalTokens = $response2->usage->input_tokens + $response2->usage->output_tokens;
    echo "\n  Total context used: {$totalTokens} tokens\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 3: Token estimation (manual approach)
echo "Example 3: Token Estimation\n";
echo "---------------------------\n";
echo "You can estimate tokens manually (~4 chars per token on average).\n\n";

try {
    $testMessage = 'Write a comprehensive guide about quantum computing, covering its principles, applications, and future prospects.';
    
    // Rough estimation: ~4 characters per token
    $estimatedTokens = (int)(strlen($testMessage) / 4);
    
    echo "Message: '{$testMessage}'\n";
    echo "Character count: " . strlen($testMessage) . "\n";
    echo "Estimated tokens: ~{$estimatedTokens} (using 4 chars/token rule)\n\n";
    
    echo "Planning token budget:\n";
    echo "  Input estimate: ~{$estimatedTokens}\n";
    echo "  Max output:     4096\n";
    echo "  Total estimate: ~" . ($estimatedTokens + 4096) . " tokens\n";
    echo "  Remaining:      ~" . (200000 - ($estimatedTokens + 4096)) . " tokens\n\n";
    
    echo "Note: For accurate counts, send a test request and check response.usage\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 4: Extended thinking with automatic token management
echo "Example 4: Extended Thinking Token Management\n";
echo "----------------------------------------------\n";
echo "With extended thinking, previous thinking blocks are automatically stripped\n";
echo "from the context window by the API (you don't need to do this yourself).\n\n";

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 4096,
        'thinking' => [
            'type' => 'enabled',
            'budget_tokens' => 2000
        ],
        'messages' => [
            ['role' => 'user', 'content' => 'What is 15 * 24? Think through this step by step.']
        ]
    ]);

    echo "Request with extended thinking enabled\n";
    echo "Budget tokens: 2000 (subset of max_tokens: 4096)\n\n";
    
    $thinkingTokens = 0;
    $textTokens = 0;
    
    foreach ($response->content as $block) {
        if ($block['type'] === 'thinking') {
            $thinkingTokens = strlen($block['thinking']) / 4; // Rough estimate
            echo "Thinking block generated (not shown)\n";
        } elseif ($block['type'] === 'text') {
            echo "Answer: " . $block['text'] . "\n";
        }
    }
    
    echo "\nToken breakdown:\n";
    echo "  Input tokens:  {$response->usage->input_tokens}\n";
    echo "  Output tokens: {$response->usage->output_tokens}\n";
    echo "  Note: Thinking tokens are billed as output tokens\n";
    echo "  Note: When passed back in multi-turn, thinking blocks are auto-stripped\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 5: Context awareness (built-in for Sonnet 4.5 and Haiku 4.5)
echo "Example 5: Context Awareness in Claude 4.5 Models\n";
echo "--------------------------------------------------\n";
echo "Claude Sonnet 4.5 and Haiku 4.5 feature context awareness.\n";
echo "The model knows its token budget and remaining capacity.\n\n";

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            [
                'role' => 'user',
                'content' => 'You have a 200K token context window. How does knowing this help you?'
            ]
        ]
    ]);

    echo "Claude's understanding of its context window:\n";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            $text = $block['text'];
            if (strlen($text) > 300) {
                $text = substr($text, 0, 300) . '...';
            }
            echo $text . "\n";
        }
    }
    
    echo "\nKey benefits:\n";
    echo "  • Claude knows: <budget:token_budget>200000</budget:token_budget>\n";
    echo "  • After tool calls: <system_warning>Token usage: X/200000; Y remaining</system_warning>\n";
    echo "  • Helps with long-running agent sessions\n";
    echo "  • Better token management for complex tasks\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 6: 1M token context window (Beta)
echo "Example 6: 1M Token Context Window (Beta)\n";
echo "------------------------------------------\n";
echo "Available for Claude Sonnet 4 and 4.5 (usage tier 4 or custom rate limits)\n";
echo "Requires 'context-1m-2025-08-07' beta header\n\n";

// Demonstrate the concept even if not available to this account
$longDocument = str_repeat("This is a paragraph about quantum computing and its applications. ", 100);
$estimatedTokens = (int)(strlen($longDocument) / 4);

echo "Example code:\n";
echo "```php\n";
echo "\$response = \$client->beta()->messages()->create([\n";
echo "    'model' => 'claude-sonnet-4-5',\n";
echo "    'max_tokens' => 1024,\n";
echo "    'betas' => ['context-1m-2025-08-07'],\n";
echo "    'messages' => [\n";
echo "        ['role' => 'user', 'content' => \$largeDocument]\n";
echo "    ]\n";
echo "]);\n";
echo "```\n\n";

echo "Would send document with ~{$estimatedTokens} tokens\n";
echo "Context window: 1,000,000 tokens (vs standard 200,000)\n\n";

echo "Important notes:\n";
echo "  • Beta feature (subject to change)\n";
echo "  • Requires usage tier 4 or custom rate limits\n";
echo "  • Premium pricing for >200K tokens (2x input, 1.5x output)\n";
echo "  • Available on API, Bedrock, and Vertex AI\n";
echo "  • Dedicated rate limits for long context requests\n\n";

echo "Note: This example doesn't make an actual API call as it requires tier 4 access\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 7: Managing context window limits
echo "Example 7: Context Window Management\n";
echo "-------------------------------------\n";
echo "Newer Claude models return validation errors instead of truncating.\n\n";

function estimateContextUsage(array $messages, int $maxTokens): array {
    // Rough estimation: ~4 characters per token
    $inputEstimate = 0;
    foreach ($messages as $message) {
        if (is_string($message['content'])) {
            $inputEstimate += strlen($message['content']) / 4;
        }
    }
    
    $totalEstimate = $inputEstimate + $maxTokens;
    $remaining = 200000 - $totalEstimate;
    
    return [
        'input_estimate' => (int)$inputEstimate,
        'max_output' => $maxTokens,
        'total_estimate' => (int)$totalEstimate,
        'remaining' => (int)$remaining,
        'within_limit' => $remaining > 0
    ];
}

$testMessages = [
    ['role' => 'user', 'content' => 'Explain machine learning.'],
    ['role' => 'assistant', 'content' => 'Machine learning is...'],
    ['role' => 'user', 'content' => 'Tell me more about neural networks.']
];

$estimate = estimateContextUsage($testMessages, 4096);

echo "Context usage estimate:\n";
echo "  Input tokens (est):  {$estimate['input_estimate']}\n";
echo "  Max output tokens:   {$estimate['max_output']}\n";
echo "  Total estimate:      {$estimate['total_estimate']}\n";
echo "  Remaining in 200K:   {$estimate['remaining']}\n";
echo "  Within limit?        " . ($estimate['within_limit'] ? 'Yes ✓' : 'No ✗') . "\n";

echo "\nBest practices:\n";
echo "  • Use countTokens() API for accurate estimates\n";
echo "  • Monitor token usage in multi-turn conversations\n";
echo "  • Consider summarization for very long conversations\n";
echo "  • Use 1M context window for large documents (if eligible)\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "✓ All context window examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "1. Standard context window: 200,000 tokens\n";
echo "2. Tokens accumulate linearly in multi-turn conversations\n";
echo "3. Extended thinking blocks auto-stripped in subsequent turns\n";
echo "4. Claude 4.5 models have native context awareness\n";
echo "5. 1M token window available (beta, tier 4+)\n";
echo "6. Use countTokens() API for accurate planning\n";
echo "7. Models return errors instead of truncating (Claude 3.7+)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Context Window Sizes by Model:\n";
echo "  Claude Sonnet 4.5:  200K tokens (1M with beta)\n";
echo "  Claude Sonnet 4:    200K tokens (1M with beta)\n";
echo "  Claude Haiku 4.5:   200K tokens\n";
echo "  Claude Opus 4.1:    200K tokens\n\n";

echo "Related examples:\n";
echo "  • examples/thinking.php - Extended thinking with token management\n";
echo "  • examples/tools.php - Tool use with context awareness\n";
echo "  • examples/working_with_messages.php - Multi-turn conversations\n";

