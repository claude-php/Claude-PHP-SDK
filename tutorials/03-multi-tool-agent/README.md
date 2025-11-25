# Tutorial 3: Multi-Tool Agent

**Time: 45 minutes** | **Difficulty: Intermediate**

In the previous tutorials, we built agents with a single tool. Real-world agents need multiple diverse tools to handle various tasks. In this tutorial, we'll create an agent with several tools and learn how Claude chooses the right one for each situation.

## 🎯 Learning Objectives

By the end of this tutorial, you'll be able to:

- Define multiple tools with clear, distinct purposes
- Help Claude choose the right tool through good descriptions
- Handle different tool types (data retrieval, computation, actions)
- Debug tool selection decisions
- Optimize tool definitions for clarity
- Manage tool execution workflows

## 🏗️ What We're Building

We'll create a **Smart Assistant Agent** with these tools:

1. **Calculator** - Mathematical computations
2. **Current Time** - Get time in any timezone
3. **Weather** - Get weather information (simulated)
4. **Web Search** - Search for information (simulated)

This agent can handle diverse requests like:

- "What time is it in Tokyo?"
- "Calculate 25% of 480"
- "What's the weather in London?"
- "Search for the population of Paris"

## 🔑 Key Concepts

### Tool Selection

Claude chooses tools based on:

1. **Tool Name**: Clear, descriptive names
2. **Tool Description**: What it does and when to use it
3. **Input Schema**: What parameters it needs
4. **Context**: The user's request

### Good vs Bad Tool Definitions

**❌ Bad Example:**

```php
[
    'name' => 'tool1',
    'description' => 'Does stuff',
    'input_schema' => [...]
]
```

**✅ Good Example:**

```php
[
    'name' => 'get_weather',
    'description' => 'Get current weather conditions for a specific city. ' .
                     'Returns temperature, conditions, and humidity. ' .
                     'Use this when the user asks about weather or temperature.',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'city' => [
                'type' => 'string',
                'description' => 'City name, e.g., "San Francisco" or "London, UK"'
            ]
        ],
        'required' => ['city']
    ]
]
```

## 📋 Defining Multiple Tools

### 1. Calculator Tool

```php
$calculatorTool = [
    'name' => 'calculate',
    'description' => 'Perform precise mathematical calculations including ' .
                     'arithmetic (+, -, *, /), percentages, and complex expressions. ' .
                     'Use this for any mathematical computation.',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'expression' => [
                'type' => 'string',
                'description' => 'Math expression like "25 * 4" or "0.25 * 480"'
            ]
        ],
        'required' => ['expression']
    ]
];
```

### 2. Time Tool

```php
$timeTool = [
    'name' => 'get_current_time',
    'description' => 'Get the current time in any timezone. ' .
                     'Returns time in 24-hour format. ' .
                     'Use this when user asks "what time is it" or needs current time.',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'timezone' => [
                'type' => 'string',
                'description' => 'IANA timezone like "America/New_York", "Europe/London", "Asia/Tokyo"'
            ]
        ],
        'required' => ['timezone']
    ]
];
```

### 3. Weather Tool

```php
$weatherTool = [
    'name' => 'get_weather',
    'description' => 'Get current weather conditions for a city. ' .
                     'Returns temperature, conditions (sunny/rainy/cloudy), and humidity. ' .
                     'Use this when user asks about weather or temperature.',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'city' => [
                'type' => 'string',
                'description' => 'City name, can include country like "London, UK"'
            ]
        ],
        'required' => ['city']
    ]
];
```

### 4. Search Tool

```php
$searchTool = [
    'name' => 'search',
    'description' => 'Search for factual information on any topic. ' .
                     'Returns relevant information from knowledge sources. ' .
                     'Use this for facts, statistics, definitions, or recent information.',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'query' => [
                'type' => 'string',
                'description' => 'Search query'
            ]
        ],
        'required' => ['query']
    ]
];
```

## 🔧 Tool Implementation

### Tool Executor Pattern

```php
function executeTool($toolName, $input) {
    return match($toolName) {
        'calculate' => executeCalculator($input['expression']),
        'get_current_time' => getCurrentTime($input['timezone']),
        'get_weather' => getWeather($input['city']),
        'search' => performSearch($input['query']),
        default => "Unknown tool: {$toolName}"
    };
}

function executeCalculator($expression) {
    // Use safe math parser in production!
    return (string)eval("return {$expression};");
}

function getCurrentTime($timezone) {
    try {
        $dt = new DateTime('now', new DateTimeZone($timezone));
        return $dt->format('Y-m-d H:i:s T');
    } catch (Exception $e) {
        return "Error: Invalid timezone";
    }
}

function getWeather($city) {
    // Simulated - in production, call real weather API
    $conditions = ['sunny', 'cloudy', 'rainy', 'partly cloudy'];
    $temp = rand(50, 85);
    $condition = $conditions[array_rand($conditions)];

    return json_encode([
        'city' => $city,
        'temperature' => $temp . '°F',
        'conditions' => $condition,
        'humidity' => rand(30, 70) . '%'
    ]);
}

function performSearch($query) {
    // Simulated - in production, call real search API
    return "Search results for '{$query}': [Simulated results...]";
}
```

## 🎯 Using Multiple Tools

### The ReAct Loop with Multiple Tools

```php
$tools = [$calculatorTool, $timeTool, $weatherTool, $searchTool];

$messages = [
    ['role' => 'user', 'content' => $userQuestion]
];

$maxIterations = 10;
$iteration = 0;

while ($iteration < $maxIterations) {
    $iteration++;

    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 4096,
        'messages' => $messages,
        'tools' => $tools  // All tools available
    ]);

    $messages[] = ['role' => 'assistant', 'content' => $response->content];

    if ($response->stop_reason === 'end_turn') {
        break;
    }

    if ($response->stop_reason === 'tool_use') {
        $toolResults = [];

        foreach ($response->content as $block) {
            if ($block['type'] === 'tool_use') {
                // Claude chose a tool - execute it
                $result = executeTool($block['name'], $block['input']);

                $toolResults[] = [
                    'type' => 'tool_result',
                    'tool_use_id' => $block['id'],
                    'content' => $result
                ];
            }
        }

        $messages[] = ['role' => 'user', 'content' => $toolResults];
    }
}
```

## 🐛 Debugging Tool Selection

### Why Claude Might Choose Wrong Tool

1. **Ambiguous Descriptions**

   - Tools have overlapping purposes
   - Description doesn't clearly state when to use

2. **Poor Tool Names**

   - Names don't convey purpose
   - Too generic or confusing

3. **Missing Context**
   - Tool description lacks detail
   - Input schema unclear

### Debugging Techniques

**Log Tool Selection:**

```php
foreach ($response->content as $block) {
    if ($block['type'] === 'tool_use') {
        echo "Claude chose: {$block['name']}\n";
        echo "  Input: " . json_encode($block['input']) . "\n";
        echo "  Why: [Look at user's question]\n";
    }
}
```

**Test Tool Selection:**

```php
$testCases = [
    ['question' => 'What is 2 + 2?', 'expected' => 'calculate'],
    ['question' => 'What time is it in Tokyo?', 'expected' => 'get_current_time'],
    ['question' => 'Is it raining in Paris?', 'expected' => 'get_weather'],
    ['question' => 'Who invented the telephone?', 'expected' => 'search'],
];

foreach ($testCases as $test) {
    // Send question and check which tool Claude chooses
    // Compare with expected tool
}
```

## 💡 Best Practices

### 1. Distinct Tool Purposes

Make each tool clearly different:

```php
// ✅ Good - Clear separation
'calculate' => 'Math operations'
'get_weather' => 'Weather data'
'search' => 'General information lookup'

// ❌ Bad - Overlap
'calculate' => 'Do math and conversions'
'convert' => 'Convert units and currencies'  // Too similar!
```

### 2. Detailed Descriptions

Be specific about what the tool does:

```php
// ✅ Good
'description' => 'Get current weather conditions for a specific city. ' .
                 'Returns temperature, conditions (sunny/rainy/cloudy), ' .
                 'and humidity percentage. Use this when user asks about ' .
                 'weather, temperature, or if it is raining/snowing.'

// ❌ Bad
'description' => 'Gets weather'
```

### 3. Clear Parameter Names

```php
// ✅ Good
'properties' => [
    'city' => [
        'type' => 'string',
        'description' => 'City name like "London" or "New York, USA"'
    ]
]

// ❌ Bad
'properties' => [
    'location' => [
        'type' => 'string',
        'description' => 'location'  // Not helpful!
    ]
]
```

### 4. Limit Number of Tools

**Too many tools** = harder for Claude to choose correctly

| Number of Tools | Recommendation                        |
| --------------- | ------------------------------------- |
| 1-5 tools       | ✅ Optimal                            |
| 6-10 tools      | ⚠️ Okay, ensure clear descriptions    |
| 11-20 tools     | ❌ Consider grouping or filtering     |
| 20+ tools       | ❌ Definitely filter based on context |

### 5. Tool Categories

For many tools, organize by category:

```php
$mathTools = [$calculator, $converter, $statistics];
$dataTools = [$weather, $stocks, $news];
$actionTools = [$sendEmail, $createFile, $schedule];

// Provide only relevant category based on request
if (isComputationalTask($userInput)) {
    $tools = $mathTools;
} else {
    $tools = array_merge($dataTools, $actionTools);
}
```

## 📊 Tool Usage Patterns

### Pattern 1: Single Tool Per Request

User: "What's 25 × 17?"

- Tool: calculate
- Iterations: 2

### Pattern 2: Multiple Same Tool

User: "Calculate 10 × 5, 20 × 3, and 15 / 3"

- Tool: calculate (3 times)
- Iterations: 4

### Pattern 3: Different Tools

User: "What's the weather in Tokyo and what time is it there?"

- Tool: get_weather
- Tool: get_current_time
- Iterations: 3

### Pattern 4: Tool Chaining

User: "Look up the population of Paris and calculate 10% of it"

- Tool: search (get population)
- Tool: calculate (compute 10%)
- Iterations: 3

## ✅ Checkpoint

Before moving on, make sure you understand:

- [ ] How to define multiple tools with distinct purposes
- [ ] What makes a good tool description
- [ ] How Claude selects tools based on context
- [ ] How to execute different tools in one agent
- [ ] How to debug tool selection issues
- [ ] Best practices for tool organization

## 🚀 Next Steps

You now have a multi-tool agent! But it's still missing crucial production features like error handling, retries, and memory.

**[Tutorial 4: Production-Ready Agent →](../04-production-ready/)**

Learn how to build robust, production-grade agents!

## 💻 Try It Yourself

Run the complete working example:

```bash
php tutorials/03-multi-tool-agent/multi_tool_agent.php
```

The script demonstrates:

- ✅ Agent with 4 different tools
- ✅ Tool selection based on request type
- ✅ Handling different types of queries
- ✅ Tool execution workflow
- ✅ Debug output for tool selection
- ✅ Test suite for tool selection accuracy

## 📚 Further Reading

- [SDK Example: tool_use_implementation.php](../../examples/tool_use_implementation.php)
- [SDK Example: token_efficient_tool_use.php](../../examples/token_efficient_tool_use.php)
- [Claude Docs: Tool Use Best Practices](https://docs.anthropic.com/en/docs/agents-and-tools/tool-use/overview)



