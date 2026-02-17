#!/usr/bin/env php
<?php
/**
 * Code Execution Tool Example
 *
 * Demonstrates the code execution tools that allow Claude to write and run
 * code in a sandboxed environment, producing text output and file artifacts.
 *
 * Tool versions:
 *   • code_execution_20250522 — Initial GA release
 *   • code_execution_20250825 — Enhanced sandbox capabilities
 *   • code_execution_20260120 — Beta: REPL state persistence (daemon mode)
 *
 * @see https://docs.anthropic.com/en/docs/build-with-claude/tool-use/code-execution
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;
use ClaudePhp\Types\ModelParam;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Code Execution Tool Examples ===\n\n";

// ---------------------------------------------------------------------------
// Example 1: Basic code execution (v2025-08-25)
// ---------------------------------------------------------------------------
echo "Example 1: Basic Code Execution\n";
echo "---------------------------------\n\n";

try {
    $response = $client->messages()->create([
        'model'      => ModelParam::MODEL_CLAUDE_SONNET_4_5,
        'max_tokens' => 4096,
        'tools'      => [
            [
                'name' => 'code_execution',
                'type' => 'code_execution_20250825',
            ],
        ],
        'messages'   => [
            [
                'role'    => 'user',
                'content' => 'Write and run a Python script that calculates the first 10 Fibonacci numbers.',
            ],
        ],
    ]);

    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo "Response:\n{$block['text']}\n";
        } elseif ($block['type'] === 'tool_use' && $block['name'] === 'code_execution') {
            echo "Code executed:\n```python\n{$block['input']['code']}\n```\n\n";
        } elseif ($block['type'] === 'code_execution_tool_result') {
            $result = $block['content'] ?? [];
            if (isset($result['stdout'])) {
                echo "Output:\n{$result['stdout']}\n";
            }
            if (!empty($result['stderr'])) {
                echo "Stderr:\n{$result['stderr']}\n";
            }
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// ---------------------------------------------------------------------------
// Example 2: Code execution with file output
// ---------------------------------------------------------------------------
echo "Example 2: Code Execution with File Output\n";
echo "--------------------------------------------\n\n";

try {
    $response = $client->messages()->create([
        'model'      => ModelParam::MODEL_CLAUDE_SONNET_4_5,
        'max_tokens' => 4096,
        'tools'      => [
            [
                'name' => 'code_execution',
                'type' => 'code_execution_20250825',
            ],
        ],
        'messages'   => [
            [
                'role'    => 'user',
                'content' => 'Generate a simple bar chart showing sales data for Q1 2026 '
                    . '(Jan: 120, Feb: 95, Mar: 140) and save it as a PNG.',
            ],
        ],
    ]);

    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo "Response:\n{$block['text']}\n";
        } elseif ($block['type'] === 'code_execution_tool_result') {
            $result = $block['content'] ?? [];
            if (!empty($result['content'])) {
                echo "File outputs:\n";
                foreach ($result['content'] as $output) {
                    if ($output['type'] === 'code_execution_output') {
                        echo "  • File ID: {$output['file_id']}\n";
                        echo "    (Download via beta()->files()->download('{$output['file_id']}')\n";
                    }
                }
            }
            if (isset($result['stdout'])) {
                echo "Stdout:\n{$result['stdout']}\n";
            }
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// ---------------------------------------------------------------------------
// Example 3: Beta REPL state persistence (v2026-01-20)
// ---------------------------------------------------------------------------
echo "Example 3: Beta REPL State Persistence (v2026-01-20)\n";
echo "------------------------------------------------------\n\n";
echo "The 20260120 version maintains state across tool calls within a request.\n\n";

try {
    $response = $client->beta()->messages()->create([
        'model'      => ModelParam::MODEL_CLAUDE_SONNET_4_5,
        'max_tokens' => 4096,
        'tools'      => [
            [
                'name' => 'code_execution',
                'type' => 'code_execution_20260120',
            ],
        ],
        'messages'   => [
            [
                'role'    => 'user',
                'content' => 'First define a variable x = 42. Then in a second step, print x * 2.',
            ],
        ],
    ]);

    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo "Response:\n{$block['text']}\n";
        } elseif ($block['type'] === 'tool_use') {
            echo "Tool: {$block['name']} (input: " . json_encode($block['input']) . ")\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// ---------------------------------------------------------------------------
// Example 4: Code execution configuration reference
// ---------------------------------------------------------------------------
echo "Example 4: Code Execution Configuration Reference\n";
echo "---------------------------------------------------\n\n";

echo "Tool versions:\n";
echo "  • code_execution_20250522 — Initial release, GA\n";
echo "  • code_execution_20250825 — Enhanced sandbox, GA\n";
echo "  • code_execution_20260120 — REPL state persistence, Beta\n\n";

echo "Key parameters:\n";
echo "  • allowed_callers — Control which callers can invoke this tool:\n";
echo "      'direct'               — Only the model itself (default)\n";
echo "      'code_execution_20250825' — Allow invocation from another code execution tool\n";
echo "  • defer_loading  — Load tool only when referenced via tool_search\n";
echo "  • strict         — Enable strict schema validation\n\n";

echo "Result types:\n";
echo "  • code_execution_result:\n";
echo "      stdout      — Standard output from the execution\n";
echo "      stderr      — Standard error (warnings, errors)\n";
echo "      return_code — Exit code (0 = success)\n";
echo "      content[]   — File outputs (type: code_execution_output)\n\n";

echo "Error codes (CodeExecutionToolResultErrorCode):\n";
echo "  • timeout         — Execution exceeded time limit\n";
echo "  • execution_error — Runtime error or OOM kill\n";
echo "  • internal_error  — Unexpected service error\n\n";

echo "Working with file outputs:\n";
echo "  • Files are identified by file_id in code_execution_output blocks\n";
echo "  • Download files via: \$client->beta()->files()->download(\$fileId)\n";
echo "  • Files are ephemeral and expire after the session\n";
