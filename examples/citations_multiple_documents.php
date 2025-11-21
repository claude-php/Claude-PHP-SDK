#!/usr/bin/env php
<?php

/**
 * Multiple Documents Citations Example
 * 
 * This example demonstrates how to use citations with multiple documents.
 * Claude can cite from different sources when answering questions.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');

$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Multiple Documents Citations Example ===\n\n";

// Create a message with multiple documents and citations enabled
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 2048,
    'messages' => [
        [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'document',
                    'source' => [
                        'type' => 'text',
                        'media_type' => 'text/plain',
                        'data' => 'PHP is a popular general-purpose scripting language that is especially suited to web development. It was created by Rasmus Lerdorf in 1993.',
                    ],
                    'title' => 'PHP History',
                    'context' => 'Information about PHP programming language history.',
                    'citations' => ['enabled' => true],
                ],
                [
                    'type' => 'document',
                    'source' => [
                        'type' => 'text',
                        'media_type' => 'text/plain',
                        'data' => 'Python is a high-level, interpreted programming language. It was created by Guido van Rossum and first released in 1991.',
                    ],
                    'title' => 'Python History',
                    'context' => 'Information about Python programming language history.',
                    'citations' => ['enabled' => true],
                ],
                [
                    'type' => 'document',
                    'source' => [
                        'type' => 'text',
                        'media_type' => 'text/plain',
                        'data' => 'JavaScript is a high-level, interpreted programming language. It was created by Brendan Eich in 1995 while he was an engineer at Netscape.',
                    ],
                    'title' => 'JavaScript History',
                    'context' => 'Information about JavaScript programming language history.',
                    'citations' => ['enabled' => true],
                ],
                [
                    'type' => 'text',
                    'text' => 'Which programming language was created first, and who created it? Provide citations for your answer.',
                ],
            ],
        ],
    ],
]);

echo "Response:\n";
echo "Model: " . $response->model . "\n";
echo "Stop Reason: " . $response->stop_reason . "\n\n";

// Display content and citations
if (isset($response->content)) {
    foreach ($response->content as $idx => $content) {
        echo "Content Block #{$idx}:\n";
        echo "Type: " . $content['type'] . "\n";
        
        if ($content['type'] === 'text') {
            echo "Text: " . $content['text'] . "\n";
            
            // Display citations if present
            if (isset($content['citations']) && !empty($content['citations'])) {
                echo "\nCitations:\n";
                foreach ($content['citations'] as $citationIdx => $citation) {
                    echo "  Citation #{$citationIdx}:\n";
                    echo "    Type: " . $citation['type'] . "\n";
                    if (isset($citation['document_title'])) {
                        echo "    Document: " . $citation['document_title'] . "\n";
                    }
                    if (isset($citation['cited_text'])) {
                        echo "    Cited text: " . $citation['cited_text'] . "\n";
                    }
                    echo "\n";
                }
            }
        }
        echo "\n";
    }
}

// Display usage information
echo "Usage:\n";
echo "  Input tokens: " . $response->usage->input_tokens . "\n";
echo "  Output tokens: " . $response->usage->output_tokens . "\n";

echo "\n=== Example Complete ===\n";

