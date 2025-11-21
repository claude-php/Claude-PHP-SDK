#!/usr/bin/env php
<?php
/**
 * Tool Use Implementation - PHP examples from:
 * https://docs.claude.com/en/docs/agents-and-tools/tool-use/implement-tool-use
 * 
 * Complete guide to implementing tool use with proper patterns and error handling.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Tool Use Implementation - Best Practices ===\n\n";

// Example 1: Multi-tool implementation
echo "Example 1: Multiple Tools\n";
echo "-------------------------\n";
echo "Provide multiple tools for Claude to choose from\n\n";

try {
    $tools = [
        [
            'name' => 'get_weather',
            'description' => 'Get current weather for a location',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'location' => ['type' => 'string', 'description' => 'City and state']
                ],
                'required' => ['location']
            ]
        ],
        [
            'name' => 'get_time',
            'description' => 'Get current time in a timezone',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'timezone' => ['type' => 'string', 'description' => 'IANA timezone']
                ],
                'required' => ['timezone']
            ]
        ],
        [
            'name' => 'calculate',
            'description' => 'Perform mathematical calculation',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'expression' => ['type' => 'string', 'description' => 'Math expression']
                ],
                'required' => ['expression']
            ]
        ]
    ];
    
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'tools' => $tools,
        'messages' => [
            ['role' => 'user', 'content' => 'What is 25 * 4?']
        ]
    ]);

    echo "Provided 3 tools: get_weather, get_time, calculate\n";
    echo "Question: What is 25 * 4?\n\n";
    echo "Claude chose: ";
    foreach ($response->content as $block) {
        if ($block['type'] === 'tool_use') {
            echo "{$block['name']} with input " . json_encode($block['input']) . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: Multi-turn tool conversation
echo "Example 2: Multi-turn Tool Conversation\n";
echo "----------------------------------------\n";
echo "Chain multiple tool uses together\n\n";

try {
    $messages = [];
    $tools = [
        [
            'name' => 'get_stock_price',
            'description' => 'Get current stock price',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'symbol' => ['type' => 'string']
                ],
                'required' => ['symbol']
            ]
        ]
    ];
    
    // Turn 1: Initial question
    $messages[] = ['role' => 'user', 'content' => 'What is Apple stock price?'];
    
    $response1 = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'tools' => $tools,
        'messages' => $messages
    ]);
    
    echo "Turn 1: User asks for Apple stock price\n";
    
    // Extract tool use
    $toolUse = null;
    foreach ($response1->content as $block) {
        if ($block['type'] === 'tool_use') {
            $toolUse = $block;
            echo "  Claude requests: {$block['name']}({$block['input']['symbol']})\n";
        }
    }
    
    if ($toolUse) {
        // Simulate tool execution
        $stockPrice = '$150.25';
        
        // Add assistant response and tool result
        $messages[] = ['role' => 'assistant', 'content' => $response1->content];
        $messages[] = [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'tool_result',
                    'tool_use_id' => $toolUse['id'],
                    'content' => $stockPrice
                ]
            ]
        ];
        
        // Turn 2: Claude uses result
        $response2 = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'tools' => $tools,
            'messages' => $messages
        ]);
        
        echo "\nTurn 2: Tool result returned ($150.25)\n";
        echo "  Claude responds: ";
        foreach ($response2->content as $block) {
            if ($block['type'] === 'text') {
                echo $block['text'] . "\n";
            }
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 3: Error handling in tool execution
echo "Example 3: Tool Execution Error Handling\n";
echo "-----------------------------------------\n";
echo "Handle errors gracefully when tools fail\n\n";

echo "```php\n";
echo "// When tool execution fails, return error in tool_result\n";
echo "\$messages[] = [\n";
echo "    'role' => 'user',\n";
echo "    'content' => [\n";
echo "        [\n";
echo "            'type' => 'tool_result',\n";
echo "            'tool_use_id' => \$toolUse['id'],\n";
echo "            'content' => 'Error: API timeout',\n";
echo "            'is_error' => true  // Signals this is an error\n";
echo "        ]\n";
echo "    ]\n";
echo "];\n";
echo "```\n\n";

echo "Best practices:\n";
echo "  • Set 'is_error' => true for failed tool executions\n";
echo "  • Provide descriptive error messages\n";
echo "  • Claude will adapt its response based on error\n";
echo "  • Consider retry logic for transient failures\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 4: Tool descriptions best practices
echo "Example 4: Writing Good Tool Descriptions\n";
echo "------------------------------------------\n\n";

echo "❌ Bad description:\n";
echo "'description' => 'Gets weather'\n\n";

echo "✅ Good description:\n";
echo "'description' => 'Get the current weather conditions and forecast for a '\n";
echo "                . 'specific location. Returns temperature, conditions, '\n";
echo "                . 'humidity, and 3-day forecast. Location should be '\n";
echo "                . 'city and state/country.'\n\n";

echo "Tips for effective descriptions:\n";
echo "  • Be specific about what the tool does\n";
echo "  • Explain the expected format for parameters\n";
echo "  • Mention what data is returned\n";
echo "  • Include any important limitations\n";
echo "  • Use clear, concise language\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "✓ Tool use implementation examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Multi-tool support - Claude chooses the right one\n";
echo "• Multi-turn conversations with tools\n";
echo "• Error handling with 'is_error' => true\n";
echo "• Force tool use with tool_choice for JSON mode\n";
echo "• Write detailed tool descriptions\n";
echo "• Include input schemas with proper validation\n";
echo "• Monitor stop_reason to detect tool use\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • examples/tool_use_overview.php - Tool use basics\n";
echo "  • examples/tools.php - Simple tool example\n";

