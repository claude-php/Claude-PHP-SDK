# Tutorial 2: ReAct Basics

**Time: 45 minutes** | **Difficulty: Intermediate**

In the previous tutorial, we built an agent that could make one tool call. But what about tasks that require multiple steps? That's where the **ReAct pattern** comes in. In this tutorial, we'll implement a proper ReAct loop that enables iterative reasoning and multi-step problem solving.

## 🎯 Learning Objectives

By the end of this tutorial, you'll be able to:

- Implement the ReAct (Reason-Act-Observe) loop
- Handle multiple tool calls in sequence
- Maintain conversation state across iterations
- Implement proper stop conditions
- Debug agent reasoning steps
- Prevent infinite loops with iteration limits

## 🔄 What is ReAct?

**ReAct** stands for **Reason** → **Act** → **Observe**, and it's the fundamental pattern for autonomous agents.

### The Loop

```
Start
  ↓
┌─────────────────────────────────┐
│  REASON                         │
│  "What do I need to do next?"   │
│  "What info is missing?"        │
└───────────┬─────────────────────┘
            ↓
┌─────────────────────────────────┐
│  ACT                            │
│  "Call tool X with params Y"    │
│  Or "I have enough to answer"   │
└───────────┬─────────────────────┘
            ↓
┌─────────────────────────────────┐
│  OBSERVE                        │
│  "Tool returned Z"              │
│  "Do I have what I need?"       │
└───────────┬─────────────────────┘
            ↓
        ┌───────┐
        │ Done? │
        └───┬───┘
            │
      No ───┴─── Yes
      │           │
      │           ↓
      │        [Return
      │         Answer]
      │
      └──> (Back to REASON)
```

### Why It Matters

Without ReAct, agents can only:

- Answer questions with their training data
- Make ONE tool call per task

With ReAct, agents can:

- Gather information step-by-step
- Chain multiple tools together
- Adapt based on tool results
- Solve complex multi-step problems

## 🏗️ What We're Building

We'll build a ReAct agent that can:

1. Accept complex tasks requiring multiple steps
2. Reason about what to do next
3. Execute tools iteratively
4. Observe results and adapt
5. Continue until the task is complete
6. Respect iteration limits

### Example Task

**Question**: "What is (50 × 30) + (100 - 25)?"

**Traditional Agent** (from Tutorial 1):

- Can only make ONE tool call
- Would fail or give incomplete answer

**ReAct Agent** (what we're building):

- Iteration 1: Calculate 50 × 30 = 1,500
- Iteration 2: Calculate 100 - 25 = 75
- Iteration 3: Calculate 1,500 + 75 = 1,575
- Final Answer: "1,575"

## 🔑 Core Components

### 1. The Main Loop

```php
$messages = [/* initial message */];
$maxIterations = 10;  // Safety limit
$iteration = 0;

while ($iteration < $maxIterations) {
    $iteration++;

    // Call Claude
    $response = $client->messages()->create([
        'messages' => $messages,
        'tools' => $tools
    ]);

    // Add response to history
    $messages[] = [
        'role' => 'assistant',
        'content' => $response->content
    ];

    // Check if done
    if ($response->stop_reason === 'end_turn') {
        // Task complete!
        break;
    }

    // Execute tools and continue
    if ($response->stop_reason === 'tool_use') {
        // Extract and execute tools
        // Add results to messages
        // Loop continues...
    }
}
```

### 2. Stop Conditions

Your loop needs to exit when:

1. **Task Complete**: `stop_reason === 'end_turn'`
2. **Max Iterations**: `$iteration >= $maxIterations`
3. **Error**: Tool execution fails critically
4. **No Tools**: `stop_reason === 'tool_use'` but no tool uses found

### 3. State Management

The conversation history is your state:

```php
$messages = [
    ['role' => 'user', 'content' => 'Task...'],           // Turn 1
    ['role' => 'assistant', 'content' => [/* tool use */]], // Turn 2
    ['role' => 'user', 'content' => [/* tool result */]],   // Turn 3
    ['role' => 'assistant', 'content' => [/* tool use */]], // Turn 4
    // ... continues until done
];
```

Each iteration adds to this history, giving Claude context about what's already been done.

## 📋 Implementation Steps

### Step 1: Initialize the Loop

```php
$messages = [
    ['role' => 'user', 'content' => $userTask]
];

$maxIterations = 10;
$iteration = 0;
$finalResponse = null;
```

### Step 2: Loop Until Done

```php
while ($iteration < $maxIterations) {
    $iteration++;

    echo "Iteration {$iteration}\n";

    // Call Claude with current conversation history
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 4096,
        'messages' => $messages,
        'tools' => $tools
    ]);

    // Add Claude's response to history
    $messages[] = [
        'role' => 'assistant',
        'content' => $response->content
    ];

    // Check stop condition
    if ($response->stop_reason === 'end_turn') {
        $finalResponse = $response;
        break;
    }

    // Handle tool use (next step)
    // ...
}
```

### Step 3: Extract and Execute Tools

```php
if ($response->stop_reason === 'tool_use') {
    $toolResults = [];

    foreach ($response->content as $block) {
        if ($block['type'] === 'tool_use') {
            // Execute the tool
            $result = executeToolFunction(
                $block['name'],
                $block['input']
            );

            // Format result
            $toolResults[] = [
                'type' => 'tool_result',
                'tool_use_id' => $block['id'],
                'content' => $result
            ];
        }
    }

    // Add results to conversation
    $messages[] = [
        'role' => 'user',
        'content' => $toolResults
    ];
}
```

### Step 4: Handle Completion

```php
if ($finalResponse) {
    // Extract text from final response
    foreach ($finalResponse->content as $block) {
        if ($block['type'] === 'text') {
            echo "Final Answer: {$block['text']}\n";
        }
    }
} else {
    echo "Max iterations reached without completion\n";
}
```

## 🐛 Debugging ReAct Loops

### Visualize Each Iteration

```php
function debugIteration($iteration, $response) {
    echo "\n╔════ Iteration {$iteration} ════╗\n";
    echo "Stop Reason: {$response->stop_reason}\n";

    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo "Text: {$block['text']}\n";
        } elseif ($block['type'] === 'tool_use') {
            echo "Tool: {$block['name']}\n";
            echo "  Input: " . json_encode($block['input']) . "\n";
        }
    }

    echo "Tokens: {$response->usage->input_tokens} in, ";
    echo "{$response->usage->output_tokens} out\n";
}
```

### Common Issues

**Issue: Infinite Loop**

**Symptom**: Agent keeps making tool calls without completing

**Causes**:

- Max iterations too high (or missing)
- Tool results not formatted correctly
- Tool always returns incomplete information

**Fix**:

```php
// Always set a reasonable limit
$maxIterations = 10;

// Check if stuck
if ($iteration >= 5 && !$hasProgressed) {
    echo "Warning: Agent may be stuck\n";
    break;
}
```

**Issue: Loop Exits Too Early**

**Symptom**: Agent stops before task is complete

**Causes**:

- Max iterations too low
- Misinterpreting stop_reason
- Tool result contains errors

**Fix**:

```php
// Increase iterations for complex tasks
$maxIterations = 15;

// Check actual stop reason
if ($response->stop_reason !== 'end_turn') {
    echo "Unexpected stop: {$response->stop_reason}\n";
}
```

**Issue: Tool Results Not Working**

**Symptom**: Agent doesn't use tool results

**Causes**:

- `tool_use_id` doesn't match
- Results not added to conversation
- Results in wrong format

**Fix**:

```php
// Verify IDs match
echo "Tool Use ID: {$toolUse['id']}\n";
echo "Tool Result ID: {$toolResult['tool_use_id']}\n";

// Ensure results are added
$messages[] = [
    'role' => 'user',  // Must be 'user'!
    'content' => $toolResults
];
```

## 📊 Iteration Limits

### How to Choose

| Task Complexity | Suggested Limit  | Example                |
| --------------- | ---------------- | ---------------------- |
| Simple          | 3-5 iterations   | Single calculation     |
| Medium          | 5-10 iterations  | Multi-step calculation |
| Complex         | 10-15 iterations | Research + analysis    |
| Very Complex    | 15-25 iterations | Multi-stage workflows  |

### Costs

Each iteration uses tokens:

- Tool definitions: ~50-200 tokens
- System prompt: ~350 tokens
- Growing message history: 100-1000+ tokens
- Claude's response: 50-500+ tokens

**Example**: 10-iteration task might use 5,000-15,000 tokens total.

### Token Management

```php
// Estimate conversation size
$conversationJson = json_encode($messages);
$estimatedTokens = strlen($conversationJson) / 4;

if ($estimatedTokens > 50000) {
    echo "Warning: Conversation getting large\n";
    // Consider trimming history
}
```

## 🎯 Best Practices

### 1. Always Set Max Iterations

```php
// ✅ Good
$maxIterations = 10;

// ❌ Bad
// No limit - potential infinite loop!
```

### 2. Preserve Conversation History

```php
// ✅ Good - Keep all messages
$messages[] = ['role' => 'assistant', 'content' => $response->content];
$messages[] = ['role' => 'user', 'content' => $toolResults];

// ❌ Bad - Losing context
$messages = [['role' => 'user', 'content' => $toolResults]];
```

### 3. Handle All Stop Reasons

```php
// ✅ Good - Handle all cases
if ($response->stop_reason === 'end_turn') {
    // Complete
} elseif ($response->stop_reason === 'tool_use') {
    // Execute tools
} elseif ($response->stop_reason === 'max_tokens') {
    // Increase max_tokens
} else {
    // Unexpected
}

// ❌ Bad - Only checking one
if ($response->stop_reason === 'end_turn') {
    // What about tool_use?
}
```

### 4. Log for Debugging

```php
// ✅ Good - Detailed logging
echo "Iteration {$iteration}: {$response->stop_reason}\n";

// ❌ Bad - No visibility
// (silent execution)
```

### 5. Validate Tool Results

```php
// ✅ Good - Check before adding
if (empty($toolResults)) {
    echo "Warning: No tool results\n";
    break;
}

// ❌ Bad - Blindly add
$messages[] = ['role' => 'user', 'content' => $toolResults];
```

## ✅ Checkpoint

Before moving on, make sure you understand:

- [ ] What ReAct (Reason-Act-Observe) means
- [ ] How to implement a basic ReAct loop
- [ ] Why iteration limits are critical
- [ ] How to maintain conversation state
- [ ] When the loop should exit
- [ ] How to debug ReAct iterations

## 🚀 Next Steps

Now you have a ReAct agent that can handle multi-step tasks with a single tool. But real agents need multiple diverse tools to be truly useful.

**[Tutorial 3: Multi-Tool Agent →](../03-multi-tool-agent/)**

Learn how to give your agent multiple tools and help Claude choose the right one!

## 💻 Try It Yourself

Run the complete working example:

```bash
php tutorials/02-react-basics/react_agent.php
```

The script demonstrates:

- ✅ Basic ReAct loop implementation
- ✅ Multi-step problem solving
- ✅ Iteration limiting
- ✅ State management
- ✅ Debug output for each iteration
- ✅ Token usage tracking

## 📚 Further Reading

- [ReAct Paper](https://arxiv.org/abs/2210.03629) - Original research
- [SDK Example: tool_use_implementation.php](../../examples/tool_use_implementation.php)
- [Claude Docs: Multi-turn Conversations](https://docs.anthropic.com/en/docs/build-with-claude/working-with-messages)



