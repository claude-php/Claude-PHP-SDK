# Tutorial 15: Context Management & Advanced Tool Use

**Time: 60 minutes** | **Difficulty: Advanced**

Master context window management with auto-compaction, effort levels, and advanced tool discovery features. These patterns are essential for building long-running agents and managing large tool collections.

## 🎯 What You'll Learn

- Auto-compaction for managing context window size
- Effort levels for controlling response quality
- Tool search for dynamic tool discovery
- MCP toolset configuration
- Computer use v5 features

## 📋 Prerequisites

Before starting this tutorial, you should:
- Complete Tutorials 0-6 (Foundation series)
- Understand ReAct patterns and tool use
- Have the Claude PHP SDK installed and configured

## 🚀 Key Concepts

### Auto-Compaction

When building agents that run for extended periods or process large amounts of data, the context window can fill up quickly. Auto-compaction automatically summarizes and compresses the message history when it exceeds a specified token threshold.

```php
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 4096,
    'messages' => $messages,
    'compaction_control' => [
        'enabled' => true,
        'context_token_threshold' => 100000,
    ],
]);
```

**When compaction triggers:**
1. Total tokens (input + output) exceeds threshold
2. System generates a continuation summary
3. Message history is replaced with summary
4. Agent continues with compressed context

### Effort Levels

Control the computational effort Claude puts into generating responses:

| Level | Use Case | Trade-offs |
|-------|----------|------------|
| `low` | Simple queries, classification | Fast, low tokens |
| `medium` | General tasks (default) | Balanced |
| `high` | Complex reasoning, analysis | Thorough, more tokens |

```php
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 4096,
    'output_config' => [
        'effort' => 'high', // For complex reasoning
    ],
    'messages' => $messages,
]);
```

### Tool Search

For large tool collections (100+ tools), loading all tools into the system prompt is inefficient. Tool search enables dynamic discovery:

```php
// Define tools with deferred loading
$tools = [
    [
        'name' => 'analyze_data',
        'description' => 'Analyze data sets',
        'defer_loading' => true, // Not loaded initially
        'input_schema' => [...],
    ],
    // ... hundreds more tools
];

// Add search tool
$tools[] = [
    'type' => 'tool_search_tool_bm25_20251119',
    'name' => 'tool_search_tool_bm25',
];

// Claude can now search for tools by name/description
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 1024,
    'tools' => $tools,
    'messages' => $messages,
    'betas' => ['tool-search-tool-2025-10-19'],
]);
```

### MCP Toolsets

Configure tools from Model Context Protocol (MCP) servers:

```php
$tools = [
    [
        'type' => 'mcp_toolset',
        'mcp_server_name' => 'database-server',
        'default_config' => [
            'enabled' => true,
            'defer_loading' => true,
        ],
        'configs' => [
            'safe_query' => ['defer_loading' => false], // Load immediately
            'drop_table' => ['enabled' => false], // Disable dangerous
        ],
    ],
];
```

## 💻 Tutorial Code

The tutorial code demonstrates:

1. **Long-running agent with auto-compaction**
2. **Task complexity detection with effort levels**
3. **Dynamic tool discovery with search**
4. **Combining all features for optimal performance**

### Running the Tutorial

```bash
cd tutorials/15-context-management
php context_agent.php
```

## 🎓 Code Walkthrough

### Part 1: Auto-Compaction Setup

```php
class ContextManagedAgent
{
    private ClaudePhp $client;
    private array $messages = [];
    private int $compactionThreshold;
    
    public function __construct(
        ClaudePhp $client,
        int $compactionThreshold = 100000
    ) {
        $this->client = $client;
        $this->compactionThreshold = $compactionThreshold;
    }
    
    public function run(string $task): void
    {
        $this->messages[] = [
            'role' => 'user',
            'content' => $task,
        ];
        
        $previousMessageCount = count($this->messages);
        
        while (true) {
            $response = $this->client->messages()->create([
                'model' => 'claude-sonnet-4-20250514',
                'max_tokens' => 4096,
                'messages' => $this->messages,
                'compaction_control' => [
                    'enabled' => true,
                    'context_token_threshold' => $this->compactionThreshold,
                ],
            ]);
            
            // Detect compaction
            $currentMessageCount = count($this->messages);
            if ($currentMessageCount < $previousMessageCount) {
                echo "🔄 Compaction occurred!\n";
                echo "Messages: {$previousMessageCount} → {$currentMessageCount}\n";
            }
            $previousMessageCount = $currentMessageCount;
            
            // Add assistant response
            $this->messages[] = [
                'role' => 'assistant',
                'content' => $response->content,
            ];
            
            if ($response->stop_reason === 'end_turn') {
                break;
            }
        }
    }
}
```

### Part 2: Dynamic Effort Selection

```php
function selectEffortLevel(string $task): string
{
    // Simple heuristics for effort selection
    $complexIndicators = [
        'analyze', 'prove', 'explain why', 'compare and contrast',
        'security', 'optimization', 'architecture', 'debug',
    ];
    
    $simpleIndicators = [
        'what is', 'define', 'list', 'translate', 'summarize briefly',
    ];
    
    $taskLower = strtolower($task);
    
    foreach ($complexIndicators as $indicator) {
        if (str_contains($taskLower, $indicator)) {
            return 'high';
        }
    }
    
    foreach ($simpleIndicators as $indicator) {
        if (str_contains($taskLower, $indicator)) {
            return 'low';
        }
    }
    
    return 'medium';
}

// Usage
$task = "Analyze the security implications of this architecture";
$effort = selectEffortLevel($task);

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 4096,
    'output_config' => ['effort' => $effort],
    'messages' => [['role' => 'user', 'content' => $task]],
]);
```

### Part 3: Tool Search Implementation

```php
function createToolSearcher(array $tools): callable
{
    return function(string $keyword) use ($tools): array {
        $results = [];
        
        foreach ($tools as $tool) {
            $searchText = json_encode($tool);
            
            if (stripos($searchText, $keyword) !== false) {
                $results[] = [
                    'type' => 'tool_reference',
                    'tool_name' => $tool['name'],
                ];
            }
        }
        
        return $results;
    };
}

// Create searchable tools
$tools = [];

// Add many deferred tools
foreach ($allToolDefinitions as $tool) {
    $tool['defer_loading'] = true;
    $tools[] = $tool;
}

// Add the search tool
$tools[] = [
    'name' => 'search_tools',
    'description' => 'Search for available tools by keyword',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'keyword' => [
                'type' => 'string',
                'description' => 'Search keyword',
            ],
        ],
        'required' => ['keyword'],
    ],
];

$toolSearcher = createToolSearcher($tools);
```

## 📊 Performance Comparison

| Feature | Without | With | Improvement |
|---------|---------|------|-------------|
| Context overflow | Fails at limit | Continues with summary | ✓ Unlimited |
| 100 tools in prompt | ~5000 tokens | ~500 tokens | 90% reduction |
| Simple query latency | Medium | Low (with low effort) | 40% faster |
| Complex analysis quality | Medium | High (with high effort) | Better output |

## 🔧 Best Practices

### Auto-Compaction

1. **Set appropriate thresholds**
   - Default: 100,000 tokens
   - Lower for memory-constrained scenarios
   - Higher when context preservation is critical

2. **Use custom summary prompts** for domain-specific tasks

3. **Monitor for compaction** and adjust thresholds as needed

### Effort Levels

1. **Match effort to task complexity**
   - Don't use high effort for simple queries
   - Reserve high for critical analysis

2. **Combine with extended thinking** for maximum depth

3. **Monitor token usage** - high effort costs more

### Tool Search

1. **Defer rarely-used tools** to reduce prompt size

2. **Use descriptive tool names** for better search results

3. **Consider BM25 vs Regex** based on search needs

## ⚠️ Common Pitfalls

1. **Context loss in compaction**: Important details may be summarized away
   - Store critical information externally
   - Use system prompts for persistent context

2. **Over-using high effort**: Increases latency and costs
   - Reserve for genuinely complex tasks

3. **Too many loaded tools**: Slows initial response
   - Use defer_loading aggressively

## 🎯 Next Steps

After this tutorial:
- Explore [examples/auto_compaction.php](../../examples/auto_compaction.php)
- Explore [examples/effort_levels.php](../../examples/effort_levels.php)
- Explore [examples/tool_search.php](../../examples/tool_search.php)
- Review [examples/mcp_toolset.php](../../examples/mcp_toolset.php)

## 📖 Related Resources

- [Context Windows](../../examples/context_windows.php)
- [Token Counting](../../examples/token_counting.php)
- [Prompt Caching](../../examples/prompt_caching.php)
- [Extended Thinking](../../examples/extended_thinking.php)

