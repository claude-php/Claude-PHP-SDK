#!/usr/bin/env php
<?php
/**
 * Tutorial 14: Autonomous Agents - Working Example
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../examples/helpers.php';
require_once __DIR__ . '/../helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║         Tutorial 14: Autonomous Agents - Goal-Directed Behavior           ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

$stateFile = __DIR__ . '/agent_state.json';

// Initialize or load state
if (file_exists($stateFile)) {
    $state = json_decode(file_get_contents($stateFile), true);
    echo "📂 Loaded existing state from previous session\n\n";
} else {
    $state = [
        'goal' => 'Calculate statistics for numbers 10, 20, 30, 40, 50',
        'sub_goals' => [
            'calculate_mean' => ['status' => 'pending', 'result' => null],
            'calculate_median' => ['status' => 'pending', 'result' => null],
            'calculate_range' => ['status' => 'pending', 'result' => null],
            'summarize' => ['status' => 'pending', 'result' => null]
        ],
        'iterations' => 0,
        'started_at' => time()
    ];
    echo "🆕 Initialized new agent state\n\n";
}

echo "🎯 Agent Goal: {$state['goal']}\n\n";

// Show current progress
echo "📊 Current Progress:\n";
foreach ($state['sub_goals'] as $name => $subgoal) {
    $icon = match($subgoal['status']) {
        'completed' => '✓',
        'in_progress' => '⏳',
        'pending' => '⭘',
        default => '?'
    };
    echo "  {$icon} {$name}: {$subgoal['status']}\n";
}
echo "\n";

// Safety limits
$MAX_ITERATIONS = 10;
$state['iterations']++;

if ($state['iterations'] > $MAX_ITERATIONS) {
    echo "⚠️  Safety limit: Max iterations reached\n";
    exit(1);
}

// Find next sub-goal
$nextGoal = null;
foreach ($state['sub_goals'] as $name => $subgoal) {
    if ($subgoal['status'] === 'pending') {
        $nextGoal = $name;
        $state['sub_goals'][$name]['status'] = 'in_progress';
        break;
    }
}

if (!$nextGoal) {
    echo "🎉 All sub-goals completed!\n\n";
    echo "Final Results:\n";
    echo str_repeat("-", 80) . "\n";
    foreach ($state['sub_goals'] as $name => $subgoal) {
        if ($subgoal['result']) {
            echo "{$name}: {$subgoal['result']}\n";
        }
    }
    
    // Cleanup
    if (file_exists($stateFile)) {
        unlink($stateFile);
    }
    exit(0);
}

echo "🔄 Iteration {$state['iterations']}: Working on {$nextGoal}\n\n";

// Execute current sub-goal
try {
    $prompt = match($nextGoal) {
        'calculate_mean' => "Calculate the mean (average) of: 10, 20, 30, 40, 50",
        'calculate_median' => "Calculate the median of: 10, 20, 30, 40, 50",
        'calculate_range' => "Calculate the range (max - min) of: 10, 20, 30, 40, 50",
        'summarize' => "Summarize these statistics:\n" .
                      "Mean: {$state['sub_goals']['calculate_mean']['result']}\n" .
                      "Median: {$state['sub_goals']['calculate_median']['result']}\n" .
                      "Range: {$state['sub_goals']['calculate_range']['result']}",
        default => "Unknown goal"
    };
    
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 512,
        'messages' => [['role' => 'user', 'content' => $prompt]]
    ]);
    
    $result = extractTextContent($response);
    echo "Result: {$result}\n\n";
    
    $state['sub_goals'][$nextGoal]['status'] = 'completed';
    $state['sub_goals'][$nextGoal]['result'] = $result;
    
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
    $state['sub_goals'][$nextGoal]['status'] = 'failed';
}

// Save state for next iteration
file_put_contents($stateFile, json_encode($state, JSON_PRETTY_PRINT));
echo "💾 State saved\n\n";

echo "📈 Progress: " . calculateProgress($state) . "%\n\n";

echo "💡 Run this script again to continue where it left off!\n";
echo "   The agent persists its state between sessions.\n\n";

function calculateProgress($state) {
    $total = count($state['sub_goals']);
    $completed = 0;
    foreach ($state['sub_goals'] as $subgoal) {
        if ($subgoal['status'] === 'completed') {
            $completed++;
        }
    }
    return round(($completed / $total) * 100);
}

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                     Tutorial Series Complete!                             ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "🎓 Congratulations! You've mastered:\n\n";
echo "  0️⃣  Agentic AI concepts\n";
echo "  1️⃣  Basic agents with tools\n";
echo "  2️⃣  ReAct loops\n";
echo "  3️⃣  Multi-tool agents\n";
echo "  4️⃣  Production patterns\n";
echo "  5️⃣  Advanced ReAct\n";
echo "  6️⃣  Agentic frameworks\n";
echo "  7️⃣  Chain of Thought\n";
echo "  8️⃣  Tree of Thoughts\n";
echo "  9️⃣  Plan-and-Execute\n";
echo "  🔟 Reflection\n";
echo "  1️⃣1️⃣  Hierarchical Agents\n";
echo "  1️⃣2️⃣  Multi-Agent Debate\n";
echo "  1️⃣3️⃣  RAG Pattern\n";
echo "  1️⃣4️⃣  Autonomous Agents\n\n";

echo "🚀 You're ready to build sophisticated AI systems!\n\n";




