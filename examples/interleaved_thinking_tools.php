#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;
use ClaudePhp\Exceptions\NotFoundError;

loadEnv(__DIR__ . '/../.env');

$client = new ClaudePhp(apiKey: getApiKey());

$calculatorTool = [
    'name' => 'calculator',
    'description' => 'Perform mathematical calculations',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'expression' => [
                'type' => 'string',
                'description' => 'Mathematical expression to evaluate',
            ],
        ],
        'required' => ['expression'],
    ],
];

$databaseTool = [
    'name' => 'database_query',
    'description' => 'Query product database',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'query' => [
                'type' => 'string',
                'description' => 'SQL query to execute',
            ],
        ],
        'required' => ['query'],
    ],
];

$prompt = "What's the total revenue if we sold 150 units of product A at $50 each, "
    . "and how does this compare to our average monthly revenue from the database?";

$tools = [$calculatorTool, $databaseTool];

try {
    echo "Initial response:\n";
    $response = $client->beta()->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 16_000,
        'thinking' => [
            'type' => 'enabled',
            'budget_tokens' => 10_000,
        ],
        'tools' => $tools,
        'betas' => ['interleaved-thinking-2025-05-14'],
        'messages' => [
            [
                'role' => 'user',
                'content' => $prompt,
            ],
        ],
    ]);

    $thinkingBlocks = [];
    $toolUseBlocks = [];

    foreach ($response->content as $block) {
        if (($block['type'] ?? null) === 'thinking') {
            $thinkingBlocks[] = $block;
            echo "Thinking: {$block['thinking']}\n";
        } elseif (($block['type'] ?? null) === 'tool_use') {
            $toolUseBlocks[] = $block;
            echo "Tool use: {$block['name']} input " . json_encode($block['input']) . "\n";
        } elseif (($block['type'] ?? null) === 'text') {
            echo "Text: {$block['text']}\n";
        }
    }

    $calculatorResult = '7500';

    echo "\nAfter calculator result:\n";
    $response2 = $client->beta()->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 16_000,
        'thinking' => [
            'type' => 'enabled',
            'budget_tokens' => 10_000,
        ],
        'tools' => $tools,
        'betas' => ['interleaved-thinking-2025-05-14'],
        'messages' => [
            ['role' => 'user', 'content' => $prompt],
            ['role' => 'assistant', 'content' => [$thinkingBlocks[0], $toolUseBlocks[0]]],
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'tool_result',
                        'tool_use_id' => $toolUseBlocks[0]['id'],
                        'content' => $calculatorResult,
                    ],
                ],
            ],
        ],
    ]);

    foreach ($response2->content as $block) {
        if (($block['type'] ?? null) === 'thinking') {
            $thinkingBlocks[] = $block;
            echo "Interleaved thinking: {$block['thinking']}\n";
        } elseif (($block['type'] ?? null) === 'tool_use') {
            $toolUseBlocks[] = $block;
            echo "Tool use: {$block['name']} input " . json_encode($block['input']) . "\n";
        }
    }

    $databaseResult = '5200';

    echo "\nAfter database result:\n";
    $response3 = $client->beta()->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 16_000,
        'thinking' => [
            'type' => 'enabled',
            'budget_tokens' => 10_000,
        ],
        'tools' => $tools,
        'betas' => ['interleaved-thinking-2025-05-14'],
        'messages' => [
            ['role' => 'user', 'content' => $prompt],
            ['role' => 'assistant', 'content' => [$thinkingBlocks[0], $toolUseBlocks[0]]],
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'tool_result',
                        'tool_use_id' => $toolUseBlocks[0]['id'],
                        'content' => $calculatorResult,
                    ],
                ],
            ],
            [
                'role' => 'assistant',
                'content' => array_merge(
                    array_slice($thinkingBlocks, 1),
                    array_slice($toolUseBlocks, 1)
                ),
            ],
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'tool_result',
                        'tool_use_id' => $toolUseBlocks[1]['id'],
                        'content' => $databaseResult,
                    ],
                ],
            ],
        ],
    ]);

    foreach ($response3->content as $block) {
        if (($block['type'] ?? null) === 'thinking') {
            echo "Final thinking: {$block['thinking']}\n";
        } elseif (($block['type'] ?? null) === 'text') {
            echo "Final response: {$block['text']}\n";
        }
    }
} catch (NotFoundError $e) {
    echo "This example requires the interleaved-thinking beta flag, which is not enabled for this API key.\n";
}
