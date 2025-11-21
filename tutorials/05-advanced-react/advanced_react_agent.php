#!/usr/bin/env php
<?php
/**
 * Tutorial 5: Advanced ReAct - Working Example
 * 
 * Demonstrates Plan-Execute-Reflect-Adjust pattern with extended thinking
 * for complex reasoning and problem-solving.
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../examples/helpers.php';
require_once __DIR__ . '/../helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║       Tutorial 5: Advanced ReAct - Planning, Reflection & Thinking         ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Tools
// ============================================================================

$calculatorTool = createTool(
    'calculate',
    'Perform mathematical calculations',
    ['expression' => ['type' => 'string', 'description' => 'Math expression']],
    ['expression']
);

$searchTool = createTool(
    'search',
    'Search for information',
    ['query' => ['type' => 'string', 'description' => 'Search query']],
    ['query']
);

$tools = [$calculatorTool, $searchTool];

// Tool executors
function executeTool($toolName, $input) {
    return match($toolName) {
        'calculate' => eval("return {$input['expression']};"),
        'search' => "Search results for '{$input['query']}': [Simulated information]",
        default => "Unknown tool"
    };
}

// ============================================================================
// Example 1: Planning Phase
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 1: Planning Before Execution\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$task = "Calculate the total cost of 3 items priced at \$25, \$37, and \$19, then add 8% tax";

echo "Task: {$task}\n\n";
echo "Phase 1: Planning with Extended Thinking\n";
echo str_repeat("-", 80) . "\n";

$planningSystem = "You are a meticulous planner. Before taking action:\n" .
                  "1. Break down the task into clear steps\n" .
                  "2. Identify what tools you'll need\n" .
                  "3. Plan the sequence of operations\n" .
                  "Create a numbered plan, then wait for permission to execute.";

try {
    $planMessages = [
        ['role' => 'user', 'content' => "Task: {$task}\n\nFirst, create a detailed plan."]
    ];
    
    $planResponse = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 8000,  // Must be greater than thinking budget
        'system' => $planningSystem,
        'messages' => $planMessages,
        'thinking' => [
            'type' => 'enabled',
            'budget_tokens' => 5000
        ]
    ]);
    
    echo "\n📋 Plan Created:\n";
    foreach ($planResponse->content as $block) {
        if ($block['type'] === 'thinking') {
            echo "💭 Thinking: " . substr($block['thinking'], 0, 200) . "...\n\n";
        }
        if ($block['type'] === 'text') {
            echo $block['text'] . "\n";
        }
    }
    
    echo "\nThinking tokens used: " . ($planResponse->usage->thinking_tokens ?? 0) . "\n";
    
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}

echo "\n" . str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 2: Complete Plan-Execute-Reflect Cycle
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 2: Complete Advanced ReAct Cycle\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$complexTask = "Find 3 numbers that sum to 100, where each number is a multiple of 7";

echo "Complex Task: {$complexTask}\n\n";

$system = "You plan carefully, execute methodically, and reflect on your work.";
$messages = [];

// PHASE 1: PLAN
echo "╔════ PHASE 1: PLAN ════╗\n\n";

$messages[] = ['role' => 'user', 'content' => "Task: {$complexTask}\n\nThink deeply and create a plan."];

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 8000,  // Must be greater than thinking budget
        'system' => $system,
        'messages' => $messages,
        'thinking' => ['type' => 'enabled', 'budget_tokens' => 5000]
    ]);
    
    $messages[] = ['role' => 'assistant', 'content' => $response->content];
    
    echo extractTextContent($response) . "\n\n";
    
    // PHASE 2: EXECUTE
    echo "╔════ PHASE 2: EXECUTE ════╗\n\n";
    
    $messages[] = ['role' => 'user', 'content' => 'Now execute your plan.'];
    
    $iteration = 0;
    while ($iteration < 10) {
        $iteration++;
        
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 4096,
            'messages' => $messages,
            'tools' => $tools,
            'thinking' => ['type' => 'enabled', 'budget_tokens' => 3000]
        ]);
        
        $messages[] = ['role' => 'assistant', 'content' => $response->content];
        
        if ($response->stop_reason === 'end_turn') {
            echo "Execution complete in {$iteration} steps\n\n";
            break;
        }
        
        if ($response->stop_reason === 'tool_use') {
            $toolResults = [];
            foreach ($response->content as $block) {
                if ($block['type'] === 'tool_use') {
                    $result = executeTool($block['name'], $block['input']);
                    echo "  Tool: {$block['name']} → {$result}\n";
                    $toolResults[] = [
                        'type' => 'tool_result',
                        'tool_use_id' => $block['id'],
                        'content' => (string)$result
                    ];
                }
            }
            if (!empty($toolResults)) {
                $messages[] = ['role' => 'user', 'content' => $toolResults];
            }
        }
    }
    
    // PHASE 3: REFLECT
    echo "\n╔════ PHASE 3: REFLECT ════╗\n\n";
    
    $messages[] = [
        'role' => 'user',
        'content' => 'Reflect on your solution. Did you achieve the goal correctly? Are there any issues?'
    ];
    
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 5000,  // Must be greater than thinking budget
        'messages' => $messages,
        'thinking' => ['type' => 'enabled', 'budget_tokens' => 3000]
    ]);
    
    echo extractTextContent($response) . "\n\n";
    
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}

echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 3: Self-Correction
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 3: Self-Correction Through Reflection\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Give agent a task it might get wrong first try
$trickTask = "Calculate 10% of 250, then multiply that result by 3";

echo "Task: {$trickTask}\n\n";
echo "Agent will attempt, reflect, and correct if needed...\n\n";

$messages = [
    ['role' => 'user', 'content' => $trickTask]
];

try {
    // First attempt
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 4096,
        'messages' => $messages,
        'tools' => $tools
    ]);
    
    $messages[] = ['role' => 'assistant', 'content' => $response->content];
    
    // Execute any tools
    if ($response->stop_reason === 'tool_use') {
        $toolResults = [];
        foreach ($response->content as $block) {
            if ($block['type'] === 'tool_use') {
                $result = executeTool($block['name'], $block['input']);
                $toolResults[] = [
                    'type' => 'tool_result',
                    'tool_use_id' => $block['id'],
                    'content' => (string)$result
                ];
            }
        }
        if (!empty($toolResults)) {
            $messages[] = ['role' => 'user', 'content' => $toolResults];
        }
        
        // Get final answer
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => $messages,
            'tools' => $tools
        ]);
        
        $messages[] = ['role' => 'assistant', 'content' => $response->content];
    }
    
    echo "Initial Answer: " . extractTextContent($response) . "\n\n";
    
    // Ask agent to verify
    $messages[] = [
        'role' => 'user',
        'content' => 'Please verify this answer step-by-step. Is it correct?'
    ];
    
    $verifyResponse = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 5000,  // Must be greater than thinking budget
        'messages' => $messages,
        'thinking' => ['type' => 'enabled', 'budget_tokens' => 3000]
    ]);
    
    echo "Verification: " . extractTextContent($verifyResponse) . "\n";
    
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

echo "✅ Advanced Patterns Implemented:\n\n";

echo "1️⃣  Planning\n";
echo "   • Think before acting\n";
echo "   • Break down complex tasks\n";
echo "   • Use extended thinking\n\n";

echo "2️⃣  Extended Thinking\n";
echo "   • Configure thinking budget\n";
echo "   • Deep reasoning for complex problems\n";
echo "   • View thinking process\n\n";

echo "3️⃣  Reflection\n";
echo "   • Analyze results\n";
echo "   • Detect issues\n";
echo "   • Propose improvements\n\n";

echo "4️⃣  Self-Correction\n";
echo "   • Verify answers\n";
echo "   • Fix mistakes\n";
echo "   • Improve accuracy\n\n";

echo "5️⃣  Complete Cycle\n";
echo "   • Plan → Execute → Reflect → Adjust\n";
echo "   • Iterative improvement\n";
echo "   • Higher quality results\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🎯 When to Use Advanced ReAct:\n\n";
echo "  ✓ Complex reasoning tasks\n";
echo "  ✓ Multi-step problems\n";
echo "  ✓ When accuracy is critical\n";
echo "  ✓ Research and analysis\n";
echo "  ✓ Problem decomposition\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🚀 Final Step: Tutorial 6 - Complete Agentic Framework\n";
echo "→ tutorials/06-agentic-framework/\n\n";

echo "Bring everything together into a full orchestration system!\n\n";

