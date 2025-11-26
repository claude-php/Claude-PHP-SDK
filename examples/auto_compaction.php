#!/usr/bin/env php
<?php
/**
 * Auto Compaction - PHP examples demonstrating automatic context compaction
 * 
 * Auto-compaction automatically summarizes and compresses the message history
 * when the context window exceeds a specified token threshold.
 * 
 * This feature is useful for long-running conversations or agentic workflows
 * where the context window might grow too large.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Auto Compaction - Context Window Management ===\n\n";
echo "Automatically summarizes and compresses message history when\n";
echo "context window exceeds token threshold.\n\n";

// Example 1: Basic compaction control configuration
echo "Example 1: Compaction Control Configuration\n";
echo "--------------------------------------------\n\n";

$compactionControl = [
    'enabled' => true,
    'context_token_threshold' => 100000, // Default: 100,000 tokens
];

echo "Configuration:\n";
echo json_encode($compactionControl, JSON_PRETTY_PRINT) . "\n\n";

echo "Settings explained:\n";
echo "  • enabled: Whether compaction is active\n";
echo "  • context_token_threshold: Token count that triggers compaction\n";
echo "  • model (optional): Model to use for generating summaries\n";
echo "  • summary_prompt (optional): Custom prompt for summarization\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 2: Using compaction with messages
echo "Example 2: Messages with Compaction\n";
echo "------------------------------------\n\n";

echo "Example request structure:\n";
echo "```php\n";
echo "\$response = \$client->messages()->create([\n";
echo "    'model' => 'claude-sonnet-4-20250514',\n";
echo "    'max_tokens' => 4096,\n";
echo "    'messages' => \$messages,\n";
echo "    'compaction_control' => [\n";
echo "        'enabled' => true,\n";
echo "        'context_token_threshold' => 50000,\n";
echo "    ],\n";
echo "]);\n";
echo "```\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 3: Custom summary prompt
echo "Example 3: Custom Summary Prompt\n";
echo "---------------------------------\n\n";

$customSummaryPrompt = <<<'PROMPT'
You have been working on the task described above but have not yet completed it.
Write a continuation summary that will allow you to resume work efficiently.

Your summary should include:
1. Task Overview - The user's core request and success criteria
2. Current State - What has been completed so far
3. Important Discoveries - Technical constraints, decisions, and errors
4. Next Steps - Specific actions needed to complete the task
5. Context to Preserve - User preferences and domain-specific details

Be concise but complete. Wrap your summary in <summary></summary> tags.
PROMPT;

$compactionWithCustomPrompt = [
    'enabled' => true,
    'context_token_threshold' => 75000,
    'summary_prompt' => $customSummaryPrompt,
];

echo "Custom prompt for specialized summarization:\n";
echo "```php\n";
echo "\$compactionControl = [\n";
echo "    'enabled' => true,\n";
echo "    'context_token_threshold' => 75000,\n";
echo "    'summary_prompt' => \$customSummaryPrompt,\n";
echo "];\n";
echo "```\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 4: Tool runner with compaction
echo "Example 4: Agentic Tool Runner with Compaction\n";
echo "-----------------------------------------------\n\n";

echo "For agentic workflows with many tool calls, compaction helps\n";
echo "maintain context while avoiding token limits:\n\n";

echo "```php\n";
echo "// Define tools\n";
echo "\$tools = [\n";
echo "    [\n";
echo "        'name' => 'search',\n";
echo "        'description' => 'Search for information',\n";
echo "        'input_schema' => [\n";
echo "            'type' => 'object',\n";
echo "            'properties' => [\n";
echo "                'query' => ['type' => 'string', 'description' => 'Search query']\n";
echo "            ],\n";
echo "            'required' => ['query']\n";
echo "        ]\n";
echo "    ],\n";
echo "];\n\n";
echo "// Run with compaction enabled\n";
echo "\$response = \$client->messages()->create([\n";
echo "    'model' => 'claude-sonnet-4-20250514',\n";
echo "    'max_tokens' => 4096,\n";
echo "    'tools' => \$tools,\n";
echo "    'messages' => [\n";
echo "        [\n";
echo "            'role' => 'user',\n";
echo "            'content' => 'Search for dogs, cats, birds, fish, and horses. Then summarize.'\n";
echo "        ]\n";
echo "    ],\n";
echo "    'compaction_control' => [\n";
echo "        'enabled' => true,\n";
echo "        'context_token_threshold' => 3000, // Low threshold for demo\n";
echo "    ],\n";
echo "]);\n";
echo "```\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 5: Detecting compaction
echo "Example 5: Detecting When Compaction Occurs\n";
echo "--------------------------------------------\n\n";

echo "You can detect compaction by monitoring message count:\n\n";

echo "```php\n";
echo "\$previousMessageCount = count(\$messages);\n\n";
echo "// After each response\n";
echo "\$currentMessageCount = count(\$messages);\n\n";
echo "if (\$currentMessageCount < \$previousMessageCount) {\n";
echo "    echo \"Compaction occurred!\";\n";
echo "    echo \"Messages went from {\$previousMessageCount} to {\$currentMessageCount}\";\n";
echo "    \n";
echo "    // The first message will contain the summary\n";
echo "    \$summary = \$messages[0]['content'];\n";
echo "}\n\n";
echo "\$previousMessageCount = \$currentMessageCount;\n";
echo "```\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 6: Using different models for compaction
echo "Example 6: Using Different Models for Compaction\n";
echo "-------------------------------------------------\n\n";

echo "Use a faster/cheaper model for generating summaries:\n\n";

$compactionWithModel = [
    'enabled' => true,
    'context_token_threshold' => 100000,
    'model' => 'claude-haiku-3-5-20241022', // Use faster model for summaries
];

echo "```php\n";
echo "\$compactionControl = [\n";
echo "    'enabled' => true,\n";
echo "    'context_token_threshold' => 100000,\n";
echo "    'model' => 'claude-haiku-3-5-20241022', // Fast summarization\n";
echo "];\n";
echo "```\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 7: Best practices
echo "Example 7: Best Practices\n";
echo "--------------------------\n\n";

echo "✓ Threshold Selection:\n";
echo "  • Default: 100,000 tokens (works for most cases)\n";
echo "  • Lower (50k-75k): Better for memory-constrained scenarios\n";
echo "  • Higher (150k+): When you need more context preserved\n\n";

echo "✓ Summary Quality:\n";
echo "  • Use default prompt for general tasks\n";
echo "  • Customize for domain-specific requirements\n";
echo "  • Include key information types in custom prompts\n\n";

echo "✓ Performance:\n";
echo "  • Use faster models (Haiku) for summarization\n";
echo "  • Balance threshold vs. summarization frequency\n";
echo "  • Monitor token usage across turns\n\n";

echo "✓ Context Preservation:\n";
echo "  • Important context may be lost during compaction\n";
echo "  • Store critical information externally if needed\n";
echo "  • Consider using system prompts for persistent context\n\n";

echo str_repeat("=", 80) . "\n\n";

echo "✓ Auto compaction examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Auto-compaction manages context window size automatically\n";
echo "• Enabled via 'compaction_control' parameter\n";
echo "• Configurable threshold, model, and summary prompt\n";
echo "• Ideal for long-running agents and multi-turn conversations\n";
echo "• Detectable by monitoring message count changes\n";
echo "• Use faster models for cost-effective summarization\n";
echo "• Custom prompts for domain-specific summarization\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • examples/token_counting.php - Track token usage\n";
echo "  • examples/context_windows.php - Understand context limits\n";
echo "  • examples/multi_turn.php - Multi-turn conversations\n";

