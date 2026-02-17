#!/usr/bin/env php
<?php
/**
 * Tutorial 17: New Features in v0.6.0
 *
 * Demonstrates the features added in v0.6.0 for parity with Python SDK v0.80.0:
 *
 *  Part 1 — Adaptive Thinking (claude-opus-4-6, Feb 2026)
 *  Part 2 — Speed / Fast-Mode parameter (Beta Messages)
 *  Part 3 — Code Execution tool (GA + Beta REPL persistence)
 *  Part 4 — Memory Tool (file-based persistence)
 *  Part 5 — Web Fetch Tool (URL content retrieval)
 *  Part 6 — Model Constants reference
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../helpers.php';

use ClaudePhp\ClaudePhp;
use ClaudePhp\Types\ModelParam;

loadEnv(__DIR__ . '/../../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Tutorial 17: v0.6.0 Features Demo ===\n";
echo "SDK Version: " . ClaudePhp::SDK_VERSION . "\n\n";

// =============================================================================
// PART 1: Adaptive Thinking
// =============================================================================
echo str_repeat("=", 80) . "\n";
echo "PART 1: Adaptive Thinking\n";
echo str_repeat("=", 80) . "\n\n";

echo "What is adaptive thinking?\n";
echo "  - New 'adaptive' type introduced with claude-opus-4-6 (Feb 2026)\n";
echo "  - Model decides automatically whether to think, and how much\n";
echo "  - Unlike 'enabled' (always thinks with a fixed budget)\n";
echo "  - Cost-efficient: simple tasks use fewer thinking tokens\n\n";

echo "Thinking mode comparison:\n";
echo "  type: 'disabled'  — No extended thinking (fastest, cheapest)\n";
echo "  type: 'enabled'   — Always thinks; you set budget_tokens\n";
echo "  type: 'adaptive'  — Model decides; no budget_tokens needed\n\n";

// Show the three modes side-by-side
$thinkingConfigs = [
    ['label' => 'Disabled', 'config' => ['type' => 'disabled']],
    ['label' => 'Enabled (budget: 5000)', 'config' => ['type' => 'enabled', 'budget_tokens' => 5000]],
    ['label' => 'Adaptive (recommended for Opus 4.6)', 'config' => ['type' => 'adaptive']],
];

foreach ($thinkingConfigs as $tc) {
    echo "Config [{$tc['label']}]:\n";
    echo "  PHP: 'thinking' => " . json_encode($tc['config'], JSON_PRETTY_PRINT) . "\n\n";
}

// Live demo — adaptive thinking on a complex question
echo "Live demo with adaptive thinking:\n";
echo "  Question: What is the P vs NP problem and why does it matter?\n\n";

try {
    $response = $client->messages()->create([
        'model'      => ModelParam::MODEL_CLAUDE_OPUS_4_6,
        'max_tokens' => 2048,
        'thinking'   => ['type' => 'adaptive'],
        'messages'   => [
            ['role' => 'user', 'content' => 'Briefly explain P vs NP in 3 sentences.'],
        ],
    ]);

    $hasThinking = false;
    foreach ($response->content as $block) {
        if ($block['type'] === 'thinking') {
            $hasThinking = true;
            echo "  [Thinking detected - " . strlen($block['thinking']) . " chars]\n";
        } elseif ($block['type'] === 'text') {
            echo "  Answer: {$block['text']}\n";
        }
    }

    if (!$hasThinking) {
        echo "  (No thinking block — model decided this question was simple enough)\n";
    }

    echo "\n  Tokens used: {$response->usage->input_tokens} in, {$response->usage->output_tokens} out\n";
} catch (\Exception $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}

// =============================================================================
// PART 2: Speed / Fast-mode Parameter
// =============================================================================
echo "\n" . str_repeat("=", 80) . "\n";
echo "PART 2: Speed / Fast-mode Parameter\n";
echo str_repeat("=", 80) . "\n\n";

echo "The 'speed' parameter (Beta Messages only) controls inference mode:\n";
echo "  speed: 'standard' — Full quality responses (default)\n";
echo "  speed: 'fast'     — High-throughput with lower latency\n\n";

echo "Best use cases for fast mode:\n";
echo "  - High-volume classification or tagging tasks\n";
echo "  - Real-time chat where speed matters more than depth\n";
echo "  - Cost reduction when perfect quality isn't required\n\n";

$testQuestion = 'Classify this as positive, negative, or neutral: "The new SDK update is fantastic!"';

echo "Demo: Sentiment classification (same question, two speeds)\n\n";

foreach (['standard', 'fast'] as $speed) {
    $start = microtime(true);
    try {
        $response = $client->beta()->messages()->create([
            'model'      => ModelParam::MODEL_CLAUDE_OPUS_4_6,
            'max_tokens' => 64,
            'speed'      => $speed,
            'messages'   => [['role' => 'user', 'content' => $testQuestion]],
        ]);

        $ms = round((microtime(true) - $start) * 1000);
        $answer = '';
        foreach ($response->content as $block) {
            if ($block['type'] === 'text') {
                $answer = trim($block['text']);
            }
        }

        echo "  Speed '{$speed}': \"{$answer}\" [{$ms} ms, {$response->usage->output_tokens} tokens]\n";
    } catch (\Exception $e) {
        echo "  Speed '{$speed}': Error — " . $e->getMessage() . "\n";
    }
}

// =============================================================================
// PART 3: Code Execution Tool
// =============================================================================
echo "\n" . str_repeat("=", 80) . "\n";
echo "PART 3: Code Execution Tool\n";
echo str_repeat("=", 80) . "\n\n";

echo "Code execution tools allow Claude to write and run code:\n";
echo "  code_execution_20250522 — Initial GA version\n";
echo "  code_execution_20250825 — Enhanced sandbox (GA)\n";
echo "  code_execution_20260120 — REPL state persistence (Beta)\n\n";

echo "Key feature of 20260120: REPL state is maintained across tool calls.\n";
echo "  - Define a variable in call 1, use it in call 2\n";
echo "  - Like a persistent Python interpreter session\n\n";

echo "Demo: Using code_execution_20250825 to generate data\n";
try {
    $response = $client->messages()->create([
        'model'      => ModelParam::MODEL_CLAUDE_SONNET_4_5,
        'max_tokens' => 2048,
        'tools'      => [
            [
                'name' => 'code_execution',
                'type' => 'code_execution_20250825',
            ],
        ],
        'messages'   => [
            [
                'role'    => 'user',
                'content' => 'Write and run a Python one-liner that prints the sum of squares from 1 to 10.',
            ],
        ],
    ]);

    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo "  Response: {$block['text']}\n";
        } elseif ($block['type'] === 'tool_use') {
            echo "  Code executed:\n    " . str_replace("\n", "\n    ", $block['input']['code'] ?? '') . "\n";
        }
    }
} catch (\Exception $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}

echo "\nTool result types:\n";
echo "  code_execution_result:\n";
echo "    - stdout, stderr, return_code\n";
echo "    - content[]: CodeExecutionOutputBlock (file_id for generated files)\n";
echo "  code_execution_tool_result_error:\n";
echo "    - error_code: timeout | execution_error | internal_error\n";

// =============================================================================
// PART 4: Memory Tool
// =============================================================================
echo "\n" . str_repeat("=", 80) . "\n";
echo "PART 4: Memory Tool\n";
echo str_repeat("=", 80) . "\n\n";

echo "The memory tool (memory_20250818) lets Claude persist information\n";
echo "across conversations using a file-based storage system.\n\n";

echo "Available commands:\n";
echo "  view        — Read a file or list directory contents\n";
echo "  create      — Create a new file with content\n";
echo "  str_replace — Replace text within an existing file\n";
echo "  insert      — Insert text at a specific line number\n";
echo "  delete      — Delete a file or directory\n";
echo "  rename      — Rename or move a file\n\n";

echo "Demo: Store and recall user preferences\n";
try {
    // Turn 1: Store preferences
    $turn1 = $client->messages()->create([
        'model'      => ModelParam::MODEL_CLAUDE_SONNET_4_5,
        'max_tokens' => 1024,
        'tools'      => [['name' => 'memory', 'type' => 'memory_20250818']],
        'system'     => 'You are a helpful assistant with persistent memory. '
            . 'Store important user preferences for future reference.',
        'messages'   => [
            [
                'role'    => 'user',
                'content' => 'I prefer concise responses, use PHP 8.1+, and work in fintech. Remember this.',
            ],
        ],
    ]);

    foreach ($turn1->content as $block) {
        if ($block['type'] === 'tool_use' && $block['name'] === 'memory') {
            $cmd = $block['input']['command'] ?? 'unknown';
            echo "  Memory command: {$cmd}\n";
            if (isset($block['input']['path'])) {
                echo "  Path: {$block['input']['path']}\n";
            }
        } elseif ($block['type'] === 'text') {
            echo "  Response: {$block['text']}\n";
        }
    }
} catch (\Exception $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}

echo "\nBeta memory tool (BetaMemoryTool20250818Param) adds:\n";
echo "  - allowed_callers: control which callers can invoke the tool\n";
echo "  - Can be invoked by code execution tools in multi-tool workflows\n";

// =============================================================================
// PART 5: Web Fetch Tool
// =============================================================================
echo "\n" . str_repeat("=", 80) . "\n";
echo "PART 5: Web Fetch Tool\n";
echo str_repeat("=", 80) . "\n\n";

echo "The web_fetch tool lets Claude retrieve content from URLs.\n\n";

echo "Tool versions:\n";
echo "  web_fetch_20250910 — GA, available via messages()->create()\n";
echo "  web_fetch_20260209 — Beta, adds allowed_callers for multi-agent workflows\n\n";

echo "Key options:\n";
echo "  allowed_domains    — Whitelist of domains Claude may fetch\n";
echo "  blocked_domains    — Blacklist of domains Claude may never fetch\n";
echo "  max_uses           — Limit total fetch calls per request\n";
echo "  max_content_tokens — Cap tokens used by fetched content\n";
echo "  citations          — Enable citations from fetched content\n\n";

echo "Demo: Fetch a URL with domain restrictions\n";
try {
    $response = $client->messages()->create([
        'model'      => ModelParam::MODEL_CLAUDE_SONNET_4_5,
        'max_tokens' => 512,
        'tools'      => [
            [
                'name'            => 'web_fetch',
                'type'            => 'web_fetch_20250910',
                'allowed_domains' => ['example.com'],
                'max_uses'        => 1,
            ],
        ],
        'messages'   => [
            ['role' => 'user', 'content' => 'Fetch https://www.example.com and give me the page title.'],
        ],
    ]);

    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo "  Response: {$block['text']}\n";
        } elseif ($block['type'] === 'tool_use') {
            echo "  Fetching: {$block['input']['url']}\n";
        }
    }
} catch (\Exception $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}

echo "\nError codes (WebFetchToolResultErrorCode):\n";
echo "  invalid_tool_input    — Malformed URL\n";
echo "  url_not_allowed       — Blocked domain\n";
echo "  url_not_accessible    — HTTP 4xx/5xx response\n";
echo "  max_uses_exceeded     — Limit reached\n";

// =============================================================================
// PART 6: Model Constants Reference
// =============================================================================
echo "\n" . str_repeat("=", 80) . "\n";
echo "PART 6: Model Constants Reference\n";
echo str_repeat("=", 80) . "\n\n";

echo "ModelParam now provides typed constants for all current models:\n\n";

$models = [
    'Claude 4.6 (Feb 2026)' => [
        ModelParam::MODEL_CLAUDE_OPUS_4_6   => 'claude-opus-4-6',
        ModelParam::MODEL_CLAUDE_SONNET_4_6 => 'claude-sonnet-4-6',
    ],
    'Claude 4.5 (Nov 2025)' => [
        ModelParam::MODEL_CLAUDE_OPUS_4_5   => 'claude-opus-4-5-20251101',
        ModelParam::MODEL_CLAUDE_SONNET_4_5 => 'claude-sonnet-4-5-20250929',
        ModelParam::MODEL_CLAUDE_HAIKU_4_5  => 'claude-haiku-4-5-20251001',
    ],
    'Claude 3.7 (Feb 2025)' => [
        ModelParam::MODEL_CLAUDE_3_7_SONNET_LATEST   => 'claude-3-7-sonnet-latest',
        ModelParam::MODEL_CLAUDE_3_7_SONNET_20250219 => 'claude-3-7-sonnet-20250219',
    ],
    'Claude 3.5 (2024)' => [
        ModelParam::MODEL_CLAUDE_3_5_HAIKU_LATEST   => 'claude-3-5-haiku-latest',
        ModelParam::MODEL_CLAUDE_3_5_HAIKU_20241022 => 'claude-3-5-haiku-20241022',
    ],
    'Claude 3 legacy' => [
        ModelParam::MODEL_CLAUDE_3_OPUS_LATEST   => 'claude-3-opus-latest',
        ModelParam::MODEL_CLAUDE_3_HAIKU_20240307 => 'claude-3-haiku-20240307',
    ],
];

foreach ($models as $family => $constants) {
    echo "{$family}:\n";
    foreach ($constants as $constant => $value) {
        echo "  ModelParam::{$this_const($constant)} = '{$constant}'\n";
    }
    echo "\n";
}

// Helper to find constant name from value
function this_const(string $value): string {
    $reflection = new ReflectionClass(ModelParam::class);
    foreach ($reflection->getConstants() as $name => $val) {
        if ($val === $value) {
            return "MODEL_" . substr($name, 12); // strip "MODEL_" prefix and return
        }
    }
    return $value;
}

// Simpler display — just print usage pattern
echo "Usage pattern:\n";
echo "  // Instead of:\n";
echo "  \$client->messages()->create(['model' => 'claude-opus-4-6', ...]);\n\n";
echo "  // Use typed constants:\n";
echo "  use ClaudePhp\\Types\\ModelParam;\n";
echo "  \$client->messages()->create(['model' => ModelParam::MODEL_CLAUDE_OPUS_4_6, ...]);\n\n";
echo "  // Benefits:\n";
echo "  //  - IDE autocomplete\n";
echo "  //  - Typo-safe\n";
echo "  //  - Easy global search/replace when models update\n";

// =============================================================================
// Summary
// =============================================================================
echo "\n" . str_repeat("=", 80) . "\n";
echo "SUMMARY: v0.6.0 Parity with Python SDK v0.80.0\n";
echo str_repeat("=", 80) . "\n\n";

$summary = [
    'Adaptive Thinking'     => "thinking: ['type' => 'adaptive'] — model decides how much to think",
    'Speed Parameter'       => "speed: 'fast' | 'standard' via beta()->messages()",
    'output_config in GA'   => "output_config: ['effort' => 'high|medium|low'] via messages()",
    'Model Constants'       => 'ModelParam::MODEL_CLAUDE_OPUS_4_6, MODEL_CLAUDE_SONNET_4_6, ...',
    'Code Execution Tool'   => 'code_execution_20250522 / 20250825 (GA), 20260120 (Beta REPL)',
    'Memory Tool'           => 'memory_20250818 — view/create/str_replace/insert/delete/rename',
    'Web Fetch Tool'        => 'web_fetch_20250910 (GA), web_fetch_20260209 (Beta)',
    'Web Search v2'         => 'web_search_20260209 (Beta) — with allowed_callers support',
];

foreach ($summary as $feature => $description) {
    echo sprintf("  %-24s %s\n", $feature . ':', $description);
}

echo "\nRelated resources:\n";
echo "  examples/adaptive_thinking.php  — Adaptive thinking demo\n";
echo "  examples/fast_mode.php          — Speed parameter examples\n";
echo "  examples/code_execution.php     — Code execution examples\n";
echo "  examples/memory_tool.php        — Memory tool examples\n";
echo "  examples/web_fetch.php          — Web fetch examples\n";
echo "  CHANGELOG.md                    — Full v0.6.0 changelog\n";
