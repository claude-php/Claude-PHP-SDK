#!/usr/bin/env php
<?php
/**
 * Model Comparison Examples
 * 
 * Demonstrates using different Claude models and their characteristics.
 * Matches Python SDK patterns for model selection.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Claude Model Comparison Examples ===\n\n";

// Test prompt that we'll use for all models
$testPrompt = "Explain quantum computing in exactly two sentences.";

// Example 1: Claude Sonnet 4.5 (Latest - Balanced performance)
echo "Example 1: Claude Sonnet 4.5 (Latest)\n";
echo "--------------------------------------\n";
echo "Best for: Balanced performance, general-purpose tasks\n";
echo "Prompt: {$testPrompt}\n\n";

$startTime = microtime(true);
try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            ['role' => 'user', 'content' => $testPrompt]
        ]
    ]);
    
    $elapsed = round((microtime(true) - $startTime) * 1000);
    
    echo "Response: ";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo $block['text'] . "\n";
        }
    }
    echo "\nTokens: {$response->usage->input_tokens} in, {$response->usage->output_tokens} out\n";
    echo "Time: ~{$elapsed}ms\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: Claude Haiku 4.5 (Fast and cost-effective)
echo "Example 2: Claude Haiku 4.5\n";
echo "----------------------------\n";
echo "Best for: Fast responses, high-volume tasks, cost-sensitive applications\n";
echo "Prompt: {$testPrompt}\n\n";

$startTime = microtime(true);
try {
    $response = $client->messages()->create([
        'model' => 'claude-haiku-4-5-20251001',
        'max_tokens' => 1024,
        'messages' => [
            ['role' => 'user', 'content' => $testPrompt]
        ]
    ]);
    
    $elapsed = round((microtime(true) - $startTime) * 1000);
    
    echo "Response: ";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo $block['text'] . "\n";
        }
    }
    echo "\nTokens: {$response->usage->input_tokens} in, {$response->usage->output_tokens} out\n";
    echo "Time: ~{$elapsed}ms (typically faster than Sonnet)\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 3: Claude Opus 4.1 (Most capable)
echo "Example 3: Claude Opus 4.1\n";
echo "---------------------------\n";
echo "Best for: Complex reasoning, analysis, creative tasks requiring highest quality\n";
echo "Prompt: {$testPrompt}\n\n";

$startTime = microtime(true);
try {
    $response = $client->messages()->create([
        'model' => 'claude-opus-4-1-20250805',
        'max_tokens' => 1024,
        'messages' => [
            ['role' => 'user', 'content' => $testPrompt]
        ]
    ]);
    
    $elapsed = round((microtime(true) - $startTime) * 1000);
    
    echo "Response: ";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo $block['text'] . "\n";
        }
    }
    echo "\nTokens: {$response->usage->input_tokens} in, {$response->usage->output_tokens} out\n";
    echo "Time: ~{$elapsed}ms\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 4: Model Selection Helper Function
echo "Example 4: Model Selection Helper\n";
echo "----------------------------------\n";

function selectModel(string $taskType): string {
    return match($taskType) {
        'fast', 'simple', 'classification' => 'claude-haiku-4-5-20251001',
        'complex', 'creative', 'research' => 'claude-opus-4-1-20250805',
        'balanced', 'general' => 'claude-sonnet-4-5',
        default => 'claude-sonnet-4-5',
    };
}

$tasks = [
    'fast' => 'Classify this as positive or negative: "I love this product!"',
    'complex' => 'Analyze the philosophical implications of artificial consciousness.',
    'balanced' => 'Write a Python function to calculate fibonacci numbers.'
];

foreach ($tasks as $taskType => $prompt) {
    $model = selectModel($taskType);
    echo "Task type: {$taskType}\n";
    echo "Selected model: {$model}\n";
    echo "Prompt: {$prompt}\n";
    
    try {
        $response = $client->messages()->create([
            'model' => $model,
            'max_tokens' => 500,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);
        
        echo "Response: ";
        foreach ($response->content as $block) {
            if ($block['type'] === 'text') {
                // Truncate long responses for display
                $text = $block['text'];
                if (strlen($text) > 200) {
                    $text = substr($text, 0, 200) . '...';
                }
                echo $text . "\n";
            }
        }
        echo "\n";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n\n";
    }
}

echo str_repeat("=", 80) . "\n\n";

// Example 5: Comparing with different temperatures
echo "Example 5: Temperature Comparison (Sonnet)\n";
echo "-------------------------------------------\n";

$creativePrompt = "Give me a unique name for a coffee shop.";

foreach ([0.0, 0.5, 1.0] as $temp) {
    echo "Temperature: {$temp}\n";
    try {
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 100,
            'temperature' => $temp,
            'messages' => [
                ['role' => 'user', 'content' => $creativePrompt]
            ]
        ]);
        
        echo "Result: ";
        foreach ($response->content as $block) {
            if ($block['type'] === 'text') {
                echo trim($block['text']) . "\n";
            }
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

echo str_repeat("=", 80) . "\n\n";

echo "✓ Model comparison examples completed!\n\n";
echo "Key Takeaways:\n";
echo "- Haiku: Fast, cost-effective, great for simple tasks\n";
echo "- Sonnet: Balanced performance, good for most applications\n";
echo "- Opus: Highest quality, best for complex reasoning\n";
echo "- Temperature: 0.0 = deterministic, 1.0 = creative\n";
echo "- Model aliases (like 'claude-sonnet-4-5') auto-update to latest version\n";

