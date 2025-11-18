# Tutorial 6: Full Agentic Framework

**Time: 90 minutes** | **Difficulty: Advanced**

Welcome to the final tutorial! We'll bring together everything you've learned to build a complete agentic framework with task decomposition, parallel execution, state management, and orchestration.

## 🎯 Learning Objectives

By the end of this tutorial, you'll be able to:

- Design a complete agent architecture
- Implement task decomposition strategies
- Orchestrate multiple sub-agents
- Manage complex state across workflows
- Handle parallel tool execution
- Build reusable agent components
- Create production-grade agentic systems

## 🏗️ Framework Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     ORCHESTRATOR                            │
│  • Receives high-level goals                                │
│  • Decomposes into subtasks                                 │
│  • Coordinates execution                                    │
│  • Aggregates results                                       │
└────────────────┬────────────────────────────────────────────┘
                 │
      ┌──────────┴──────────┐
      │                     │
      ▼                     ▼
┌─────────────┐       ┌─────────────┐
│  SUB-AGENT  │       │  SUB-AGENT  │
│     #1      │       │     #2      │
│             │       │             │
│  • Focused  │       │  • Focused  │
│    task     │       │    task     │
│  • Tools    │       │  • Tools    │
│  • Memory   │       │  • Memory   │
└──────┬──────┘       └──────┬──────┘
       │                     │
       └──────────┬──────────┘
                  │
                  ▼
          ┌───────────────┐
          │ STATE MANAGER │
          │  • History    │
          │  • Memory     │
          │  • Context    │
          └───────────────┘
```

## 🧩 Core Components

### 1. Task Decomposer

Breaks complex tasks into manageable subtasks:

```php
class TaskDecomposer {
    public function decompose($task) {
        // Use Claude to break down task
        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'messages' => [[
                'role' => 'user',
                'content' => "Break down this task into 3-5 subtasks: {$task}"
            ]],
            'thinking' => ['type' => 'enabled', 'budget_tokens' => 5000]
        ]);

        return $this->parseSubtasks($response);
    }
}
```

### 2. Agent Pool

Manages multiple specialized agents:

```php
class AgentPool {
    private $agents = [];

    public function registerAgent($name, $agent) {
        $this->agents[$name] = $agent;
    }

    public function getAgent($name) {
        return $this->agents[$name] ?? null;
    }

    public function executeWithAgent($agentName, $task) {
        $agent = $this->getAgent($agentName);
        if (!$agent) {
            throw new Exception("Agent not found: {$agentName}");
        }

        return $agent->execute($task);
    }
}
```

### 3. State Manager

Maintains context across the workflow:

```php
class StateManager {
    private $state = [];
    private $history = [];

    public function setState($key, $value) {
        $this->state[$key] = $value;
        $this->history[] = [
            'action' => 'set',
            'key' => $key,
            'timestamp' => time()
        ];
    }

    public function getState($key) {
        return $this->state[$key] ?? null;
    }

    public function getFullState() {
        return $this->state;
    }

    public function getHistory() {
        return $this->history;
    }
}
```

### 4. Orchestrator

Coordinates the entire workflow:

```php
class Orchestrator {
    private $decomposer;
    private $agentPool;
    private $stateManager;

    public function execute($goal) {
        // 1. Decompose goal into subtasks
        $subtasks = $this->decomposer->decompose($goal);

        // 2. Execute each subtask
        $results = [];
        foreach ($subtasks as $subtask) {
            $agent = $this->selectAgent($subtask);
            $result = $this->agentPool->executeWithAgent($agent, $subtask);
            $results[] = $result;

            // Store in state
            $this->stateManager->setState(
                "subtask_{$subtask['id']}",
                $result
            );
        }

        // 3. Synthesize results
        return $this->synthesize($goal, $results);
    }
}
```

## 🎯 Complete Framework Implementation

### Framework Class

```php
class AgenticFramework {
    private $client;
    private $tools = [];
    private $agents = [];
    private $state;

    public function __construct($client) {
        $this->client = $client;
        $this->state = new StateManager();
    }

    public function registerTool($tool) {
        $this->tools[] = $tool;
    }

    public function registerAgent($name, $config) {
        $this->agents[$name] = new Agent(
            $this->client,
            $config['tools'] ?? $this->tools,
            $config['system'] ?? '',
            $this->state
        );
    }

    public function execute($goal, $options = []) {
        // Decompose
        $subtasks = $this->decompose($goal);

        // Execute
        $results = [];
        foreach ($subtasks as $subtask) {
            $agentName = $this->matchAgent($subtask);
            $result = $this->agents[$agentName]->run($subtask);
            $results[] = $result;
        }

        // Synthesize
        return $this->synthesize($goal, $results);
    }

    private function decompose($goal) {
        // Implementation...
    }

    private function matchAgent($subtask) {
        // Select best agent for subtask
        return 'default';
    }

    private function synthesize($goal, $results) {
        // Combine results into final answer
    }
}
```

## 🔄 Workflow Patterns

### Pattern 1: Sequential Execution

```php
// Subtasks executed in order
$results = [];
foreach ($subtasks as $subtask) {
    $result = $agent->execute($subtask);
    $results[] = $result;

    // Next subtask can use previous results
    $context = array_merge($context, $result);
}
```

### Pattern 2: Parallel Execution

```php
// Execute independent subtasks simultaneously
$promises = [];
foreach ($subtasks as $subtask) {
    if ($this->isIndependent($subtask)) {
        $promises[] = $this->executeAsync($subtask);
    }
}

$results = $this->waitAll($promises);
```

### Pattern 3: Conditional Execution

```php
// Execute based on previous results
foreach ($subtasks as $subtask) {
    if ($this->shouldExecute($subtask, $previousResults)) {
        $result = $agent->execute($subtask);
        $results[] = $result;
    }
}
```

### Pattern 4: Recursive Decomposition

```php
function executeRecursive($task, $depth = 0) {
    if ($depth > $maxDepth || $this->isAtomic($task)) {
        return $agent->execute($task);
    }

    $subtasks = $this->decompose($task);
    $results = [];

    foreach ($subtasks as $subtask) {
        $results[] = $this->executeRecursive($subtask, $depth + 1);
    }

    return $this->combine($results);
}
```

## 💡 Advanced Techniques

### 1. Dynamic Tool Selection

```php
// Provide different tools based on subtask
$tools = $this->selectToolsFor($subtask);
$agent->setTools($tools);
```

### 2. Checkpointing

```php
// Save state at key points
$this->state->checkpoint($subtaskId);

// Resume from checkpoint if error
if ($error) {
    $this->state->restore($checkpointId);
}
```

### 3. Result Caching

```php
// Cache expensive operations
$cacheKey = $this->getCacheKey($subtask);
if ($cached = $this->cache->get($cacheKey)) {
    return $cached;
}

$result = $agent->execute($subtask);
$this->cache->set($cacheKey, $result);
```

### 4. Load Balancing

```php
// Distribute work across multiple agent instances
$agent = $this->pool->getLeastBusyAgent();
$result = $agent->execute($subtask);
```

## 📊 Monitoring & Observability

### Metrics to Track

```php
class Metrics {
    public function track($event, $data) {
        $this->metrics[] = [
            'event' => $event,
            'data' => $data,
            'timestamp' => microtime(true)
        ];
    }

    public function getMetrics() {
        return [
            'total_tasks' => $this->countTasks(),
            'avg_duration' => $this->avgDuration(),
            'success_rate' => $this->successRate(),
            'tool_usage' => $this->toolUsage(),
            'token_usage' => $this->tokenUsage()
        ];
    }
}
```

## 🎯 Example Use Cases

### 1. Research Assistant

```php
$framework = new AgenticFramework($client);

// Register specialized agents
$framework->registerAgent('researcher', [
    'tools' => [$searchTool, $webFetchTool],
    'system' => 'You are a research specialist'
]);

$framework->registerAgent('analyzer', [
    'tools' => [$calculatorTool, $statisticsTool],
    'system' => 'You analyze data and find insights'
]);

$framework->registerAgent('writer', [
    'tools' => [],
    'system' => 'You synthesize information into clear reports'
]);

// Execute complex research task
$result = $framework->execute(
    "Research recent AI developments and create a summary report"
);
```

### 2. Data Pipeline

```php
// Extract → Transform → Load pattern
$framework->registerAgent('extractor', [...]);
$framework->registerAgent('transformer', [...]);
$framework->registerAgent('loader', [...]);

$result = $framework->execute("Process customer data from API");
```

### 3. Multi-Step Workflow

```php
$workflow = [
    'gather_requirements' => ['agent' => 'analyst'],
    'design_solution' => ['agent' => 'architect'],
    'implement' => ['agent' => 'developer'],
    'test' => ['agent' => 'tester'],
    'deploy' => ['agent' => 'devops']
];

foreach ($workflow as $step => $config) {
    $result = $framework->agents[$config['agent']]->run($step);
    $framework->state->setState($step, $result);
}
```

## ✅ Checkpoint

Congratulations! You've completed the entire tutorial series. You should now understand:

- [ ] Complete agent architecture design
- [ ] Task decomposition strategies
- [ ] Agent orchestration
- [ ] State management across workflows
- [ ] Production deployment considerations
- [ ] Advanced agentic patterns

## 🎓 What You've Learned

Across all 7 tutorials, you've mastered:

1. **Foundations** - What agents are and how they work
2. **Basics** - Building your first agent with tools
3. **ReAct Loop** - Iterative reasoning and action
4. **Multi-Tool** - Agents with diverse capabilities
5. **Production** - Robust, error-handling agents
6. **Advanced** - Planning, reflection, and thinking
7. **Framework** - Complete orchestration systems

## 🚀 Next Steps

### Continue Learning

- Read the [SDK Examples](../../examples/)
- Study [Claude Documentation](https://docs.anthropic.com/)
- Explore [Research Papers](#further-reading)
- Join [Community Discussions](https://github.com/claude-php/claude-php-sdk/discussions)

### Build Your Own

Ideas for practice projects:

- Personal assistant bot
- Data analysis pipeline
- Content creation system
- Customer support agent
- Research automation tool

### Contribute

Help improve this SDK:

- Report issues
- Submit PRs
- Share your agents
- Write tutorials

## 💻 Try It Yourself

Run the complete framework example:

```bash
php tutorials/06-agentic-framework/agentic_framework.php
```

## 📚 Further Reading

- [ReAct: Synergizing Reasoning and Acting](https://arxiv.org/abs/2210.03629)
- [Chain-of-Thought Prompting](https://arxiv.org/abs/2201.11903)
- [Reflexion: Language Agents with Verbal Reinforcement Learning](https://arxiv.org/abs/2303.11366)
- [Claude Documentation](https://docs.anthropic.com/)

---

**Thank you for completing this tutorial series!** 🎉

You're now equipped to build sophisticated AI agents. Go build amazing things!

