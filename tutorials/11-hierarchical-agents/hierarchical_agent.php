#!/usr/bin/env php
<?php
/**
 * Tutorial 11: Hierarchical Agents - Working Example
 * 
 * Demonstrates master-worker agent architecture with specialized agents
 * for different domains coordinated by a master agent.
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../examples/helpers.php';
require_once __DIR__ . '/../helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║          Tutorial 11: Hierarchical Agents - Master-Worker Pattern         ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Agent Classes
// ============================================================================

/**
 * Base Worker Agent
 */
class WorkerAgent {
    protected $client;
    protected $name;
    protected $specialty;
    protected $systemPrompt;
    
    public function __construct($client, $name, $specialty, $systemPrompt) {
        $this->client = $client;
        $this->name = $name;
        $this->specialty = $specialty;
        $this->systemPrompt = $systemPrompt;
    }
    
    public function execute($task) {
        try {
            $response = $this->client->messages()->create([
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 2048,
                'system' => $this->systemPrompt,
                'messages' => [
                    ['role' => 'user', 'content' => $task]
                ]
            ]);
            
            return extractTextContent($response);
        } catch (Exception $e) {
            return "Error in {$this->name}: {$e->getMessage()}";
        }
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getSpecialty() {
        return $this->specialty;
    }
}

/**
 * Master Agent - Coordinates workers
 */
class MasterAgent {
    private $client;
    private $workers = [];
    
    public function __construct($client) {
        $this->client = $client;
    }
    
    public function registerWorker($worker) {
        $this->workers[$worker->getName()] = $worker;
    }
    
    public function decompose($task) {
        // Build description of available workers
        $workersList = "";
        foreach ($this->workers as $name => $worker) {
            $workersList .= "- {$name}: {$worker->getSpecialty()}\n";
        }
        
        $decompositionPrompt = "Complex task: {$task}\n\n" .
                              "Available specialized agents:\n{$workersList}\n" .
                              "Decompose this task into subtasks. For each subtask:\n" .
                              "1. Specify which agent should handle it\n" .
                              "2. Describe the subtask clearly\n" .
                              "3. Note any dependencies\n\n" .
                              "Format:\n" .
                              "Agent: [agent_name]\n" .
                              "Subtask: [description]\n" .
                              "Depends on: [other subtasks, or 'none']";
        
        try {
            $response = $this->client->messages()->create([
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 2048,
                'system' => 'You are a master coordinator. Delegate tasks efficiently to specialized agents.',
                'messages' => [['role' => 'user', 'content' => $decompositionPrompt]]
            ]);
            
            return extractTextContent($response);
        } catch (Exception $e) {
            return "Decomposition error: {$e->getMessage()}";
        }
    }
    
    public function synthesize($task, $results) {
        $resultsText = "";
        foreach ($results as $agent => $output) {
            $resultsText .= "=== {$agent} Output ===\n{$output}\n\n";
        }
        
        $synthesisPrompt = "Original task: {$task}\n\n" .
                          "Worker outputs:\n{$resultsText}\n" .
                          "Synthesize these into a comprehensive, coherent final answer.";
        
        try {
            $response = $this->client->messages()->create([
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 2048,
                'system' => 'You synthesize outputs from multiple agents into clear, unified responses.',
                'messages' => [['role' => 'user', 'content' => $synthesisPrompt]]
            ]);
            
            return extractTextContent($response);
        } catch (Exception $e) {
            return "Synthesis error: {$e->getMessage()}";
        }
    }
    
    public function getWorker($name) {
        return $this->workers[$name] ?? null;
    }
}

/**
 * Simple task parser
 */
function parseSubtasks($decomposition) {
    $lines = explode("\n", $decomposition);
    $subtasks = [];
    $current = null;
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (preg_match('/^Agent:\s*(.+)$/i', $line, $matches)) {
            if ($current) $subtasks[] = $current;
            $current = ['agent' => trim($matches[1])];
        } elseif (preg_match('/^Subtask:\s*(.+)$/i', $line, $matches)) {
            if ($current) $current['task'] = trim($matches[1]);
        }
    }
    if ($current && isset($current['task'])) {
        $subtasks[] = $current;
    }
    
    return $subtasks;
}

// ============================================================================
// Example 1: Basic Hierarchical System
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 1: Basic Master-Worker System\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$complexTask = "Calculate the average of 45, 67, and 89, then write a brief paragraph explaining what an average represents in statistics.";

echo "🎯 Complex Task:\n{$complexTask}\n\n";

// Create master and workers
$master = new MasterAgent($client);

$mathAgent = new WorkerAgent(
    $client,
    'math_agent',
    'mathematical calculations and statistics',
    'You are a mathematics expert. Solve calculations precisely and explain statistical concepts clearly.'
);

$writingAgent = new WorkerAgent(
    $client,
    'writing_agent',
    'professional writing and explanations',
    'You are a professional writer. Create clear, engaging explanations that are easy to understand.'
);

$master->registerWorker($mathAgent);
$master->registerWorker($writingAgent);

// Phase 1: Decomposition
echo "╔════ Phase 1: Master Decomposes Task ════╗\n\n";

$decomposition = $master->decompose($complexTask);
echo $decomposition . "\n\n";

// Phase 2: Worker Execution
echo "╔════ Phase 2: Workers Execute Subtasks ════╗\n\n";

$subtasks = parseSubtasks($decomposition);
$results = [];

foreach ($subtasks as $i => $subtask) {
    $agentName = $subtask['agent'];
    $task = $subtask['task'];
    
    echo "Subtask " . ($i + 1) . " → {$agentName}\n";
    echo "Task: {$task}\n";
    echo str_repeat("-", 80) . "\n";
    
    $worker = $master->getWorker($agentName);
    if ($worker) {
        $output = $worker->execute($task);
        $results[$agentName] = $output;
        echo "Output: {$output}\n\n";
    } else {
        echo "⚠️ Worker '{$agentName}' not found!\n\n";
    }
}

// Phase 3: Synthesis
echo "╔════ Phase 3: Master Synthesizes Results ════╗\n\n";

if (!empty($results)) {
    $finalAnswer = $master->synthesize($complexTask, $results);
    echo $finalAnswer . "\n\n";
}

echo "💡 Master coordinated specialized workers for optimal results!\n";
echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 2: Four-Agent System
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 2: Multi-Domain Task with Four Agents\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$businessTask = "Analyze whether our company should expand to a new market. " .
                "Consider: market size (assume 1M potential customers), " .
                "required investment (\$500K), expected revenue (\$2M/year), " .
                "and create a brief recommendation.";

echo "🎯 Business Task:\n{$businessTask}\n\n";

// Create expanded agent system
$master2 = new MasterAgent($client);

$researchAgent = new WorkerAgent(
    $client,
    'research_agent',
    'market research and information gathering',
    'You are a market research analyst. Analyze market conditions and trends.'
);

$financeAgent = new WorkerAgent(
    $client,
    'finance_agent',
    'financial analysis and ROI calculations',
    'You are a financial analyst. Calculate ROI, break-even points, and financial metrics.'
);

$strategyAgent = new WorkerAgent(
    $client,
    'strategy_agent',
    'business strategy and recommendations',
    'You are a strategy consultant. Provide balanced, actionable business recommendations.'
);

$master2->registerWorker($researchAgent);
$master2->registerWorker($financeAgent);
$master2->registerWorker($strategyAgent);

echo "Available agents:\n";
echo "  • research_agent - Market analysis\n";
echo "  • finance_agent - Financial calculations\n";
echo "  • strategy_agent - Strategic recommendations\n\n";

// Decompose and execute
echo "Master decomposing task...\n";
echo str_repeat("-", 80) . "\n";

$decomp2 = $master2->decompose($businessTask);
$subtasks2 = parseSubtasks($decomp2);

echo "Identified " . count($subtasks2) . " subtasks\n\n";

$results2 = [];
foreach ($subtasks2 as $i => $subtask) {
    $agentName = $subtask['agent'];
    $worker = $master2->getWorker($agentName);
    
    if ($worker) {
        echo "→ " . ($i + 1) . ". {$agentName}: {$subtask['task']}\n";
        $output = $worker->execute($subtask['task']);
        $results2[$agentName] = $output;
    }
}

echo "\n";

// Synthesize
echo "Master synthesizing recommendations...\n";
echo str_repeat("-", 80) . "\n";

$final2 = $master2->synthesize($businessTask, $results2);
echo $final2 . "\n\n";

echo "💡 Multiple specialists provided comprehensive analysis!\n";
echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 3: Load Tracking
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 3: Agent Load Distribution\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Simulate multiple tasks
$tasks = [
    "Calculate compound interest on \$10,000 at 5% for 3 years",
    "Explain the concept of compound interest",
    "Calculate the area of a circle with radius 5",
    "Write a summary of key statistical concepts"
];

$taskCounts = [
    'math_agent' => 0,
    'writing_agent' => 0
];

echo "Processing " . count($tasks) . " tasks through hierarchical system...\n\n";

foreach ($tasks as $i => $task) {
    echo "Task " . ($i + 1) . ": " . substr($task, 0, 50) . "...\n";
    
    // Determine agent (simplified)
    if (stripos($task, 'calculate') !== false || stripos($task, 'area') !== false) {
        $agentName = 'math_agent';
        $taskCounts['math_agent']++;
    } else {
        $agentName = 'writing_agent';
        $taskCounts['writing_agent']++;
    }
    
    echo "  → Assigned to: {$agentName}\n";
}

echo "\n📊 Load Distribution:\n";
echo str_repeat("-", 80) . "\n";

$totalTasks = array_sum($taskCounts);
foreach ($taskCounts as $agent => $count) {
    $percentage = round(($count / $totalTasks) * 100);
    $bar = str_repeat("█", $percentage / 5);
    echo sprintf("%-15s: %2d tasks (%3d%%) %s\n", $agent, $count, $percentage, $bar);
}

echo "\n💡 Master agent distributed work across specialists!\n";
echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 4: Error Handling in Hierarchy
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 4: Handling Worker Failures\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Scenario: One worker fails, master handles gracefully\n\n";

$testTask = "Calculate 25 × 17 and explain the multiplication algorithm";

$testResults = [
    'math_agent' => '425',  // Successful
    'writing_agent' => 'Error in writing_agent: Timeout' // Failed
];

echo "Task: {$testTask}\n\n";
echo "Results from workers:\n";
foreach ($testResults as $agent => $result) {
    $status = str_contains($result, 'Error') ? '❌' : '✅';
    echo "  {$status} {$agent}: " . substr($result, 0, 50) . "\n";
}

echo "\nMaster handling partial failure...\n";
echo str_repeat("-", 80) . "\n";

// In real implementation, master would have retry logic or use backup agents
echo "Strategy:\n";
echo "  1. Identify failed worker (writing_agent)\n";
echo "  2. Use successful result from math_agent\n";
echo "  3. Retry failed task or use backup agent\n";
echo "  4. Synthesize with available results\n\n";

echo "💡 Hierarchical systems need robust error handling!\n";
echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 5: Visualization of Hierarchy
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 5: System Architecture Visualization\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Hierarchical Agent System Architecture:\n\n";

echo "                  ┌─────────────────────┐\n";
echo "                  │   Master Agent      │\n";
echo "                  │   (Coordinator)     │\n";
echo "                  └──────────┬──────────┘\n";
echo "                             │\n";
echo "            ┌────────────────┼────────────────┐\n";
echo "            │                │                │\n";
echo "       ┌────▼─────┐    ┌────▼─────┐    ┌────▼─────┐\n";
echo "       │  Math    │    │ Research │    │ Writing  │\n";
echo "       │  Agent   │    │  Agent   │    │  Agent   │\n";
echo "       └──────────┘    └──────────┘    └──────────┘\n";
echo "       • Calculate     • Find info     • Compose\n";
echo "       • Statistics    • Validate      • Edit\n";
echo "       • Formulas      • Sources       • Format\n\n";

echo "Flow:\n";
echo "  1. User → Master: Complex task\n";
echo "  2. Master → Workers: Decomposed subtasks\n";
echo "  3. Workers → Master: Individual results\n";
echo "  4. Master → User: Synthesized answer\n\n";

echo "Benefits:\n";
echo "  ✓ Specialized expertise\n";
echo "  ✓ Parallel execution\n";
echo "  ✓ Clear responsibility\n";
echo "  ✓ Scalable architecture\n";
echo "  ✓ Maintainable code\n\n";

echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Summary
// ============================================================================

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                           Tutorial Summary                                 ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "✅ Hierarchical Agent Patterns Demonstrated:\n\n";

echo "1️⃣  Master-Worker Architecture\n";
echo "   • Master coordinates and delegates\n";
echo "   • Workers specialize in domains\n";
echo "   • Clear separation of concerns\n\n";

echo "2️⃣  Task Decomposition\n";
echo "   • Break complex into simple\n";
echo "   • Match tasks to specialists\n";
echo "   • Track dependencies\n\n";

echo "3️⃣  Specialized Agents\n";
echo "   • Math agent for calculations\n";
echo "   • Writing agent for composition\n";
echo "   • Research agent for information\n";
echo "   • Domain-specific expertise\n\n";

echo "4️⃣  Result Aggregation\n";
echo "   • Collect worker outputs\n";
echo "   • Synthesize coherent answer\n";
echo "   • Maintain context\n\n";

echo "5️⃣  System Management\n";
echo "   • Load distribution\n";
echo "   • Error handling\n";
echo "   • Performance monitoring\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🎯 When to Use Hierarchical Agents:\n\n";

echo "  ✓ Complex multi-domain tasks\n";
echo "  ✓ Need for specialization\n";
echo "  ✓ Parallel execution beneficial\n";
echo "  ✓ Clear task boundaries\n";
echo "  ✓ Scalability important\n\n";

echo "⚠️  When to Use Simpler Patterns:\n\n";

echo "  • Single-domain tasks\n";
echo "  • Simple workflows\n";
echo "  • Low coordination overhead needed\n";
echo "  • Resource constrained\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "💡 Key Insights:\n\n";

echo "  • Specialization improves quality\n";
echo "  • Master must coordinate effectively\n";
echo "  • Workers should have clear domains\n";
echo "  • Synthesis is critical for coherence\n";
echo "  • Error handling prevents cascading failures\n";
echo "  • Monitor load for optimal distribution\n\n";

echo "🚀 Hierarchies enable complex multi-domain tasks!\n\n";
echo "Next: Tutorial 12 - Multi-Agent Debate for decision making\n";
echo "→ tutorials/12-multi-agent-debate/\n\n";
