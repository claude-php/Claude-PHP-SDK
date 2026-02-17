#!/usr/bin/env php
<?php
/**
 * Memory Tool Example
 *
 * Demonstrates the memory tool (memory_20250818) that allows Claude to persist
 * information across conversations using a file-based memory system. Claude can
 * create, read, update, and delete memory files to maintain context over time.
 *
 * Commands:
 *   • view        — Read a file or list directory contents
 *   • create      — Create a new file with content
 *   • str_replace — Replace text within an existing file
 *   • insert      — Insert text at a specific line
 *   • delete      — Delete a file or directory
 *   • rename      — Rename or move a file
 *
 * @see https://docs.anthropic.com/en/docs/build-with-claude/tool-use/memory-tool
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;
use ClaudePhp\Types\ModelParam;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Memory Tool Examples ===\n\n";

// ---------------------------------------------------------------------------
// Example 1: Using the memory tool to store user preferences
// ---------------------------------------------------------------------------
echo "Example 1: Storing User Preferences\n";
echo "-------------------------------------\n\n";

try {
    $response = $client->messages()->create([
        'model'      => ModelParam::MODEL_CLAUDE_SONNET_4_5,
        'max_tokens' => 2048,
        'tools'      => [
            [
                'name' => 'memory',
                'type' => 'memory_20250818',
            ],
        ],
        'system'     => 'You are a helpful assistant with persistent memory. '
            . 'Store important user information for future reference.',
        'messages'   => [
            [
                'role'    => 'user',
                'content' => 'My name is Alice, I prefer dark mode, and I work in software engineering. '
                    . 'Please remember these preferences.',
            ],
        ],
    ]);

    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo "Response:\n{$block['text']}\n";
        } elseif ($block['type'] === 'tool_use' && $block['name'] === 'memory') {
            $cmd = $block['input']['command'] ?? 'unknown';
            echo "Memory command: {$cmd}\n";
            if ($cmd === 'create') {
                echo "  Path: {$block['input']['path']}\n";
                echo "  Content preview: " . substr($block['input']['file_text'], 0, 100) . "...\n";
            }
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// ---------------------------------------------------------------------------
// Example 2: Recalling stored information in a new conversation
// ---------------------------------------------------------------------------
echo "Example 2: Recalling Stored Information\n";
echo "-----------------------------------------\n\n";

try {
    $response = $client->messages()->create([
        'model'      => ModelParam::MODEL_CLAUDE_SONNET_4_5,
        'max_tokens' => 1024,
        'tools'      => [
            [
                'name' => 'memory',
                'type' => 'memory_20250818',
            ],
        ],
        'system'     => 'You are a helpful assistant with persistent memory. '
            . 'Look up stored information about the user before responding.',
        'messages'   => [
            [
                'role'    => 'user',
                'content' => 'What do you remember about me?',
            ],
        ],
    ]);

    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo "Response:\n{$block['text']}\n";
        } elseif ($block['type'] === 'tool_use' && $block['name'] === 'memory') {
            $cmd = $block['input']['command'] ?? 'unknown';
            echo "Memory command: {$cmd}\n";
            if (isset($block['input']['path'])) {
                echo "  Path: {$block['input']['path']}\n";
            }
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// ---------------------------------------------------------------------------
// Example 3: Memory tool with Beta API and allowed_callers
// ---------------------------------------------------------------------------
echo "Example 3: Memory Tool via Beta API with allowed_callers\n";
echo "---------------------------------------------------------\n\n";
echo "The Beta version supports being invoked by code execution tools.\n\n";

try {
    $response = $client->beta()->messages()->create([
        'model'      => ModelParam::MODEL_CLAUDE_SONNET_4_5,
        'max_tokens' => 2048,
        'tools'      => [
            [
                'name'            => 'memory',
                'type'            => 'memory_20250818',
                'allowed_callers' => ['direct'],
            ],
        ],
        'messages'   => [
            [
                'role'    => 'user',
                'content' => 'Create a memory file with today\'s date and the note: "SDK updated to v0.6.0"',
            ],
        ],
    ]);

    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo "Response:\n{$block['text']}\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// ---------------------------------------------------------------------------
// Example 4: Memory tool command reference
// ---------------------------------------------------------------------------
echo "Example 4: Memory Tool Command Reference\n";
echo "------------------------------------------\n\n";

echo "Available commands:\n\n";

$commands = [
    'view'        => "Read a file or list directory:\n"
        . "  ['command' => 'view', 'path' => '/memory/user.md']\n"
        . "  ['command' => 'view', 'path' => '/memory/', 'view_range' => [1, 10]]",
    'create'      => "Create a new file:\n"
        . "  ['command' => 'create', 'path' => '/memory/prefs.md', 'file_text' => '# Preferences\n...']",
    'str_replace' => "Replace text in a file:\n"
        . "  ['command' => 'str_replace', 'path' => '/memory/notes.md', "
        . "'old_str' => 'v0.5.3', 'new_str' => 'v0.6.0']",
    'insert'      => "Insert text at a line:\n"
        . "  ['command' => 'insert', 'path' => '/memory/list.md', 'insert_line' => 5, "
        . "'insert_text' => '- New item']",
    'delete'      => "Delete a file or directory:\n"
        . "  ['command' => 'delete', 'path' => '/memory/old_data.md']",
    'rename'      => "Rename or move a file:\n"
        . "  ['command' => 'rename', 'old_path' => '/memory/temp.md', 'new_path' => '/memory/final.md']",
];

foreach ($commands as $cmd => $desc) {
    echo "  {$cmd}:\n";
    echo "    {$desc}\n\n";
}

echo "Tips:\n";
echo "  • Files persist within the session scope configured by the API\n";
echo "  • Organize memories under a consistent directory structure\n";
echo "  • Use create for new notes, str_replace for incremental updates\n";
echo "  • view with view_range is efficient for large files\n";
echo "  • input_examples parameter guides the model on tool usage patterns\n";
