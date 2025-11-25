#!/usr/bin/env php
<?php
/**
 * Context Editing - PHP examples from:
 * https://docs.claude.com/en/docs/build-with-claude/context-editing
 * 
 * Automatically manage conversation context as it grows with context editing.
 * Beta feature - requires 'context-management-2025-06-27' header.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Context Editing - Automatic Context Management ===\n\n";
echo "⚠️  Context editing is currently in BETA\n";
echo "Requires beta header: 'context-management-2025-06-27'\n\n";

// Example 1: Basic tool result clearing
echo "Example 1: Basic Tool Result Clearing\n";
echo "--------------------------------------\n";
echo "Automatically clears tool results when context exceeds threshold.\n";
echo "Default: Clears at 100K tokens, keeps last 3 tool uses\n\n";

try {
    $response = $client->beta()->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            ['role' => 'user', 'content' => 'What is the weather like today?']
        ],
        'tools' => [
            [
                'name' => 'get_weather',
                'description' => 'Get the current weather in a location',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'location' => [
                            'type' => 'string',
                            'description' => 'City and state, e.g. San Francisco, CA'
                        ]
                    ],
                    'required' => ['location']
                ]
            ]
        ],
        'betas' => ['context-management-2025-06-27'],
        'context_management' => [
            'edits' => [
                ['type' => 'clear_tool_uses_20250919']  // Basic configuration
            ]
        ]
    ]);

    echo "✓ Request successful with basic tool result clearing enabled\n";
    echo "Model: {$response->model}\n";
    
    // Check if any edits were applied
    if (isset($response->context_management)) {
        echo "\nContext management applied:\n";
        print_r($response->context_management);
    } else {
        echo "\nNo context edits applied (context below threshold)\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: Advanced tool result clearing configuration
echo "Example 2: Advanced Tool Result Clearing\n";
echo "-----------------------------------------\n";
echo "Custom configuration with trigger, keep, clear_at_least, and exclude_tools\n\n";

echo "Configuration:\n";
echo "  • Trigger: 30,000 input tokens\n";
echo "  • Keep: 3 most recent tool uses\n";
echo "  • Clear at least: 5,000 tokens (for cache efficiency)\n";
echo "  • Exclude: web_search tool (never cleared)\n\n";

$advancedConfig = [
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 4096,
    'messages' => [
        ['role' => 'user', 'content' => 'Help me build a calculator app']
    ],
    'tools' => [
        [
            'type' => 'text_editor_20250728',
            'name' => 'str_replace_based_edit_tool',
            'max_characters' => 10000
        ],
        [
            'type' => 'web_search_20250305',
            'name' => 'web_search',
            'max_uses' => 3
        ]
    ],
    'betas' => ['context-management-2025-06-27'],
    'context_management' => [
        'edits' => [
            [
                'type' => 'clear_tool_uses_20250919',
                'trigger' => [
                    'type' => 'input_tokens',
                    'value' => 30000
                ],
                'keep' => [
                    'type' => 'tool_uses',
                    'value' => 3
                ],
                'clear_at_least' => [
                    'type' => 'input_tokens',
                    'value' => 5000
                ],
                'exclude_tools' => ['web_search']
            ]
        ]
    ]
];

echo "Example configuration:\n";
echo json_encode($advancedConfig['context_management'], JSON_PRETTY_PRINT) . "\n\n";

echo "This configuration:\n";
echo "  ✓ Activates when prompt exceeds 30K tokens\n";
echo "  ✓ Preserves last 3 tool interactions\n";
echo "  ✓ Clears at least 5K tokens (worthwhile for cache invalidation)\n";
echo "  ✓ Never clears web_search results (important context)\n";
echo "  ✓ Clears oldest tool results first\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 3: Thinking block clearing
echo "Example 3: Thinking Block Clearing\n";
echo "-----------------------------------\n";
echo "Manages thinking blocks when extended thinking is enabled.\n";
echo "Default: Keeps thinking blocks from last 1 assistant turn\n\n";

try {
    $response = $client->beta()->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 2048,
        'thinking' => [
            'type' => 'enabled',
            'budget_tokens' => 1500
        ],
        'messages' => [
            ['role' => 'user', 'content' => 'Calculate 157 * 89 step by step']
        ],
        'betas' => ['context-management-2025-06-27'],
        'context_management' => [
            'edits' => [
                [
                    'type' => 'clear_thinking_20251015',
                    'keep' => [
                        'type' => 'thinking_turns',
                        'value' => 1  // Keep last 1 turn with thinking
                    ]
                ]
            ]
        ]
    ]);

    echo "✓ Extended thinking with context management enabled\n";
    
    foreach ($response->content as $block) {
        if ($block['type'] === 'thinking') {
            echo "Thinking block present (will be cleared in next turn)\n";
        } elseif ($block['type'] === 'text') {
            echo "Answer: " . substr($block['text'], 0, 100) . "...\n";
        }
    }
    
    if (isset($response->context_management)) {
        echo "\nContext management:\n";
        print_r($response->context_management);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 4: Thinking block clearing - keep all (maximize cache hits)
echo "Example 4: Keep All Thinking Blocks\n";
echo "------------------------------------\n";
echo "Keep all thinking blocks to maximize prompt cache hits.\n\n";

$keepAllConfig = [
    'type' => 'clear_thinking_20251015',
    'keep' => 'all'  // Keep all thinking blocks
];

echo "Configuration:\n";
echo json_encode($keepAllConfig, JSON_PRETTY_PRINT) . "\n\n";

echo "Benefits of 'keep: all':\n";
echo "  • Maximizes prompt cache hits\n";
echo "  • Preserves thinking context across turns\n";
echo "  • Better for conversations with frequent cache reuse\n\n";

echo "Trade-off:\n";
echo "  • Uses more context window tokens\n";
echo "  • May approach context limits faster\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 5: Combining both strategies
echo "Example 5: Combining Strategies\n";
echo "--------------------------------\n";
echo "Use both thinking block clearing AND tool result clearing together.\n";
echo "⚠️  clear_thinking_20251015 MUST be listed first in edits array\n\n";

$combinedConfig = [
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'thinking' => [
        'type' => 'enabled',
        'budget_tokens' => 2000
    ],
    'messages' => [
        ['role' => 'user', 'content' => 'Research and summarize AI developments']
    ],
    'tools' => [
        [
            'type' => 'web_search_20250305',
            'name' => 'web_search'
        ]
    ],
    'betas' => ['context-management-2025-06-27'],
    'context_management' => [
        'edits' => [
            // MUST be first!
            [
                'type' => 'clear_thinking_20251015',
                'keep' => [
                    'type' => 'thinking_turns',
                    'value' => 2  // Keep last 2 turns with thinking
                ]
            ],
            // Tool clearing second
            [
                'type' => 'clear_tool_uses_20250919',
                'trigger' => [
                    'type' => 'input_tokens',
                    'value' => 50000
                ],
                'keep' => [
                    'type' => 'tool_uses',
                    'value' => 5
                ]
            ]
        ]
    ]
];

echo "Combined configuration:\n";
echo json_encode($combinedConfig['context_management'], JSON_PRETTY_PRINT) . "\n\n";

echo "This setup:\n";
echo "  1. Clears thinking blocks (keeps last 2 turns)\n";
echo "  2. Clears tool results when exceeding 50K tokens (keeps last 5)\n";
echo "  3. Allows long-running conversations with both features\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 6: Configuration options summary
echo "Example 6: Configuration Options Reference\n";
echo "-------------------------------------------\n\n";

echo "Tool Result Clearing (clear_tool_uses_20250919):\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• trigger (default: 100,000 tokens)\n";
echo "  - When to start clearing\n";
echo "  - Can use 'input_tokens' or 'tool_uses' as type\n\n";
echo "• keep (default: 3 tool uses)\n";
echo "  - How many recent tool use/result pairs to preserve\n";
echo "  - Oldest are cleared first\n\n";
echo "• clear_at_least (default: none)\n";
echo "  - Minimum tokens to clear per activation\n";
echo "  - Useful for cache efficiency\n\n";
echo "• exclude_tools (default: none)\n";
echo "  - List of tool names to never clear\n";
echo "  - Preserves important context\n\n";
echo "• clear_tool_inputs (default: false)\n";
echo "  - Whether to clear tool call parameters too\n";
echo "  - By default, only results are cleared\n\n";

echo "Thinking Block Clearing (clear_thinking_20251015):\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• keep (default: 1 thinking turn)\n";
echo "  - How many recent assistant turns with thinking to keep\n";
echo "  - Options:\n";
echo "    - {type: 'thinking_turns', value: N} - Keep last N turns\n";
echo "    - 'all' - Keep all thinking (maximizes cache hits)\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 7: Context editing response
echo "Example 7: Context Editing Response\n";
echo "------------------------------------\n";
echo "The response includes context_management field with applied edits.\n\n";

echo "Example response structure:\n";
echo "{\n";
echo "  \"id\": \"msg_123\",\n";
echo "  \"type\": \"message\",\n";
echo "  \"role\": \"assistant\",\n";
echo "  \"content\": [...],\n";
echo "  \"usage\": {...},\n";
echo "  \"context_management\": {\n";
echo "    \"applied_edits\": [\n";
echo "      {\n";
echo "        \"type\": \"clear_thinking_20251015\",\n";
echo "        \"cleared_thinking_turns\": 3,\n";
echo "        \"cleared_input_tokens\": 15000\n";
echo "      },\n";
echo "      {\n";
echo "        \"type\": \"clear_tool_uses_20250919\",\n";
echo "        \"cleared_tool_uses\": 8,\n";
echo "        \"cleared_input_tokens\": 50000\n";
echo "      }\n";
echo "    ]\n";
echo "  }\n";
echo "}\n\n";

echo "Response fields tell you:\n";
echo "  • Which strategies were applied\n";
echo "  • How many items were cleared\n";
echo "  • How many tokens were saved\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 8: Token counting with context management
echo "Example 8: Token Counting Preview\n";
echo "----------------------------------\n";
echo "Preview token usage after context editing is applied.\n\n";

echo "The countTokens endpoint supports context_management parameter.\n";
echo "Response includes both:\n";
echo "  • input_tokens - Final count after editing\n";
echo "  • original_input_tokens - Count before editing\n\n";

echo "Example usage:\n";
echo "```php\n";
echo "\$tokenCount = \$client->messages()->countTokens([\n";
echo "    'model' => 'claude-sonnet-4-5',\n";
echo "    'messages' => [\n";
echo "        ['role' => 'user', 'content' => 'Continue our conversation...']\n";
echo "    ],\n";
echo "    'tools' => [...],\n";
echo "    'context_management' => [\n";
echo "        'edits' => [\n";
echo "            [\n";
echo "                'type' => 'clear_tool_uses_20250919',\n";
echo "                'trigger' => ['type' => 'input_tokens', 'value' => 30000],\n";
echo "                'keep' => ['type' => 'tool_uses', 'value' => 5]\n";
echo "            ]\n";
echo "        ]\n";
echo "    ]\n";
echo "]);\n\n";
echo "// Response: {\n";
echo "//   \"input_tokens\": 25000,\n";
echo "//   \"context_management\": {\n";
echo "//     \"original_input_tokens\": 70000\n";
echo "//   }\n";
echo "// }\n";
echo "```\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 9: Using with Memory Tool
echo "Example 9: Combining with Memory Tool\n";
echo "--------------------------------------\n";
echo "Context editing + Memory Tool = unlimited conversation length!\n\n";

echo "How it works:\n";
echo "1. Claude receives warning when approaching clearing threshold\n";
echo "2. Claude saves important info to memory files\n";
echo "3. Tool results are cleared from conversation\n";
echo "4. Claude can look up info from memory when needed\n\n";

$memoryConfig = [
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 4096,
    'messages' => [
        ['role' => 'user', 'content' => 'Help me manage a long project']
    ],
    'tools' => [
        [
            'type' => 'memory_20250818',
            'name' => 'memory'
        ],
        [
            'type' => 'text_editor_20250728',
            'name' => 'str_replace_based_edit_tool',
            'max_characters' => 10000
        ]
    ],
    'betas' => ['context-management-2025-06-27'],
    'context_management' => [
        'edits' => [
            ['type' => 'clear_tool_uses_20250919']
        ]
    ]
];

echo "Configuration:\n";
echo json_encode($memoryConfig['context_management'], JSON_PRETTY_PRINT) . "\n\n";

echo "Benefits:\n";
echo "  • Preserve important context in memory files\n";
echo "  • Enable long-running agentic workflows\n";
echo "  • Access cleared information on demand\n";
echo "  • Exceed context limits without losing information\n\n";

echo "Example workflow:\n";
echo "  1. Claude edits many files\n";
echo "  2. Summarizes changes to memory\n";
echo "  3. Tool results are cleared\n";
echo "  4. Claude continues work with memory context\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 10: Best practices
echo "Example 10: Best Practices\n";
echo "---------------------------\n\n";

echo "✓ Server-side Processing:\n";
echo "  • Context editing happens server-side\n";
echo "  • Keep full conversation history in your client\n";
echo "  • No need to sync client state with edited version\n\n";

echo "✓ Prompt Caching Interaction:\n";
echo "  • Tool clearing: Invalidates cache when cleared\n";
echo "  • Use clear_at_least to make invalidation worthwhile\n";
echo "  • Thinking clearing: 'keep: all' preserves cache\n\n";

echo "✓ Configuration Tips:\n";
echo "  • Start with defaults, tune based on your use case\n";
echo "  • Use exclude_tools for critical context\n";
echo "  • Combine with memory tool for long sessions\n";
echo "  • Monitor applied_edits in responses\n\n";

echo "✓ Supported Models:\n";
echo "  • Claude Opus 4.5 (claude-opus-4-5-20251101)\n";
echo "  • Claude Opus 4 (claude-opus-4-20250514)\n";
echo "  • Claude Sonnet 4.5 (claude-sonnet-4-5-20250929)\n";
echo "  • Claude Sonnet 4 (claude-sonnet-4-20250514)\n";
echo "  • Claude Haiku 4.5 (claude-haiku-4-5-20251001)\n\n";

echo "✓ When to Use:\n";
echo "  • Long-running agent sessions\n";
echo "  • Multi-turn conversations with many tool calls\n";
echo "  • Extended thinking in long conversations\n";
echo "  • Cost optimization for large contexts\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "✓ Context editing examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Context editing is BETA - requires 'context-management-2025-06-27' header\n";
echo "• Two strategies: tool result clearing and thinking block clearing\n";
echo "• Happens server-side - keep full history in your client\n";
echo "• Tool clearing invalidates cache, thinking 'keep: all' preserves it\n";
echo "• Combine with memory tool for unlimited conversation length\n";
echo "• Monitor context_management in responses for statistics\n";
echo "• Available on Claude 4.x and 4.5 models\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • examples/context_windows.php - Token management\n";
echo "  • examples/tools.php - Tool use patterns\n";
echo "  • examples/thinking.php - Extended thinking\n";
echo "  • examples/prompt_caching.php - Cache optimization\n";

