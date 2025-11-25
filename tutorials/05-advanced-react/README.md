# Tutorial 5: Advanced ReAct

**Time: 60 minutes** | **Difficulty: Advanced**

You've built production-ready agents. Now let's make them smarter with **planning**, **reflection**, and **extended thinking**. These advanced patterns enable agents to solve complex problems through deliberate reasoning.

## 🎯 Learning Objectives

By the end of this tutorial, you'll be able to:

- Implement the Plan-Execute-Reflect-Adjust pattern
- Use extended thinking for complex reasoning
- Enable agent self-correction
- Decompose complex tasks into subtasks
- Implement reflection and adaptation
- Balance thinking depth with costs

## 🧠 What is Advanced ReAct?

Standard ReAct: `Reason → Act → Observe → Repeat`

Advanced ReAct adds:

- **Planning**: Think ahead before acting
- **Reflection**: Analyze what worked and what didn't
- **Extended Thinking**: Deep reasoning for complex problems
- **Self-Correction**: Detect and fix mistakes

## 🏗️ The Advanced Pattern

```
┌────────────────────────────────────┐
│  1. PLAN                           │
│     "Break down the task"          │
│     "What steps are needed?"       │
│     "What could go wrong?"         │
└────────────┬───────────────────────┘
             ↓
┌────────────────────────────────────┐
│  2. EXECUTE (Standard ReAct)       │
│     Reason → Act → Observe         │
└────────────┬───────────────────────┘
             ↓
┌────────────────────────────────────┐
│  3. REFLECT                        │
│     "Did it work as expected?"     │
│     "What went well?"              │
│     "What needs improvement?"      │
└────────────┬───────────────────────┘
             ↓
┌────────────────────────────────────┐
│  4. ADJUST                         │
│     "Change approach if needed"    │
│     "Try alternative strategy"     │
│     "Continue or complete"         │
└────────────┬───────────────────────┘
             │
      ┌──────┴──────┐
      │   Complete? │
      └──────┬──────┘
             │
       No────┴────Yes
       │           │
       └─→ PLAN    └─→ [Done]
```

## 💭 Extended Thinking

Extended thinking gives Claude more "thinking tokens" for complex reasoning.

### Configuration

```php
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 4096,
    'messages' => $messages,
    'tools' => $tools,
    'thinking' => [
        'type' => 'enabled',
        'budget_tokens' => 10000  // Up to 32K for Opus
    ]
]);
```

### When to Use

- **Complex Analysis**: Multi-step logical reasoning
- **Planning**: Breaking down complex tasks
- **Problem Solving**: Finding non-obvious solutions
- **Debugging**: Analyzing why something failed

### Cost Considerations

Thinking tokens are **priced differently**:

- Sonnet 4.5: Input rate for both cache hit/miss
- Opus: Input rate
- Budget wisely (1K-32K tokens)

## 📋 Planning Pattern

### System Prompt for Planning

```php
$planningSystem = "You are a meticulous planner. Before taking action:\n" .
                  "1. Break down the task into clear steps\n" .
                  "2. Identify what information is needed\n" .
                  "3. Anticipate potential issues\n" .
                  "4. Propose a strategy\n\n" .
                  "Only after planning, execute the plan step by step.";
```

### Implementation

```php
// Phase 1: Planning
$messages = [
    ['role' => 'user', 'content' => "Task: {$task}\n\nFirst, create a plan."]
];

$planResponse = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 2048,
    'system' => $planningSystem,
    'messages' => $messages,
    'thinking' => ['type' => 'enabled', 'budget_tokens' => 5000]
]);

// Extract the plan
$plan = extractTextContent($planResponse);
echo "📋 Plan:\n{$plan}\n\n";

// Phase 2: Execute with tools
$messages[] = ['role' => 'assistant', 'content' => $planResponse->content];
$messages[] = ['role' => 'user', 'content' => 'Now execute the plan.'];

// Continue with standard ReAct loop...
```

## 🔍 Reflection Pattern

### System Prompt for Reflection

```php
$reflectionSystem = "After completing actions, reflect on:\n" .
                    "1. What worked well\n" .
                    "2. What didn't work as expected\n" .
                    "3. What could be improved\n" .
                    "4. Whether the task is truly complete\n\n" .
                    "If issues found, propose corrections.";
```

### Implementation

```php
// After execution
$messages[] = [
    'role' => 'user',
    'content' => 'Reflect on what you just did. Did it achieve the goal?'
];

$reflectionResponse = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 2048,
    'system' => $reflectionSystem,
    'messages' => $messages,
    'thinking' => ['type' => 'enabled', 'budget_tokens' => 3000]
]);
```

## 🔄 Self-Correction Pattern

### Detecting Mistakes

```php
// Check if reflection reveals issues
$reflection = extractTextContent($reflectionResponse);

if (containsWords($reflection, ['issue', 'problem', 'incorrect', 'wrong'])) {
    echo "⚠️  Agent detected issues. Attempting correction...\n";

    $messages[] = ['role' => 'assistant', 'content' => $reflectionResponse->content];
    $messages[] = [
        'role' => 'user',
        'content' => 'Please correct the identified issues.'
    ];

    // Continue loop for correction...
}
```

## 🎯 Complete Advanced ReAct Implementation

```php
function advancedReActAgent($client, $task, $tools) {
    $system = "You are a thoughtful agent that plans before acting " .
              "and reflects after executing.";

    // Phase 1: Planning
    echo "Phase 1: Planning\n";
    $messages = [
        ['role' => 'user', 'content' => "Task: {$task}\n\nCreate a detailed plan."]
    ];

    $planResponse = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 2048,
        'system' => $system,
        'messages' => $messages,
        'thinking' => ['type' => 'enabled', 'budget_tokens' => 5000]
    ]);

    $messages[] = ['role' => 'assistant', 'content' => $planResponse->content];

    // Phase 2: Execution
    echo "\nPhase 2: Execution\n";
    $messages[] = ['role' => 'user', 'content' => 'Execute your plan step by step.'];

    $maxIterations = 10;
    $iteration = 0;

    while ($iteration < $maxIterations) {
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
            break;
        }

        if ($response->stop_reason === 'tool_use') {
            // Execute tools...
            $toolResults = executeTools($response->content);
            $messages[] = ['role' => 'user', 'content' => $toolResults];
        }
    }

    // Phase 3: Reflection
    echo "\nPhase 3: Reflection\n";
    $messages[] = [
        'role' => 'user',
        'content' => 'Reflect: Did you achieve the goal? Any issues?'
    ];

    $reflectionResponse = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 2048,
        'messages' => $messages,
        'thinking' => ['type' => 'enabled', 'budget_tokens' => 3000]
    ]);

    return [
        'plan' => extractTextContent($planResponse),
        'execution' => $messages,
        'reflection' => extractTextContent($reflectionResponse)
    ];
}
```

## 💡 Advanced Patterns

### 1. Multi-Step Task Decomposition

```php
// Agent breaks complex task into subtasks
$task = "Research recent AI developments, summarize findings, and calculate market impact";

// Agent will:
// 1. Decompose into: research → summarize → calculate
// 2. Execute each subtask
// 3. Combine results
```

### 2. Hypothesis Testing

```php
// Agent formulates and tests hypotheses
$messages[] = [
    'role' => 'user',
    'content' => 'Generate 3 hypotheses for why X might be happening, ' .
                 'then test each one.'
];
```

### 3. Progressive Refinement

```php
// Start with rough solution, refine iteratively
for ($refinement = 1; $refinement <= 3; $refinement++) {
    echo "Refinement {$refinement}\n";

    $messages[] = [
        'role' => 'user',
        'content' => 'Review and improve your previous answer.'
    ];

    // Agent refines...
}
```

## 📊 Thinking Budget Guidelines

| Task Complexity   | Suggested Budget | Model  |
| ----------------- | ---------------- | ------ |
| Simple planning   | 1,000-2,000      | Sonnet |
| Medium planning   | 3,000-5,000      | Sonnet |
| Complex reasoning | 5,000-10,000     | Sonnet |
| Deep analysis     | 10,000-20,000    | Opus   |
| Research/proof    | 20,000-32,000    | Opus   |

## ✅ Checkpoint

Before moving on, make sure you understand:

- [ ] The Plan-Execute-Reflect-Adjust pattern
- [ ] How to enable extended thinking
- [ ] When extended thinking is worth the cost
- [ ] How to implement planning phase
- [ ] How to implement reflection phase
- [ ] Self-correction strategies
- [ ] Task decomposition techniques

## 🚀 Next Steps

You now have advanced agentic patterns! The final tutorial brings it all together into a complete framework.

**[Tutorial 6: Agentic Framework →](../06-agentic-framework/)**

Build a complete orchestration system!

## 💻 Try It Yourself

Run the complete working example:

```bash
php tutorials/05-advanced-react/advanced_react_agent.php
```

The script demonstrates:

- ✅ Planning before execution
- ✅ Extended thinking integration
- ✅ Reflection after execution
- ✅ Self-correction
- ✅ Complex task decomposition
- ✅ Token budget management

## 📚 Further Reading

- [SDK Example: extended_thinking.php](../../examples/extended_thinking.php)
- [Claude Docs: Extended Thinking](https://docs.anthropic.com/en/docs/build-with-claude/extended-thinking)
- [ReAct Paper](https://arxiv.org/abs/2210.03629)



