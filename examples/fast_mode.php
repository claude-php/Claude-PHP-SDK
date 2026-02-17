#!/usr/bin/env php
<?php
/**
 * Fast Mode (Speed Parameter) Example
 *
 * Demonstrates the `speed` parameter available in the Beta Messages API.
 * Setting speed to "fast" enables high-throughput inference with lower latency
 * at the cost of some quality. Setting it to "standard" (default) gives full
 * quality responses.
 *
 * Currently supported: claude-opus-4-6
 *
 * @see https://docs.anthropic.com/en/docs/build-with-claude/fast-mode
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;
use ClaudePhp\Types\ModelParam;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Fast Mode (Speed Parameter) Examples ===\n\n";

$question = 'Summarize the key advantages of PHP 8.1 readonly properties in 2-3 sentences.';

// ---------------------------------------------------------------------------
// Example 1: Standard speed (default)
// ---------------------------------------------------------------------------
echo "Example 1: Standard Speed (Default)\n";
echo "-------------------------------------\n\n";

$startTime = microtime(true);
try {
    $response = $client->beta()->messages()->create([
        'model'      => ModelParam::MODEL_CLAUDE_OPUS_4_6,
        'max_tokens' => 512,
        'speed'      => 'standard',
        'messages'   => [
            ['role' => 'user', 'content' => $question],
        ],
    ]);

    $elapsed = round((microtime(true) - $startTime) * 1000);
    echo "Question: {$question}\n\n";

    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo "Answer:\n{$block['text']}\n";
        }
    }

    echo "\nStats:\n";
    echo "  Speed:         standard\n";
    echo "  Time:          {$elapsed} ms\n";
    echo "  Input tokens:  {$response->usage->input_tokens}\n";
    echo "  Output tokens: {$response->usage->output_tokens}\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// ---------------------------------------------------------------------------
// Example 2: Fast speed
// ---------------------------------------------------------------------------
echo "Example 2: Fast Speed\n";
echo "----------------------\n\n";

$startTime = microtime(true);
try {
    $response = $client->beta()->messages()->create([
        'model'      => ModelParam::MODEL_CLAUDE_OPUS_4_6,
        'max_tokens' => 512,
        'speed'      => 'fast',
        'messages'   => [
            ['role' => 'user', 'content' => $question],
        ],
    ]);

    $elapsed = round((microtime(true) - $startTime) * 1000);

    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo "Answer:\n{$block['text']}\n";
        }
    }

    echo "\nStats:\n";
    echo "  Speed:         fast\n";
    echo "  Time:          {$elapsed} ms\n";
    echo "  Input tokens:  {$response->usage->input_tokens}\n";
    echo "  Output tokens: {$response->usage->output_tokens}\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// ---------------------------------------------------------------------------
// Example 3: Fast mode with streaming
// ---------------------------------------------------------------------------
echo "Example 3: Fast Mode with Streaming\n";
echo "-------------------------------------\n\n";

try {
    $stream = $client->beta()->messages()->stream([
        'model'      => ModelParam::MODEL_CLAUDE_OPUS_4_6,
        'max_tokens' => 512,
        'speed'      => 'fast',
        'messages'   => [
            ['role' => 'user', 'content' => 'List three use cases for fast inference mode.'],
        ],
    ]);

    echo "Streaming (fast mode):\n";
    foreach ($stream as $event) {
        $data = $event['data'] ?? null;
        if (!$data) {
            continue;
        }
        if ($data['type'] === 'content_block_delta'
            && ($data['delta']['type'] ?? '') === 'text_delta'
        ) {
            echo $data['delta']['text'];
            flush();
        }
    }
    echo "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// ---------------------------------------------------------------------------
// Example 4: Fast mode guidance
// ---------------------------------------------------------------------------
echo "Example 4: When to Use Fast Mode\n";
echo "----------------------------------\n\n";

echo "Use 'fast' speed when:\n";
echo "  • High-volume, latency-sensitive applications\n";
echo "  • Simple classification or routing tasks\n";
echo "  • Real-time chat where response speed matters more than depth\n";
echo "  • Cost reduction is a priority and some quality trade-off is acceptable\n\n";

echo "Use 'standard' speed when:\n";
echo "  • Complex reasoning or analysis tasks\n";
echo "  • Code generation that must be correct\n";
echo "  • Tasks where quality is paramount\n";
echo "  • Extended thinking is enabled (always use standard with thinking)\n\n";

echo "Speed parameter notes:\n";
echo "  • Available only via the Beta Messages API (beta()->messages())\n";
echo "  • Currently supported: claude-opus-4-6\n";
echo "  • Default is 'standard' if not specified\n";
echo "  • Works with streaming, tool use, and multi-turn conversations\n";
