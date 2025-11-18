#!/usr/bin/env php
<?php
/**
 * Memory Tool - PHP examples from:
 * https://docs.claude.com/en/docs/agents-and-tools/tool-use/memory-tool
 * 
 * Enable Claude to persist information across conversations.
 * Requires 'memory_20250818' type - server-side with file storage.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Memory Tool - Persistent Knowledge ===\n\n";

// Example 1: Basic memory tool
echo "Example 1: Basic Memory Tool Setup\n";
echo "-----------------------------------\n";
echo "Enable Claude to remember information across sessions\n\n";

$tools = [
    [
        'type' => 'memory_20250818',
        'name' => 'memory'
    ]
];

echo "Tool definition:\n";
echo json_encode($tools[0], JSON_PRETTY_PRINT) . "\n\n";

echo "Memory operations:\n";
echo "  • create - Create a new memory file\n";
echo "  • read - Read memory file contents\n";
echo "  • update - Update existing memory\n";
echo "  • delete - Remove memory file\n";
echo "  • list - List all memory files\n\n";

echo "File structure:\n";
echo "  • Memories stored as files\n";
echo "  • Organized in folders\n";
echo "  • Markdown format\n";
echo "  • Persistent across conversations\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: Using memory in conversations
echo "Example 2: Using Memory in Conversations\n";
echo "-----------------------------------------\n";
echo "Claude automatically saves and retrieves information\n\n";

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 2048,
        'tools' => [
            [
                'type' => 'memory_20250818',
                'name' => 'memory'
            ]
        ],
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Remember that my favorite programming language is PHP and I work on AI projects.'
            ]
        ]
    ]);

    echo "User: Remember my preferences\n\n";
    
    foreach ($response->content as $block) {
        if ($block['type'] === 'server_tool_use' && $block['name'] === 'memory') {
            echo "Claude saves to memory:\n";
            echo "  Operation: create/update\n";
            echo "  File: user_preferences.md (or similar)\n";
        } elseif ($block['type'] === 'text') {
            echo "Response: {$block['text']}\n";
        }
    }
    
    echo "\nMemory persists for future conversations\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 3: Memory with context editing
echo "Example 3: Memory with Context Editing\n";
echo "---------------------------------------\n";
echo "Perfect combination for unlimited conversation length\n\n";

$combinedConfig = [
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 4096,
    'tools' => [
        [
            'type' => 'memory_20250818',
            'name' => 'memory'
        ],
        [
            'type' => 'text_editor_20250728',
            'name' => 'str_replace_based_edit_tool',
            'max_characters' => 10000
        ]
    ],
    'betas' => ['context-management-2025-06-27'],
    'context_management' => [
        'edits' => [
            ['type' => 'clear_tool_uses_20250919']
        ]
    ]
];

echo "Configuration:\n";
echo json_encode(['tools' => $combinedConfig['tools'], 'context_management' => $combinedConfig['context_management']], JSON_PRETTY_PRINT) . "\n\n";

echo "How it works:\n";
echo "  1. Claude saves important info to memory files\n";
echo "  2. Context editing clears old tool results\n";
echo "  3. Claude retrieves from memory when needed\n";
echo "  4. Unlimited conversation length!\n\n";

echo "Benefits:\n";
echo "  • Exceed context window limits\n";
echo "  • Preserve important information\n";
echo "  • Long-running agentic workflows\n";
echo "  • Cost optimization\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 4: Memory file organization
echo "Example 4: Memory File Organization\n";
echo "------------------------------------\n\n";

echo "Best practices for memory files:\n\n";

echo "✓ Clear Naming:\n";
echo "  • user_preferences.md\n";
echo "  • project_status.md\n";
echo "  • conversation_notes.md\n";
echo "  • important_decisions.md\n\n";

echo "✓ Structured Content:\n";
echo "  • Use markdown formatting\n";
echo "  • Organize with headers\n";
echo "  • Bullet points for lists\n";
echo "  • Clear sections\n\n";

echo "✓ Folder Organization:\n";
echo "  • /user/ - User information\n";
echo "  • /project/ - Project details\n";
echo "  • /context/ - Conversation context\n";
echo "  • /decisions/ - Important choices\n\n";

echo "✓ Content Guidelines:\n";
echo "  • Keep concise and relevant\n";
echo "  • Update rather than duplicate\n";
echo "  • Include timestamps when useful\n";
echo "  • Remove outdated information\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 5: Use cases
echo "Example 5: Memory Tool Use Cases\n";
echo "---------------------------------\n\n";

echo "✓ Long-Running Conversations:\n";
echo "  • Remember user preferences\n";
echo "  • Track conversation history\n";
echo "  • Maintain project state\n";
echo "  • Preserve decisions\n\n";

echo "✓ Agentic Workflows:\n";
echo "  • Save progress between tasks\n";
echo "  • Remember completed steps\n";
echo "  • Track blockers and issues\n";
echo "  • Maintain task context\n\n";

echo "✓ Personalization:\n";
echo "  • User preferences\n";
echo "  • Learning from interactions\n";
echo "  • Custom settings\n";
echo "  • Behavioral patterns\n\n";

echo "✓ Knowledge Management:\n";
echo "  • Important facts\n";
echo "  • Reference information\n";
echo "  • Lessons learned\n";
echo "  • Best practices\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "✓ Memory tool examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Tool type: 'memory_20250818'\n";
echo "• SERVER-SIDE tool with file storage\n";
echo "• Operations: create, read, update, delete, list\n";
echo "• Files stored in markdown format\n";
echo "• Persists across conversations\n";
echo "• Perfect with context editing for unlimited conversations\n";
echo "• Use for: Preferences, project state, knowledge management\n";
echo "• Organize with clear naming and folder structure\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • examples/context_editing.php - Context management with memory\n";
echo "  • examples/tool_use_overview.php - Tool use basics\n";

