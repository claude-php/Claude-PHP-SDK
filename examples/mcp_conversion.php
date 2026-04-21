<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\Lib\Tools\Mcp;

// MCP conversion helpers convert MCP protocol shapes into Anthropic API shapes.
// Your MCP client integration is BYO — these helpers only do shape conversion.

// Convert an MCP tool definition to an Anthropic Beta tool param
$mcpTool = [
    'name' => 'get_weather',
    'description' => 'Get current weather for a city',
    'inputSchema' => [
        'type' => 'object',
        'properties' => [
            'city' => ['type' => 'string', 'description' => 'City name'],
        ],
        'required' => ['city'],
    ],
];

$betaTool = Mcp::tool($mcpTool, [
    'cache_control' => ['type' => 'ephemeral'],
    'strict' => true,
]);

echo "Beta tool param:\n";
echo json_encode($betaTool, JSON_PRETTY_PRINT) . "\n\n";

// Convert MCP content to Anthropic content block
$mcpContent = ['type' => 'text', 'text' => 'The weather in Sydney is 22°C and sunny.'];
$block = Mcp::content($mcpContent);
echo "Content block:\n";
echo json_encode($block, JSON_PRETTY_PRINT) . "\n\n";

// Convert an MCP prompt message to an Anthropic message param
$mcpMessage = ['role' => 'user', 'content' => 'What is the weather like?'];
$message = Mcp::message($mcpMessage);
echo "Message param:\n";
echo json_encode($message, JSON_PRETTY_PRINT) . "\n\n";

// Convert MCP resource read results to content blocks
$readResult = [
    'contents' => [
        ['text' => '# README\nThis is a project readme.', 'uri' => 'file:///project/README.md'],
    ],
];
$blocks = Mcp::resourceToContent($readResult);
echo "Resource content blocks:\n";
echo json_encode($blocks, JSON_PRETTY_PRINT) . "\n";
