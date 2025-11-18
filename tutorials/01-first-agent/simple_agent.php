#!/usr/bin/env php
<?php
/**
 * Tutorial 1: Your First Agent - Working Example
 * 
 * This script demonstrates building a simple calculator agent with one tool.
 * It shows the complete Request → Tool Call → Execute → Response cycle.
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../examples/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║              Tutorial 1: Your First Agent - Calculator                    ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Step 1: Define the Calculator Tool
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Step 1: Tool Definition\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$calculatorTool = [
    'name' => 'calculate',
    'description' => 'Perform precise mathematical calculations. ' .
                     'Supports basic arithmetic operations: ' .
                     'addition (+), subtraction (-), multiplication (*), ' .
                     'division (/), and parentheses for order of operations.',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'expression' => [
                'type' => 'string',
                'description' => 'The mathematical expression to evaluate. ' .
                                'Examples: "2 + 2", "15 * 8", "(100 - 25) / 5"'
            ]
        ],
        'required' => ['expression']
    ]
];

echo "✓ Tool Name: {$calculatorTool['name']}\n";
echo "✓ Description: {$calculatorTool['description']}\n";
echo "✓ Required Parameters: " . implode(', ', $calculatorTool['input_schema']['required']) . "\n\n";

// ============================================================================
// Example 1: Basic Single Tool Call
// ============================================================================

echo str_repeat("═", 80) . "\n\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 1: Basic Calculator Agent\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$userQuestion = "What is 157 × 89?";
echo "User Question: \"{$userQuestion}\"\n\n";

try {
    // Step 2: Send request with tool
    echo "Step 2: Sending request to Claude with calculator tool...\n";
    
    $response1 = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            ['role' => 'user', 'content' => $userQuestion]
        ],
        'tools' => [$calculatorTool]
    ]);
    
    echo "✓ Response received\n";
    echo "  Stop Reason: {$response1->stop_reason}\n";
    echo "  Input Tokens: {$response1->usage->input_tokens}\n";
    echo "  Output Tokens: {$response1->usage->output_tokens}\n\n";
    
    // Step 3: Check if Claude wants to use the tool
    if ($response1->stop_reason === 'tool_use') {
        echo "Step 3: Claude wants to use a tool!\n\n";
        
        // Extract the tool use block
        $toolUse = null;
        foreach ($response1->content as $block) {
            if ($block['type'] === 'tool_use') {
                $toolUse = $block;
                break;
            }
        }
        
        if ($toolUse) {
            echo "Tool Use Details:\n";
            echo "  Tool Name: {$toolUse['name']}\n";
            echo "  Tool ID: {$toolUse['id']}\n";
            echo "  Input: " . json_encode($toolUse['input']) . "\n\n";
            
            // Step 4: Execute the tool
            echo "Step 4: Executing calculator tool...\n";
            
            $expression = $toolUse['input']['expression'];
            echo "  Expression: {$expression}\n";
            
            // WARNING: eval() is used here for demonstration only!
            // In production, use a proper math parser library
            try {
                $result = eval("return {$expression};");
                echo "  Result: {$result}\n\n";
            } catch (Exception $e) {
                $result = "Error: " . $e->getMessage();
                echo "  Error: {$result}\n\n";
            }
            
            // Step 5: Return result to Claude
            echo "Step 5: Returning result to Claude...\n";
            
            $messages = [
                ['role' => 'user', 'content' => $userQuestion],
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
            ];
            
            $response2 = $client->messages()->create([
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 1024,
                'messages' => $messages,
                'tools' => [$calculatorTool]
            ]);
            
            echo "✓ Final response received\n";
            echo "  Stop Reason: {$response2->stop_reason}\n\n";
            
            // Step 6: Display final answer
            echo "Step 6: Final Answer:\n";
            echo str_repeat("-", 80) . "\n";
            foreach ($response2->content as $block) {
                if ($block['type'] === 'text') {
                    echo $block['text'] . "\n";
                }
            }
            echo str_repeat("-", 80) . "\n\n";
            
            // Show total tokens used
            $totalTokens = $response1->usage->input_tokens + $response1->usage->output_tokens +
                          $response2->usage->input_tokens + $response2->usage->output_tokens;
            echo "Total tokens used: {$totalTokens}\n";
        }
    } elseif ($response1->stop_reason === 'end_turn') {
        echo "Claude provided a direct answer without using tools:\n";
        foreach ($response1->content as $block) {
            if ($block['type'] === 'text') {
                echo $block['text'] . "\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}

echo "\n" . str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 2: Multiple Calculations
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 2: Handling Different Types of Calculations\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$testQuestions = [
    "What is 25 + 17?",
    "Calculate 1000 - 337",
    "What's 12.5 * 8?",
    "Divide 100 by 4"
];

foreach ($testQuestions as $i => $question) {
    echo "\nTest " . ($i + 1) . ": \"{$question}\"\n";
    
    try {
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => $question]
            ],
            'tools' => [$calculatorTool]
        ]);
        
        if ($response->stop_reason === 'tool_use') {
            // Extract and execute tool
            foreach ($response->content as $block) {
                if ($block['type'] === 'tool_use') {
                    $expression = $block['input']['expression'];
                    $result = eval("return {$expression};");
                    
                    // Get final answer
                    $finalResponse = $client->messages()->create([
                        'model' => 'claude-sonnet-4-5',
                        'max_tokens' => 1024,
                        'messages' => [
                            ['role' => 'user', 'content' => $question],
                            ['role' => 'assistant', 'content' => $response->content],
                            [
                                'role' => 'user',
                                'content' => [
                                    [
                                        'type' => 'tool_result',
                                        'tool_use_id' => $block['id'],
                                        'content' => (string)$result
                                    ]
                                ]
                            ]
                        ],
                        'tools' => [$calculatorTool]
                    ]);
                    
                    echo "  → ";
                    foreach ($finalResponse->content as $contentBlock) {
                        if ($contentBlock['type'] === 'text') {
                            echo $contentBlock['text'];
                        }
                    }
                    echo "\n";
                }
            }
        } else {
            // Direct answer
            foreach ($response->content as $block) {
                if ($block['type'] === 'text') {
                    echo "  → {$block['text']}\n";
                }
            }
        }
        
    } catch (Exception $e) {
        echo "  Error: {$e->getMessage()}\n";
    }
}

echo "\n" . str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 3: When Claude Doesn't Need Tools
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 3: Understanding When Tools Are NOT Used\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Testing different question types to see when Claude uses the calculator...\n\n";

$testCases = [
    [
        'question' => 'What is 2 + 2?',
        'expectTool' => false,
        'reason' => 'Simple arithmetic Claude can do mentally'
    ],
    [
        'question' => 'What is 9,876 × 5,432?',
        'expectTool' => true,
        'reason' => 'Large numbers requiring precision'
    ],
    [
        'question' => 'What is a calculator?',
        'expectTool' => false,
        'reason' => 'Conceptual question, not a calculation'
    ]
];

foreach ($testCases as $i => $test) {
    echo "Test " . ($i + 1) . ": \"{$test['question']}\"\n";
    echo "Expected: " . ($test['expectTool'] ? 'Use tool' : 'Direct answer') . "\n";
    echo "Reason: {$test['reason']}\n";
    
    try {
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => $test['question']]
            ],
            'tools' => [$calculatorTool]
        ]);
        
        $usedTool = $response->stop_reason === 'tool_use';
        $match = ($usedTool === $test['expectTool']) ? '✓' : '✗';
        
        echo "Result: " . ($usedTool ? 'Used tool' : 'Direct answer') . " {$match}\n\n";
        
    } catch (Exception $e) {
        echo "Error: {$e->getMessage()}\n\n";
    }
}

echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 4: Error Handling
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 4: Handling Tool Execution Errors\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Simulating a division by zero error...\n\n";

try {
    $errorQuestion = "What is 100 divided by 0?";
    
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            ['role' => 'user', 'content' => $errorQuestion]
        ],
        'tools' => [$calculatorTool]
    ]);
    
    if ($response->stop_reason === 'tool_use') {
        foreach ($response->content as $block) {
            if ($block['type'] === 'tool_use') {
                echo "Tool requested: {$block['input']['expression']}\n";
                
                // Simulate error in tool execution
                $errorMessage = "Error: Division by zero";
                
                echo "Tool execution failed: {$errorMessage}\n\n";
                
                // Return error to Claude with is_error flag
                $finalResponse = $client->messages()->create([
                    'model' => 'claude-sonnet-4-5',
                    'max_tokens' => 1024,
                    'messages' => [
                        ['role' => 'user', 'content' => $errorQuestion],
                        ['role' => 'assistant', 'content' => $response->content],
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'tool_result',
                                    'tool_use_id' => $block['id'],
                                    'content' => $errorMessage,
                                    'is_error' => true  // Signal this is an error
                                ]
                            ]
                        ]
                    ],
                    'tools' => [$calculatorTool]
                ]);
                
                echo "Claude's response to error:\n";
                echo str_repeat("-", 80) . "\n";
                foreach ($finalResponse->content as $contentBlock) {
                    if ($contentBlock['type'] === 'text') {
                        echo $contentBlock['text'] . "\n";
                    }
                }
                echo str_repeat("-", 80) . "\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}

echo "\n" . str_repeat("═", 80) . "\n\n";

// ============================================================================
// Summary
// ============================================================================

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                           Tutorial Summary                                 ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "✅ You've learned:\n\n";
echo "1️⃣  How to define a tool with input_schema\n";
echo "   • Name, description, and parameters\n";
echo "   • JSON Schema for validation\n\n";

echo "2️⃣  How to send requests with tools\n";
echo "   • Include tools in the API request\n";
echo "   • Claude decides when to use them\n\n";

echo "3️⃣  How to handle tool use responses\n";
echo "   • Check stop_reason\n";
echo "   • Extract tool_use blocks\n";
echo "   • Get tool name and parameters\n\n";

echo "4️⃣  How to execute tools and return results\n";
echo "   • Run the actual tool function\n";
echo "   • Format as tool_result\n";
echo "   • Match tool_use_id\n\n";

echo "5️⃣  How to handle errors gracefully\n";
echo "   • Use is_error flag\n";
echo "   • Provide helpful error messages\n";
echo "   • Let Claude adapt to errors\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🎓 Key Concepts:\n\n";
echo "• stop_reason='tool_use' → Claude wants to execute a tool\n";
echo "• stop_reason='end_turn' → Claude has finished responding\n";
echo "• tool_use_id must match between request and result\n";
echo "• Tools extend Claude's capabilities beyond training data\n";
echo "• Good descriptions help Claude choose the right tool\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🚀 Next Steps:\n\n";
echo "This agent handles ONE tool call at a time. But what if your task\n";
echo "requires MULTIPLE tool calls in sequence?\n\n";

echo "Continue to Tutorial 2: ReAct Basics\n";
echo "→ tutorials/02-react-basics/\n\n";

echo "You'll learn how to build a ReAct loop that enables iterative\n";
echo "reasoning and multi-step problem solving!\n\n";


