#!/usr/bin/env php
<?php
/**
 * Tutorial 4: Production-Ready Agent - Working Example
 * 
 * This script demonstrates production-grade patterns including error handling,
 * retries, memory, logging, and graceful degradation.
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../examples/helpers.php';
require_once __DIR__ . '/../helpers.php';

use ClaudePhp\ClaudePhp;
use ClaudePhp\Exceptions\{RateLimitError, APIConnectionError, AuthenticationError, APIStatusError};

loadEnv(__DIR__ . '/../../.env');
$client = new ClaudePhp(
    apiKey: getApiKey(),
    timeout: 30.0,
    maxRetries: 2
);

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║            Tutorial 4: Production-Ready Agent with Error Handling          ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Tool Definitions
// ============================================================================

$calculatorTool = createTool(
    'calculate',
    'Perform mathematical calculations',
    ['expression' => ['type' => 'string', 'description' => 'Math expression']],
    ['expression']
);

$weatherTool = createTool(
    'get_weather',
    'Get current weather for a city',
    ['city' => ['type' => 'string', 'description' => 'City name']],
    ['city']
);

// Note: Memory tool is a beta feature and may not be available
// $memoryTool = [
//     'type' => 'memory_20250818',
//     'name' => 'memory',
//     'max_uses' => 10
// ];

$tools = [$calculatorTool, $weatherTool];

// ============================================================================
// Production-Grade Tool Executor with Error Handling
// ============================================================================

function executeToolSafely(string $toolName, array $input): array
{
    try {
        $result = match ($toolName) {
            'calculate' => executeCalculator($input['expression']),
            'get_weather' => getWeather($input['city']),
            default => throw new Exception("Unknown tool: {$toolName}")
        };

        return [
            'success' => true,
            'content' => $result,
            'is_error' => false
        ];
    } catch (Exception $e) {
        logError("Tool execution failed: {$toolName} - " . $e->getMessage());

        return [
            'success' => false,
            'content' => "Tool error: " . $e->getMessage(),
            'is_error' => true
        ];
    }
}

function executeCalculator(string $expression): string
{
    // Validate input
    if (!preg_match('/^[0-9+\-*\/().\s]+$/', $expression)) {
        throw new Exception("Invalid expression characters");
    }

    try {
        $result = eval("return {$expression};");
        return (string)$result;
    } catch (Exception $e) {
        throw new Exception("Calculation error: " . $e->getMessage());
    }
}

function getWeather(string $city): string
{
    // Simulate occasional failures
    if (rand(1, 5) === 1) {
        throw new Exception("Weather service temporarily unavailable");
    }

    $conditions = ['sunny', 'cloudy', 'rainy'];
    return json_encode([
        'city' => $city,
        'temperature' => rand(50, 85) . '°F',
        'conditions' => $conditions[array_rand($conditions)]
    ]);
}

// Note: retryWithBackoff() is available from tutorials/helpers.php

// ============================================================================
// Logging Functions
// ============================================================================

function logError(string $message): void
{
    $log = sprintf("[%s] ERROR: %s\n", date('Y-m-d H:i:s'), $message);
    file_put_contents(__DIR__ . '/agent.log', $log, FILE_APPEND);
    echo "🔴 " . $message . "\n";
}

function logInfo(string $message): void
{
    $log = sprintf("[%s] INFO: %s\n", date('Y-m-d H:i:s'), $message);
    file_put_contents(__DIR__ . '/agent.log', $log, FILE_APPEND);
}

function logIteration(int $iteration, $response): void
{
    $log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'iteration' => $iteration,
        'stop_reason' => $response->stop_reason,
        'tokens' => [
            'input' => $response->usage->input_tokens,
            'output' => $response->usage->output_tokens
        ]
    ];

    $logLine = json_encode($log) . "\n";
    file_put_contents(__DIR__ . '/agent.log', $logLine, FILE_APPEND);
}

// ============================================================================
// Production Agent Loop
// ============================================================================

function runProductionAgent($client, string $task, array $tools, array $config = []): array
{
    $maxIterations = $config['max_iterations'] ?? 10;
    $debug = $config['debug'] ?? false;

    $messages = [
        ['role' => 'user', 'content' => $task]
    ];

    $iteration = 0;
    $finalResponse = null;
    $errors = [];

    while ($iteration < $maxIterations) {
        $iteration++;

        if ($debug) {
            echo "\n╔════ Iteration {$iteration} ════╗\n";
        }

        try {
            // Make API call with retry logic
            $response = retryWithBackoff(function () use ($client, $messages, $tools) {
                return $client->messages()->create([
                    'model' => 'claude-sonnet-4-5',
                    'max_tokens' => 4096,
                    'messages' => $messages,
                    'tools' => $tools
                ]);
            });

            logIteration($iteration, $response);
        } catch (AuthenticationError $e) {
            logError("Authentication failed - check API key");
            return [
                'success' => false,
                'error' => 'authentication_failed',
                'message' => $e->getMessage()
            ];
        } catch (Exception $e) {
            logError("API call failed: " . $e->getMessage());
            $errors[] = $e->getMessage();

            // If too many errors, abort
            if (count($errors) >= 3) {
                return [
                    'success' => false,
                    'error' => 'too_many_failures',
                    'errors' => $errors
                ];
            }

            continue;
        }

        $messages[] = ['role' => 'assistant', 'content' => $response->content];

        if ($response->stop_reason === 'end_turn') {
            $finalResponse = $response;
            if ($debug) {
                echo "✅ Task complete!\n";
            }
            break;
        }

        if ($response->stop_reason === 'tool_use') {
            $toolResults = [];

            foreach ($response->content as $block) {
                if ($block['type'] === 'tool_use') {
                    if ($debug) {
                        echo "🔧 Executing: {$block['name']}\n";
                    }

                    // Execute tool safely
                    $result = executeToolSafely($block['name'], $block['input']);

                    $toolResults[] = [
                        'type' => 'tool_result',
                        'tool_use_id' => $block['id'],
                        'content' => $result['content'],
                        'is_error' => $result['is_error']
                    ];

                    if ($result['is_error']) {
                        logError("Tool execution error: {$block['name']}");
                    }
                }
            }

            if (!empty($toolResults)) {
                $messages[] = ['role' => 'user', 'content' => $toolResults];
            }
        }
    }

    if (!$finalResponse) {
        return [
            'success' => false,
            'error' => 'max_iterations_reached',
            'iterations' => $iteration
        ];
    }

    return [
        'success' => true,
        'response' => $finalResponse,
        'iterations' => $iteration,
        'errors' => $errors
    ];
}

// ============================================================================
// Example 1: Basic Production Agent
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 1: Production Agent with Error Handling\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$task = "Calculate 25 * 17 and tell me the weather in Tokyo";

$result = runProductionAgent($client, $task, $tools, ['debug' => true]);

if ($result['success']) {
    echo "\n📝 Final Answer:\n";
    echo str_repeat("-", 80) . "\n";
    echo extractTextContent($result['response']) . "\n";
    echo str_repeat("-", 80) . "\n";
    echo "\nCompleted in {$result['iterations']} iterations\n";

    if (!empty($result['errors'])) {
        $errorCount = count($result['errors']);
        echo "⚠️  Encountered {$errorCount} non-fatal errors\n";
    }
} else {
    echo "\n❌ Agent failed: {$result['error']}\n";
    if (isset($result['message'])) {
        echo "   {$result['message']}\n";
    }
}

echo "\n" . str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 2: Testing Error Recovery
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 2: Error Recovery\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Testing tool execution with simulated failures...\n\n";

$tests = [
    "What's the weather in London?",  // Might fail
    "What's the weather in Paris?",   // Might fail
    "Calculate 50 + 50",              // Should succeed
];

foreach ($tests as $i => $test) {
    echo "Test " . ($i + 1) . ": {$test}\n";

    $result = runProductionAgent($client, $test, $tools, ['debug' => false]);

    if ($result['success']) {
        echo "  ✓ Success\n";
    } else {
        echo "  ✗ Failed: {$result['error']}\n";
    }
    echo "\n";
}

echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 3: Server-Side Tools (Web Search)
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 3: Web Search Tool Integration\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Note: This example demonstrates server-side tools that Claude executes automatically.\n";
echo "Web search must be enabled in your organization's Claude Console.\n\n";

// Web search is a server-side tool - Claude executes it automatically
$webSearchTools = [
    [
        'type' => 'web_search_20250305',
        'name' => 'web_search',
        'max_uses' => 3,  // Limit searches per request
    ]
];

echo "Asking a question that benefits from web search...\n";
$searchTask = "What is the current version of PHP and when was it released?";
$searchResult = runProductionAgent($client, $searchTask, $webSearchTools, ['debug' => false]);

if ($searchResult['success']) {
    $answer = extractTextContent($searchResult['response']);
    echo "✓ Answer: " . substr($answer, 0, 200) . "...\n\n";
    echo "Note: Web search tools are executed server-side by Claude automatically.\n";
    echo "No tool implementation needed on your end!\n";
} else {
    echo "✗ Web search unavailable or not enabled for your organization\n";
    echo "  To enable: Visit Console → Settings → Web Search\n";
}

echo "\n" . str_repeat("═", 80) . "\n\n";

// ============================================================================
// Summary
// ============================================================================

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                           Tutorial Summary                                 ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "✅ Production Patterns Implemented:\n\n";

echo "1️⃣  Error Handling\n";
echo "   • Catch specific exception types\n";
echo "   • Handle different failure modes\n";
echo "   • Continue on non-fatal errors\n\n";

echo "2️⃣  Retry Logic\n";
echo "   • Exponential backoff\n";
echo "   • Respect retry-after headers\n";
echo "   • Max attempts limit\n\n";

echo "3️⃣  Tool Safety\n";
echo "   • Wrap execution in try-catch\n";
echo "   • Return errors with is_error flag\n";
echo "   • Validate inputs\n\n";

echo "4️⃣  Logging\n";
echo "   • Structured log entries\n";
echo "   • Track iterations and tokens\n";
echo "   • Monitor errors\n\n";

echo "5️⃣  Server-Side Tools\n";
echo "   • Web search integration\n";
echo "   • Claude executes automatically\n";
echo "   • No implementation needed\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🚀 Your agent is now production-ready!\n\n";
echo "Next: Tutorial 5 - Advanced ReAct with Planning & Reflection\n";
echo "→ tutorials/05-advanced-react/\n\n";
