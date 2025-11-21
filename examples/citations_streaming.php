#!/usr/bin/env php
<?php

/**
 * Citations with Streaming Example
 * 
 * This example demonstrates how to use citations with streaming responses.
 * Citations can be received incrementally as the response streams.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');

$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Citations with Streaming Example ===\n\n";

$document = <<<EOT
The Claude API supports multiple response formats. Standard responses return the complete message 
at once. Streaming responses send the message incrementally using Server-Sent Events (SSE), 
allowing for real-time display as text is generated. Streaming is particularly useful for 
interactive applications where users want to see responses as they arrive rather than waiting 
for the complete message.
EOT;

echo "Streaming response with citations...\n\n";

$stream = $client->messages()->stream([
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
                        'data' => $document,
                    ],
                    'title' => 'Claude API Documentation',
                    'citations' => ['enabled' => true],
                ],
                [
                    'type' => 'text',
                    'text' => 'What is streaming and why is it useful?',
                ],
            ],
        ],
    ],
]);

$fullText = '';
$citations = [];
$currentBlock = 0;

echo "Response: ";

foreach ($stream as $event) {
    $type = $event['type'] ?? null;
    
    switch ($type) {
        case 'content_block_start':
            $index = $event['index'] ?? 0;
            $blockType = $event['content_block']['type'] ?? '';
            $currentBlock = $index;
            
            if ($blockType === 'text') {
                echo "\n[Content Block {$index} started]\n";
            }
            break;
            
        case 'content_block_delta':
            $delta = $event['delta'] ?? [];
            $deltaType = $delta['type'] ?? '';
            
            if ($deltaType === 'text_delta') {
                $text = $delta['text'] ?? '';
                echo $text;
                $fullText .= $text;
                flush();
            }
            break;
            
        case 'content_block_stop':
            // Content block completed
            break;
            
        case 'message_delta':
            // Message metadata updated
            break;
            
        case 'message_stop':
            echo "\n\n[Stream completed]\n";
            break;
    }
}

echo "\n=== Post-Stream Analysis ===\n\n";

// After streaming completes, we need to get the final message with citations
// Note: Citations are included in the final message, not in individual stream events
echo "Note: With streaming, citations are typically available in the complete message.\n";
echo "For real-time citation tracking, you may need to make a non-streaming request\n";
echo "or accumulate the complete response before processing citations.\n\n";

// Make a non-streaming request to get the full citations
echo "Making non-streaming request to retrieve full citation data...\n\n";

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
                        'data' => $document,
                    ],
                    'title' => 'Claude API Documentation',
                    'citations' => ['enabled' => true],
                ],
                [
                    'type' => 'text',
                    'text' => 'What is streaming and why is it useful?',
                ],
            ],
        ],
    ],
]);

if (isset($response->content)) {
    $citationCount = 0;
    
    foreach ($response->content as $idx => $content) {
        if ($content['type'] === 'text' && isset($content['citations'])) {
            $blockCitations = $content['citations'];
            $citationCount += count($blockCitations);
            
            echo "Content Block #{$idx} Citations:\n";
            foreach ($blockCitations as $citIdx => $citation) {
                echo "  [" . ($citIdx + 1) . "] \"{$citation['cited_text']}\"\n";
                echo "      Source: {$citation['document_title']}\n";
                echo "      Position: {$citation['start_char_index']}-{$citation['end_char_index']}\n\n";
            }
        }
    }
    
    if ($citationCount === 0) {
        echo "No citations found in the response.\n";
    } else {
        echo "Total citations found: {$citationCount}\n";
    }
}

echo "\n=== Key Takeaways ===\n";
echo "- Streaming provides real-time text output as it's generated\n";
echo "- Citations are available in the complete message after streaming\n";
echo "- For immediate citation access, use non-streaming requests\n";
echo "- Stream events focus on content deltas, not complete metadata\n";

echo "\n=== Example Complete ===\n";


