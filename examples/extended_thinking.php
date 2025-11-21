#!/usr/bin/env php
<?php
/**
 * Extended Thinking - PHP examples from:
 * https://docs.claude.com/en/docs/build-with-claude/extended-thinking
 * 
 * Enhanced reasoning capabilities with step-by-step thought process visibility.
 * Supported models: Claude Sonnet 4.5, Sonnet 4, Haiku 4.5, Opus 4.1, Opus 4
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Extended Thinking - Enhanced Reasoning Capabilities ===\n\n";

// Example 1: Basic extended thinking
echo "Example 1: Basic Extended Thinking\n";
echo "-----------------------------------\n";
echo "Minimum budget: 1,024 tokens\n";
echo "Claude creates 'thinking' blocks for internal reasoning\n\n";

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 16000,
        'thinking' => [
            'type' => 'enabled',
            'budget_tokens' => 10000  // Maximum tokens for reasoning
        ],
        'messages' => [
            ['role' => 'user', 'content' => 'Are there an infinite number of prime numbers such that n mod 4 == 3?']
        ]
    ]);

    echo "Question: Are there an infinite number of prime numbers such that n mod 4 == 3?\n\n";

    foreach ($response->content as $block) {
        if ($block['type'] === 'thinking') {
            echo "Thinking (summarized):\n";
            echo substr($block['thinking'], 0, 300) . "...\n\n";

            if (isset($block['signature'])) {
                echo "Signature present: " . substr($block['signature'], 0, 50) . "...\n\n";
            }
        } elseif ($block['type'] === 'text') {
            echo "Final Answer:\n";
            echo $block['text'] . "\n";
        }
    }

    echo "\nToken usage:\n";
    echo "  Input:  {$response->usage->input_tokens}\n";
    echo "  Output: {$response->usage->output_tokens} (includes thinking tokens)\n";
    echo "\nNote: You're billed for FULL thinking tokens, not the summary you see\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: Summarized thinking (Claude 4 models)
echo "Example 2: Summarized Thinking (Claude 4 Models)\n";
echo "-------------------------------------------------\n";
echo "Claude 4 models return SUMMARIZED thinking output.\n";
echo "Key points:\n";
echo "  • Billed for full thinking tokens (not summary)\n";
echo "  • First few lines are more verbose for prompt engineering\n";
echo "  • Summarization preserves key ideas with minimal latency\n";
echo "  • Subject to change as feature improves\n\n";

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 8000,
        'thinking' => [
            'type' => 'enabled',
            'budget_tokens' => 5000
        ],
        'messages' => [
            ['role' => 'user', 'content' => 'Solve this logic puzzle: A bat and a ball cost $1.10 in total. The bat costs $1.00 more than the ball. How much does the ball cost?']
        ]
    ]);

    echo "Logic puzzle: A bat and a ball cost $1.10 in total.\n";
    echo "The bat costs $1.00 more than the ball. How much does the ball cost?\n\n";

    $thinkingTokens = 0;
    $textTokens = 0;

    foreach ($response->content as $block) {
        if ($block['type'] === 'thinking') {
            echo "Claude's thinking process (summarized):\n";
            echo $block['thinking'] . "\n\n";
        } elseif ($block['type'] === 'text') {
            echo "Answer:\n";
            echo $block['text'] . "\n";
        }
    }

    echo "\n💰 Billing Note:\n";
    echo "  Output tokens billed: {$response->usage->output_tokens}\n";
    echo "  This is the FULL thinking + text output (not just what you see)\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 3: Working with thinking budgets
echo "Example 3: Working with Thinking Budgets\n";
echo "-----------------------------------------\n";
echo "Budget optimization strategies:\n\n";

$budgetExamples = [
    [
        'task' => 'Simple math problem',
        'budget' => 1024,
        'description' => 'Minimum budget (1024 tokens) - basic reasoning'
    ],
    [
        'task' => 'Code review',
        'budget' => 5000,
        'description' => 'Medium budget - moderate complexity'
    ],
    [
        'task' => 'Complex algorithm design',
        'budget' => 16000,
        'description' => 'Large budget - comprehensive analysis'
    ],
    [
        'task' => 'Research paper analysis',
        'budget' => 32000,
        'description' => 'Maximum practical budget - use batch processing above this'
    ]
];

foreach ($budgetExamples as $example) {
    echo "Task: {$example['task']}\n";
    echo "  Budget: " . number_format($example['budget']) . " tokens\n";
    echo "  Use case: {$example['description']}\n\n";
}

echo "Tips:\n";
echo "  • Start at minimum (1024) and increase incrementally\n";
echo "  • Higher budgets improve quality but with diminishing returns\n";
echo "  • Above 32k tokens, use batch processing to avoid timeouts\n";
echo "  • Budget is a target, not strict limit\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 4: Multi-turn conversations with thinking
echo "Example 4: Multi-turn Conversations\n";
echo "------------------------------------\n";
echo "You MUST include thinking blocks from last assistant turn.\n";
echo "API automatically ignores thinking from previous turns.\n\n";

try {
    // First turn
    $response1 = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 4096,
        'thinking' => [
            'type' => 'enabled',
            'budget_tokens' => 2000
        ],
        'messages' => [
            ['role' => 'user', 'content' => 'What is 137 * 89?']
        ]
    ]);

    echo "Turn 1: What is 137 * 89?\n";

    $firstAnswer = '';
    foreach ($response1->content as $block) {
        if ($block['type'] === 'text') {
            $firstAnswer = $block['text'];
            echo "Answer: {$firstAnswer}\n\n";
        }
    }

    // Second turn - include complete response from turn 1
    $response2 = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 4096,
        'thinking' => [
            'type' => 'enabled',
            'budget_tokens' => 2000
        ],
        'messages' => [
            ['role' => 'user', 'content' => 'What is 137 * 89?'],
            ['role' => 'assistant', 'content' => $response1->content], // Include thinking blocks
            ['role' => 'user', 'content' => 'Now divide that by 3']
        ]
    ]);

    echo "Turn 2: Now divide that by 3\n";

    foreach ($response2->content as $block) {
        if ($block['type'] === 'text') {
            echo "Answer: {$block['text']}\n";
        }
    }

    echo "\nNote: Thinking blocks from turn 1 were included but not counted toward context\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 5: Thinking redaction
echo "Example 5: Thinking Redaction\n";
echo "------------------------------\n";
echo "When thinking contains flagged content, it's redacted for safety.\n";
echo "Redacted blocks are still billable and usable in subsequent requests.\n\n";

echo "Response format with redacted thinking:\n";
echo "{\n";
echo "  \"type\": \"redacted_thinking\",\n";
echo "  \"signature\": \"Wad8/dW5hR7xJ0aP1oLs9yTcMnKVf2wRpEGjH9XZaBt...\"\n";
echo "}\n\n";

echo "Key points:\n";
echo "  • Redacted thinking is EXPECTED behavior\n";
echo "  • Model can still use this reasoning internally\n";
echo "  • Maintains safety guardrails\n";
echo "  • Must include unmodified in multi-turn conversations\n\n";

echo "Test redaction with special string:\n";
echo "ANTHROPIC_MAGIC_STRING_TRIGGER_REDACTED_THINKING_...\n";
echo "(See documentation for full test string)\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 6: Extended thinking with tool use
echo "Example 6: Extended Thinking with Tool Use\n";
echo "-------------------------------------------\n";
echo "Claude 4 models support INTERLEAVED THINKING with tools.\n";
echo "Claude can think between tool calls and after receiving results.\n\n";

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 4096,
        'thinking' => [
            'type' => 'enabled',
            'budget_tokens' => 2000
        ],
        'tools' => [
            [
                'name' => 'calculate',
                'description' => 'Perform a calculation',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'expression' => [
                            'type' => 'string',
                            'description' => 'Mathematical expression to evaluate'
                        ]
                    ],
                    'required' => ['expression']
                ]
            ]
        ],
        'messages' => [
            ['role' => 'user', 'content' => 'Calculate 15 * 24 + 8']
        ]
    ]);

    echo "Request with tools and thinking enabled\n";
    echo "Claude can think before/between/after tool calls\n\n";

    foreach ($response->content as $block) {
        if ($block['type'] === 'thinking') {
            echo "Thinking: " . substr($block['thinking'], 0, 100) . "...\n";
        } elseif ($block['type'] === 'tool_use') {
            echo "Tool use: {$block['name']}({$block['input']['expression']})\n";
        } elseif ($block['type'] === 'text') {
            echo "Text: {$block['text']}\n";
        }
    }

    echo "\nNote: With interleaved thinking, budget can exceed budget_tokens\n";
    echo "up to the full context window (200K tokens)\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 7: Token management and pricing
echo "Example 7: Token Management and Pricing\n";
echo "----------------------------------------\n\n";

echo "Pricing (per million tokens):\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Model              Input      Output\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Opus 4.1           \$15        \$75\n";
echo "Opus 4             \$15        \$75\n";
echo "Sonnet 4.5         \$3         \$15\n";
echo "Sonnet 4           \$3         \$15\n";
echo "Haiku 4.5          \$1         \$5\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Thinking charges:\n";
echo "  • Thinking tokens (output tokens)\n";
echo "  • Thinking blocks in subsequent requests (input tokens)\n";
echo "  • Standard text output tokens\n\n";

echo "Summarized thinking billing:\n";
echo "  • Input tokens: Your original request\n";
echo "  • Output tokens (billed): Full original thinking\n";
echo "  • Output tokens (visible): Summarized thinking\n";
echo "  • No charge: Summary generation\n\n";

echo "Context window management:\n";
echo "  • Previous thinking blocks auto-stripped\n";
echo "  • Only last assistant turn thinking counts\n";
echo "  • Thinking blocks from last turn billed as input\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 8: Best practices
echo "Example 8: Best Practices\n";
echo "-------------------------\n\n";

echo "✓ Task Selection:\n";
echo "  • Use for complex tasks: math, coding, analysis\n";
echo "  • Step-by-step reasoning problems\n";
echo "  • Multi-step problem solving\n\n";

echo "✓ Budget Optimization:\n";
echo "  • Start at minimum (1024 tokens)\n";
echo "  • Increase incrementally for your use case\n";
echo "  • Test different settings for critical tasks\n";
echo "  • Use batch processing for >32k budgets\n\n";

echo "✓ Performance:\n";
echo "  • Expect longer response times\n";
echo "  • Use streaming for large responses\n";
echo "  • Monitor token usage for cost optimization\n\n";

echo "✓ Feature Compatibility:\n";
echo "  • ✗ Not compatible: temperature, top_k, forced tool use\n";
echo "  • ✗ Cannot prefill responses\n";
echo "  • ✓ Works with: top_p (0.95-1.0), prompt caching, tools\n";
echo "  • ✓ Streaming required when max_tokens > 21,333\n\n";

echo "✓ Context Handling:\n";
echo "  • Always include thinking from last assistant turn\n";
echo "  • Don't manually remove thinking blocks (API handles it)\n";
echo "  • Thinking blocks don't count toward context limits\n\n";

echo "✓ Prompt Caching:\n";
echo "  • System prompts and tools: Cache preserved\n";
echo "  • Messages with thinking: Cache invalidated\n";
echo "  • Changing budget invalidates message cache\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 9: Supported models
echo "Example 9: Supported Models\n";
echo "----------------------------\n\n";

$supportedModels = [
    'claude-sonnet-4-5-20250929' => 'Latest Sonnet - Summarized thinking',
    'claude-sonnet-4-20250514' => 'Sonnet 4 - Summarized thinking',
    'claude-haiku-4-5-20251001' => 'Fast model - Summarized thinking',
    'claude-opus-4-1-20250805' => 'Most capable - Summarized thinking',
    'claude-opus-4-20250514' => 'Opus 4 - Summarized thinking',
    'claude-3-7-sonnet-20250219' => 'DEPRECATED - Full thinking output'
];

foreach ($supportedModels as $model => $description) {
    echo "• {$model}\n";
    echo "  {$description}\n\n";
}

echo "Model differences:\n";
echo "  • Claude Sonnet 3.7: Full thinking output (deprecated)\n";
echo "  • Claude 4 models: Summarized thinking output\n";
echo "  • Interleaved thinking: Only Claude 4 models\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 10: Configuration examples
echo "Example 10: Configuration Examples\n";
echo "-----------------------------------\n\n";

echo "Simple math (minimum budget):\n";
echo "```php\n";
echo "'thinking' => [\n";
echo "    'type' => 'enabled',\n";
echo "    'budget_tokens' => 1024\n";
echo "]\n";
echo "```\n\n";

echo "Code analysis (medium budget):\n";
echo "```php\n";
echo "'thinking' => [\n";
echo "    'type' => 'enabled',\n";
echo "    'budget_tokens' => 8000\n";
echo "]\n";
echo "```\n\n";

echo "Complex research (large budget):\n";
echo "```php\n";
echo "'thinking' => [\n";
echo "    'type' => 'enabled',\n";
echo "    'budget_tokens' => 20000\n";
echo "]\n";
echo "```\n\n";

echo "With tools and caching:\n";
echo "```php\n";
echo "[\n";
echo "    'model' => 'claude-sonnet-4-5',\n";
echo "    'max_tokens' => 16000,\n";
echo "    'thinking' => [\n";
echo "        'type' => 'enabled',\n";
echo "        'budget_tokens' => 10000\n";
echo "    ],\n";
echo "    'tools' => [...],\n";
echo "    'system' => [\n";
echo "        ['type' => 'text', 'text' => \$instructions,\n";
echo "         'cache_control' => ['type' => 'ephemeral']]\n";
echo "    ]\n";
echo "]\n";
echo "```\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "✓ Extended thinking examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Enhanced reasoning for complex tasks (math, coding, analysis)\n";
echo "• Claude 4 models return SUMMARIZED thinking (billed for full tokens)\n";
echo "• Minimum budget: 1,024 tokens; optimal range varies by task\n";
echo "• Include thinking blocks from last assistant turn in multi-turn\n";
echo "• Thinking blocks auto-stripped from previous turns (don't count toward context)\n";
echo "• Interleaved thinking (Claude 4): Think between tool calls\n";
echo "• Not compatible with temperature, top_k, or response prefilling\n";
echo "• Use batch processing for budgets >32k tokens\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • examples/thinking.php - Basic extended thinking example\n";
echo "  • examples/thinking_stream.php - Streaming extended thinking\n";
echo "  • examples/context_windows.php - Token management\n";
echo "  • examples/prompt_caching.php - Cache optimization\n";
echo "  • examples/tools.php - Tool use with thinking\n";
