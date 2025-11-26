#!/usr/bin/env php
<?php
/**
 * Tutorial 15: Context Management & Advanced Tool Use
 * 
 * This tutorial demonstrates:
 * - Auto-compaction for managing context window size
 * - Effort levels for controlling response quality
 * - Tool search for dynamic tool discovery
 * - Combining features for optimal performance
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../examples/helpers.php';
require_once __DIR__ . '/../helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║          Tutorial 15: Context Management & Advanced Tool Use               ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Part 1: Auto-Compaction Configuration
// ============================================================================

echo "PART 1: AUTO-COMPACTION CONFIGURATION\n";
echo str_repeat("─", 80) . "\n\n";

echo "Auto-compaction automatically manages context window size by summarizing\n";
echo "message history when it exceeds a token threshold.\n\n";

// Compaction configuration
$compactionControl = [
    'enabled' => true,
    'context_token_threshold' => 100000, // Default threshold
];

echo "Configuration:\n";
echo json_encode($compactionControl, JSON_PRETTY_PRINT) . "\n\n";

// Custom summary prompt for specialized domains
$customSummaryPrompt = <<<'PROMPT'
You have been working on a task but haven't completed it yet.
Write a continuation summary that allows you to resume efficiently.

Include:
1. Task Overview - Core request and success criteria
2. Current State - What's completed, files modified
3. Discoveries - Constraints, decisions, errors encountered
4. Next Steps - Actions needed to complete
5. Context - User preferences, domain details

Be concise but complete. Wrap in <summary></summary> tags.
PROMPT;

echo "Custom Summary Prompt:\n";
echo "─────────────────────\n";
echo substr($customSummaryPrompt, 0, 200) . "...\n\n";

echo str_repeat("=", 80) . "\n\n";

// ============================================================================
// Part 2: Effort Level Selection
// ============================================================================

echo "PART 2: EFFORT LEVEL SELECTION\n";
echo str_repeat("─", 80) . "\n\n";

echo "Effort levels control computational intensity:\n";
echo "  • low    → Fast, basic reasoning\n";
echo "  • medium → Balanced (default)\n";
echo "  • high   → Thorough, deep reasoning\n\n";

/**
 * Select appropriate effort level based on task complexity
 */
function selectEffortLevel(string $task): string
{
    $taskLower = strtolower($task);
    
    // Complex tasks needing high effort
    $complexIndicators = [
        'analyze', 'prove', 'explain why', 'compare and contrast',
        'security', 'optimization', 'architecture', 'debug',
        'mathematical', 'proof', 'implications', 'critical',
    ];
    
    // Simple tasks suitable for low effort
    $simpleIndicators = [
        'what is', 'define', 'list', 'translate',
        'summarize briefly', 'yes or no', 'spam',
    ];
    
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

// Test effort selection
$testTasks = [
    "What is the capital of France?" => 'low',
    "Help me write a blog post" => 'medium',
    "Analyze the security implications of this architecture" => 'high',
];

echo "Effort Level Selection Examples:\n";
foreach ($testTasks as $task => $expected) {
    $selected = selectEffortLevel($task);
    $match = $selected === $expected ? "✓" : "✗";
    echo "  {$match} \"{$task}\"\n";
    echo "    → {$selected} (expected: {$expected})\n";
}
echo "\n";

echo str_repeat("=", 80) . "\n\n";

// ============================================================================
// Part 3: Tool Search Configuration
// ============================================================================

echo "PART 3: TOOL SEARCH CONFIGURATION\n";
echo str_repeat("─", 80) . "\n\n";

echo "Tool search enables dynamic discovery from large tool collections.\n";
echo "Tools with defer_loading=true aren't in the initial prompt.\n\n";

// Define a collection of tools with deferred loading
$deferredTools = [
    [
        'name' => 'get_weather',
        'description' => 'Get current weather for a location',
        'defer_loading' => true,
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'location' => ['type' => 'string'],
            ],
            'required' => ['location'],
        ],
    ],
    [
        'name' => 'get_forecast',
        'description' => 'Get 7-day weather forecast',
        'defer_loading' => true,
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'location' => ['type' => 'string'],
            ],
            'required' => ['location'],
        ],
    ],
    [
        'name' => 'calculate_statistics',
        'description' => 'Calculate statistical measures',
        'defer_loading' => true,
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'data' => ['type' => 'array', 'items' => ['type' => 'number']],
            ],
            'required' => ['data'],
        ],
    ],
    [
        'name' => 'search_database',
        'description' => 'Search the knowledge database',
        'defer_loading' => true,
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'query' => ['type' => 'string'],
            ],
            'required' => ['query'],
        ],
    ],
];

/**
 * Create a tool searcher function
 */
function createToolSearcher(array $tools): callable
{
    return function(string $keyword) use ($tools): array {
        $results = [];
        
        foreach ($tools as $tool) {
            $searchText = strtolower(json_encode($tool));
            
            if (str_contains($searchText, strtolower($keyword))) {
                $results[] = [
                    'type' => 'tool_reference',
                    'tool_name' => $tool['name'],
                ];
            }
        }
        
        return $results;
    };
}

$toolSearcher = createToolSearcher($deferredTools);

// Test the searcher
echo "Tool Search Results:\n";
$weatherTools = $toolSearcher('weather');
echo "  Query: 'weather'\n";
echo "  Results: " . json_encode(array_column($weatherTools, 'tool_name')) . "\n\n";

$statsTools = $toolSearcher('statistics');
echo "  Query: 'statistics'\n";
echo "  Results: " . json_encode(array_column($statsTools, 'tool_name')) . "\n\n";

// BM25 search tool configuration
$bm25SearchTool = [
    'type' => 'tool_search_tool_bm25_20251119',
    'name' => 'tool_search_tool_bm25',
    'allowed_callers' => ['direct', 'code_execution_20250825'],
    'defer_loading' => false,
];

echo "BM25 Search Tool Configuration:\n";
echo json_encode($bm25SearchTool, JSON_PRETTY_PRINT) . "\n\n";

echo str_repeat("=", 80) . "\n\n";

// ============================================================================
// Part 4: MCP Toolset Configuration
// ============================================================================

echo "PART 4: MCP TOOLSET CONFIGURATION\n";
echo str_repeat("─", 80) . "\n\n";

echo "MCP toolsets configure tools from Model Context Protocol servers.\n\n";

$mcpToolset = [
    'type' => 'mcp_toolset',
    'mcp_server_name' => 'database-server',
    'cache_control' => [
        'type' => 'ephemeral',
    ],
    'default_config' => [
        'enabled' => true,
        'defer_loading' => true, // Defer all by default
    ],
    'configs' => [
        // Override for specific tools
        'safe_query' => [
            'enabled' => true,
            'defer_loading' => false, // Load immediately
        ],
        'drop_table' => [
            'enabled' => false, // Disable dangerous operation
        ],
        'create_index' => [
            'enabled' => true,
            'defer_loading' => true,
        ],
    ],
];

echo "MCP Toolset Configuration:\n";
echo json_encode($mcpToolset, JSON_PRETTY_PRINT) . "\n\n";

echo "This configuration:\n";
echo "  ✓ Enables all tools by default\n";
echo "  ✓ Defers loading for all tools by default\n";
echo "  ✓ Loads 'safe_query' immediately (frequently used)\n";
echo "  ✓ Disables 'drop_table' (security)\n";
echo "  ✓ Keeps 'create_index' deferred (rarely used)\n\n";

echo str_repeat("=", 80) . "\n\n";

// ============================================================================
// Part 5: Computer Use V5 Configuration
// ============================================================================

echo "PART 5: COMPUTER USE V5 (20251124)\n";
echo str_repeat("─", 80) . "\n\n";

echo "Computer Use V5 adds zoom, security controls, and optimizations.\n\n";

$computerToolV5 = [
    'type' => 'computer_20251124',
    'name' => 'computer',
    'display_width_px' => 1920,
    'display_height_px' => 1080,
    'enable_zoom' => true,
    'allowed_callers' => ['direct'], // Security: only direct calls
    'defer_loading' => false,
    'display_number' => 0,
    'strict' => true,
];

echo "Computer Use V5 Configuration:\n";
echo json_encode($computerToolV5, JSON_PRETTY_PRINT) . "\n\n";

echo "New V5 Features:\n";
echo "  ✓ enable_zoom - Request zoomed screenshots\n";
echo "  ✓ allowed_callers - Security control\n";
echo "  ✓ defer_loading - Performance optimization\n";
echo "  ✓ display_number - Multi-display support\n";
echo "  ✓ strict - Enhanced validation\n\n";

echo str_repeat("=", 80) . "\n\n";

// ============================================================================
// Part 6: Complete Agent Example
// ============================================================================

echo "PART 6: COMPLETE CONTEXT-MANAGED AGENT\n";
echo str_repeat("─", 80) . "\n\n";

echo "Combining all features for an optimal agent:\n\n";

/**
 * Context-managed agent with all advanced features
 */
class ContextManagedAgent
{
    private ClaudePhp $client;
    private array $messages = [];
    private array $tools;
    /** @var callable */
    private $toolSearcher;
    private int $compactionThreshold;
    private int $previousMessageCount = 0;
    
    public function __construct(
        ClaudePhp $client,
        array $tools = [],
        int $compactionThreshold = 100000
    ) {
        $this->client = $client;
        $this->tools = $tools;
        $this->compactionThreshold = $compactionThreshold;
        $this->toolSearcher = $this->createToolSearcher($tools);
    }
    
    private function createToolSearcher(array $tools): callable
    {
        return function(string $keyword) use ($tools): array {
            $results = [];
            foreach ($tools as $tool) {
                if (isset($tool['name']) && isset($tool['description'])) {
                    $searchText = strtolower($tool['name'] . ' ' . $tool['description']);
                    if (str_contains($searchText, strtolower($keyword))) {
                        $results[] = [
                            'type' => 'tool_reference',
                            'tool_name' => $tool['name'],
                        ];
                    }
                }
            }
            return $results;
        };
    }
    
    private function selectEffort(string $task): string
    {
        $taskLower = strtolower($task);
        $complexIndicators = ['analyze', 'prove', 'security', 'architecture'];
        $simpleIndicators = ['what is', 'list', 'define'];
        
        foreach ($complexIndicators as $ind) {
            if (str_contains($taskLower, $ind)) {
                return 'high';
            }
        }
        foreach ($simpleIndicators as $ind) {
            if (str_contains($taskLower, $ind)) {
                return 'low';
            }
        }
        return 'medium';
    }
    
    public function query(string $task): array
    {
        $this->messages[] = [
            'role' => 'user',
            'content' => $task,
        ];
        
        $effort = $this->selectEffort($task);
        echo "  Effort level: {$effort}\n";
        
        $params = [
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4096,
            'messages' => $this->messages,
            'output_config' => [
                'effort' => $effort,
            ],
            'compaction_control' => [
                'enabled' => true,
                'context_token_threshold' => $this->compactionThreshold,
            ],
        ];
        
        if (!empty($this->tools)) {
            $params['tools'] = $this->tools;
        }
        
        // Simulate response (actual API call would go here)
        $response = [
            'content' => [
                ['type' => 'text', 'text' => "Response to: {$task}"],
            ],
            'stop_reason' => 'end_turn',
            'usage' => [
                'input_tokens' => 150,
                'output_tokens' => 50,
            ],
        ];
        
        // Detect compaction
        $currentMessageCount = count($this->messages);
        if ($currentMessageCount < $this->previousMessageCount) {
            echo "  🔄 Compaction triggered!\n";
            echo "  Messages: {$this->previousMessageCount} → {$currentMessageCount}\n";
        }
        $this->previousMessageCount = $currentMessageCount;
        
        // Add response to history
        $this->messages[] = [
            'role' => 'assistant',
            'content' => $response['content'],
        ];
        
        return $response;
    }
    
    public function searchTools(string $keyword): array
    {
        return ($this->toolSearcher)($keyword);
    }
}

// Create the agent
$agent = new ContextManagedAgent(
    client: $client,
    tools: $deferredTools,
    compactionThreshold: 50000
);

echo "Agent created with:\n";
echo "  • Auto-compaction enabled (50k token threshold)\n";
echo "  • Dynamic effort selection\n";
echo "  • Tool search capability\n";
echo "  • " . count($deferredTools) . " deferred tools\n\n";

// Simulate queries
echo "Simulated queries:\n";
echo "───────────────────\n";

$queries = [
    "What is the weather in Paris?",
    "Analyze the security of this API design",
    "List available tools for data analysis",
];

foreach ($queries as $query) {
    echo "\nQuery: \"{$query}\"\n";
    $response = $agent->query($query);
    echo "  Response received\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// ============================================================================
// Summary
// ============================================================================

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                           TUTORIAL SUMMARY                                  ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "Key Features Demonstrated:\n\n";

echo "1. AUTO-COMPACTION\n";
echo "   • Manages context window automatically\n";
echo "   • Configurable token threshold\n";
echo "   • Custom summary prompts\n\n";

echo "2. EFFORT LEVELS\n";
echo "   • low → Fast, simple tasks\n";
echo "   • medium → Balanced (default)\n";
echo "   • high → Deep reasoning\n\n";

echo "3. TOOL SEARCH\n";
echo "   • BM25 and Regex search tools\n";
echo "   • defer_loading for efficiency\n";
echo "   • tool_reference results\n\n";

echo "4. MCP TOOLSETS\n";
echo "   • Configure MCP server tools\n";
echo "   • Per-tool overrides\n";
echo "   • Security controls\n\n";

echo "5. COMPUTER USE V5\n";
echo "   • Zoom capability\n";
echo "   • Allowed callers\n";
echo "   • Multi-display support\n\n";

echo "Best Practices:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Match effort level to task complexity\n";
echo "• Use defer_loading for rarely-used tools\n";
echo "• Set appropriate compaction thresholds\n";
echo "• Disable dangerous tools in MCP configs\n";
echo "• Monitor for compaction in long sessions\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related Examples:\n";
echo "  • examples/auto_compaction.php\n";
echo "  • examples/effort_levels.php\n";
echo "  • examples/tool_search.php\n";
echo "  • examples/mcp_toolset.php\n";
echo "  • examples/computer_use_v5.php\n\n";

echo "✓ Tutorial 15 completed!\n";

