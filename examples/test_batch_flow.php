<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

// Create a batch
echo "Creating batch...\n";
$batch = $client->beta()->messages()->batches()->create([
    'requests' => [
        [
            'custom_id' => 'test-1',
            'params' => [
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 100,
                'messages' => [['role' => 'user', 'content' => 'Say hello']],
            ],
        ],
    ],
]);

$batchId = $batch['id'];
echo "Created: {$batchId}\n";
echo "Status: {$batch['processing_status']}\n\n";

// Immediately retrieve with beta API
echo "Retrieving with beta API...\n";
try {
    $retrieved = $client->beta()->messages()->batches()->retrieve($batchId);
    echo "Success! Status: {$retrieved['processing_status']}\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
