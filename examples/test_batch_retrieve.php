<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

// Try to retrieve with beta API
echo "Testing beta API retrieve:\n";
try {
    $batch = $client->beta()->messages()->batches()->retrieve('msgbatch_01S2xWPH3HHoUPi9arNXmjtN');
    echo "Success! Status: " . $batch['processing_status'] . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nTesting non-beta API retrieve:\n";
try {
    $batch = $client->messages()->batches()->retrieve('msgbatch_01S2xWPH3HHoUPi9arNXmjtN');
    echo "Success! Status: " . $batch['processing_status'] . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
