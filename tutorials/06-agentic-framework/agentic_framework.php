#!/usr/bin/env php
<?php
/**
 * Tutorial 6: Full Agentic Framework - Working Example
 * 
 * Demonstrates a complete agentic framework with task decomposition,
 * orchestration, and state management.
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../examples/helpers.php';
require_once __DIR__ . '/../helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║            Tutorial 6: Complete Agentic Framework                          ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Framework Components
// ============================================================================

class StateManager {
    private $state = [];
    private $history = [];
    
    public function setState($key, $value) {
        $this->state[$key] = $value;
        $this->history[] = [
            'action' => 'set',
            'key' => $key,
            'value' => $value,
            'timestamp' => time()
        ];
    }
    
    public function getState($key) {
        return $this->state[$key] ?? null;
    }
    
    public function getAllState() {
        return $this->state;
    }
}

class Agent {
    private $client;
    private $tools;
    private $system;
    private $state;
    
    public function __construct($client, $tools, $system, $state) {
        $this->client = $client;
        $this->tools = $tools;
        $this->system = $system;
        $this->state = $state;
    }
    
    public function run($task) {
        $messages = [['role' => 'user', 'content' => $task]];
        $iteration = 0;
        $maxIterations = 10;
        
        while ($iteration < $maxIterations) {
            $iteration++;
            
            try {
                $response = $this->client->messages()->create([
                    'model' => 'claude-sonnet-4-5',
                    'max_tokens' => 4096,
                    'system' => $this->system,
                    'messages' => $messages,
                    'tools' => $this->tools
                ]);
            } catch (Exception $e) {
                return ['error' => $e->getMessage()];
            }
            
            $messages[] = ['role' => 'assistant', 'content' => $response->content];
            
            if ($response->stop_reason === 'end_turn') {
                return [
                    'success' => true,
                    'result' => extractTextContent($response),
                    'iterations' => $iteration
                ];
            }
            
            if ($response->stop_reason === 'tool_use') {
                $toolResults = [];
                foreach ($response->content as $block) {
                    if ($block['type'] === 'tool_use') {
                        $result = $this->executeTool($block['name'], $block['input']);
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
        
        return ['error' => 'Max iterations reached'];
    }
    
    private function executeTool($name, $input) {
        $result = match($name) {
            'calculate' => eval("return {$input['expression']};"),
            'search' => "Results for: {$input['query']}",
            default => "Unknown tool"
        };
        // Tool results must be strings
        return is_string($result) ? $result : (string)$result;
    }
}

class AgenticFramework {
    private $client;
    private $agents = [];
    private $state;
    
    public function __construct($client) {
        $this->client = $client;
        $this->state = new StateManager();
    }
    
    public function registerAgent($name, $tools, $system = '') {
        $this->agents[$name] = new Agent($this->client, $tools, $system, $this->state);
    }
    
    public function getState() {
        return $this->state;
    }
    
    public function execute($goal) {
        echo "🎯 Goal: {$goal}\n\n";
        
        // Step 1: Decompose
        echo "Step 1: Task Decomposition\n";
        echo str_repeat("-", 80) . "\n";
        $subtasks = $this->decompose($goal);
        
        foreach ($subtasks as $i => $subtask) {
            echo "  " . ($i + 1) . ". {$subtask['task']}\n";
        }
        echo "\n";
        
        // Step 2: Execute each subtask
        echo "Step 2: Execution\n";
        echo str_repeat("-", 80) . "\n";
        $results = [];
        
        foreach ($subtasks as $i => $subtask) {
            echo "\nSubtask " . ($i + 1) . ": {$subtask['task']}\n";
            
            $agentName = $subtask['agent'] ?? 'default';
            if (!isset($this->agents[$agentName])) {
                $agentName = 'default';
            }
            
            $result = $this->agents[$agentName]->run($subtask['task']);
            
            if (isset($result['success'])) {
                echo "  ✓ Complete: {$result['result']}\n";
                $results[] = $result['result'];
                $this->state->setState("subtask_{$i}", $result['result']);
            } else {
                echo "  ✗ Error: {$result['error']}\n";
            }
        }
        
        // Step 3: Synthesize
        echo "\nStep 3: Synthesis\n";
        echo str_repeat("-", 80) . "\n";
        $final = $this->synthesize($goal, $results);
        
        return $final;
    }
    
    private function decompose($goal) {
        try {
            $response = $this->client->messages()->create([
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 2048,
                'messages' => [[
                    'role' => 'user',
                    'content' => "Break this goal into 2-4 concrete subtasks:\n\n{$goal}\n\n" .
                                "Format each as: 1. [subtask description]"
                ]],
                'thinking' => ['type' => 'enabled', 'budget_tokens' => 3000]
            ]);
            
            $text = extractTextContent($response);
            return $this->parseSubtasks($text);
        } catch (Exception $e) {
            return [['task' => $goal, 'agent' => 'default']];
        }
    }
    
    private function parseSubtasks($text) {
        $subtasks = [];
        $lines = explode("\n", $text);
        
        foreach ($lines as $line) {
            if (preg_match('/^\d+\.\s+(.+)$/', trim($line), $matches)) {
                $subtasks[] = [
                    'task' => $matches[1],
                    'agent' => 'default'
                ];
            }
        }
        
        return !empty($subtasks) ? $subtasks : [['task' => $text, 'agent' => 'default']];
    }
    
    private function synthesize($goal, $results) {
        if (empty($results)) {
            return "No results to synthesize";
        }
        
        try {
            $resultsText = implode("\n\n", array_map(fn($r, $i) => ($i + 1) . ". {$r}", $results, array_keys($results)));
            
            $response = $this->client->messages()->create([
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 2048,
                'messages' => [[
                    'role' => 'user',
                    'content' => "Original goal: {$goal}\n\n" .
                                "Subtask results:\n{$resultsText}\n\n" .
                                "Provide a final synthesized answer to the original goal."
                ]]
            ]);
            
            return extractTextContent($response);
        } catch (Exception $e) {
            return "Synthesis error: " . $e->getMessage();
        }
    }
}

// ============================================================================
// Example 1: Basic Framework Usage
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 1: Research and Analysis Task\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$framework = new AgenticFramework($client);

// Register specialized agents
$calcTool = createTool('calculate', 'Math calculations', 
    ['expression' => ['type' => 'string', 'description' => 'Math expression']], 
    ['expression']);

$searchTool = createTool('search', 'Search information',
    ['query' => ['type' => 'string', 'description' => 'Search query']],
    ['query']);

$framework->registerAgent('default', [$calcTool, $searchTool], 
    'You are a helpful assistant that can calculate and search');

$framework->registerAgent('calculator', [$calcTool],
    'You specialize in mathematical calculations');

$framework->registerAgent('researcher', [$searchTool],
    'You specialize in finding and summarizing information');

// Execute complex goal
$goal = "Calculate 25% of 480, then explain what that percentage means in practical terms";

$result = $framework->execute($goal);

echo "\n📝 Final Result:\n";
echo str_repeat("=", 80) . "\n";
echo $result . "\n";
echo str_repeat("=", 80) . "\n\n";

// ============================================================================
// Example 2: State Management
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 2: Viewing Framework State\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "State after execution:\n";
$state = $framework->getState()->getAllState();
foreach ($state as $key => $value) {
    echo "  {$key}: " . (strlen($value) > 60 ? substr($value, 0, 60) . '...' : $value) . "\n";
}

echo "\n" . str_repeat("═", 80) . "\n\n";

// ============================================================================
// Summary
// ============================================================================

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                   🎉 Tutorial Series Complete! 🎉                         ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "✅ What You've Built:\n\n";

echo "📚 Tutorial 0: Learned agentic AI concepts\n";
echo "🤖 Tutorial 1: Built your first agent with tools\n";
echo "🔄 Tutorial 2: Implemented ReAct loop\n";
echo "🛠️  Tutorial 3: Created multi-tool agents\n";
echo "🏭 Tutorial 4: Made production-ready systems\n";
echo "🧠 Tutorial 5: Added planning and reflection\n";
echo "🏗️  Tutorial 6: Completed full framework\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🎯 Key Skills Mastered:\n\n";
echo "  • Tool definition and execution\n";
echo "  • ReAct loop implementation\n";
echo "  • Error handling and retries\n";
echo "  • Extended thinking\n";
echo "  • Task decomposition\n";
echo "  • Agent orchestration\n";
echo "  • State management\n";
echo "  • Production patterns\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🚀 You're Ready To:\n\n";
echo "  ✓ Build sophisticated AI agents\n";
echo "  ✓ Deploy production systems\n";
echo "  ✓ Design agent architectures\n";
echo "  ✓ Solve complex problems with AI\n";
echo "  ✓ Create your own frameworks\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "💡 Next Steps:\n\n";
echo "  1. Review SDK examples: examples/\n";
echo "  2. Build your own agent\n";
echo "  3. Explore Claude documentation\n";
echo "  4. Join the community\n";
echo "  5. Contribute back!\n\n";

echo "Thank you for completing this tutorial series! 🙏\n";
echo "Go build amazing things with AI agents! 🤖✨\n\n";

