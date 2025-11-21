#!/usr/bin/env php
<?php
/**
 * Tutorial 0: Introduction to Agentic AI - Code Examples
 * 
 * This file demonstrates core concepts of agentic AI through working examples.
 * Run this to see the difference between chatbot-style and agent-style interactions.
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../examples/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║         Tutorial 0: Introduction to Agentic AI - Core Concepts            ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Example 1: Traditional Chatbot (No Tools)
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 1: Traditional Chatbot Behavior (No Tools)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Question: What is 1,234 × 5,678?\n\n";

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            ['role' => 'user', 'content' => 'What is 1,234 × 5,678?']
        ]
    ]);
    
    echo "🤖 Chatbot Response:\n";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo "   {$block['text']}\n";
        }
    }
    
    echo "\n💡 Observation: The chatbot tries to calculate mentally but may be approximate.\n";
    echo "   It's limited to its training and reasoning capabilities.\n";
    
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}

echo "\n" . str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 2: Agent with Tool (Calculator)
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 2: Agent Behavior (With Calculator Tool)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Same question: What is 1,234 × 5,678?\n\n";

// Define a calculator tool
$calculatorTool = [
    'name' => 'calculate',
    'description' => 'Perform precise mathematical calculations. Supports +, -, *, /, and parentheses.',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'expression' => [
                'type' => 'string',
                'description' => 'Mathematical expression to evaluate, e.g., "1234 * 5678"'
            ]
        ],
        'required' => ['expression']
    ]
];

try {
    // Step 1: Send request with tool
    echo "🧠 Agent Reasoning Phase:\n";
    $response1 = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'tools' => [$calculatorTool],
        'messages' => [
            ['role' => 'user', 'content' => 'What is 1,234 × 5,678?']
        ]
    ]);
    
    // Check if agent wants to use the tool
    $toolUse = null;
    foreach ($response1->content as $block) {
        if ($block['type'] === 'tool_use') {
            $toolUse = $block;
            echo "   ✓ Agent decided to use tool: '{$block['name']}'\n";
            echo "   ✓ With parameters: " . json_encode($block['input']) . "\n";
        }
    }
    
    if ($toolUse) {
        // Step 2: Execute the tool (our code)
        echo "\n🔧 Tool Execution Phase:\n";
        $expression = $toolUse['input']['expression'];
        echo "   ✓ Evaluating: {$expression}\n";
        
        // Simple eval (in production, use a proper math parser!)
        // For demo purposes only - eval() is dangerous with untrusted input
        $result = eval("return {$expression};");
        echo "   ✓ Result: {$result}\n";
        
        // Step 3: Return result to agent
        echo "\n🧠 Agent Response Phase:\n";
        $response2 = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'tools' => [$calculatorTool],
            'messages' => [
                ['role' => 'user', 'content' => 'What is 1,234 × 5,678?'],
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
            ]
        ]);
        
        echo "   Agent Final Response:\n";
        foreach ($response2->content as $block) {
            if ($block['type'] === 'text') {
                echo "   {$block['text']}\n";
            }
        }
        
        echo "\n💡 Observation: The agent used a tool to get the EXACT answer.\n";
        echo "   This is the power of agentic behavior!\n";
    }
    
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}

echo "\n" . str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 3: Demonstrating the ReAct Loop
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 3: The ReAct Loop in Action\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Task: Calculate (100 × 50) + (25 × 30)\n";
echo "This requires TWO tool calls - let's see the agent figure it out!\n\n";

$messages = [
    ['role' => 'user', 'content' => 'What is (100 × 50) + (25 × 30)?']
];

$iteration = 0;
$maxIterations = 5;

while ($iteration < $maxIterations) {
    $iteration++;
    
    echo "╔════ Iteration {$iteration} ════╗\n\n";
    
    // Call Claude
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'tools' => [$calculatorTool],
        'messages' => $messages
    ]);
    
    echo "🧠 REASON: Agent is thinking...\n";
    echo "🛑 Stop Reason: {$response->stop_reason}\n\n";
    
    // Add assistant response to conversation
    $messages[] = ['role' => 'assistant', 'content' => $response->content];
    
    // Check what the agent wants to do
    if ($response->stop_reason === 'end_turn') {
        echo "✅ COMPLETE: Agent finished!\n\n";
        echo "📝 Final Answer:\n";
        foreach ($response->content as $block) {
            if ($block['type'] === 'text') {
                echo "   {$block['text']}\n";
            }
        }
        break;
    }
    
    if ($response->stop_reason === 'tool_use') {
        $toolResults = [];
        
        foreach ($response->content as $block) {
            if ($block['type'] === 'tool_use') {
                echo "🔧 ACT: Using tool '{$block['name']}'\n";
                echo "   Input: {$block['input']['expression']}\n";
                
                // Execute tool
                $expression = $block['input']['expression'];
                $result = eval("return {$expression};");
                
                echo "👁️  OBSERVE: Tool returned: {$result}\n\n";
                
                $toolResults[] = [
                    'type' => 'tool_result',
                    'tool_use_id' => $block['id'],
                    'content' => (string)$result
                ];
            }
        }
        
        // Add tool results to conversation
        if (!empty($toolResults)) {
            $messages[] = ['role' => 'user', 'content' => $toolResults];
        }
    }
}

if ($iteration >= $maxIterations) {
    echo "⚠️  Max iterations reached!\n";
}

echo "\n💡 Observation: The agent executed multiple tool calls in sequence,\n";
echo "   using the results to solve the complete problem. This is ReAct!\n";

echo "\n" . str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 4: Showing Stop Reasons
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 4: Understanding Stop Reasons\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$testCases = [
    [
        'name' => 'Direct Answer (No Tool)',
        'message' => 'What is the capital of France?',
        'expected' => 'end_turn'
    ],
    [
        'name' => 'Tool Use Needed',
        'message' => 'What is 987 × 654?',
        'expected' => 'tool_use'
    ]
];

foreach ($testCases as $test) {
    echo "Test: {$test['name']}\n";
    echo "Question: \"{$test['message']}\"\n";
    
    try {
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'tools' => [$calculatorTool],
            'messages' => [
                ['role' => 'user', 'content' => $test['message']]
            ]
        ]);
        
        $stopReason = $response->stop_reason;
        $match = $stopReason === $test['expected'] ? '✓' : '✗';
        
        echo "  Stop Reason: {$stopReason} {$match}\n";
        echo "  Expected: {$test['expected']}\n\n";
        
    } catch (Exception $e) {
        echo "  Error: {$e->getMessage()}\n\n";
    }
}

echo "💡 Key Insight: The 'stop_reason' tells you what the agent wants to do next:\n";
echo "   • 'end_turn': Agent has completed its response\n";
echo "   • 'tool_use': Agent wants to execute a tool\n";
echo "   • 'max_tokens': Response was cut off (increase max_tokens)\n\n";

echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Summary
// ============================================================================

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                              Key Takeaways                                 ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "1️⃣  Chatbots respond → Agents take action\n";
echo "   Chatbots are limited to their training. Agents use tools to extend capabilities.\n\n";

echo "2️⃣  ReAct Loop = Reason → Act → Observe → Repeat\n";
echo "   Agents iterate until the task is complete or max iterations reached.\n\n";

echo "3️⃣  Tools are the agent's superpowers\n";
echo "   They enable getting real-time data, performing calculations, taking actions.\n\n";

echo "4️⃣  Stop reasons guide the loop\n";
echo "   'tool_use' = needs to execute a tool\n";
echo "   'end_turn' = task complete\n\n";

echo "5️⃣  Iteration limits prevent infinite loops\n";
echo "   Always set a maximum to avoid runaway execution.\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🎓 Ready to build your first real agent?\n";
echo "   Continue to Tutorial 1: Your First Agent\n";
echo "   → tutorials/01-first-agent/\n\n";

echo "📚 Concepts covered:\n";
echo "   ✓ Chatbot vs Agent behavior\n";
echo "   ✓ Tool definitions and usage\n";
echo "   ✓ The ReAct loop pattern\n";
echo "   ✓ Stop reasons and their meanings\n";
echo "   ✓ Multi-step problem solving\n\n";


