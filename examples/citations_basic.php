#!/usr/bin/env php
<?php

/**
 * Basic Citations Example
 * 
 * This example demonstrates how to use the citations feature with Claude.
 * Citations allow Claude to reference specific parts of documents when answering questions.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');

$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Basic Citations Example ===\n\n";

// Create a message with a document and citations enabled
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => [
        [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'document',
                    'source' => [
                        'type' => 'text',
                        'media_type' => 'text/plain',
                        'data' => 'The grass is green. The sky is blue.',
                    ],
                    'title' => 'Color Facts',
                    'context' => 'This is a trustworthy document about colors in nature.',
                    'citations' => ['enabled' => true],
                ],
                [
                    'type' => 'text',
                    'text' => 'What color is the grass and sky?',
                ],
            ],
        ],
    ],
]);

echo "Response:\n";
print_r($response);

echo "\n\n=== Extracting Citation Information ===\n\n";

// Extract and display citations if present
if (isset($response->content)) {
    foreach ($response->content as $content) {
        if ($content['type'] === 'text') {
            echo "Text: " . $content['text'] . "\n";
            
            // Check for citations in the text content
            if (isset($content['citations']) && !empty($content['citations'])) {
                echo "\nCitations found:\n";
                foreach ($content['citations'] as $citation) {
                    echo "  - Type: " . $citation['type'] . "\n";
                    if (isset($citation['document_title'])) {
                        echo "    Document: " . $citation['document_title'] . "\n";
                    }
                    if (isset($citation['cited_text'])) {
                        echo "    Cited text: " . $citation['cited_text'] . "\n";
                    }
                    if (isset($citation['start_char_index']) && isset($citation['end_char_index'])) {
                        echo "    Location: characters {$citation['start_char_index']}-{$citation['end_char_index']}\n";
                    }
                    echo "\n";
                }
            }
        }
    }
}

echo "\n=== Example Complete ===\n";

