#!/usr/bin/env php
<?php
/**
 * Tutorial 9: Plan-and-Execute - Working Example
 * 
 * Demonstrates the Plan-and-Execute pattern where planning is separated
 * from execution for more efficient and systematic task completion.
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../examples/helpers.php';
require_once __DIR__ . '/../helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║         Tutorial 9: Plan-and-Execute - Systematic Task Completion         ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Tools Available
// ============================================================================

$tools = [
    [
        'name' => 'calculate',
        'description' => 'Perform mathematical calculations',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'expression' => ['type' => 'string', 'description' => 'Math expression']
            ],
            'required' => ['expression']
        ]
    ],
    [
        'name' => 'search',
        'description' => 'Search for information',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'query' => ['type' => 'string', 'description' => 'Search query']
            ],
            'required' => ['query']
        ]
    ]
];

function executeTool($name, $input) {
    return match($name) {
        'calculate' => (string)eval("return {$input['expression']};"),
        'search' => "Search results for '{$input['query']}': [Simulated data]",
        default => "Unknown tool"
    };
}

// ============================================================================
// Example 1: Basic Plan-and-Execute
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 1: Research Task with Planning\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$task = "Calculate the average of 25, 37, and 48, then determine what percentage it is of 100.";

echo "Task: {$task}\n\n";

// PHASE 1: PLANNING
echo "╔════ PHASE 1: PLANNING ════╗\n\n";

$planningPrompt = "Task: {$task}\n\n" .
                  "Available tools:\n" .
                  "- calculate: perform math operations\n" .
                  "- search: find information\n\n" .
                  "Create a detailed execution plan. For each step specify:\n" .
                  "Step N: [Description]\n" .
                  "Tool: [tool_name]\n" .
                  "Expected: [what you'll get]\n\n" .
                  "Then explain dependencies between steps.";

try {
    $planResponse = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1536,
        'messages' => [
            ['role' => 'user', 'content' => $planningPrompt]
        ]
    ]);
    
    $plan = extractTextContent($planResponse);
    echo $plan . "\n\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
    exit(1);
}

// PHASE 2: EXECUTION
echo "╔════ PHASE 2: EXECUTION ════╗\n\n";

$messages = [
    ['role' => 'user', 'content' => $planningPrompt],
    ['role' => 'assistant', 'content' => $planResponse->content],
    ['role' => 'user', 'content' => 'Now execute this plan step by step using the available tools.']
];

$iteration = 0;
$maxIterations = 10;

while ($iteration < $maxIterations) {
    $iteration++;
    echo "Iteration {$iteration}\n";
    
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
        echo "✓ Plan execution complete!\n\n";
        echo "Final Result:\n";
        echo str_repeat("-", 80) . "\n";
        echo extractTextContent($response) . "\n\n";
        break;
    }
    
    if ($response->stop_reason === 'tool_use') {
        $toolResults = [];
        
        foreach ($response->content as $block) {
            if ($block['type'] === 'tool_use') {
                echo "  → Using tool: {$block['name']}\n";
                $result = executeTool($block['name'], $block['input']);
                echo "  ← Result: {$result}\n";
                
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

echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 2: Plan Visualization
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 2: Visualizing Execution Plan\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Simulated plan structure
$executionPlan = [
    ['step' => 1, 'action' => 'Calculate average of 25, 37, 48', 'tool' => 'calculate', 'status' => 'completed'],
    ['step' => 2, 'action' => 'Calculate percentage of 100', 'tool' => 'calculate', 'status' => 'completed'],
    ['step' => 3, 'action' => 'Format final answer', 'tool' => 'none', 'status' => 'completed']
];

echo "EXECUTION PLAN VISUALIZATION\n";
echo str_repeat("=", 80) . "\n\n";

foreach ($executionPlan as $item) {
    $statusIcon = match($item['status']) {
        'completed' => '✓',
        'in_progress' => '⏳',
        'pending' => '⭘',
        'failed' => '✗',
        default => '?'
    };
    
    echo "{$statusIcon} Step {$item['step']}: {$item['action']}\n";
    echo "   Tool: {$item['tool']}\n";
    echo "   Status: {$item['status']}\n\n";
}

echo "Plan Metrics:\n";
echo "  Total Steps: " . count($executionPlan) . "\n";
echo "  Completed: 3/3\n";
echo "  Success Rate: 100%\n\n";

echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 3: Plan Comparison (ReAct vs Plan-and-Execute)
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 3: Comparing ReAct vs Plan-and-Execute\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Task: Calculate compound interest for \$1000 at 5% for 3 years\n\n";

echo "🔄 ReAct Approach (Interleaved):\n";
echo str_repeat("-", 80) . "\n";
echo "Think: Need principal amount... let's calculate\n";
echo "Act: Calculate 1000 * 1.05\n";
echo "Observe: Got 1050\n";
echo "Think: Now for year 2...\n";
echo "Act: Calculate 1050 * 1.05\n";
echo "Observe: Got 1102.50\n";
echo "Think: Now for year 3...\n";
echo "Act: Calculate 1102.50 * 1.05\n";
echo "Observe: Got 1157.63\n";
echo "Result: \$1157.63\n";
echo "→ 7 steps (think-act-observe cycles)\n\n";

echo "📋 Plan-and-Execute Approach:\n";
echo str_repeat("-", 80) . "\n";
echo "PLAN:\n";
echo "  1. Use compound interest formula: A = P(1 + r)^t\n";
echo "  2. Calculate: 1000 * (1.05)^3\n";
echo "  3. Return result\n\n";
echo "EXECUTE:\n";
echo "  Step 1: Calculate (1.05)^3 = 1.157625\n";
echo "  Step 2: Calculate 1000 * 1.157625 = 1157.63\n";
echo "Result: \$1157.63\n";
echo "→ 2 steps (more efficient)\n\n";

echo "💡 Plan-and-Execute can be more efficient for well-defined tasks!\n";
echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 4: Handling Plan Revision
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 4: Plan Revision on Failure\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Original Plan:\n";
echo "  1. Search for data\n";
echo "  2. Analyze data\n";
echo "  3. Generate report\n\n";

echo "Execution Log:\n";
echo "  ✓ Step 1: Completed (found data)\n";
echo "  ✗ Step 2: Failed (data format incompatible)\n\n";

echo "Revising Plan...\n";
echo str_repeat("-", 80) . "\n";

$revisionPrompt = "Original plan failed at step 2 because data format was incompatible. " .
                  "Step 1 (search) completed successfully. " .
                  "Revise the plan to handle the format issue and still complete the task.";

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            ['role' => 'user', 'content' => $revisionPrompt]
        ]
    ]);
    
    echo extractTextContent($response) . "\n\n";
    echo "💡 Plans can be revised when execution reveals new information!\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Summary
// ============================================================================

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                           Tutorial Summary                                 ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "✅ Plan-and-Execute Concepts Demonstrated:\n\n";

echo "1️⃣  Separate Planning Phase\n";
echo "   • Analyze task completely\n";
echo "   • Create detailed step plan\n";
echo "   • Identify dependencies\n\n";

echo "2️⃣  Systematic Execution\n";
echo "   • Follow plan in order\n";
echo "   • Execute one step at a time\n";
echo "   • Track progress\n\n";

echo "3️⃣  Monitoring & Visualization\n";
echo "   • Show execution status\n";
echo "   • Track metrics\n";
echo "   • Identify bottlenecks\n\n";

echo "4️⃣  Plan Revision\n";
echo "   • Handle failures gracefully\n";
echo "   • Revise based on results\n";
echo "   • Maintain progress\n\n";

echo "5️⃣  Efficiency Gains\n";
echo "   • Fewer wasted actions\n";
echo "   • Predictable resource use\n";
echo "   • Better audit trail\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🎯 When to Use Plan-and-Execute:\n\n";

echo "  ✓ Well-defined tasks\n";
echo "  ✓ Predictable steps\n";
echo "  ✓ Efficiency important\n";
echo "  ✓ Resource constraints\n";
echo "  ✓ Need audit trail\n\n";

echo "⚠️  When to Use ReAct Instead:\n\n";

echo "  • Exploratory tasks\n";
echo "  • Uncertain outcomes\n";
echo "  • Dynamic environments\n";
echo "  • Learning as you go\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🚀 Plan-and-Execute enables systematic task completion!\n\n";
echo "Next: Tutorial 10 - Reflection for self-improvement\n";
echo "→ tutorials/10-reflection/\n\n";


