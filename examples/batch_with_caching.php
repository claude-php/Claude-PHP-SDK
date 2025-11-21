#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

// Batch processing with prompt caching
echo "=== Batch Processing with Prompt Caching ===\n\n";

$client = createClient();

try {
    // Shared context that will be cached
    $sharedSystemPrompt = [
        [
            'type' => 'text',
            'text' => 'You are an AI assistant specialized in analyzing customer feedback. ' .
                     'Your task is to categorize feedback and provide insights.',
        ],
        [
            'type' => 'text',
            'text' => "Product Guidelines:\n" .
                     "- Our product is a cloud-based project management tool\n" .
                     "- Key features: task management, team collaboration, time tracking\n" .
                     "- Common issues: sync delays, mobile app crashes, notification problems\n" .
                     "- Priority levels: Critical (system down), High (data loss), Medium (UX issues), Low (feature requests)",
            'cache_control' => ['type' => 'ephemeral'],
        ],
    ];
    
    // Sample feedback to analyze
    $feedbackItems = [
        "The mobile app keeps crashing when I try to upload files. Very frustrating!",
        "Love the new time tracking feature! Makes billing so much easier.",
        "Sync is really slow between desktop and mobile. Takes 5+ minutes sometimes.",
        "Would be great to have dark mode. Eye strain is real!",
        "Can't access the app at all today. Getting 500 errors.",
    ];
    
    // Create batch requests with prompt caching
    $requests = [];
    foreach ($feedbackItems as $index => $feedback) {
        $requests[] = [
            'custom_id' => "feedback-{$index}",
            'params' => [
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 500,
                'system' => $sharedSystemPrompt,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => "Analyze this customer feedback and provide: " .
                                   "1) Category (Bug/Feature Request/Praise/Complaint) " .
                                   "2) Priority (Critical/High/Medium/Low) " .
                                   "3) Brief summary\n\n" .
                                   "Feedback: {$feedback}",
                    ],
                ],
            ],
        ];
    }
    
    echo "Creating batch with " . count($requests) . " requests...\n";
    echo "Using prompt caching for shared context\n\n";
    
    $batch = $client->beta()->messages()->batches()->create([
        'requests' => $requests,
    ]);
    
    echo "✓ Batch created successfully\n";
    echo "Batch ID: {$batch['id']}\n";
    echo "Status: {$batch['processing_status']}\n";
    echo "\nWith prompt caching enabled, requests after the first may see:\n";
    echo "- Reduced cost (cache reads are cheaper than input tokens)\n";
    echo "- Faster processing (cached content doesn't need reprocessing)\n";
    echo "\nNote: Cache hit rates in batches are best-effort (typically 30-98%)\n";
    echo "\nUse batch_poll.php to wait for completion, then batch_results.php to see results.\n";
    
    // Save batch ID
    file_put_contents(__DIR__ . '/.last_batch_id', $batch['id']);
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
