#!/usr/bin/env php
<?php
/**
 * Effort Levels - PHP examples demonstrating the effort parameter
 * 
 * The effort parameter allows you to control the computational effort
 * Claude puts into generating responses. Higher effort may produce
 * more thorough, thoughtful responses at the cost of latency and tokens.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Effort Levels - Control Response Quality ===\n\n";
echo "The effort parameter controls computational effort for responses.\n";
echo "Higher effort = more thorough responses, but higher latency.\n\n";

// Example 1: Effort levels overview
echo "Example 1: Effort Levels Overview\n";
echo "----------------------------------\n\n";

echo "Available effort levels:\n";
echo "  • low    - Fast responses, basic reasoning\n";
echo "  • medium - Balanced responses (default behavior)\n";
echo "  • high   - Thorough responses, deep reasoning\n\n";

echo "Configuration:\n";
echo "```php\n";
echo "\$response = \$client->messages()->create([\n";
echo "    'model' => 'claude-sonnet-4-20250514',\n";
echo "    'max_tokens' => 4096,\n";
echo "    'output_config' => [\n";
echo "        'effort' => 'high', // low, medium, or high\n";
echo "    ],\n";
echo "    'messages' => \$messages,\n";
echo "]);\n";
echo "```\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 2: Low effort - Quick responses
echo "Example 2: Low Effort - Quick Responses\n";
echo "----------------------------------------\n\n";

echo "Use low effort for:\n";
echo "  • Simple queries with clear answers\n";
echo "  • High-volume, latency-sensitive applications\n";
echo "  • Straightforward text generation\n";
echo "  • Cost-sensitive scenarios\n\n";

$lowEffortConfig = [
    'output_config' => [
        'effort' => 'low',
    ],
];

echo "Configuration:\n";
echo json_encode($lowEffortConfig, JSON_PRETTY_PRINT) . "\n\n";

echo "Example use case - Simple classification:\n";
echo "```php\n";
echo "\$response = \$client->messages()->create([\n";
echo "    'model' => 'claude-sonnet-4-20250514',\n";
echo "    'max_tokens' => 100,\n";
echo "    'output_config' => ['effort' => 'low'],\n";
echo "    'messages' => [[\n";
echo "        'role' => 'user',\n";
echo "        'content' => 'Is this email spam? Subject: You won a prize!'\n";
echo "    ]],\n";
echo "]);\n";
echo "```\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 3: Medium effort - Balanced responses
echo "Example 3: Medium Effort - Balanced Responses\n";
echo "----------------------------------------------\n\n";

echo "Medium effort is the default and works well for:\n";
echo "  • General conversations\n";
echo "  • Content creation\n";
echo "  • Standard coding assistance\n";
echo "  • Most typical use cases\n\n";

$mediumEffortConfig = [
    'output_config' => [
        'effort' => 'medium',
    ],
];

echo "Configuration:\n";
echo json_encode($mediumEffortConfig, JSON_PRETTY_PRINT) . "\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 4: High effort - Deep reasoning
echo "Example 4: High Effort - Deep Reasoning\n";
echo "----------------------------------------\n\n";

echo "Use high effort for:\n";
echo "  • Complex problem-solving\n";
echo "  • Detailed analysis and research\n";
echo "  • Mathematical proofs and reasoning\n";
echo "  • Critical decision support\n";
echo "  • Multi-step logical reasoning\n\n";

$highEffortConfig = [
    'output_config' => [
        'effort' => 'high',
    ],
];

echo "Configuration:\n";
echo json_encode($highEffortConfig, JSON_PRETTY_PRINT) . "\n\n";

echo "Example use case - Complex analysis:\n";
echo "```php\n";
echo "\$response = \$client->messages()->create([\n";
echo "    'model' => 'claude-opus-4-20250514',\n";
echo "    'max_tokens' => 8192,\n";
echo "    'output_config' => ['effort' => 'high'],\n";
echo "    'messages' => [[\n";
echo "        'role' => 'user',\n";
echo "        'content' => 'Analyze the security implications of this architecture...'\n";
echo "    ]],\n";
echo "]);\n";
echo "```\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 5: Combining with extended thinking
echo "Example 5: Combining with Extended Thinking\n";
echo "--------------------------------------------\n\n";

echo "Effort levels can be combined with extended thinking for\n";
echo "even more thorough responses:\n\n";

echo "```php\n";
echo "\$response = \$client->messages()->create([\n";
echo "    'model' => 'claude-sonnet-4-20250514',\n";
echo "    'max_tokens' => 16384,\n";
echo "    'output_config' => [\n";
echo "        'effort' => 'high',\n";
echo "    ],\n";
echo "    'thinking' => [\n";
echo "        'type' => 'enabled',\n";
echo "        'budget_tokens' => 10000,\n";
echo "    ],\n";
echo "    'messages' => [[\n";
echo "        'role' => 'user',\n";
echo "        'content' => 'Prove that the square root of 2 is irrational.'\n";
echo "    ]],\n";
echo "]);\n";
echo "```\n\n";

echo "Note: Extended thinking already provides deep reasoning.\n";
echo "Adding high effort may further enhance thoroughness.\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 6: Performance considerations
echo "Example 6: Performance Considerations\n";
echo "--------------------------------------\n\n";

echo "Trade-offs by effort level:\n\n";

echo "┌─────────┬─────────────┬────────────┬──────────────┐\n";
echo "│ Effort  │ Latency     │ Tokens     │ Quality      │\n";
echo "├─────────┼─────────────┼────────────┼──────────────┤\n";
echo "│ low     │ Fastest     │ Fewest     │ Basic        │\n";
echo "│ medium  │ Balanced    │ Moderate   │ Good         │\n";
echo "│ high    │ Slowest     │ Most       │ Best         │\n";
echo "└─────────┴─────────────┴────────────┴──────────────┘\n\n";

echo "Recommendations:\n";
echo "  • Start with medium (default) for most use cases\n";
echo "  • Use low for high-volume, simple tasks\n";
echo "  • Reserve high for critical, complex reasoning\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 7: Use case recommendations
echo "Example 7: Use Case Recommendations\n";
echo "------------------------------------\n\n";

echo "LOW EFFORT:\n";
echo "  ✓ Chatbots with simple Q&A\n";
echo "  ✓ Content moderation\n";
echo "  ✓ Sentiment analysis\n";
echo "  ✓ Quick translations\n";
echo "  ✓ Entity extraction\n\n";

echo "MEDIUM EFFORT:\n";
echo "  ✓ General conversation\n";
echo "  ✓ Content creation\n";
echo "  ✓ Code generation\n";
echo "  ✓ Summarization\n";
echo "  ✓ Customer support\n\n";

echo "HIGH EFFORT:\n";
echo "  ✓ Mathematical proofs\n";
echo "  ✓ Security analysis\n";
echo "  ✓ Legal document review\n";
echo "  ✓ Scientific reasoning\n";
echo "  ✓ Complex debugging\n";
echo "  ✓ Strategic planning\n\n";

echo str_repeat("=", 80) . "\n\n";

echo "✓ Effort levels examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Effort levels: low, medium, high\n";
echo "• Use 'output_config.effort' to set the level\n";
echo "• Higher effort = better quality but slower/more tokens\n";
echo "• Match effort to task complexity\n";
echo "• Can combine with extended thinking for deep reasoning\n";
echo "• Default (medium) works well for most use cases\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • examples/extended_thinking.php - Deep reasoning\n";
echo "  • examples/model_comparison.php - Model capabilities\n";
echo "  • examples/streaming.php - Streaming responses\n";

