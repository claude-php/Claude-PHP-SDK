#!/usr/bin/env php
<?php
/**
 * Tool Search - PHP examples demonstrating tool search functionality
 * 
 * Tool search allows Claude to dynamically discover and use tools
 * from a large collection without loading all tools into the initial
 * system prompt. This improves performance and token efficiency.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Tool Search - Dynamic Tool Discovery ===\n\n";
echo "Enables Claude to discover and use tools from large collections\n";
echo "without loading all tools into the system prompt.\n\n";

// Example 1: Tool search overview
echo "Example 1: Tool Search Overview\n";
echo "--------------------------------\n\n";

echo "Tool search tool types:\n";
echo "  • tool_search_tool_bm25_20251119 - BM25 algorithm-based search\n";
echo "  • tool_search_tool_regex_20251119 - Regex-based search\n\n";

echo "Key concepts:\n";
echo "  • defer_loading: Tool not loaded in initial prompt\n";
echo "  • tool_reference: Reference to a tool in search results\n";
echo "  • allowed_callers: Who can invoke the tool\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 2: BM25 search tool
echo "Example 2: BM25 Search Tool\n";
echo "----------------------------\n\n";

$bm25Tool = [
    'type' => 'tool_search_tool_bm25_20251119',
    'name' => 'tool_search_tool_bm25',
    'allowed_callers' => ['direct', 'code_execution_20250825'],
    'defer_loading' => false,
    'strict' => false,
];

echo "BM25 Search Tool Configuration:\n";
echo json_encode($bm25Tool, JSON_PRETTY_PRINT) . "\n\n";

echo "BM25 is ideal for:\n";
echo "  • Keyword-based tool discovery\n";
echo "  • Natural language queries\n";
echo "  • Relevance-ranked results\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 3: Regex search tool
echo "Example 3: Regex Search Tool\n";
echo "-----------------------------\n\n";

$regexTool = [
    'type' => 'tool_search_tool_regex_20251119',
    'name' => 'tool_search_tool_regex',
    'allowed_callers' => ['direct'],
    'defer_loading' => false,
    'strict' => false,
];

echo "Regex Search Tool Configuration:\n";
echo json_encode($regexTool, JSON_PRETTY_PRINT) . "\n\n";

echo "Regex search is ideal for:\n";
echo "  • Pattern-based tool discovery\n";
echo "  • Exact name matching\n";
echo "  • Category filtering\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 4: Deferred tool loading
echo "Example 4: Deferred Tool Loading\n";
echo "----------------------------------\n\n";

echo "When defer_loading is true, tools are only loaded when returned\n";
echo "via tool_reference from a search:\n\n";

$deferredTool = [
    'name' => 'get_weather',
    'description' => 'Get weather for a location',
    'defer_loading' => true, // Not in initial system prompt
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'location' => ['type' => 'string', 'description' => 'City name'],
            'units' => ['type' => 'string', 'enum' => ['c', 'f']],
        ],
        'required' => ['location'],
    ],
];

echo "Deferred tool configuration:\n";
echo json_encode($deferredTool, JSON_PRETTY_PRINT) . "\n\n";

echo "Benefits of defer_loading:\n";
echo "  • Reduces initial prompt size\n";
echo "  • Saves tokens for large tool collections\n";
echo "  • Faster initial response\n";
echo "  • Tools loaded on-demand\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 5: Tool reference blocks
echo "Example 5: Tool Reference Blocks\n";
echo "----------------------------------\n\n";

echo "Search results return tool references:\n\n";

$toolReference = [
    'type' => 'tool_reference',
    'tool_name' => 'get_weather',
];

echo "Tool reference structure:\n";
echo json_encode($toolReference, JSON_PRETTY_PRINT) . "\n\n";

echo "Tool reference in search results:\n";
$searchResult = [
    'type' => 'tool_search_tool_search_result',
    'tool_references' => [
        ['type' => 'tool_reference', 'tool_name' => 'get_weather'],
        ['type' => 'tool_reference', 'tool_name' => 'get_forecast'],
    ],
];

echo json_encode($searchResult, JSON_PRETTY_PRINT) . "\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 6: Custom tool searcher implementation
echo "Example 6: Custom Tool Searcher Implementation\n";
echo "------------------------------------------------\n\n";

echo "Implement a custom tool searcher:\n\n";

echo "```php\n";
echo "function createToolSearcher(\$tools) {\n";
echo "    return function(\$keyword) use (\$tools) {\n";
echo "        \$results = [];\n";
echo "        \n";
echo "        foreach (\$tools as \$tool) {\n";
echo "            // Search in tool name and description\n";
echo "            \$searchText = json_encode(\$tool);\n";
echo "            \n";
echo "            if (stripos(\$searchText, \$keyword) !== false) {\n";
echo "                \$results[] = [\n";
echo "                    'type' => 'tool_reference',\n";
echo "                    'tool_name' => \$tool['name'],\n";
echo "                ];\n";
echo "            }\n";
echo "        }\n";
echo "        \n";
echo "        return \$results;\n";
echo "    };\n";
echo "}\n\n";
echo "// Usage\n";
echo "\$tools = [\n";
echo "    ['name' => 'get_weather', 'description' => 'Get weather data'],\n";
echo "    ['name' => 'get_forecast', 'description' => 'Get weather forecast'],\n";
echo "    ['name' => 'get_stock_price', 'description' => 'Get stock prices'],\n";
echo "];\n\n";
echo "\$searcher = createToolSearcher(\$tools);\n";
echo "\$results = \$searcher('weather');\n";
echo "// Returns: get_weather, get_forecast\n";
echo "```\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 7: Full workflow with tool search
echo "Example 7: Full Workflow with Tool Search\n";
echo "-------------------------------------------\n\n";

echo "Complete example with search tool and deferred tools:\n\n";

echo "```php\n";
echo "// Many deferred tools\n";
echo "\$deferredTools = array_map(function(\$tool) {\n";
echo "    \$tool['defer_loading'] = true;\n";
echo "    return \$tool;\n";
echo "}, \$allTools);\n\n";
echo "// Add search tool\n";
echo "\$searchTool = [\n";
echo "    'name' => 'search_available_tools',\n";
echo "    'description' => 'Search for useful tools using a query string',\n";
echo "    'input_schema' => [\n";
echo "        'type' => 'object',\n";
echo "        'properties' => [\n";
echo "            'keyword' => ['type' => 'string', 'description' => 'Search keyword'],\n";
echo "        ],\n";
echo "        'required' => ['keyword'],\n";
echo "    ],\n";
echo "];\n\n";
echo "// Create request\n";
echo "\$response = \$client->messages()->create([\n";
echo "    'model' => 'claude-sonnet-4-5-20250929',\n";
echo "    'max_tokens' => 1024,\n";
echo "    'tools' => array_merge(\$deferredTools, [\$searchTool]),\n";
echo "    'messages' => [[\n";
echo "        'role' => 'user',\n";
echo "        'content' => 'What is the weather in San Francisco?'\n";
echo "    ]],\n";
echo "    'betas' => ['tool-search-tool-2025-10-19'],\n";
echo "]);\n";
echo "```\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 8: Error handling
echo "Example 8: Error Handling\n";
echo "--------------------------\n\n";

echo "Search tool result error types:\n\n";

$errorTypes = [
    'invalid_tool_input' => 'Invalid search query or parameters',
    'unavailable' => 'Search service temporarily unavailable',
    'too_many_requests' => 'Rate limit exceeded',
    'execution_time_exceeded' => 'Search took too long',
];

echo "Error codes:\n";
foreach ($errorTypes as $code => $description) {
    echo "  • {$code}: {$description}\n";
}
echo "\n";

echo "Error response structure:\n";
$errorResult = [
    'type' => 'tool_search_tool_result_error',
    'error_code' => 'unavailable',
    'error_message' => 'Search service is temporarily unavailable',
];

echo json_encode($errorResult, JSON_PRETTY_PRINT) . "\n\n";

echo str_repeat("=", 80) . "\n\n";

echo "✓ Tool search examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Tool search enables dynamic tool discovery\n";
echo "• Types: BM25 (keyword-based) and Regex (pattern-based)\n";
echo "• defer_loading keeps tools out of initial prompt\n";
echo "• tool_reference blocks point to discovered tools\n";
echo "• Ideal for large tool collections (100+ tools)\n";
echo "• Reduces token usage and improves performance\n";
echo "• Enable via 'tool-search-tool-2025-10-19' beta\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • examples/tools.php - Basic tool use\n";
echo "  • examples/tool_use_overview.php - Tool use patterns\n";
echo "  • examples/streaming_with_tools.php - Streaming tools\n";

