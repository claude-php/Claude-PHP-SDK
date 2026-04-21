<?php

require_once __DIR__ . '/helpers.php';

$client = createClient();

// Top-level cache_control enables automatic caching for the entire request.
// The API will cache the conversation context for faster follow-up requests.
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-6',
    'max_tokens' => 200,
    'cache_control' => ['type' => 'ephemeral'],
    'system' => 'You are a helpful assistant that remembers context from previous messages.',
    'messages' => [
        ['role' => 'user', 'content' => 'What are the three laws of thermodynamics? Be brief.'],
    ],
]);

echo "Response:\n";
foreach ($response->content as $block) {
    if ('text' === ($block['type'] ?? '')) {
        echo $block['text'] . "\n";
    }
}

echo "\nUsage:\n";
echo "  Input tokens: {$response->usage->input_tokens}\n";
echo "  Output tokens: {$response->usage->output_tokens}\n";
if ($response->usage->cache_creation_input_tokens) {
    echo "  Cache creation tokens: {$response->usage->cache_creation_input_tokens}\n";
}
if ($response->usage->cache_read_input_tokens) {
    echo "  Cache read tokens: {$response->usage->cache_read_input_tokens}\n";
}
