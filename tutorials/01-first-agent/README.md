# Tutorial 1: Your First Agent

**Time: 30 minutes** | **Difficulty: Beginner**

Now that you understand the concepts, let's build your first working AI agent! In this tutorial, we'll create a simple agent with a single tool and walk through every step of the process.

## 🎯 Learning Objectives

By the end of this tutorial, you'll be able to:

- Define a tool with proper input schemas
- Send requests with tools to Claude
- Handle tool use requests from Claude
- Execute tools and return results
- Complete the full agent interaction cycle
- Debug agent behavior

## 🏗️ What We're Building

We'll create a **Calculator Agent** that can:

1. Receive math questions from users
2. Recognize when calculation is needed
3. Use a calculator tool to compute exact answers
4. Respond with the results

This simple agent demonstrates the complete Request → Tool Call → Execute → Response flow.

## 📋 Prerequisites

Make sure you have:

- Completed [Tutorial 0: Introduction to Agentic AI](../00-introduction/)
- PHP 8.1+ installed
- Anthropic API key configured in `.env`
- Claude PHP SDK installed

## 🔍 The Tool Use Flow

Before we code, let's visualize what happens:

```
┌─────────────────────────────────────────────────────────────┐
│  1. User Request                                            │
│     "What is 157 × 89?"                                     │
└────────────────────────────┬────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│  2. Your Code: Send to Claude with Tools                   │
│     POST /v1/messages                                       │
│     {                                                       │
│       "messages": [...],                                    │
│       "tools": [calculator_tool]                            │
│     }                                                       │
└────────────────────────────┬────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│  3. Claude: Analyzes & Decides                              │
│     "I need to calculate 157 × 89"                          │
│     Returns: stop_reason='tool_use'                         │
│     Tool request: calculate("157 * 89")                     │
└────────────────────────────┬────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│  4. Your Code: Execute Tool                                 │
│     result = 157 * 89 = 13,973                             │
└────────────────────────────┬────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│  5. Your Code: Return Result to Claude                      │
│     POST /v1/messages                                       │
│     {                                                       │
│       "messages": [                                         │
│         ...previous messages...,                            │
│         { "role": "user",                                   │
│           "content": [{"type": "tool_result", ...}] }      │
│       ]                                                     │
│     }                                                       │
└────────────────────────────┬────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│  6. Claude: Formulates Final Response                       │
│     "157 × 89 equals 13,973"                                │
│     Returns: stop_reason='end_turn'                         │
└────────────────────────────┬────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│  7. Your Code: Display to User                              │
│     "157 × 89 equals 13,973"                                │
└─────────────────────────────────────────────────────────────┘
```

## 🛠️ Step 1: Define Your Tool

A tool definition tells Claude three things:

1. **Name**: What to call it
2. **Description**: What it does (helps Claude decide when to use it)
3. **Input Schema**: What parameters it needs

```php
$calculatorTool = [
    'name' => 'calculate',

    'description' => 'Perform precise mathematical calculations. ' .
                     'Supports basic arithmetic operations: ' .
                     'addition (+), subtraction (-), ' .
                     'multiplication (*), division (/), ' .
                     'and parentheses for order of operations.',

    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'expression' => [
                'type' => 'string',
                'description' => 'The mathematical expression to evaluate. ' .
                                'Examples: "2 + 2", "15 * 8", "(100 - 25) / 5"'
            ]
        ],
        'required' => ['expression']
    ]
];
```

### 🔑 Key Points

- **Good descriptions** help Claude choose the right tool at the right time
- **Input schema** follows JSON Schema format
- **Required fields** ensure Claude provides all necessary parameters
- **Parameter descriptions** guide Claude on formatting

## 🚀 Step 2: Make the First Request

Send the user's question along with available tools:

```php
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'What is 157 × 89?']
    ],
    'tools' => [$calculatorTool]  // Provide the tool
]);
```

## 🔍 Step 3: Check the Response

Claude's response will include:

```php
// Check what Claude wants to do
$stopReason = $response->stop_reason;

if ($stopReason === 'tool_use') {
    // Claude wants to use a tool!
} elseif ($stopReason === 'end_turn') {
    // Claude has a final answer (no tool needed)
} elseif ($stopReason === 'max_tokens') {
    // Response was truncated (increase max_tokens)
}
```

### Understanding `stop_reason`

| Value        | Meaning                        | Action                                       |
| ------------ | ------------------------------ | -------------------------------------------- |
| `tool_use`   | Claude wants to execute a tool | Extract tool use block and execute           |
| `end_turn`   | Claude finished its response   | Display response to user                     |
| `max_tokens` | Hit token limit                | Increase `max_tokens` or handle continuation |

## 🔧 Step 4: Extract Tool Use

When `stop_reason === 'tool_use'`, extract the tool request:

```php
$toolUse = null;

foreach ($response->content as $block) {
    if ($block['type'] === 'tool_use') {
        $toolUse = $block;
        // $toolUse contains:
        // - 'id': Unique identifier for this tool call
        // - 'name': The tool name ('calculate')
        // - 'input': Parameters (e.g., ['expression' => '157 * 89'])
        break;
    }
}
```

## ⚙️ Step 5: Execute the Tool

Now run the actual tool function:

```php
if ($toolUse) {
    $toolName = $toolUse['name'];
    $expression = $toolUse['input']['expression'];

    // Execute the calculator
    if ($toolName === 'calculate') {
        try {
            // In production, use a safe math parser library
            // eval() is used here for demonstration only!
            $result = eval("return {$expression};");
        } catch (Exception $e) {
            $result = "Error: " . $e->getMessage();
        }
    }
}
```

### ⚠️ Security Note

In production code, **never use `eval()` with user input!** Use a proper math expression parser like:

- `mossadal/math-parser`
- `nxp/math-executor`
- Or implement safe parsing

## 📤 Step 6: Return Results to Claude

Create a tool_result and send it back:

```php
$messages = [
    // Original user message
    ['role' => 'user', 'content' => 'What is 157 × 89?'],

    // Claude's response (with tool_use)
    ['role' => 'assistant', 'content' => $response->content],

    // Tool result
    [
        'role' => 'user',
        'content' => [
            [
                'type' => 'tool_result',
                'tool_use_id' => $toolUse['id'],  // Must match!
                'content' => (string)$result       // Result as string
            ]
        ]
    ]
];

// Send back to Claude
$finalResponse = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => $messages,
    'tools' => [$calculatorTool]  // Tools still available
]);
```

### 🔑 Critical Points

1. **tool_use_id must match** - This is how Claude knows which tool call you're responding to
2. **Content must be a string** - Convert numbers/objects to strings
3. **Preserve conversation history** - Include all previous messages
4. **Keep tools available** - Claude might want to use them again

## ✅ Step 7: Display Final Response

Now Claude has the tool result and can formulate a final answer:

```php
foreach ($finalResponse->content as $block) {
    if ($block['type'] === 'text') {
        echo $block['text'] . "\n";
        // Output: "157 × 89 equals 13,973"
    }
}
```

## 🐛 Debugging Tips

### Issue: Claude doesn't use the tool

**Possible causes:**

- Tool description doesn't match the task
- Claude can answer without the tool
- Tool name/description too vague

**Fix:** Make the description more specific about when to use it.

### Issue: "tool_use_id not found"

**Cause:** The `tool_use_id` in your tool_result doesn't match the one from Claude.

**Fix:** Save the ID from Claude's response and use it exactly:

```php
$toolUseId = $toolUse['id'];  // Save this
// Later...
'tool_use_id' => $toolUseId   // Use exact same value
```

### Issue: Tool not executing

**Check:**

1. Does the tool name match exactly?
2. Is the input schema valid?
3. Are required parameters provided?
4. Check `$response->stop_reason`

## 📊 Monitoring & Costs

Always check token usage:

```php
echo "Tokens used:\n";
echo "  Input: {$response->usage->input_tokens}\n";
echo "  Output: {$response->usage->output_tokens}\n";
echo "  Total: " . ($response->usage->input_tokens + $response->usage->output_tokens) . "\n";
```

### Token Breakdown

For each tool use request:

- Tool definitions (~50-200 tokens depending on complexity)
- System prompt for tool use (~350 tokens)
- Your messages
- Claude's response

## 🎯 Complete Example

Here's the full flow in one place:

```php
// 1. Define tool
$tool = [...];

// 2. First request
$response = $client->messages()->create([
    'messages' => [['role' => 'user', 'content' => 'Calculate 157 × 89']],
    'tools' => [$tool]
]);

// 3. Extract tool use
$toolUse = /* extract from $response->content */;

// 4. Execute tool
$result = /* execute based on $toolUse */;

// 5. Return result
$finalResponse = $client->messages()->create([
    'messages' => [
        ['role' => 'user', 'content' => 'Calculate 157 × 89'],
        ['role' => 'assistant', 'content' => $response->content],
        ['role' => 'user', 'content' => [
            ['type' => 'tool_result', 'tool_use_id' => $toolUse['id'], 'content' => $result]
        ]]
    ],
    'tools' => [$tool]
]);

// 6. Display
echo extractTextContent($finalResponse);
```

## 🧪 Try It Yourself

Run the complete working example:

```bash
php tutorials/01-first-agent/simple_agent.php
```

The script demonstrates:

- ✅ Basic calculator agent
- ✅ Multiple calculations in sequence
- ✅ Error handling
- ✅ Debug output
- ✅ Token usage tracking

## ✅ Checkpoint

Before moving on, make sure you understand:

- [ ] How to define a tool with input_schema
- [ ] How to send tools with your request
- [ ] What `stop_reason` means
- [ ] How to extract tool_use blocks
- [ ] How to format tool_result
- [ ] Why tool_use_id must match

## 🚀 Next Steps

Congratulations! You've built your first agent. But it only handles one tool call per task. What if the task requires multiple steps?

**[Tutorial 2: ReAct Basics →](../02-react-basics/)**

Learn how to implement the ReAct loop for multi-step reasoning!

## 💡 Common Patterns

### Pattern: Simple Tool Execution

```php
function executeTool($toolUse) {
    $name = $toolUse['name'];
    $input = $toolUse['input'];

    return match($name) {
        'calculate' => calculate($input['expression']),
        'get_weather' => getWeather($input['location']),
        default => "Unknown tool: {$name}"
    };
}
```

### Pattern: Error Handling in Tools

```php
function executeToolSafely($toolUse) {
    try {
        $result = executeTool($toolUse);
        return [
            'type' => 'tool_result',
            'tool_use_id' => $toolUse['id'],
            'content' => $result
        ];
    } catch (Exception $e) {
        return [
            'type' => 'tool_result',
            'tool_use_id' => $toolUse['id'],
            'content' => "Error: " . $e->getMessage(),
            'is_error' => true  // Signal this is an error
        ];
    }
}
```

## 📚 Further Reading

- [SDK Example: tool_use_overview.php](../../examples/tool_use_overview.php)
- [SDK Example: tool_use_implementation.php](../../examples/tool_use_implementation.php)
- [Claude Docs: Tool Use](https://docs.anthropic.com/en/docs/agents-and-tools/tool-use/overview)



