#!/usr/bin/env php
<?php

/**
 * Citations with Context Example
 * 
 * This example demonstrates how to use the context field with citations
 * to provide additional information about document reliability.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');

$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Citations with Context Example ===\n\n";

// Sample medical research document
$medicalDocument = <<<EOT
A randomized controlled trial published in the Journal of Medicine (2024) found that:
- Regular exercise reduces the risk of heart disease by 30%
- Participants who exercised 150 minutes per week showed significant improvements
- The study followed 10,000 participants over 5 years
- Results were statistically significant (p < 0.001)
EOT;

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
                        'data' => $medicalDocument,
                    ],
                    'title' => 'Medical Research Study - 2024',
                    'context' => 'This is a peer-reviewed study from a reputable medical journal. The research was conducted by certified medical researchers and followed standard clinical trial protocols.',
                    'citations' => ['enabled' => true],
                ],
                [
                    'type' => 'text',
                    'text' => 'What does the research say about exercise and heart disease? Please cite your sources.',
                ],
            ],
        ],
    ],
]);

echo "Question: What does the research say about exercise and heart disease?\n\n";
echo "Response:\n";

// Display the response with citations
if (isset($response->content)) {
    foreach ($response->content as $content) {
        if ($content['type'] === 'text') {
            echo $content['text'] . "\n\n";
            
            // Display detailed citation information
            if (isset($content['citations']) && !empty($content['citations'])) {
                echo "=== Citation Details ===\n";
                foreach ($content['citations'] as $idx => $citation) {
                    echo "\nCitation " . ($idx + 1) . ":\n";
                    echo "  Type: " . $citation['type'] . "\n";
                    
                    if (isset($citation['document_title'])) {
                        echo "  Source Document: " . $citation['document_title'] . "\n";
                    }
                    
                    if (isset($citation['cited_text'])) {
                        echo "  Cited Text: \"" . $citation['cited_text'] . "\"\n";
                    }
                    
                    if (isset($citation['start_char_index']) && isset($citation['end_char_index'])) {
                        echo "  Character Range: {$citation['start_char_index']} to {$citation['end_char_index']}\n";
                    }
                }
            } else {
                echo "(No citations found in response)\n";
            }
        }
    }
}

echo "\n=== Example Complete ===\n";

