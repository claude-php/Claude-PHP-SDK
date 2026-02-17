#!/usr/bin/env php
<?php
/**
 * Adaptive Thinking Example
 *
 * Demonstrates the "adaptive" thinking mode introduced with Claude Opus 4.6 (Feb 2026).
 * With adaptive thinking, the model automatically decides whether and how much extended
 * thinking to apply based on the complexity of each request — no need to manually tune
 * a budget_tokens value.
 *
 * Supported models: claude-opus-4-6, claude-sonnet-4-6, and other Claude 4+ models.
 *
 * @see https://docs.anthropic.com/en/docs/build-with-claude/extended-thinking
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;
use ClaudePhp\Types\ModelParam;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Adaptive Thinking Examples ===\n\n";

// ---------------------------------------------------------------------------
// Example 1: Adaptive thinking for a simple question
// ---------------------------------------------------------------------------
echo "Example 1: Adaptive Thinking — Simple Question\n";
echo "------------------------------------------------\n";
echo "The model decides on its own whether thinking is needed.\n\n";

try {
    $response = $client->messages()->create([
        'model' => ModelParam::MODEL_CLAUDE_OPUS_4_6,
        'max_tokens' => 4096,
        'thinking' => [
            'type' => 'adaptive',
        ],
        'messages' => [
            ['role' => 'user', 'content' => 'What is the capital of France?'],
        ],
    ]);

    foreach ($response->content as $block) {
        if ($block['type'] === 'thinking') {
            echo "Thinking: " . substr($block['thinking'], 0, 200) . "...\n\n";
        } elseif ($block['type'] === 'text') {
            echo "Answer: {$block['text']}\n";
        }
    }

    echo "\nUsage:\n";
    echo "  Input tokens:  {$response->usage->input_tokens}\n";
    echo "  Output tokens: {$response->usage->output_tokens}\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// ---------------------------------------------------------------------------
// Example 2: Adaptive thinking for a complex problem
// ---------------------------------------------------------------------------
echo "Example 2: Adaptive Thinking — Complex Problem\n";
echo "------------------------------------------------\n";
echo "For harder tasks the model allocates more reasoning automatically.\n\n";

try {
    $response = $client->messages()->create([
        'model' => ModelParam::MODEL_CLAUDE_OPUS_4_6,
        'max_tokens' => 16000,
        'thinking' => [
            'type' => 'adaptive',
        ],
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Prove that there are infinitely many prime numbers using Euclid\'s method, '
                    . 'then explain the proof in simple terms.',
            ],
        ],
    ]);

    $thinkingShown = false;
    foreach ($response->content as $block) {
        if ($block['type'] === 'thinking' && !$thinkingShown) {
            echo "Thinking excerpt:\n";
            echo substr($block['thinking'], 0, 400) . "...\n\n";
            $thinkingShown = true;
        } elseif ($block['type'] === 'text') {
            echo "Answer:\n";
            echo $block['text'] . "\n";
        }
    }

    echo "\nUsage:\n";
    echo "  Input tokens:  {$response->usage->input_tokens}\n";
    echo "  Output tokens: {$response->usage->output_tokens}\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// ---------------------------------------------------------------------------
// Example 3: Comparing adaptive vs. enabled thinking
// ---------------------------------------------------------------------------
echo "Example 3: Adaptive vs. Enabled Thinking\n";
echo "-----------------------------------------\n\n";

echo "Thinking mode comparison:\n";
echo "  • type: 'enabled'   — You set budget_tokens; model always thinks\n";
echo "  • type: 'adaptive'  — Model decides whether to think and how much\n";
echo "  • type: 'disabled'  — No extended thinking (default)\n\n";

echo "When to use adaptive:\n";
echo "  • Mixed workloads with varying complexity\n";
echo "  • When you want optimal cost vs. quality tradeoff automatically\n";
echo "  • Recommended default for claude-opus-4-6\n\n";

echo "When to use enabled:\n";
echo "  • Consistently complex tasks needing full reasoning\n";
echo "  • When you need predictable token usage\n";
echo "  • Benchmarking and evaluation\n\n";

// ---------------------------------------------------------------------------
// Example 4: Streaming with adaptive thinking
// ---------------------------------------------------------------------------
echo "Example 4: Streaming with Adaptive Thinking\n";
echo "---------------------------------------------\n\n";

try {
    $stream = $client->messages()->stream([
        'model' => ModelParam::MODEL_CLAUDE_OPUS_4_6,
        'max_tokens' => 4096,
        'thinking' => [
            'type' => 'adaptive',
        ],
        'messages' => [
            ['role' => 'user', 'content' => 'What are three key benefits of using adaptive thinking?'],
        ],
    ]);

    $inThinkingBlock = false;
    $inTextBlock = false;

    foreach ($stream as $event) {
        $data = $event['data'] ?? null;
        if (!$data) {
            continue;
        }

        if ($data['type'] === 'content_block_start') {
            $blockType = $data['content_block']['type'] ?? '';
            if ($blockType === 'thinking') {
                $inThinkingBlock = true;
                echo "[Thinking...]\n";
            } elseif ($blockType === 'text') {
                $inTextBlock = true;
                echo "[Response]\n";
            }
        } elseif ($data['type'] === 'content_block_delta') {
            $delta = $data['delta'] ?? [];
            if ($inTextBlock && $delta['type'] === 'text_delta') {
                echo $delta['text'];
            }
        } elseif ($data['type'] === 'content_block_stop') {
            if ($inThinkingBlock) {
                $inThinkingBlock = false;
                echo "\n";
            } elseif ($inTextBlock) {
                $inTextBlock = false;
                echo "\n";
            }
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "Key Takeaways:\n";
echo "  • 'adaptive' thinking is the recommended mode for claude-opus-4-6\n";
echo "  • The model allocates thinking tokens proportional to task complexity\n";
echo "  • No budget_tokens parameter required — model decides autonomously\n";
echo "  • Works seamlessly with streaming, tool use, and multi-turn conversations\n";
echo "  • Cost-efficient: simple tasks use fewer thinking tokens automatically\n";
