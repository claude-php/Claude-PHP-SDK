# Tutorial 11: Hierarchical Agents

**Time: 60 minutes** | **Difficulty: Advanced**

Hierarchical agent systems organize multiple specialized agents under a coordinator, enabling efficient task delegation, parallel execution, and domain-specific expertise. This pattern is essential for complex, multi-domain tasks.

## 🎯 Learning Objectives

By the end of this tutorial, you'll be able to:

- Build master-worker agent architectures
- Implement task delegation strategies
- Create specialized sub-agents for different domains
- Aggregate and synthesize results from multiple agents
- Handle inter-agent communication
- Optimize parallel vs sequential execution
- Design agent hierarchies for real-world problems

## 🏗️ What We're Building

A hierarchical system with:

1. **Master Agent (Coordinator)** - Analyzes tasks and delegates
2. **Specialized Workers** - Domain experts (math, research, writing, coding)
3. **Task Router** - Intelligent agent selection
4. **Result Synthesizer** - Combines worker outputs
5. **Error Handler** - Manages worker failures

## 📋 Prerequisites

Make sure you have:

- Completed [Tutorial 10: Reflection](../10-reflection/)
- Understanding of agent patterns from previous tutorials
- PHP 8.1+ installed
- Claude PHP SDK configured

## 🤔 What is Hierarchical Architecture?

Hierarchical systems organize agents in layers:

```
                 ┌─────────────────┐
                 │  Master Agent   │
                 │  (Coordinator)  │
                 └────────┬────────┘
                          │
         ┌────────────────┼────────────────┐
         │                │                │
    ┌────▼────┐      ┌────▼────┐     ┌────▼────┐
    │  Math   │      │Research │     │ Writing │
    │  Agent  │      │ Agent   │     │  Agent  │
    └─────────┘      └─────────┘     └─────────┘
```

### Why Hierarchical?

**Advantages:**
- ✅ **Specialization** - Each agent excels in its domain
- ✅ **Scalability** - Add agents without redesigning system
- ✅ **Parallel Execution** - Multiple agents work simultaneously
- ✅ **Clear Responsibility** - Each agent has defined role
- ✅ **Maintainability** - Isolated components

**Disadvantages:**
- ❌ **Complexity** - More components to manage
- ❌ **Coordination Overhead** - Master must orchestrate
- ❌ **Potential Bottleneck** - Master can be limiting factor

## 🔑 Key Concepts

### 1. Task Decomposition

Master agent breaks complex tasks into subtasks:

```php
$masterPrompt = "Complex task: {$task}\n\n" .
                "Available specialized agents:\n" .
                "- math_agent: Calculations, statistics, formulas\n" .
                "- research_agent: Information lookup, fact-checking\n" .
                "- writing_agent: Composition, editing, formatting\n" .
                "- code_agent: Programming, algorithms, debugging\n\n" .
                "Decompose into subtasks. For each subtask specify:\n" .
                "1. Which agent should handle it\n" .
                "2. What the subtask is\n" .
                "3. Any dependencies on other subtasks\n" .
                "4. Expected output";
```

### 2. Agent Specialization

Each worker has specific expertise:

```php
class MathAgent {
    private $system = "You are a mathematics expert. " .
                     "Solve calculations precisely. " .
                     "Provide step-by-step solutions.";
    private $tools = [$calculatorTool, $statisticsTool];
}

class ResearchAgent {
    private $system = "You are a research specialist. " .
                     "Find accurate information. " .
                     "Cite sources.";
    private $tools = [$searchTool, $webFetchTool];
}

class WritingAgent {
    private $system = "You are a professional writer. " .
                     "Create clear, engaging content. " .
                     "Use proper structure.";
    private $tools = []; // Pure language work
}
```

### 3. Delegation Strategy

Route tasks to appropriate agents:

```php
function selectAgent($subtask, $agents) {
    // Analyze subtask requirements
    $keywords = extractKeywords($subtask);
    
    // Match to agent specialties
    foreach ($agents as $agent) {
        $matchScore = calculateMatch($keywords, $agent->specialty);
        if ($matchScore > 0.7) {
            return $agent;
        }
    }
    
    return $defaultAgent;
}
```

### 4. Result Aggregation

Combine outputs from multiple agents:

```php
function synthesizeResults($task, $results) {
    $synthesisPrompt = "Original task: {$task}\n\n";
    
    foreach ($results as $agent => $output) {
        $synthesisPrompt .= "{$agent} result:\n{$output}\n\n";
    }
    
    $synthesisPrompt .= "Synthesize these results into a coherent final answer.";
    
    return synthesize($synthesisPrompt);
}
```

## 💡 Implementation Patterns

### Basic Hierarchical System

```php
class HierarchicalSystem {
    private $master;
    private $workers = [];
    
    public function __construct($client) {
        $this->master = new MasterAgent($client);
        $this->workers['math'] = new MathAgent($client);
        $this->workers['research'] = new ResearchAgent($client);
        $this->workers['writing'] = new WritingAgent($client);
    }
    
    public function execute($task) {
        // 1. Decompose
        $subtasks = $this->master->decompose($task, $this->workers);
        
        // 2. Delegate and execute
        $results = [];
        foreach ($subtasks as $subtask) {
            $agent = $this->workers[$subtask->agent];
            $results[$subtask->agent] = $agent->execute($subtask->task);
        }
        
        // 3. Synthesize
        return $this->master->synthesize($task, $results);
    }
}
```

### Worker Agent Implementation

```php
class WorkerAgent {
    private $client;
    private $system;
    private $tools;
    
    public function __construct($client, $system, $tools = []) {
        $this->client = $client;
        $this->system = $system;
        $this->tools = $tools;
    }
    
    public function execute($task) {
        $messages = [['role' => 'user', 'content' => $task]];
        $maxIterations = 5;
        
        for ($i = 0; $i < $maxIterations; $i++) {
            $response = $this->client->messages()->create([
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 2048,
                'system' => $this->system,
                'messages' => $messages,
                'tools' => $this->tools
            ]);
            
            if ($response->stop_reason === 'end_turn') {
                return extractTextContent($response);
            }
            
            // Handle tool use
            $messages[] = ['role' => 'assistant', 'content' => $response->content];
            
            if ($response->stop_reason === 'tool_use') {
                $toolResults = $this->executeTools($response->content);
                $messages[] = ['role' => 'user', 'content' => $toolResults];
            }
        }
        
        return "Max iterations reached";
    }
    
    private function executeTools($content) {
        $results = [];
        foreach ($content as $block) {
            if ($block['type'] === 'tool_use') {
                $result = $this->callTool($block['name'], $block['input']);
                $results[] = [
                    'type' => 'tool_result',
                    'tool_use_id' => $block['id'],
                    'content' => $result
                ];
            }
        }
        return $results;
    }
}
```

## 🎯 Example Use Cases

### 1. Research Paper Writing

```
Master: Decompose task
├─ Research Agent: Find papers on topic
├─ Math Agent: Verify statistics in papers
├─ Writing Agent: Create outline
├─ Research Agent: Fill in each section
└─ Writing Agent: Edit and format

Master: Synthesize final paper
```

### 2. Business Analysis

```
Master: Analyze business problem
├─ Research Agent: Market research
├─ Math Agent: Financial calculations
├─ Code Agent: Data analysis script
└─ Writing Agent: Executive summary

Master: Create comprehensive report
```

### 3. Software Documentation

```
Master: Document codebase
├─ Code Agent: Analyze code structure
├─ Code Agent: Extract API signatures
├─ Writing Agent: Write descriptions
└─ Writing Agent: Create examples

Master: Compile documentation
```

## 📊 Advanced Patterns

### Parallel Execution

Execute independent subtasks simultaneously:

```php
function executeParallel($subtasks, $workers) {
    // Identify independent tasks
    $batches = groupByDependencies($subtasks);
    $results = [];
    
    foreach ($batches as $batch) {
        // These can run in parallel
        $batchResults = [];
        
        foreach ($batch as $subtask) {
            $agent = $workers[$subtask->agent];
            // In real implementation, use async execution
            $batchResults[] = $agent->execute($subtask->task);
        }
        
        $results = array_merge($results, $batchResults);
    }
    
    return $results;
}
```

### Dynamic Agent Selection

Choose agents based on runtime analysis:

```php
function selectBestAgent($subtask, $availableAgents) {
    // Analyze subtask
    $analysis = analyzeTa sk($subtask);
    
    // Score each agent
    $scores = [];
    foreach ($availableAgents as $agent) {
        $scores[$agent->name] = [
            'capability' => $agent->canHandle($analysis),
            'load' => $agent->currentLoad(),
            'cost' => $agent->estimatedCost($subtask)
        ];
    }
    
    // Select based on weighted score
    return selectHighestScore($scores);
}
```

### Hierarchical Error Handling

```php
function executeWithFallback($subtask, $primaryAgent, $backupAgent) {
    try {
        return $primaryAgent->execute($subtask);
    } catch (Exception $e) {
        logError("Primary agent failed: {$e->getMessage()}");
        
        try {
            return $backupAgent->execute($subtask);
        } catch (Exception $e2) {
            return handleFailure($subtask, $e2);
        }
    }
}
```

### Agent Collaboration

Agents can consult each other:

```php
class CollaborativeAgent extends WorkerAgent {
    private $peers = [];
    
    public function setPeers($agents) {
        $this->peers = $agents;
    }
    
    public function execute($task) {
        $result = parent::execute($task);
        
        // Ask peer for review if uncertain
        if ($this->needsPeerReview($result)) {
            $peer = $this->selectReviewer();
            $review = $peer->review($result);
            $result = $this->incorporate($result, $review);
        }
        
        return $result;
    }
}
```

## ⚙️ Configuration and Tuning

### Agent Configuration

```php
$agentConfig = [
    'math_agent' => [
        'max_iterations' => 5,
        'timeout' => 30,
        'model' => 'claude-sonnet-4-5',
        'temperature' => 0.0, // Deterministic for math
        'tools' => ['calculate', 'plot_graph']
    ],
    'writing_agent' => [
        'max_iterations' => 3,
        'timeout' => 60,
        'model' => 'claude-sonnet-4-5',
        'temperature' => 0.7, // More creative
        'tools' => []
    ]
];
```

### Load Balancing

```php
class LoadBalancer {
    private $agents = [];
    
    public function distribute($tasks) {
        // Sort agents by current load
        usort($this->agents, fn($a, $b) => 
            $a->getCurrentLoad() <=> $b->getCurrentLoad()
        );
        
        // Assign to least loaded agent
        foreach ($tasks as $task) {
            $agent = $this->agents[0]; // Least loaded
            $agent->assignTask($task);
            $this->rebalance();
        }
    }
}
```

## 🎨 Design Patterns

### 1. Chain of Command

Master → Supervisor → Worker hierarchy:

```
Master Agent
  ├─ Research Supervisor
  │    ├─ Web Search Worker
  │    └─ Document Analysis Worker
  └─ Analysis Supervisor
       ├─ Data Worker
       └─ Visualization Worker
```

### 2. Specialist Pool

Master with interchangeable specialists:

```php
$specialistPool = [
    'math' => [$mathAgent1, $mathAgent2],
    'research' => [$researchAgent1, $researchAgent2, $researchAgent3],
    'writing' => [$writingAgent1]
];

// Distribute load across specialists
$agent = selectFromPool($specialistPool['math']);
```

### 3. Pipeline Architecture

Sequential processing through agents:

```
Input → Agent 1 → Agent 2 → Agent 3 → Output
(Research)  (Analysis)  (Writing)  (Review)
```

## 📈 Monitoring and Metrics

Track system performance:

```php
$metrics = [
    'total_tasks' => 150,
    'tasks_per_agent' => [
        'math' => 45,
        'research' => 78,
        'writing' => 27
    ],
    'avg_execution_time' => [
        'math' => 2.3,      // seconds
        'research' => 5.7,
        'writing' => 8.2
    ],
    'success_rate' => [
        'math' => 0.98,
        'research' => 0.94,
        'writing' => 1.00
    ],
    'total_cost' => 2.45    // dollars
];
```

## ✅ Checkpoint

Before moving on, make sure you understand:

- [ ] Master-worker architecture pattern
- [ ] Task decomposition strategies
- [ ] Agent specialization benefits
- [ ] Delegation and routing logic
- [ ] Result aggregation techniques
- [ ] Parallel vs sequential execution
- [ ] Error handling in hierarchies
- [ ] When to use hierarchical systems

## 🚀 Next Steps

You've mastered Hierarchical Agents! But what if agents need to debate to reach better decisions?

**[Tutorial 12: Multi-Agent Debate →](../12-multi-agent-debate/)**

Learn how agents can challenge each other's ideas for better outcomes!

## 💻 Try It Yourself

Run the complete working example:

```bash
php tutorials/11-hierarchical-agents/hierarchical_agent.php
```

The script demonstrates:

- ✅ Master-worker architecture
- ✅ Task decomposition strategies
- ✅ Specialized agent delegation
- ✅ Result synthesis and aggregation
- ✅ Load distribution across workers
- ✅ Failure handling and recovery

## 💡 Key Takeaways

1. **Specialization enables excellence** - Domain experts > generalists
2. **Master coordinates effectively** - Clear delegation is key
3. **Parallel execution saves time** - Independent tasks simultaneously
4. **Clear interfaces matter** - Well-defined agent responsibilities
5. **Hierarchies scale well** - Add agents without redesign
6. **Overhead is real** - More agents = more coordination
7. **Choose wisely** - Not all problems need hierarchies
8. **Monitor performance** - Track which agents are bottlenecks

## 📚 Further Reading

### Research Papers

- **[AutoGPT: Multi-Agent Architecture](https://github.com/Significant-Gravitas/AutoGPT)** - Open source implementation
- **[MetaGPT: Multi-Agent Framework](https://arxiv.org/abs/2308.00352)** - Software company simulation
- **[Communicative Agents](https://arxiv.org/abs/2308.03688)** - Inter-agent communication

### Related Tutorials

- [Tutorial 6: Agentic Framework](../06-agentic-framework/) - Foundation concepts
- [Tutorial 9: Plan-and-Execute](../09-plan-and-execute/) - Task decomposition
- [Tutorial 12: Multi-Agent Debate](../12-multi-agent-debate/) - Agent collaboration

## 🎓 Practice Exercises

Try building hierarchical systems for:

1. **E-commerce Analysis** - Research agent (products) + Math agent (pricing) + Writing agent (recommendations)
2. **Code Review System** - Code agent (analysis) + Security agent (vulnerabilities) + Style agent (best practices)
3. **Content Creation** - Research agent (facts) + Writing agent (draft) + Editing agent (polish)
4. **Financial Planning** - Data agent (gather) + Math agent (project) + Writing agent (explain)

## 🔧 Troubleshooting

**Issue**: Master agent poor at task decomposition
- **Solution**: Provide examples of good decomposition, be specific about agent capabilities

**Issue**: Agents duplicating work
- **Solution**: Master should track what's assigned, avoid overlapping subtasks

**Issue**: Poor result synthesis
- **Solution**: Give master access to original task context, clear synthesis instructions

**Issue**: One agent becomes bottleneck
- **Solution**: Add redundant agents for high-demand skills, implement load balancing

**Issue**: High coordination overhead
- **Solution**: Reduce master <-> worker communication, batch similar tasks
