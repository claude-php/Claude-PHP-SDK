#!/usr/bin/env php
<?php
/**
 * Server-Side Tools Example
 *
 * Demonstrates how server-side tools (like code execution) are handled
 * by the tool runners. Server-side tools are executed by the Claude API,
 * not locally, so no local handler is required.
 *
 * Based on Python SDK v0.76.0 server-side tools support (#1086).
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;
use function ClaudePhp\Lib\Tools\beta_tool;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Server-Side Tools Example ===\n\n";

// Example 1: Understanding server-side vs client-side tools
echo "Example 1: Server-Side vs Client-Side Tools\n";
echo "--------------------------------------------\n\n";

echo "Tool Types:\n\n";
echo "CLIENT-SIDE TOOLS:\n";
echo "  • Executed by your PHP application\n";
echo "  • You provide a handler function\n";
echo "  • Examples: get_weather, search_database, send_email\n";
echo "  • Block type: 'tool_use'\n\n";

echo "SERVER-SIDE TOOLS:\n";
echo "  • Executed by Claude's API (on Anthropic's servers)\n";
echo "  • No handler function needed\n";
echo "  • Examples: code_execution, bash_20250124\n";
echo "  • Block type: 'server_tool_use'\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 2: Using BetaToolRunner with server-side tools
echo "Example 2: BetaToolRunner with Server-Side Tools\n";
echo "-------------------------------------------------\n\n";

echo "The tool runner automatically handles both types:\n\n";

echo "```php\n";
echo "// Define only client-side tools\n";
echo "\$getWeather = beta_tool(\n";
echo "    handler: fn(\$args) => \"Weather in {\$args['location']}: Sunny\",\n";
echo "    name: 'get_weather',\n";
echo "    description: 'Get weather for a location',\n";
echo "    inputSchema: [\n";
echo "        'type' => 'object',\n";
echo "        'properties' => ['location' => ['type' => 'string']],\n";
echo "        'required' => ['location'],\n";
echo "    ]\n";
echo ");\n\n";
echo "// Run tool runner - it will handle both client and server tools\n";
echo "\$runner = \$client->beta()->messages()->toolRunner([\n";
echo "    'model' => 'claude-sonnet-4-5-20250929',\n";
echo "    'max_tokens' => 4096,\n";
echo "    'messages' => [\n";
echo "        ['role' => 'user', 'content' => 'Get SF weather and execute: print(2+2)'],\n";
echo "    ],\n";
echo "], [\$getWeather]);\n\n";
echo "foreach (\$runner as \$message) {\n";
echo "    // Tool runner handles:\n";
echo "    // 1. Client-side get_weather -> executes locally\n";
echo "    // 2. Server-side code_execution -> handled by API\n";
echo "    echo \$message->content[0]['text'] ?? '';\n";
echo "}\n";
echo "```\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 3: Mixed tools in a single request
echo "Example 3: Mixed Client-Side and Server-Side Tools\n";
echo "---------------------------------------------------\n\n";

echo "In a single conversation:\n\n";
echo "USER: 'Get weather for NYC and execute some Python code'\n\n";
echo "CLAUDE's response might include:\n";
echo "  1. tool_use block: get_weather (client-side)\n";
echo "     → Your handler executes and returns result\n";
echo "  2. server_tool_use block: code_execution (server-side)\n";
echo "     → API executes and includes result automatically\n\n";

echo "The tool runner handles this seamlessly:\n";
echo "  • Executes client-side tools locally\n";
echo "  • Skips execution for server-side tools (API handles them)\n";
echo "  • Continues conversation with all results\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 4: Server-side tools don't require handlers
echo "Example 4: No Handler Required for Server Tools\n";
echo "------------------------------------------------\n\n";

echo "You don't need to provide handlers for server-side tools:\n\n";

echo "```php\n";
echo "// No code_execution handler needed!\n";
echo "\$runner = \$client->beta()->messages()->toolRunner([\n";
echo "    'model' => 'claude-sonnet-4-5-20250929',\n";
echo "    'max_tokens' => 4096,\n";
echo "    'messages' => [\n";
echo "        ['role' => 'user', 'content' => 'Execute: print(\"Hello\")'],\n";
echo "    ],\n";
echo "], []);  // Empty tools array - server tools work anyway!\n\n";
echo "foreach (\$runner as \$message) {\n";
echo "    // Claude will use server-side code execution\n";
echo "    // without any local handler\n";
echo "}\n";
echo "```\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 5: Tool use block structure
echo "Example 5: Understanding Tool Block Structures\n";
echo "-----------------------------------------------\n\n";

echo "CLIENT-SIDE TOOL BLOCK:\n";
echo "```json\n";
echo "{\n";
echo "  \"type\": \"tool_use\",\n";
echo "  \"id\": \"toolu_01ABC123\",\n";
echo "  \"name\": \"get_weather\",\n";
echo "  \"input\": {\"location\": \"San Francisco\"}\n";
echo "}\n";
echo "```\n\n";

echo "SERVER-SIDE TOOL BLOCK:\n";
echo "```json\n";
echo "{\n";
echo "  \"type\": \"server_tool_use\",\n";
echo "  \"id\": \"toolu_server_001\",\n";
echo "  \"name\": \"code_execution\",\n";
echo "  \"input\": {\n";
echo "    \"language\": \"python\",\n";
echo "    \"code\": \"print('Hello, World!')\"\n";
echo "  }\n";
echo "}\n";
echo "```\n\n";

echo "The key difference is the 'type' field:\n";
echo "  • 'tool_use' = client-side (needs handler)\n";
echo "  • 'server_tool_use' = server-side (no handler)\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 6: Regular ToolRunner also supports server-side tools
echo "Example 6: All Tool Runners Support Server Tools\n";
echo "-------------------------------------------------\n\n";

echo "Server-side tool support is available in:\n\n";

echo "1. BetaToolRunner (recommended for new code):\n";
echo "   \$client->beta()->messages()->toolRunner(...)\n\n";

echo "2. ToolRunner (standard synchronous):\n";
echo "   new ToolRunner(\$client, \$tools)\n\n";

echo "3. StreamingToolRunner (with streaming):\n";
echo "   new StreamingToolRunner(\$client, \$tools)\n\n";

echo "All three automatically handle server-side tools!\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 7: Common server-side tools
echo "Example 7: Common Server-Side Tools\n";
echo "------------------------------------\n\n";

echo "Server-side tools available from Claude:\n\n";

echo "• code_execution: Execute Python code securely\n";
echo "  - Sandboxed environment\n";
echo "  - Access to common libraries (numpy, pandas, etc.)\n";
echo "  - Returns stdout, stderr, and exit code\n\n";

echo "• bash_20250124: Execute bash commands\n";
echo "  - Sandboxed shell environment\n";
echo "  - Limited to safe operations\n";
echo "  - Returns command output\n\n";

echo "Note: Availability depends on your API tier and beta features enabled.\n\n";

echo str_repeat("=", 80) . "\n\n";

echo "✓ Server-side tools examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Server-side tools are executed by Claude's API, not locally\n";
echo "• No handler functions needed for server-side tools\n";
echo "• Tool runners automatically differentiate between types\n";
echo "• You can mix client-side and server-side tools in one request\n";
echo "• Block type 'server_tool_use' indicates server-side execution\n";
echo "• All tool runner classes support server-side tools\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • examples/tool_use_overview.php - General tool use\n";
echo "  • examples/computer_use_tool.php - Advanced server-side tools\n";
echo "  • examples/code_execution_tool.php - Code execution examples\n";
