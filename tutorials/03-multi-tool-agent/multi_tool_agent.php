#!/usr/bin/env php
<?php
/**
 * Tutorial 3: Multi-Tool Agent - Working Example
 * 
 * This script demonstrates building an agent with multiple diverse tools
 * and how Claude selects the appropriate tool for different tasks.
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../examples/helpers.php';
require_once __DIR__ . '/../helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║              Tutorial 3: Multi-Tool Agent - Smart Assistant                ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Define Multiple Tools
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Step 1: Defining Multiple Tools\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$calculatorTool = [
    'name' => 'calculate',
    'description' => 'Perform precise mathematical calculations including arithmetic (+, -, *, /), ' .
        'percentages, and complex expressions. Use this for any mathematical computation.',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'expression' => [
                'type' => 'string',
                'description' => 'Math expression like "25 * 4" or "0.25 * 480"'
            ]
        ],
        'required' => ['expression']
    ]
];

$timeTool = [
    'name' => 'get_current_time',
    'description' => 'Get the current time in any timezone. Returns time in 24-hour format. ' .
        'Use this when user asks "what time is it" or needs current time.',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'timezone' => [
                'type' => 'string',
                'description' => 'IANA timezone like "America/New_York", "Europe/London", "Asia/Tokyo"'
            ]
        ],
        'required' => ['timezone']
    ]
];

$weatherTool = [
    'name' => 'get_weather',
    'description' => 'Get current weather conditions for a city. Returns temperature, conditions ' .
        '(sunny/rainy/cloudy), and humidity. Use this when user asks about weather or temperature.',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'city' => [
                'type' => 'string',
                'description' => 'City name, can include country like "London, UK"'
            ]
        ],
        'required' => ['city']
    ]
];

$searchTool = [
    'name' => 'search',
    'description' => 'Search for factual information on any topic. Returns relevant information ' .
        'from knowledge sources. Use this for facts, statistics, definitions, or recent information.',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'query' => [
                'type' => 'string',
                'description' => 'Search query'
            ]
        ],
        'required' => ['query']
    ]
];

$tools = [$calculatorTool, $timeTool, $weatherTool, $searchTool];

echo "✓ Defined 4 tools:\n";
echo "  1. calculate - Mathematical computations\n";
echo "  2. get_current_time - Get time in any timezone\n";
echo "  3. get_weather - Weather information\n";
echo "  4. search - General information lookup\n\n";

// ============================================================================
// Tool Executor Functions
// ============================================================================

function executeCalculator($expression)
{
    try {
        // WARNING: eval() for demo only! Use proper parser in production
        $result = eval("return {$expression};");
        return (string)$result;
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
}

function getCurrentTime($timezone)
{
    try {
        $dt = new DateTime('now', new DateTimeZone($timezone));
        return $dt->format('Y-m-d H:i:s T');
    } catch (Exception $e) {
        return "Error: Invalid timezone '{$timezone}'";
    }
}

function getWeather($city)
{
    // Simulated weather data
    $conditions = ['sunny', 'cloudy', 'rainy', 'partly cloudy'];
    $temp = rand(50, 85);
    $condition = $conditions[array_rand($conditions)];

    return json_encode([
        'city' => $city,
        'temperature' => $temp . '°F',
        'conditions' => $condition,
        'humidity' => rand(30, 70) . '%'
    ], JSON_PRETTY_PRINT);
}

function performSearch($query)
{
    // Simulated search results
    $responses = [
        'population of paris' => 'The population of Paris is approximately 2.2 million in the city proper.',
        'who invented the telephone' => 'Alexander Graham Bell is credited with inventing the telephone in 1876.',
        'capital of japan' => 'Tokyo is the capital of Japan.',
        'default' => "Search results for '{$query}': [Relevant information about {$query}]"
    ];

    $lowerQuery = strtolower($query);
    foreach ($responses as $key => $response) {
        if (str_contains($lowerQuery, $key)) {
            return $response;
        }
    }

    return $responses['default'];
}

function executeTool($toolName, $input)
{
    return match ($toolName) {
        'calculate' => executeCalculator($input['expression']),
        'get_current_time' => getCurrentTime($input['timezone']),
        'get_weather' => getWeather($input['city']),
        'search' => performSearch($input['query']),
        default => "Unknown tool: {$toolName}"
    };
}

echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 1: Testing Tool Selection
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 1: Tool Selection Tests\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$testQueries = [
    ['question' => 'What is 25 * 17?', 'expected_tool' => 'calculate'],
    ['question' => 'What time is it in Tokyo?', 'expected_tool' => 'get_current_time'],
    ['question' => 'What is the weather in London?', 'expected_tool' => 'get_weather'],
    ['question' => 'Who invented the telephone?', 'expected_tool' => 'search'],
];

foreach ($testQueries as $i => $test) {
    echo "Test " . ($i + 1) . ": \"{$test['question']}\"\n";
    echo "Expected tool: {$test['expected_tool']}\n";

    try {
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => $test['question']]
            ],
            'tools' => $tools
        ]);

        $actualTool = null;
        foreach ($response->content as $block) {
            if ($block['type'] === 'tool_use') {
                $actualTool = $block['name'];
                break;
            }
        }

        if ($actualTool) {
            $match = ($actualTool === $test['expected_tool']) ? '✓' : '✗';
            echo "Actual tool: {$actualTool} {$match}\n\n";
        } else {
            echo "No tool used (direct answer) ✗\n\n";
        }
    } catch (Exception $e) {
        echo "Error: {$e->getMessage()}\n\n";
    }
}

echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 2: Complete Multi-Tool Interactions
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 2: Complete Agent Interactions\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$tasks = [
    "Calculate 15% of 250",
    "What time is it in New York?",
    "Is it raining in Paris?",
];

foreach ($tasks as $i => $task) {
    echo "\nTask " . ($i + 1) . ": \"{$task}\"\n";
    echo str_repeat("-", 80) . "\n";

    $messages = [['role' => 'user', 'content' => $task]];
    $iteration = 0;
    $maxIterations = 5;

    while ($iteration < $maxIterations) {
        $iteration++;

        try {
            $response = $client->messages()->create([
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 2048,
                'messages' => $messages,
                'tools' => $tools
            ]);
        } catch (Exception $e) {
            echo "Error: {$e->getMessage()}\n";
            break;
        }

        $messages[] = ['role' => 'assistant', 'content' => $response->content];

        if ($response->stop_reason === 'end_turn') {
            // Task complete
            foreach ($response->content as $block) {
                if ($block['type'] === 'text') {
                    echo "Result: {$block['text']}\n";
                }
            }
            break;
        }

        if ($response->stop_reason === 'tool_use') {
            $toolResults = [];

            foreach ($response->content as $block) {
                if ($block['type'] === 'tool_use') {
                    echo "Using: {$block['name']}(";
                    echo json_encode($block['input']) . ")\n";

                    $result = executeTool($block['name'], $block['input']);
                    echo "  → {$result}\n";

                    $toolResults[] = [
                        'type' => 'tool_result',
                        'tool_use_id' => $block['id'],
                        'content' => $result
                    ];
                }
            }

            if (!empty($toolResults)) {
                $messages[] = ['role' => 'user', 'content' => $toolResults];
            }
        }
    }
}

echo "\n" . str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 3: Multi-Tool Task
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 3: Task Requiring Multiple Different Tools\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$complexTask = "What's the weather in Tokyo and what time is it there?";
echo "Task: \"{$complexTask}\"\n";
echo "This requires TWO different tools: get_weather AND get_current_time\n\n";

$messages = [['role' => 'user', 'content' => $complexTask]];
$iteration = 0;
$toolsUsed = [];

while ($iteration < 10) {
    $iteration++;

    try {
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 4096,
            'messages' => $messages,
            'tools' => $tools
        ]);
    } catch (Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        break;
    }

    $messages[] = ['role' => 'assistant', 'content' => $response->content];

    if ($response->stop_reason === 'end_turn') {
        echo "\n✅ Task Complete!\n\n";
        echo "Tools used: " . implode(', ', array_unique($toolsUsed)) . "\n";
        echo "Iterations: {$iteration}\n\n";

        echo "Final Answer:\n";
        echo str_repeat("-", 80) . "\n";
        foreach ($response->content as $block) {
            if ($block['type'] === 'text') {
                echo $block['text'] . "\n";
            }
        }
        echo str_repeat("-", 80) . "\n";
        break;
    }

    if ($response->stop_reason === 'tool_use') {
        $toolResults = [];

        foreach ($response->content as $block) {
            if ($block['type'] === 'tool_use') {
                $toolName = $block['name'];
                $toolsUsed[] = $toolName;

                echo "Step {$iteration}: Using {$toolName}\n";

                $result = executeTool($toolName, $block['input']);

                $toolResults[] = [
                    'type' => 'tool_result',
                    'tool_use_id' => $block['id'],
                    'content' => $result
                ];
            }
        }

        if (!empty($toolResults)) {
            $messages[] = ['role' => 'user', 'content' => $toolResults];
        }
    }
}

echo "\n" . str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 4: Tool Chaining
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 4: Tool Chaining (One Tool's Output → Another Tool's Input)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$chainTask = "Search for the population of Paris, then calculate 10% of that number";
echo "Task: \"{$chainTask}\"\n";
echo "Chain: search → calculate\n\n";

$messages = [['role' => 'user', 'content' => $chainTask]];
$iteration = 0;

echo "Agent workflow:\n\n";

while ($iteration < 10) {
    $iteration++;

    try {
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 4096,
            'messages' => $messages,
            'tools' => $tools
        ]);
    } catch (Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        break;
    }

    $messages[] = ['role' => 'assistant', 'content' => $response->content];

    if ($response->stop_reason === 'end_turn') {
        echo "\n✅ Chain Complete!\n\n";
        foreach ($response->content as $block) {
            if ($block['type'] === 'text') {
                echo "Final: {$block['text']}\n";
            }
        }
        break;
    }

    if ($response->stop_reason === 'tool_use') {
        $toolResults = [];

        foreach ($response->content as $block) {
            if ($block['type'] === 'tool_use') {
                echo "{$iteration}. {$block['name']}(";
                echo json_encode($block['input']) . ")\n";

                $result = executeTool($block['name'], $block['input']);
                echo "   → {$result}\n\n";

                $toolResults[] = [
                    'type' => 'tool_result',
                    'tool_use_id' => $block['id'],
                    'content' => $result
                ];
            }
        }

        if (!empty($toolResults)) {
            $messages[] = ['role' => 'user', 'content' => $toolResults];
        }
    }
}

echo "\n" . str_repeat("═", 80) . "\n\n";

// ============================================================================
// Summary
// ============================================================================

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                           Tutorial Summary                                 ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "✅ You've learned:\n\n";

echo "1️⃣  Defining Multiple Tools\n";
echo "   • Each tool has distinct purpose\n";
echo "   • Clear descriptions guide selection\n";
echo "   • Good parameter schemas\n\n";

echo "2️⃣  Tool Selection\n";
echo "   • Claude chooses based on description\n";
echo "   • Context from user's question matters\n";
echo "   • Test selection accuracy\n\n";

echo "3️⃣  Tool Execution\n";
echo "   • Match pattern for routing\n";
echo "   • Handle different input/output types\n";
echo "   • Return results consistently\n\n";

echo "4️⃣  Multi-Tool Workflows\n";
echo "   • Multiple tools in one task\n";
echo "   • Tool chaining (output → input)\n";
echo "   • Sequential execution\n\n";

echo "5️⃣  Best Practices\n";
echo "   • Distinct tool purposes\n";
echo "   • Detailed descriptions\n";
echo "   • Clear parameter names\n";
echo "   • Limit number of tools (1-10 optimal)\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🎯 Key Patterns:\n\n";
echo "Tool Executor:\n";
echo "```php\n";
echo "function executeTool(\$toolName, \$input) {\n";
echo "    return match(\$toolName) {\n";
echo "        'calculate' => executeCalculator(\$input['expression']),\n";
echo "        'get_weather' => getWeather(\$input['city']),\n";
echo "        // ... more tools\n";
echo "    };\n";
echo "}\n";
echo "```\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🚀 Next Steps:\n\n";
echo "Your agent now has multiple tools, but it's still missing critical\n";
echo "production features like error handling, retries, and memory!\n\n";

echo "Continue to Tutorial 4: Production-Ready Agent\n";
echo "→ tutorials/04-production-ready/\n\n";

echo "You'll learn:\n";
echo "  • Comprehensive error handling\n";
echo "  • Retry logic with exponential backoff\n";
echo "  • Persistent memory with Memory Tool\n";
echo "  • Logging and monitoring\n";
echo "  • Graceful degradation\n\n";



