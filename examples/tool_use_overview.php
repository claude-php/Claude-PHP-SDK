#!/usr/bin/env php
<?php
/**
 * Tool Use Overview - PHP examples from:
 * https://docs.claude.com/en/docs/agents-and-tools/tool-use/overview
 * 
 * Comprehensive guide to using tools with Claude, including client and server tools.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Tool Use with Claude - Complete Overview ===\n\n";

// Example 1: Single tool (client-side)
echo "Example 1: Single Client Tool\n";
echo "------------------------------\n";
echo "Client tools execute on your systems\n\n";

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'tools' => [
            [
                'name' => 'get_weather',
                'description' => 'Get the current weather in a given location',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'location' => [
                            'type' => 'string',
                            'description' => 'The city and state, e.g. San Francisco, CA'
                        ],
                        'unit' => [
                            'type' => 'string',
                            'enum' => ['celsius', 'fahrenheit'],
                            'description' => 'The unit of temperature'
                        ]
                    ],
                    'required' => ['location']
                ]
            ]
        ],
        'messages' => [
            ['role' => 'user', 'content' => 'What is the weather like in San Francisco?']
        ]
    ]);

    echo "Response:\n";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo "Text: {$block['text']}\n";
        } elseif ($block['type'] === 'tool_use') {
            echo "Tool use: {$block['name']}\n";
            echo "  Input: " . json_encode($block['input']) . "\n";
            echo "  ID: {$block['id']}\n";
        }
    }
    
    echo "\nStop reason: {$response->stop_reason}\n";
    echo "Note: stop_reason='tool_use' signals Claude wants to use a tool\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: Server-side tool (web search)
echo "Example 2: Server-Side Tool (Web Search)\n";
echo "-----------------------------------------\n";
echo "Server tools execute on Anthropic's servers\n\n";

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'tools' => [
            [
                'type' => 'web_search_20250305',
                'name' => 'web_search',
                'max_uses' => 3
            ]
        ],
        'messages' => [
            ['role' => 'user', 'content' => 'What are the latest developments in AI?']
        ]
    ]);

    echo "Response with web search:\n";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            $text = $block['text'];
            if (strlen($text) > 200) {
                $text = substr($text, 0, 200) . '...';
            }
            echo $text . "\n";
        }
    }
    
    if (isset($response->usage->server_tool_use)) {
        echo "\nServer tool usage:\n";
        echo "  Web searches: {$response->usage->server_tool_use['web_search_requests']}\n";
    }
    
    echo "\nNote: Server tools execute automatically, no client implementation needed\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 3: Complete client tool workflow
echo "Example 3: Complete Client Tool Workflow\n";
echo "-----------------------------------------\n";
echo "4-step process: Define → Claude requests → Execute → Return result\n\n";

try {
    $userMessage = ['role' => 'user', 'content' => 'Calculate 157 * 89'];
    
    $tools = [
        [
            'name' => 'calculator',
            'description' => 'Perform mathematical calculations',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'operation' => ['type' => 'string', 'enum' => ['add', 'subtract', 'multiply', 'divide']],
                    'a' => ['type' => 'number'],
                    'b' => ['type' => 'number']
                ],
                'required' => ['operation', 'a', 'b']
            ]
        ]
    ];
    
    // Step 1 & 2: Claude requests tool
    echo "Step 1 & 2: Claude decides to use calculator tool\n";
    $response1 = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [$userMessage],
        'tools' => $tools
    ]);
    
    $toolUse = null;
    foreach ($response1->content as $block) {
        if ($block['type'] === 'tool_use') {
            $toolUse = $block;
            echo "  Tool: {$block['name']}\n";
            echo "  Input: " . json_encode($block['input']) . "\n\n";
        }
    }
    
    if ($toolUse) {
        // Step 3: Execute tool on client side
        echo "Step 3: Execute tool on client\n";
        $operation = $toolUse['input']['operation'];
        $a = $toolUse['input']['a'];
        $b = $toolUse['input']['b'];
        
        $result = match($operation) {
            'multiply' => $a * $b,
            'add' => $a + $b,
            'subtract' => $a - $b,
            'divide' => $b != 0 ? $a / $b : 'Error: Division by zero',
            default => 'Unknown operation'
        };
        
        echo "  Calculation: {$a} {$operation} {$b} = {$result}\n\n";
        
        // Step 4: Return result to Claude
        echo "Step 4: Send result back to Claude\n";
        $response2 = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => [
                $userMessage,
                ['role' => 'assistant', 'content' => $response1->content],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'tool_result',
                            'tool_use_id' => $toolUse['id'],
                            'content' => (string)$result
                        ]
                    ]
                ]
            ],
            'tools' => $tools
        ]);
        
        echo "  Final response: ";
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

// Example 4: JSON mode with forced tool use
echo "Example 4: JSON Mode (Forced Tool Use)\n";
echo "---------------------------------------\n";
echo "Use tool_choice to force structured JSON output\n\n";

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'tools' => [
            [
                'name' => 'record_summary',
                'description' => 'Record a structured summary',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'title' => ['type' => 'string'],
                        'summary' => ['type' => 'string'],
                        'key_points' => [
                            'type' => 'array',
                            'items' => ['type' => 'string']
                        ]
                    ],
                    'required' => ['title', 'summary', 'key_points']
                ]
            ]
        ],
        'tool_choice' => ['type' => 'tool', 'name' => 'record_summary'],
        'messages' => [
            ['role' => 'user', 'content' => 'Summarize: Machine learning is transforming industries.']
        ]
    ]);

    foreach ($response->content as $block) {
        if ($block['type'] === 'tool_use') {
            echo "Structured JSON output:\n";
            echo json_encode($block['input'], JSON_PRETTY_PRINT) . "\n";
        }
    }
    
    echo "\nNote: tool_choice forces Claude to use the specified tool\n";
    echo "Perfect for getting structured JSON output\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 5: Tool choice options
echo "Example 5: Tool Choice Options\n";
echo "-------------------------------\n\n";

echo "Tool choice configurations:\n\n";
echo "• 'auto' (default)\n";
echo "  Claude decides whether to use tools\n";
echo "  {'tool_choice' => ['type' => 'auto']}\n\n";

echo "• 'any'\n";
echo "  Claude must use at least one tool\n";
echo "  {'tool_choice' => ['type' => 'any']}\n\n";

echo "• 'tool'\n";
echo "  Claude must use specific tool\n";
echo "  {'tool_choice' => ['type' => 'tool', 'name' => 'get_weather']}\n\n";

echo "• Disable tool use\n";
echo "  Send request without 'tools' parameter\n";
echo "  Or set tool_choice to ['type' => 'none'] (if tools provided)\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 6: Tool use pricing
echo "Example 6: Tool Use Pricing\n";
echo "---------------------------\n\n";

echo "Token costs include:\n";
echo "  1. Tool definitions (names, descriptions, schemas)\n";
echo "  2. Tool use blocks in messages\n";
echo "  3. Tool result blocks in messages\n";
echo "  4. Special system prompt (346-530 tokens depending on model)\n\n";

echo "System prompt tokens by model:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Model              auto/none    any/tool\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Opus 4.1/4         346 tokens   313 tokens\n";
echo "Sonnet 4.5/4       346 tokens   313 tokens\n";
echo "Haiku 4.5          346 tokens   313 tokens\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Server tool pricing:\n";
echo "  • Web search: Additional per-search charges\n";
echo "  • Web fetch: Additional per-fetch charges\n";
echo "  • Check usage.server_tool_use in response\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "✓ Tool use overview examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Two types: Client tools (you execute) and Server tools (auto-executed)\n";
echo "• Client workflow: Define → Claude requests → Execute → Return result\n";
echo "• Server workflow: Define → Claude executes automatically\n";
echo "• Use tool_choice to control behavior (auto, any, tool, none)\n";
echo "• JSON mode: Force tool use for structured output\n";
echo "• Pricing includes tool definitions + special system prompt\n";
echo "• stop_reason='tool_use' indicates Claude wants to use a tool\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • examples/tools.php - Basic tool use\n";
echo "  • examples/tool_use_implementation.php - Complete implementation guide\n";
echo "  • examples/web_search.php - Server tool example\n";

