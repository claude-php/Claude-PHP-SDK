#!/usr/bin/env php
<?php
/**
 * Working with Messages - PHP versions of examples from:
 * https://docs.claude.com/en/docs/build-with-claude/working-with-messages
 * 
 * Practical patterns and examples for using the Messages API effectively.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Working with Messages API - Practical Patterns ===\n\n";

// Example 1: Basic request and response
echo "Example 1: Basic Request and Response\n";
echo "--------------------------------------\n";
try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            ['role' => 'user', 'content' => 'Hello, Claude']
        ]
    ]);

    echo "Request: Hello, Claude\n";
    echo "Response: ";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo $block['text'] . "\n";
        }
    }
    echo "\nUsage: {$response->usage->input_tokens} input tokens, ";
    echo "{$response->usage->output_tokens} output tokens\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: Multiple conversational turns
echo "Example 2: Multiple Conversational Turns\n";
echo "-----------------------------------------\n";
echo "The Messages API is stateless - you send the full conversational history.\n";
echo "Earlier turns don't need to originate from Claude (you can use synthetic messages).\n\n";

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            ['role' => 'user', 'content' => 'Hello, Claude'],
            ['role' => 'assistant', 'content' => 'Hello!'],
            ['role' => 'user', 'content' => 'Can you describe LLMs to me?']
        ]
    ]);

    echo "Conversation history:\n";
    echo "  User: Hello, Claude\n";
    echo "  Assistant: Hello!\n";
    echo "  User: Can you describe LLMs to me?\n";
    echo "\nClaude's response: ";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            // Truncate for display
            $text = $block['text'];
            if (strlen($text) > 200) {
                $text = substr($text, 0, 200) . '...';
            }
            echo $text . "\n";
        }
    }
    echo "\nUsage: {$response->usage->input_tokens} input tokens, ";
    echo "{$response->usage->output_tokens} output tokens\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 3: Putting words in Claude's mouth (Prefilling)
echo "Example 3: Putting Words in Claude's Mouth (Prefilling)\n";
echo "--------------------------------------------------------\n";
echo "You can pre-fill part of Claude's response to shape the output.\n";
echo "This example uses max_tokens: 1 to get a single multiple choice answer.\n\n";

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1,
        'messages' => [
            [
                'role' => 'user',
                'content' => 'What is latin for Ant? (A) Apoidea, (B) Rhopalocera, (C) Formicidae'
            ],
            [
                'role' => 'assistant',
                'content' => 'The answer is ('
            ]
        ]
    ]);

    echo "Question: What is latin for Ant? (A) Apoidea, (B) Rhopalocera, (C) Formicidae\n";
    echo "Prefill: The answer is (\n";
    echo "Claude completes: ";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo $block['text'] . "\n";
        }
    }
    echo "\nFull answer: The answer is (";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo $block['text'];
        }
    }
    echo ")\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 4: Vision - Base64-encoded image
echo "Example 4: Vision - Base64-encoded Image\n";
echo "-----------------------------------------\n";
echo "Claude can read images in both base64 and URL formats.\n";
echo "Supported formats: image/jpeg, image/png, image/gif, image/webp\n\n";

try {
    // Use the logo.png file if it exists
    $imagePath = __DIR__ . '/logo.png';
    if (file_exists($imagePath)) {
        $imageData = base64_encode(file_get_contents($imagePath));
        $mediaType = 'image/png';
        
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'image',
                            'source' => [
                                'type' => 'base64',
                                'media_type' => $mediaType,
                                'data' => $imageData
                            ]
                        ],
                        [
                            'type' => 'text',
                            'text' => 'What is in the above image?'
                        ]
                    ]
                ]
            ]
        ]);

        echo "Sent base64-encoded image (logo.png)\n";
        echo "Claude's response: ";
        foreach ($response->content as $block) {
            if ($block['type'] === 'text') {
                echo $block['text'] . "\n";
            }
        }
        echo "\nNote: Image was {$response->usage->input_tokens} tokens\n";
    } else {
        echo "Skipping base64 example - logo.png not found\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 5: Vision - URL-referenced image
echo "Example 5: Vision - URL-referenced Image\n";
echo "-----------------------------------------\n";
echo "Images can also be referenced by URL (no need to download/encode).\n\n";

try {
    $imageUrl = 'https://upload.wikimedia.org/wikipedia/commons/a/a7/Camponotus_flavomarginatus_ant.jpg';
    
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'image',
                        'source' => [
                            'type' => 'url',
                            'url' => $imageUrl
                        ]
                    ],
                    [
                        'type' => 'text',
                        'text' => 'What is in the above image?'
                    ]
                ]
            ]
        ]
    ]);

    echo "Image URL: {$imageUrl}\n";
    echo "Claude's response: ";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo $block['text'] . "\n";
        }
    }
    echo "\nUsage: {$response->usage->input_tokens} input tokens, ";
    echo "{$response->usage->output_tokens} output tokens\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 6: Multiple images in one request
echo "Example 6: Multiple Images in One Request\n";
echo "------------------------------------------\n";
echo "You can send multiple images in a single message.\n\n";

try {
    $imageUrl = 'https://upload.wikimedia.org/wikipedia/commons/a/a7/Camponotus_flavomarginatus_ant.jpg';
    
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'Compare these images and tell me what you see:'
                    ],
                    [
                        'type' => 'image',
                        'source' => [
                            'type' => 'url',
                            'url' => $imageUrl
                        ]
                    ],
                    [
                        'type' => 'image',
                        'source' => [
                            'type' => 'url',
                            'url' => $imageUrl
                        ]
                    ]
                ]
            ]
        ]
    ]);

    echo "Sent 2 images with text prompt\n";
    echo "Claude's response: ";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            $text = $block['text'];
            if (strlen($text) > 200) {
                $text = substr($text, 0, 200) . '...';
            }
            echo $text . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "✓ All examples completed successfully!\n\n";
echo "Key Takeaways:\n";
echo "- Messages API is stateless - always send full conversation history\n";
echo "- Use prefilling to shape Claude's responses\n";
echo "- Images can be base64-encoded or URL-referenced\n";
echo "- Multiple images can be sent in one request\n";
echo "- For tools and streaming, see tools.php and messages_stream.php\n\n";

echo "Related examples:\n";
echo "- examples/tools.php - Tool use and function calling\n";
echo "- examples/messages_stream.php - Streaming responses\n";
echo "- examples/images.php - More vision examples\n";

