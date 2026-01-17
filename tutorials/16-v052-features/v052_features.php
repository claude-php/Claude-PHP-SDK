#!/usr/bin/env php
<?php
/**
 * Tutorial 16: New Features in v0.5.2
 * 
 * Demonstrates the new features introduced in v0.5.2:
 * - Server-side tools (executed by Claude's API)
 * - Authentication flexibility
 * - Enhanced stream management
 * 
 * This agent uses both client-side and server-side tools seamlessly.
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../helpers.php';

use ClaudePhp\ClaudePhp;
use function ClaudePhp\Lib\Tools\beta_tool;

loadEnv(__DIR__ . '/../../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Tutorial 16: v0.5.2 Features Demo ===\n\n";

// Part 1: Server-Side vs Client-Side Tools
echo "PART 1: Understanding Tool Types\n";
echo str_repeat("=", 80) . "\n\n";

echo "This demo will show:\n";
echo "1. Client-side tool (executed by your PHP code)\n";
echo "2. Server-side tool (executed by Claude's API)\n";
echo "3. How tool runners handle both automatically\n\n";

// Define client-side tools
$weatherTool = beta_tool(
    handler: function(array $args): string {
        $location = $args['location'] ?? 'Unknown';
        echo "🔧 [CLIENT] Executing weather tool for: {$location}\n";
        
        // Simulate API call
        sleep(1);
        $weather = ['Sunny', 'Cloudy', 'Rainy'][rand(0, 2)];
        $temp = rand(60, 85);
        
        return "Weather in {$location}: {$weather}, {$temp}°F";
    },
    name: 'get_weather',
    description: 'Get current weather for a location',
    inputSchema: [
        'type' => 'object',
        'properties' => [
            'location' => [
                'type' => 'string',
                'description' => 'City name or location'
            ],
        ],
        'required' => ['location'],
    ]
);

$searchTool = beta_tool(
    handler: function(array $args): string {
        $query = $args['query'] ?? '';
        echo "🔧 [CLIENT] Executing search tool for: {$query}\n";
        
        // Simulate search
        sleep(1);
        return "Search results for '{$query}':\n1. Result about {$query}\n2. More info on {$query}\n3. Latest news about {$query}";
    },
    name: 'web_search',
    description: 'Search the web for information',
    inputSchema: [
        'type' => 'object',
        'properties' => [
            'query' => [
                'type' => 'string',
                'description' => 'Search query'
            ],
        ],
        'required' => ['query'],
    ]
);

echo "Starting agent with mixed tools...\n\n";

// Run the agent
$runner = $client->beta()->messages()->toolRunner([
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 4096,
    'messages' => [
        [
            'role' => 'user',
            'content' => 'What is the weather in Paris? Also, search for "Eiffel Tower facts".',
        ],
    ],
], [$weatherTool, $searchTool]);

$iterationCount = 0;

foreach ($runner as $message) {
    $iterationCount++;
    
    echo "\n" . str_repeat("─", 80) . "\n";
    echo "Iteration {$iterationCount}: Message ID = {$message->id}\n";
    echo str_repeat("─", 80) . "\n\n";
    
    $hasTools = false;
    
    foreach ($message->content as $block) {
        $type = $block['type'] ?? '';
        
        if ($type === 'text') {
            echo "💬 Claude's Response:\n";
            echo "   " . $block['text'] . "\n\n";
        } 
        elseif ($type === 'tool_use') {
            $hasTools = true;
            echo "🔧 CLIENT-SIDE TOOL CALL:\n";
            echo "   Tool: {$block['name']}\n";
            echo "   ID: {$block['id']}\n";
            echo "   Input: " . json_encode($block['input']) . "\n";
            echo "   ℹ️  This will be executed by your PHP code\n\n";
        }
        elseif ($type === 'server_tool_use') {
            $hasTools = true;
            echo "🖥️  SERVER-SIDE TOOL CALL:\n";
            echo "   Tool: {$block['name']}\n";
            echo "   ID: {$block['id']}\n";
            echo "   Input: " . json_encode($block['input']) . "\n";
            echo "   ℹ️  This is executed by Claude's API, not locally\n\n";
        }
    }
    
    if (!$hasTools && $message->stop_reason === 'end_turn') {
        echo "✅ Agent completed! Stop reason: {$message->stop_reason}\n";
    }
    
    echo "📊 Tokens Used: Input={$message->usage->input_tokens}, Output={$message->usage->output_tokens}\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Part 2: Authentication Flexibility Demo
echo "PART 2: Authentication Flexibility\n";
echo str_repeat("=", 80) . "\n\n";

echo "The SDK now supports multiple authentication methods:\n\n";

echo "1. Traditional API Key (default):\n";
echo "   \$client = new ClaudePhp(apiKey: \$_ENV['ANTHROPIC_API_KEY']);\n\n";

echo "2. OAuth2 Bearer Token:\n";
echo "   \$client = new ClaudePhp(\n";
echo "       apiKey: null,\n";
echo "       customHeaders: ['Authorization' => 'Bearer your-token']\n";
echo "   );\n\n";

echo "3. Custom x-api-key (for proxies):\n";
echo "   \$client = new ClaudePhp(\n";
echo "       apiKey: null,\n";
echo "       customHeaders: ['x-api-key' => 'proxy-key']\n";
echo "   );\n\n";

echo "4. Enterprise SSO:\n";
echo "   \$client = new ClaudePhp(\n";
echo "       apiKey: null,\n";
echo "       customHeaders: [\n";
echo "           'Authorization' => \"Bearer {\$azureToken}\",\n";
echo "           'X-Tenant-ID' => 'tenant-id'\n";
echo "       ]\n";
echo "   );\n\n";

echo str_repeat("=", 80) . "\n\n";

// Part 3: Stream Management Demo
echo "PART 3: Enhanced Stream Management\n";
echo str_repeat("=", 80) . "\n\n";

echo "Demonstrating automatic stream cleanup...\n\n";

$stream = $client->messages()->stream([
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 200,
    'messages' => [
        ['role' => 'user', 'content' => 'Say hello in 3 words'],
    ],
]);

echo "Streaming response: ";

foreach ($stream as $event) {
    if (($event['type'] ?? '') === 'content_block_delta') {
        $text = $event['delta']['text'] ?? '';
        echo $text;
        flush();
    }
}

echo "\n\n✅ Stream automatically closed after iteration!\n";
echo "   No manual cleanup needed - handled by __destruct()\n\n";

echo str_repeat("=", 80) . "\n\n";

// Summary
echo "📚 TUTORIAL SUMMARY\n";
echo str_repeat("=", 80) . "\n\n";

echo "What you learned:\n\n";

echo "✅ Server-Side Tools:\n";
echo "   - Executed by Claude's API, not your code\n";
echo "   - Identified by 'server_tool_use' type\n";
echo "   - No handler function required\n";
echo "   - Perfect for code execution, bash commands\n\n";

echo "✅ Client-Side Tools:\n";
echo "   - Executed by your PHP application\n";
echo "   - Type: 'tool_use'\n";
echo "   - You provide the handler function\n";
echo "   - Great for APIs, databases, file operations\n\n";

echo "✅ Tool Runners:\n";
echo "   - Automatically handle both tool types\n";
echo "   - No code changes needed\n";
echo "   - Seamless execution\n\n";

echo "✅ Authentication Flexibility:\n";
echo "   - Multiple auth methods supported\n";
echo "   - Perfect for enterprise scenarios\n";
echo "   - OAuth2, Bearer tokens, proxies\n\n";

echo "✅ Stream Management:\n";
echo "   - Automatic cleanup via __destruct()\n";
echo "   - Idempotent close() method\n";
echo "   - Guaranteed resource freeing\n\n";

echo str_repeat("=", 80) . "\n\n";

echo "🎯 Next Steps:\n";
echo "   1. Try adding server-side tools to your agents\n";
echo "   2. Experiment with custom authentication\n";
echo "   3. Review streaming patterns in production code\n";
echo "   4. Mix client and server tools freely!\n\n";

echo "📖 See examples/server_side_tools.php for more examples\n";
echo "📖 See examples/authentication_flexibility.php for auth patterns\n\n";

echo "Tutorial complete! 🎉\n";
