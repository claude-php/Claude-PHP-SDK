#!/usr/bin/env php
<?php
/**
 * MCP Toolset - PHP examples for Model Context Protocol toolset configuration
 * 
 * MCP (Model Context Protocol) toolsets allow you to configure tools
 * from MCP servers with fine-grained control over individual tool settings.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== MCP Toolset - Model Context Protocol Tools ===\n\n";
echo "Configure tools from MCP servers with fine-grained control.\n\n";

// Example 1: MCP Toolset overview
echo "Example 1: MCP Toolset Overview\n";
echo "---------------------------------\n\n";

echo "MCP Toolset allows you to:\n";
echo "  • Configure tools from MCP servers\n";
echo "  • Set default configurations for all tools\n";
echo "  • Override settings for specific tools\n";
echo "  • Control tool loading behavior\n\n";

echo "Key components:\n";
echo "  • mcp_server_name: Name of the MCP server\n";
echo "  • default_config: Settings for all tools\n";
echo "  • configs: Per-tool overrides\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 2: Basic MCP toolset configuration
echo "Example 2: Basic MCP Toolset Configuration\n";
echo "--------------------------------------------\n\n";

$mcpToolset = [
    'type' => 'mcp_toolset',
    'mcp_server_name' => 'my-mcp-server',
    'default_config' => [
        'enabled' => true,
        'defer_loading' => false,
    ],
];

echo "Basic Configuration:\n";
echo json_encode($mcpToolset, JSON_PRETTY_PRINT) . "\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 3: Per-tool configuration
echo "Example 3: Per-Tool Configuration\n";
echo "-----------------------------------\n\n";

$mcpToolsetWithConfigs = [
    'type' => 'mcp_toolset',
    'mcp_server_name' => 'data-server',
    'default_config' => [
        'enabled' => true,
        'defer_loading' => true, // Defer all by default
    ],
    'configs' => [
        // Override for specific tools
        'get_user' => [
            'enabled' => true,
            'defer_loading' => false, // Load immediately
        ],
        'delete_user' => [
            'enabled' => false, // Disable dangerous tool
        ],
        'list_users' => [
            'enabled' => true,
            'defer_loading' => true,
        ],
    ],
];

echo "Configuration with per-tool overrides:\n";
echo json_encode($mcpToolsetWithConfigs, JSON_PRETTY_PRINT) . "\n\n";

echo "This configuration:\n";
echo "  • Enables all tools by default\n";
echo "  • Defers loading for all tools by default\n";
echo "  • Loads 'get_user' immediately\n";
echo "  • Disables 'delete_user' entirely\n";
echo "  • Keeps 'list_users' deferred\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 4: Cache control with MCP toolsets
echo "Example 4: Cache Control with MCP Toolsets\n";
echo "--------------------------------------------\n\n";

$mcpToolsetWithCache = [
    'type' => 'mcp_toolset',
    'mcp_server_name' => 'analytics-server',
    'cache_control' => [
        'type' => 'ephemeral',
    ],
    'default_config' => [
        'enabled' => true,
    ],
];

echo "Configuration with cache control:\n";
echo json_encode($mcpToolsetWithCache, JSON_PRETTY_PRINT) . "\n\n";

echo "Cache control benefits:\n";
echo "  • Reduces repeated processing costs\n";
echo "  • Speeds up subsequent requests\n";
echo "  • Creates cache breakpoint at this block\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 5: Multiple MCP servers
echo "Example 5: Multiple MCP Servers\n";
echo "---------------------------------\n\n";

echo "Configure tools from multiple MCP servers:\n\n";

echo "```php\n";
echo "\$tools = [\n";
echo "    // Database server tools\n";
echo "    [\n";
echo "        'type' => 'mcp_toolset',\n";
echo "        'mcp_server_name' => 'database-server',\n";
echo "        'default_config' => ['enabled' => true],\n";
echo "        'configs' => [\n";
echo "            'execute_query' => ['enabled' => true],\n";
echo "            'drop_table' => ['enabled' => false], // Dangerous!\n";
echo "        ],\n";
echo "    ],\n";
echo "    // Analytics server tools\n";
echo "    [\n";
echo "        'type' => 'mcp_toolset',\n";
echo "        'mcp_server_name' => 'analytics-server',\n";
echo "        'default_config' => [\n";
echo "            'enabled' => true,\n";
echo "            'defer_loading' => true,\n";
echo "        ],\n";
echo "    ],\n";
echo "    // File server tools\n";
echo "    [\n";
echo "        'type' => 'mcp_toolset',\n";
echo "        'mcp_server_name' => 'file-server',\n";
echo "        'default_config' => ['enabled' => true],\n";
echo "        'configs' => [\n";
echo "            'read_file' => ['defer_loading' => false],\n";
echo "            'delete_file' => ['enabled' => false],\n";
echo "        ],\n";
echo "    ],\n";
echo "];\n";
echo "```\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 6: Security patterns
echo "Example 6: Security Patterns\n";
echo "------------------------------\n\n";

echo "Disable dangerous operations:\n\n";

$secureConfig = [
    'type' => 'mcp_toolset',
    'mcp_server_name' => 'admin-server',
    'default_config' => [
        'enabled' => false, // Disable all by default
    ],
    'configs' => [
        // Only enable safe operations
        'get_status' => ['enabled' => true],
        'list_items' => ['enabled' => true],
        'get_details' => ['enabled' => true],
        // Dangerous operations stay disabled
        // 'delete_all' => disabled by default
        // 'reset_system' => disabled by default
    ],
];

echo "Secure configuration (whitelist approach):\n";
echo json_encode($secureConfig, JSON_PRETTY_PRINT) . "\n\n";

echo "Security best practices:\n";
echo "  • Default to disabled, enable explicitly\n";
echo "  • Never enable destructive operations\n";
echo "  • Use defer_loading for rarely-used tools\n";
echo "  • Audit tool usage regularly\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 7: Performance optimization
echo "Example 7: Performance Optimization\n";
echo "-------------------------------------\n\n";

echo "Optimize for large tool collections:\n\n";

$performanceConfig = [
    'type' => 'mcp_toolset',
    'mcp_server_name' => 'comprehensive-server',
    'default_config' => [
        'enabled' => true,
        'defer_loading' => true, // Defer all by default
    ],
    'configs' => [
        // Only load frequently-used tools immediately
        'common_operation_1' => ['defer_loading' => false],
        'common_operation_2' => ['defer_loading' => false],
        // Rarely-used tools remain deferred
        // They load on-demand via tool search
    ],
];

echo "Performance-optimized configuration:\n";
echo json_encode($performanceConfig, JSON_PRETTY_PRINT) . "\n\n";

echo "Performance benefits:\n";
echo "  • Smaller initial system prompt\n";
echo "  • Faster time-to-first-token\n";
echo "  • Lower token costs\n";
echo "  • Tools available on-demand\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 8: Integration with message creation
echo "Example 8: Integration with Message Creation\n";
echo "----------------------------------------------\n\n";

echo "```php\n";
echo "\$response = \$client->messages()->create([\n";
echo "    'model' => 'claude-sonnet-4-20250514',\n";
echo "    'max_tokens' => 4096,\n";
echo "    'tools' => [\n";
echo "        // Regular tool\n";
echo "        [\n";
echo "            'name' => 'calculator',\n";
echo "            'description' => 'Perform calculations',\n";
echo "            'input_schema' => [...],\n";
echo "        ],\n";
echo "        // MCP Toolset\n";
echo "        [\n";
echo "            'type' => 'mcp_toolset',\n";
echo "            'mcp_server_name' => 'my-server',\n";
echo "            'default_config' => ['enabled' => true],\n";
echo "        ],\n";
echo "    ],\n";
echo "    'messages' => [\n";
echo "        ['role' => 'user', 'content' => 'Help me analyze this data'],\n";
echo "    ],\n";
echo "]);\n";
echo "```\n\n";

echo str_repeat("=", 80) . "\n\n";

echo "✓ MCP Toolset examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Toolset type: 'mcp_toolset'\n";
echo "• Configure tools from MCP servers\n";
echo "• default_config: Settings for all tools\n";
echo "• configs: Per-tool overrides by name\n";
echo "• enabled: Turn tools on/off\n";
echo "• defer_loading: Load on-demand\n";
echo "• Use whitelist approach for security\n";
echo "• Defer rarely-used tools for performance\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • examples/tools.php - Basic tool use\n";
echo "  • examples/tool_search.php - Tool discovery\n";
echo "  • examples/prompt_caching.php - Cache control\n";

