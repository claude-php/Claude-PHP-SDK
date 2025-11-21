#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;
use ClaudePhp\Lib\Streaming\MessageStream;

// Basic streaming example - equivalent to Python SDK example
echo "=== Basic Streaming Example ===\n\n";

$client = createClient();

try {
    $rawStream = $client->messages()->stream([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Hello',
            ],
        ],
    ]);

    $stream = new MessageStream($rawStream);
    
    // Stream text as it arrives (equivalent to Python's text_stream)
    echo $stream->textStream();
    
    echo "\n\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "✓ Basic streaming completed successfully\n";
