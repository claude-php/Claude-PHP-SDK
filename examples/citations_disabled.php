#!/usr/bin/env php
<?php

/**
 * Citations Disabled Example
 * 
 * This example demonstrates the difference between responses with
 * citations enabled vs disabled.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');

$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Citations Comparison Example ===\n\n";

$documentContent = 'The Eiffel Tower is 330 meters tall. It was completed in 1889. The tower has three levels for visitors.';
$question = 'How tall is the Eiffel Tower?';

// First request: WITH citations enabled
echo "1. REQUEST WITH CITATIONS ENABLED:\n";
echo "   Question: {$question}\n\n";

$responseWithCitations = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 512,
    'messages' => [
        [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'document',
                    'source' => [
                        'type' => 'text',
                        'media_type' => 'text/plain',
                        'data' => $documentContent,
                    ],
                    'title' => 'Eiffel Tower Facts',
                    'citations' => ['enabled' => true],
                ],
                [
                    'type' => 'text',
                    'text' => $question,
                ],
            ],
        ],
    ],
]);

echo "   Response: ";
if (isset($responseWithCitations->content[0]['text'])) {
    echo $responseWithCitations->content[0]['text'] . "\n";
}

echo "   Citations present: ";
$hasCitations = isset($responseWithCitations->content[0]['citations']) 
    && !empty($responseWithCitations->content[0]['citations']);
echo ($hasCitations ? "Yes (" . count($responseWithCitations->content[0]['citations']) . " citation(s))" : "No") . "\n\n";

// Second request: WITHOUT citations (not specifying the field)
echo "2. REQUEST WITHOUT CITATIONS SPECIFIED:\n";
echo "   Question: {$question}\n\n";

$responseWithoutCitations = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 512,
    'messages' => [
        [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'document',
                    'source' => [
                        'type' => 'text',
                        'media_type' => 'text/plain',
                        'data' => $documentContent,
                    ],
                    'title' => 'Eiffel Tower Facts',
                    // No citations field specified
                ],
                [
                    'type' => 'text',
                    'text' => $question,
                ],
            ],
        ],
    ],
]);

echo "   Response: ";
if (isset($responseWithoutCitations->content[0]['text'])) {
    echo $responseWithoutCitations->content[0]['text'] . "\n";
}

echo "   Citations present: ";
$hasNoCitations = isset($responseWithoutCitations->content[0]['citations']) 
    && !empty($responseWithoutCitations->content[0]['citations']);
echo ($hasNoCitations ? "Yes" : "No") . "\n\n";

// Third request: Explicitly DISABLED citations
echo "3. REQUEST WITH CITATIONS EXPLICITLY DISABLED:\n";
echo "   Question: {$question}\n\n";

$responseDisabled = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 512,
    'messages' => [
        [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'document',
                    'source' => [
                        'type' => 'text',
                        'media_type' => 'text/plain',
                        'data' => $documentContent,
                    ],
                    'title' => 'Eiffel Tower Facts',
                    'citations' => ['enabled' => false],
                ],
                [
                    'type' => 'text',
                    'text' => $question,
                ],
            ],
        ],
    ],
]);

echo "   Response: ";
if (isset($responseDisabled->content[0]['text'])) {
    echo $responseDisabled->content[0]['text'] . "\n";
}

echo "   Citations present: ";
$hasDisabledCitations = isset($responseDisabled->content[0]['citations']) 
    && !empty($responseDisabled->content[0]['citations']);
echo ($hasDisabledCitations ? "Yes" : "No") . "\n\n";

echo "=== Key Takeaways ===\n";
echo "- Citations must be explicitly enabled with citations => ['enabled' => true]\n";
echo "- Without this setting, Claude will not include citation metadata\n";
echo "- Use citations when you need verifiable references to source documents\n";
echo "\n=== Example Complete ===\n";

