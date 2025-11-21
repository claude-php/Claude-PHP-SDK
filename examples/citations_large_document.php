#!/usr/bin/env php
<?php

/**
 * Large Document Citations Example
 * 
 * This example demonstrates citations with a larger, more realistic document.
 * It shows how Claude can cite specific sections from longer texts.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');

$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Large Document Citations Example ===\n\n";

// A longer, more realistic document
$document = <<<EOT
COMPANY POLICY HANDBOOK - 2024

Section 1: Working Hours
Standard working hours are Monday through Friday, 9:00 AM to 5:00 PM. 
Employees are entitled to a one-hour lunch break between 12:00 PM and 2:00 PM.
Flexible working arrangements may be approved by department managers on a case-by-case basis.

Section 2: Remote Work Policy
Employees may work remotely up to two days per week with prior approval from their supervisor.
Remote work days must be scheduled in advance and documented in the company calendar system.
All remote workers must be available during core business hours (10:00 AM to 3:00 PM) and maintain
the same productivity standards as in-office work.

Section 3: Vacation and Time Off
All full-time employees accrue vacation time at a rate of 15 days per year.
Vacation requests must be submitted at least two weeks in advance.
Unused vacation days may be carried over to the next year, up to a maximum of 5 days.

Section 4: Professional Development
The company supports continuing education and professional development.
Employees may request up to $2,000 per year for approved training, conferences, or certifications.
All professional development requests must be submitted through the HR portal with manager approval.

Section 5: Health and Safety
The company is committed to providing a safe working environment.
All workplace accidents must be reported to HR within 24 hours.
Safety training is mandatory for all employees and must be completed annually.

Section 6: Code of Conduct
Employees are expected to maintain professional behavior at all times.
Harassment of any kind will not be tolerated and may result in immediate termination.
Any violations of the code of conduct should be reported to HR or through the anonymous hotline.
EOT;

// Ask multiple questions to demonstrate citations
$questions = [
    'How many vacation days do employees get per year?',
    'What is the policy on remote work?',
    'How much can employees spend on professional development?',
];

foreach ($questions as $idx => $question) {
    echo "Question " . ($idx + 1) . ": {$question}\n";
    echo str_repeat("-", 80) . "\n";
    
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
                        'title' => 'Company Policy Handbook 2024',
                        'context' => 'Official company policy document, approved by HR department.',
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
    
    if (isset($response->content[0])) {
        $content = $response->content[0];
        
        echo "Answer: " . $content['text'] . "\n\n";
        
        if (isset($content['citations']) && !empty($content['citations'])) {
            echo "Citations:\n";
            foreach ($content['citations'] as $citIdx => $citation) {
                echo "  [" . ($citIdx + 1) . "] ";
                if (isset($citation['cited_text'])) {
                    $citedText = $citation['cited_text'];
                    // Truncate if too long
                    if (strlen($citedText) > 100) {
                        $citedText = substr($citedText, 0, 100) . '...';
                    }
                    echo "\"{$citedText}\"\n";
                }
                if (isset($citation['document_title'])) {
                    echo "      Source: {$citation['document_title']}\n";
                }
            }
        } else {
            echo "(No citations provided)\n";
        }
    }
    
    echo "\n" . str_repeat("=", 80) . "\n\n";
}

echo "=== Example Complete ===\n";

