#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

$userMessage = [
    'role' => 'user',
    'content' => 'What is the weather in SF?',
];

$tools = [
    [
        'name' => 'get_weather',
        'description' => 'Get the weather for a specific location',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'location' => ['type' => 'string'],
            ],
        ],
    ],
];

echo "Tool Use Example:\n";
echo "=================\n\n";

$message = $client->messages()->create([
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 1024,
    'messages' => [$userMessage],
    'tools' => $tools,
]);

echo "Initial response:\n";
print_r($message);
echo "\n";

if ($message->stop_reason === 'tool_use') {
    // Find the tool use block
    $toolUse = null;
    foreach ($message->content as $block) {
        if ($block['type'] === 'tool_use') {
            $toolUse = $block;
            break;
        }
    }

    if ($toolUse) {
        echo "Claude wants to use tool: {$toolUse['name']}\n";
        echo "With input: " . json_encode($toolUse['input']) . "\n\n";

        // Simulate tool execution
        $toolResult = 'The weather is 73°F';

        // Send tool result back
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 1024,
            'messages' => [
                $userMessage,
                [
                    'role' => $message->role,
                    'content' => $message->content,
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'tool_result',
                            'tool_use_id' => $toolUse['id'],
                            'content' => [['type' => 'text', 'text' => $toolResult]],
                        ],
                    ],
                ],
            ],
            'tools' => $tools,
        ]);

        echo "Final response:\n";
        foreach ($response->content as $block) {
            if ($block['type'] === 'text') {
                echo $block['text'] . "\n";
            }
        }
    }
}
