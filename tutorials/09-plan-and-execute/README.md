# Tutorial 9: Plan-and-Execute

**Time: 45 minutes** | **Difficulty: Intermediate**

The Plan-and-Execute pattern separates planning from execution into two distinct phases. Unlike ReAct which interleaves thinking and action, Plan-and-Execute creates a complete plan upfront, then executes it systematically.

## 🎯 Learning Objectives

By the end of this tutorial, you'll be able to:

- Understand the Plan-and-Execute pattern
- Separate planning from execution
- Create detailed action plans before executing
- Monitor execution and handle failures
- Revise plans based on execution results
- Compare Plan-and-Execute with ReAct

## 🏗️ What We're Building

We'll implement agents that:

1. **Plan Phase** - Analyze task and create detailed action plan
2. **Execute Phase** - Systematically execute each planned step
3. **Monitor Phase** - Track progress and detect issues
4. **Revise Phase** - Update plan if needed

## 📋 Prerequisites

Make sure you have:

- Completed [Tutorial 8: Tree of Thoughts](../08-tree-of-thoughts/)
- Understanding of ReAct pattern
- PHP 8.1+ installed
- Claude PHP SDK configured

## 🤔 What is Plan-and-Execute?

Plan-and-Execute divides work into two phases:

### React Pattern (Interleaved)

```
Think → Act → Observe → Think → Act → Observe → ...
```

### Plan-and-Execute Pattern (Sequential)

```
PLAN: Analyze → Break down → Sequence steps
↓
EXECUTE: Step 1 → Step 2 → Step 3 → ...
```

## 🔑 Key Concepts

### 1. Planning Phase

Create comprehensive plan before any action:

```php
$planPrompt = "Task: {$task}\n\n" .
              "Create a detailed step-by-step plan. For each step:\n" .
              "1. Describe the action\n" .
              "2. What tool to use\n" .
              "3. Expected outcome\n" .
              "4. Dependencies on previous steps";
```

### 2. Execution Phase

Follow the plan systematically:

```php
foreach ($plan->steps as $step) {
    echo "Executing: {$step->action}\n";
    $result = executeTool($step->tool, $step->input);
    $step->result = $result;
    
    if ($result->isError) {
        // Handle failure
    }
}
```

### 3. Monitoring

Track execution progress:

```php
$monitor = [
    'completed' => [],
    'current' => $currentStep,
    'remaining' => $remainingSteps,
    'failures' => []
];
```

### 4. Plan Revision

Update plan if execution reveals issues:

```php
if ($executionFailed) {
    $revisedPlan = revisePlan($originalPlan, $executionResults);
}
```

## 📊 Plan-and-Execute vs ReAct

| Aspect | Plan-and-Execute | ReAct |
|--------|-----------------|-------|
| **Planning** | Upfront, complete | Interleaved with action |
| **Flexibility** | Less (follows plan) | High (adapts constantly) |
| **Efficiency** | Better (no wasted actions) | Can be exploratory |
| **Complexity** | Simpler execution | Complex loop |
| **Best For** | Well-defined tasks | Exploratory tasks |
| **Resource Use** | Predictable | Variable |

## 💡 Planning Implementation

### Step 1: Task Analysis

```php
$analysisPrompt = "Task: {$task}\n\n" .
                  "Analyze this task:\n" .
                  "1. What is the end goal?\n" .
                  "2. What information do we need?\n" .
                  "3. What tools are available?\n" .
                  "4. What are the constraints?";
```

### Step 2: Plan Generation

```php
$planningPrompt = "Task: {$task}\n\n" .
                  "Available tools: {$toolsList}\n\n" .
                  "Create a detailed plan with these sections:\n\n" .
                  "STEPS:\n" .
                  "1. [Action] - Tool: [tool_name] - Expected: [outcome]\n" .
                  "2. ...\n\n" .
                  "DEPENDENCIES:\n" .
                  "- Step 2 depends on Step 1 result\n\n" .
                  "RISKS:\n" .
                  "- Potential issues and mitigation";
```

### Step 3: Plan Validation

```php
function validatePlan($plan) {
    // Check all dependencies are satisfied
    // Verify tools exist
    // Ensure steps are ordered correctly
    // Check for circular dependencies
}
```

## 🚀 Execution Implementation

### Sequential Execution

```php
function executePlan($client, $plan, $tools) {
    $results = [];
    $context = [];
    
    foreach ($plan->steps as $i => $step) {
        echo "Step " . ($i + 1) . ": {$step->description}\n";
        
        // Execute with context from previous steps
        $result = executeStep($step, $context, $tools);
        
        if ($result->success) {
            $results[] = $result;
            $context[$step->id] = $result->data;
        } else {
            // Handle failure
            return handleFailure($plan, $i, $result);
        }
    }
    
    return $results;
}
```

### Error Handling

```php
function handleFailure($plan, $failedStepIndex, $error) {
    // Options:
    // 1. Retry the step
    // 2. Skip and continue
    // 3. Revise plan
    // 4. Abort mission
    
    if ($error->isRecoverable) {
        return retryStep($plan->steps[$failedStepIndex]);
    } else {
        return revisePlan($plan, $failedStepIndex, $error);
    }
}
```

## 🔄 Plan Revision

When execution reveals issues:

```php
$revisionPrompt = "Original plan: {$originalPlan}\n\n" .
                  "Execution so far:\n" .
                  "- Completed: {$completedSteps}\n" .
                  "- Failed: {$failedStep} - Reason: {$error}\n\n" .
                  "Revise the plan to:\n" .
                  "1. Work around the failure\n" .
                  "2. Maintain completed progress\n" .
                  "3. Still achieve the goal";
```

## 🎯 Example: Research Task

```
Task: Research and summarize the benefits of serverless architecture

PLAN:
1. Search for serverless architecture overview
   Tool: web_search
   Expected: General information about serverless

2. Search for serverless benefits
   Tool: web_search
   Expected: List of key advantages

3. Search for serverless case studies
   Tool: web_search
   Expected: Real-world examples

4. Synthesize findings
   Tool: none (pure reasoning)
   Expected: Coherent summary with sources

EXECUTE:
✓ Step 1: Found overview (5 sources)
✓ Step 2: Found 8 key benefits
✓ Step 3: Found 3 case studies
✓ Step 4: Created summary document
```

## 🛠️ Advanced Features

### Parallel Execution

For independent steps:

```php
// Identify independent steps
$parallelBatches = groupIndependentSteps($plan);

foreach ($parallelBatches as $batch) {
    $promises = [];
    foreach ($batch as $step) {
        $promises[] = executeStepAsync($step);
    }
    $results[] = await($promises);
}
```

### Conditional Steps

Plans with branches:

```php
"Step 3: IF Step 2 found more than 5 results THEN
           summarize top 5
         ELSE
           search with broader query";
```

### Resource Allocation

Track and limit resource usage:

```php
$plan->estimatedCost = [
    'api_calls' => 10,
    'tokens' => 15000,
    'time_seconds' => 30
];
```

## ⚠️ When to Use Plan-and-Execute

**Use Plan-and-Execute when:**
- ✅ Task is well-defined
- ✅ Steps are predictable
- ✅ Efficiency is important
- ✅ Resource budgets are fixed
- ✅ Need audit trail
- ✅ Parallel execution possible

**Use ReAct when:**
- ✅ Task is exploratory
- ✅ Outcomes uncertain
- ✅ Flexibility needed
- ✅ Learning as you go
- ✅ Dynamic environments

## 📈 Monitoring & Metrics

Track execution:

```php
$metrics = [
    'plan_generation_time' => 2.5, // seconds
    'total_steps' => 8,
    'completed_steps' => 6,
    'failed_steps' => 1,
    'retries' => 2,
    'execution_time' => 45.2,
    'cost' => 0.0234, // dollars
    'success_rate' => 0.875
];
```

## 🎨 Plan Visualization

```php
function visualizePlan($plan) {
    echo "EXECUTION PLAN\n";
    echo str_repeat("=", 60) . "\n\n";
    
    foreach ($plan->steps as $i => $step) {
        $status = $step->completed ? "✓" : 
                 ($step->inProgress ? "⏳" : "⭘");
        
        echo "{$status} Step " . ($i + 1) . ": {$step->description}\n";
        echo "   Tool: {$step->tool}\n";
        echo "   Expected: {$step->expected}\n";
        
        if ($step->dependencies) {
            echo "   Depends on: " . implode(", ", $step->dependencies) . "\n";
        }
        echo "\n";
    }
}
```

## ✅ Checkpoint

Before moving on, make sure you understand:

- [ ] Difference between Plan-and-Execute and ReAct
- [ ] How to create comprehensive plans
- [ ] Systematic execution of planned steps
- [ ] When to revise vs abort
- [ ] Monitoring execution progress
- [ ] When each pattern is most appropriate

## 🚀 Next Steps

You've mastered Plan-and-Execute! But what if we want our agent to evaluate and improve its own work?

**[Tutorial 10: Reflection & Self-Critique →](../10-reflection/)**

Learn how to build agents that reflect on and improve their outputs!

## 💻 Try It Yourself

Run the complete working example:

```bash
php tutorials/09-plan-and-execute/plan_execute_agent.php
```

The script demonstrates:

- ✅ Planning phase with explicit strategies
- ✅ Systematic execution of plan steps
- ✅ Progress tracking and monitoring
- ✅ Plan revision on failure
- ✅ Comparison with ReAct approach
- ✅ Visualization of execution flow

## 💡 Key Takeaways

1. **Separate planning from execution** - Think first, act later
2. **Complete plans upfront** - Less trial and error
3. **Systematic execution** - Follow the plan
4. **Monitor progress** - Track what works
5. **Revise when needed** - Plans aren't perfect
6. **Better efficiency** - Fewer wasted actions
7. **Audit trail** - Know what was done and why

## 📚 Further Reading

- [LangChain Plan-and-Execute](https://python.langchain.com/docs/use_cases/more/agents/autonomous_agents/plan_and_execute)
- [AutoGPT Architecture](https://github.com/Significant-Gravitas/AutoGPT)
- [Tutorial 2: ReAct Basics](../02-react-basics/) - Comparison
- [Tutorial 11: Hierarchical Agents](../11-hierarchical-agents/) - Next level

## 🎓 Real-World Applications

### Project Management
Break down projects into tasks, assign resources, execute systematically.

### Data Pipeline
Plan data transformation steps, execute in order, handle failures gracefully.

### Content Creation
Plan article structure, research each section, write systematically.

### System Deployment
Plan deployment steps, execute with rollback capability, monitor progress.

