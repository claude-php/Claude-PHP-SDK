#!/usr/bin/env php
<?php
/**
 * Token-Efficient Tool Use - PHP examples from:
 * https://docs.claude.com/en/docs/agents-and-tools/tool-use/token-efficient-tool-use
 * 
 * Optimize token usage when using tools to reduce costs and stay within context limits.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Token-Efficient Tool Use - Optimization Strategies ===\n\n";

// Example 1: Minimize tool descriptions
echo "Example 1: Minimize Tool Descriptions\n";
echo "--------------------------------------\n";
echo "Keep descriptions concise but clear\n\n";

echo "❌ Verbose (unnecessary tokens):\n";
echo "'description' => 'This tool is used to get the current weather conditions '\n";
echo "                . 'including temperature, humidity, wind speed, and forecast '\n";
echo "                . 'for any location in the world. You can specify the location '\n";
echo "                . 'by providing a city name and optionally a state or country. '\n";
echo "                . 'The tool will return comprehensive weather information.'\n\n";

echo "✅ Concise (token-efficient):\n";
echo "'description' => 'Get current weather for a location. '\n";
echo "                . 'Returns temperature, conditions, and forecast.'\n\n";

echo "Tips:\n";
echo "  • Be specific but brief\n";
echo "  • Remove redundant information\n";
echo "  • Put details in input_schema descriptions\n";
echo "  • Focus on what Claude needs to know\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: Use parameter descriptions
echo "Example 2: Leverage Parameter Descriptions\n";
echo "-------------------------------------------\n";
echo "Move details to input_schema instead of tool description\n\n";

$efficientTool = [
    'name' => 'search_database',
    'description' => 'Search the product database',  // Brief
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'query' => [
                'type' => 'string',
                'description' => 'Search query. Supports keywords, product names, and SKUs'  // Detailed
            ],
            'category' => [
                'type' => 'string',
                'description' => 'Filter by category: electronics, clothing, or home'
            ],
            'max_results' => [
                'type' => 'integer',
                'description' => 'Maximum results to return (1-100)',
                'default' => 10
            ]
        ],
        'required' => ['query']
    ]
];

echo "Efficient tool definition:\n";
echo json_encode($efficientTool, JSON_PRETTY_PRINT) . "\n\n";

echo "Benefits:\n";
echo "  • Main description is brief (fewer tokens per request)\n";
echo "  • Parameter details where needed\n";
echo "  • Claude still gets full information\n";
echo "  • Token savings compound across requests\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 3: Limit number of tools
echo "Example 3: Limit Number of Tools\n";
echo "---------------------------------\n";
echo "Each tool definition adds tokens to every request\n\n";

echo "Tool count impact:\n";
echo "  • 1 tool:  ~100 tokens overhead\n";
echo "  • 5 tools: ~500 tokens overhead\n";
echo "  • 10 tools: ~1000 tokens overhead\n\n";

echo "Strategies:\n";
echo "  ✓ Only include tools relevant to current task\n";
echo "  ✓ Combine related functionality into single tools\n";
echo "  ✓ Use conditional tool inclusion\n";
echo "  ✓ Consider tool_choice to limit execution\n\n";

echo "Example - Conditional tools:\n";
echo "```php\n";
echo "\$tools = [];\n";
echo "\n";
echo "// Always include\n";
echo "\$tools[] = ['name' => 'calculator', ...];\n";
echo "\n";
echo "// Only for specific tasks\n";
echo "if (\$needsWebAccess) {\n";
echo "    \$tools[] = ['type' => 'web_search_20250305', 'name' => 'web_search'];\n";
echo "}\n";
echo "\n";
echo "if (\$needsFileAccess) {\n";
echo "    \$tools[] = ['type' => 'text_editor_20250728', 'name' => 'editor'];\n";
echo "}\n";
echo "```\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 4: Optimize tool results
echo "Example 4: Optimize Tool Results\n";
echo "---------------------------------\n";
echo "Return only necessary information in tool_result\n\n";

echo "❌ Inefficient (verbose result):\n";
echo "```php\n";
echo "\$result = [\n";
echo "    'status' => 'success',\n";
echo "    'timestamp' => date('Y-m-d H:i:s'),\n";
echo "    'request_id' => uniqid(),\n";
echo "    'data' => \$actualData,\n";
echo "    'metadata' => ['version' => '1.0', 'source' => 'api'],\n";
echo "    'debug_info' => [...]\n";
echo "];\n";
echo "```\n\n";

echo "✅ Efficient (essential only):\n";
echo "```php\n";
echo "\$result = \$actualData;  // Just the data Claude needs\n";
echo "```\n\n";

echo "Tips:\n";
echo "  • Return only what Claude needs\n";
echo "  • Remove metadata and debug info\n";
echo "  • Summarize large responses\n";
echo "  • Truncate long text appropriately\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 5: Token optimization summary
echo "Example 5: Token Optimization Summary\n";
echo "--------------------------------------\n\n";

echo "✓ Tool Definitions:\n";
echo "  • Brief descriptions (move details to parameters)\n";
echo "  • Minimal tool count (only necessary ones)\n";
echo "  • Efficient schema design\n";
echo "  • Remove redundant properties\n\n";

echo "✓ Tool Results:\n";
echo "  • Return essential data only\n";
echo "  • Summarize long content\n";
echo "  • Remove debug information\n";
echo "  • Truncate appropriately\n\n";

echo "✓ Caching:\n";
echo "  • Cache tool definitions with prompt caching\n";
echo "  • 90% cost reduction on repeated tools\n";
echo "  • Mark last tool with cache_control\n\n";

echo "✓ Context Management:\n";
echo "  • Use context editing to clear old tool results\n";
echo "  • Combine with memory for persistence\n";
echo "  • Monitor token usage\n";
echo "  • Plan context budgets\n\n";

echo "Example - Cached tools:\n";
echo "```php\n";
echo "\$tools = [\n";
echo "    ['name' => 'tool1', ...],\n";
echo "    ['name' => 'tool2', ...],\n";
echo "    [\n";
echo "        'name' => 'tool3',\n";
echo "        ...,\n";
echo "        'cache_control' => ['type' => 'ephemeral']  // Cache all tools\n";
echo "    ]\n";
echo "];\n";
echo "```\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "✓ Token-efficient tool use examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Keep tool descriptions brief (details in parameters)\n";
echo "• Limit number of tools per request\n";
echo "• Return minimal data in tool_result\n";
echo "• Cache tool definitions for 90% savings\n";
echo "• Use context editing to clear old results\n";
echo "• Combine with memory for persistence\n";
echo "• Monitor token usage across requests\n";
echo "• Token savings compound significantly over time\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • examples/prompt_caching.php - Cache tool definitions\n";
echo "  • examples/context_editing.php - Clear tool results\n";
echo "  • examples/memory_tool.php - Persistent storage\n";

