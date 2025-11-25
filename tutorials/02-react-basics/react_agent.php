#!/usr/bin/env php
<?php
/**
 * Tutorial 2: ReAct Basics - Working Example
 * 
 * This script demonstrates the ReAct (Reason-Act-Observe) pattern, enabling
 * iterative multi-step problem solving through tool use.
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../examples/helpers.php';
require_once __DIR__ . '/../helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                   Tutorial 2: ReAct Loop Implementation                    ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Define Calculator Tool
// ============================================================================

$calculatorTool = [
    'name' => 'calculate',
    'description' => 'Perform precise mathematical calculations. ' .
                     'Supports: +, -, *, /, parentheses.',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'expression' => [
                'type' => 'string',
                'description' => 'Math expression to evaluate'
            ]
        ],
        'required' => ['expression']
    ]
];

// Tool executor function
function executeCalculator($expression) {
    try {
        // WARNING: eval() for demo only! Use proper parser in production
        $result = eval("return {$expression};");
        return (string)$result;
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
}

// ============================================================================
// Example 1: Basic ReAct Loop
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 1: Basic ReAct Loop - Single Tool, Multiple Steps\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$task = "What is (50 × 30) + (100 - 25)?";
echo "Task: \"{$task}\"\n";
echo "This requires multiple calculation steps.\n\n";

// Initialize conversation
$messages = [
    ['role' => 'user', 'content' => $task]
];

$maxIterations = 10;
$iteration = 0;
$finalResponse = null;

echo "Starting ReAct loop (max {$maxIterations} iterations)...\n";
echo str_repeat("═", 80) . "\n";

while ($iteration < $maxIterations) {
    $iteration++;
    
    echo "\n╔════ Iteration {$iteration} ════╗\n";
    
    // REASON: Call Claude with current state
    try {
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 4096,
            'messages' => $messages,
            'tools' => [$calculatorTool]
        ]);
    } catch (Exception $e) {
        echo "❌ Error: {$e->getMessage()}\n";
        break;
    }
    
    // Log what's happening
    echo "🧠 REASON: Claude is thinking...\n";
    echo "   Stop Reason: {$response->stop_reason}\n";
    echo "   Tokens: {$response->usage->input_tokens} in, {$response->usage->output_tokens} out\n";
    
    // Add assistant response to history
    $messages[] = [
        'role' => 'assistant',
        'content' => $response->content
    ];
    
    // Check if we're done
    if ($response->stop_reason === 'end_turn') {
        echo "\n✅ COMPLETE: Agent finished!\n\n";
        $finalResponse = $response;
        break;
    }
    
    // ACT: Execute tool if requested
    if ($response->stop_reason === 'tool_use') {
        $toolResults = [];
        
        foreach ($response->content as $block) {
            if ($block['type'] === 'tool_use') {
                echo "\n🔧 ACT: Using tool '{$block['name']}'\n";
                echo "   Input: {$block['input']['expression']}\n";
                
                // Execute the tool
                $result = executeCalculator($block['input']['expression']);
                
                echo "👁️  OBSERVE: Tool returned: {$result}\n";
                
                // Format tool result
                $toolResults[] = [
                    'type' => 'tool_result',
                    'tool_use_id' => $block['id'],
                    'content' => $result
                ];
            }
        }
        
        // Add tool results to conversation
        if (!empty($toolResults)) {
            $messages[] = [
                'role' => 'user',
                'content' => $toolResults
            ];
        }
    } else {
        echo "⚠️  Unexpected stop reason: {$response->stop_reason}\n";
        $finalResponse = $response;
        break;
    }
}

// Show final result
if ($finalResponse) {
    echo "\n" . str_repeat("═", 80) . "\n";
    echo "📝 Final Answer:\n";
    echo str_repeat("-", 80) . "\n";
    foreach ($finalResponse->content as $block) {
        if ($block['type'] === 'text') {
            echo $block['text'] . "\n";
        }
    }
    echo str_repeat("-", 80) . "\n";
    echo "\nCompleted in {$iteration} iterations\n";
} else {
    echo "\n⚠️  Max iterations ({$maxIterations}) reached without completion\n";
}

echo "\n" . str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 2: More Complex Multi-Step Task
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 2: Complex Multi-Step Calculation\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$complexTask = "Calculate the average of 127, 893, 456, and 234";
echo "Task: \"{$complexTask}\"\n";
echo "Steps needed: Add all numbers, then divide by count\n\n";

$messages = [
    ['role' => 'user', 'content' => $complexTask]
];

$iteration = 0;
$finalResponse = null;

while ($iteration < $maxIterations) {
    $iteration++;
    
    echo "Iteration {$iteration}: ";
    
    try {
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 4096,
            'messages' => $messages,
            'tools' => [$calculatorTool]
        ]);
    } catch (Exception $e) {
        echo "Error - {$e->getMessage()}\n";
        break;
    }
    
    $messages[] = ['role' => 'assistant', 'content' => $response->content];
    
    if ($response->stop_reason === 'end_turn') {
        echo "Done!\n";
        $finalResponse = $response;
        break;
    }
    
    if ($response->stop_reason === 'tool_use') {
        $toolResults = [];
        
        foreach ($response->content as $block) {
            if ($block['type'] === 'tool_use') {
                $expr = $block['input']['expression'];
                $result = executeCalculator($expr);
                echo "calculate(\"{$expr}\") = {$result}";
                
                $toolResults[] = [
                    'type' => 'tool_result',
                    'tool_use_id' => $block['id'],
                    'content' => $result
                ];
            }
        }
        
        echo "\n";
        
        if (!empty($toolResults)) {
            $messages[] = ['role' => 'user', 'content' => $toolResults];
        }
    }
}

if ($finalResponse) {
    echo "\nResult: ";
    foreach ($finalResponse->content as $block) {
        if ($block['type'] === 'text') {
            echo $block['text'] . "\n";
        }
    }
}

echo "\n" . str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 3: Understanding Stop Conditions
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 3: Demonstrating Different Stop Conditions\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$testCases = [
    [
        'task' => 'What is the capital of France?',
        'expected_stop' => 'end_turn',
        'expected_iterations' => 1,
        'reason' => 'No tools needed - direct answer'
    ],
    [
        'task' => 'Calculate 789 * 456',
        'expected_stop' => 'end_turn',
        'expected_iterations' => 2,
        'reason' => 'One tool call needed'
    ],
    [
        'task' => 'What is (25 * 4) + (100 / 5)?',
        'expected_stop' => 'end_turn',
        'expected_iterations' => 3,
        'reason' => 'Multiple tool calls needed'
    ]
];

foreach ($testCases as $i => $test) {
    echo "\nTest " . ($i + 1) . ": {$test['task']}\n";
    echo "Expected: {$test['expected_iterations']} iteration(s), stop_reason='{$test['expected_stop']}'\n";
    echo "Reason: {$test['reason']}\n";
    
    $messages = [['role' => 'user', 'content' => $test['task']]];
    $iteration = 0;
    $actualStop = null;
    
    while ($iteration < 5) {
        $iteration++;
        
        try {
            $response = $client->messages()->create([
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 1024,
                'messages' => $messages,
                'tools' => [$calculatorTool]
            ]);
        } catch (Exception $e) {
            echo "  Error: {$e->getMessage()}\n";
            break;
        }
        
        $messages[] = ['role' => 'assistant', 'content' => $response->content];
        $actualStop = $response->stop_reason;
        
        if ($actualStop === 'end_turn') {
            break;
        }
        
        if ($actualStop === 'tool_use') {
            $toolResults = [];
            foreach ($response->content as $block) {
                if ($block['type'] === 'tool_use') {
                    $result = executeCalculator($block['input']['expression']);
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
    
    $iterMatch = ($iteration === $test['expected_iterations']) ? '✓' : '✗';
    $stopMatch = ($actualStop === $test['expected_stop']) ? '✓' : '✗';
    
    echo "Actual: {$iteration} iteration(s) {$iterMatch}, stop_reason='{$actualStop}' {$stopMatch}\n";
}

echo "\n" . str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 4: Demonstrating Iteration Limits
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 4: Why Iteration Limits Matter\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Let's see what happens with different iteration limits...\n\n";

$complexTask = "Calculate ((10 * 5) + (20 * 3) - (15 / 3))";

$limits = [3, 5, 10];

foreach ($limits as $limit) {
    echo "Testing with max_iterations = {$limit}:\n";
    
    $messages = [['role' => 'user', 'content' => $complexTask]];
    $iteration = 0;
    $completed = false;
    
    while ($iteration < $limit) {
        $iteration++;
        
        try {
            $response = $client->messages()->create([
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 1024,
                'messages' => $messages,
                'tools' => [$calculatorTool]
            ]);
        } catch (Exception $e) {
            echo "  Error at iteration {$iteration}\n";
            break;
        }
        
        $messages[] = ['role' => 'assistant', 'content' => $response->content];
        
        if ($response->stop_reason === 'end_turn') {
            $completed = true;
            break;
        }
        
        if ($response->stop_reason === 'tool_use') {
            $toolResults = [];
            foreach ($response->content as $block) {
                if ($block['type'] === 'tool_use') {
                    $result = executeCalculator($block['input']['expression']);
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
    
    if ($completed) {
        echo "  ✓ Completed in {$iteration} iterations\n";
    } else {
        echo "  ✗ Hit limit without completing ({$iteration} iterations)\n";
    }
    echo "\n";
}

echo "💡 Insight: Set iteration limits high enough for your task complexity,\n";
echo "   but not so high that errors become expensive!\n";

echo "\n" . str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 5: Visualizing the ReAct Flow
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 5: Detailed ReAct Flow Visualization\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$task = "What is 123 + 456 - 789?";
echo "Task: \"{$task}\"\n\n";

$messages = [['role' => 'user', 'content' => $task]];
$iteration = 0;

echo "┌─── ReAct Flow ───┐\n\n";

while ($iteration < 5) {
    $iteration++;
    
    echo "╔═══ Iteration {$iteration} ═══╗\n";
    echo "║\n";
    
    // REASON
    echo "║ 🧠 REASON\n";
    echo "║    Calling Claude with current state...\n";
    
    try {
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => $messages,
            'tools' => [$calculatorTool]
        ]);
    } catch (Exception $e) {
        echo "║    Error: {$e->getMessage()}\n";
        break;
    }
    
    echo "║    Stop reason: {$response->stop_reason}\n";
    echo "║\n";
    
    $messages[] = ['role' => 'assistant', 'content' => $response->content];
    
    if ($response->stop_reason === 'end_turn') {
        // COMPLETE
        echo "║ ✅ COMPLETE\n";
        echo "║    Agent has finished the task\n";
        echo "║\n";
        echo "╚══════════════════╝\n\n";
        
        echo "Final Answer:\n";
        foreach ($response->content as $block) {
            if ($block['type'] === 'text') {
                echo "  {$block['text']}\n";
            }
        }
        break;
    }
    
    if ($response->stop_reason === 'tool_use') {
        // ACT
        echo "║ 🔧 ACT\n";
        $toolResults = [];
        
        foreach ($response->content as $block) {
            if ($block['type'] === 'tool_use') {
                echo "║    Tool: {$block['name']}\n";
                echo "║    Input: {$block['input']['expression']}\n";
                
                // OBSERVE
                $result = executeCalculator($block['input']['expression']);
                
                echo "║\n";
                echo "║ 👁️  OBSERVE\n";
                echo "║    Result: {$result}\n";
                
                $toolResults[] = [
                    'type' => 'tool_result',
                    'tool_use_id' => $block['id'],
                    'content' => $result
                ];
            }
        }
        
        echo "║\n";
        echo "╚══════════════════╝\n";
        echo "        ↓\n";
        echo "   (Continue loop)\n\n";
        
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

echo "1️⃣  The ReAct Loop Pattern\n";
echo "   • REASON: Claude analyzes the situation\n";
echo "   • ACT: Execute tools based on Claude's decision\n";
echo "   • OBSERVE: Get tool results and continue\n";
echo "   • Repeat until task is complete\n\n";

echo "2️⃣  Loop Implementation\n";
echo "   • Initialize with user task\n";
echo "   • Iterate until stop_reason='end_turn'\n";
echo "   • Maintain conversation history\n";
echo "   • Always set max iterations\n\n";

echo "3️⃣  Stop Conditions\n";
echo "   • 'end_turn' = Task complete\n";
echo "   • 'tool_use' = Need to execute tools\n";
echo "   • Max iterations = Safety limit\n\n";

echo "4️⃣  State Management\n";
echo "   • Conversation history is your state\n";
echo "   • Each iteration adds to messages array\n";
echo "   • Preserve all context for best results\n\n";

echo "5️⃣  Debugging Techniques\n";
echo "   • Log each iteration\n";
echo "   • Track stop reasons\n";
echo "   • Monitor token usage\n";
echo "   • Visualize the flow\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🎯 Key Patterns:\n\n";
echo "```php\n";
echo "// Basic ReAct Loop\n";
echo "while (\$iteration < \$maxIterations) {\n";
echo "    \$response = \$client->messages()->create([...]);\n";
echo "    \$messages[] = ['role' => 'assistant', 'content' => \$response->content];\n";
echo "    \n";
echo "    if (\$response->stop_reason === 'end_turn') break;\n";
echo "    \n";
echo "    if (\$response->stop_reason === 'tool_use') {\n";
echo "        // Execute tools and add results\n";
echo "        \$messages[] = ['role' => 'user', 'content' => \$toolResults];\n";
echo "    }\n";
echo "}\n";
echo "```\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🚀 Next Steps:\n\n";
echo "You now have a ReAct agent with one tool. But real-world agents need\n";
echo "MULTIPLE tools to be truly useful!\n\n";

echo "Continue to Tutorial 3: Multi-Tool Agent\n";
echo "→ tutorials/03-multi-tool-agent/\n\n";

echo "You'll learn:\n";
echo "  • How to define multiple diverse tools\n";
echo "  • How Claude chooses the right tool\n";
echo "  • Tool orchestration strategies\n";
echo "  • Debugging tool selection\n\n";




