#!/usr/bin/env php
<?php
/**
 * Vision (Comprehensive) - PHP examples from:
 * https://docs.claude.com/en/docs/build-with-claude/vision
 * 
 * Complete vision capabilities: base64, URLs, multiple images, PDFs.
 * Supported formats: JPEG, PNG, GIF, WebP
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Vision - Comprehensive Image Analysis ===\n\n";

// Example 1: Base64-encoded image
echo "Example 1: Base64-encoded Image\n";
echo "--------------------------------\n";
echo "Supported formats: image/jpeg, image/png, image/gif, image/webp\n\n";

try {
    $imagePath = __DIR__ . '/logo.png';
    
    if (file_exists($imagePath)) {
        $imageData = base64_encode(file_get_contents($imagePath));
        
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
                                'media_type' => 'image/png',
                                'data' => $imageData
                            ]
                        ],
                        [
                            'type' => 'text',
                            'text' => 'Describe this image in detail.'
                        ]
                    ]
                ]
            ]
        ]);

        echo "Image: logo.png (base64-encoded)\n";
        echo "Response: ";
        foreach ($response->content as $block) {
            if ($block['type'] === 'text') {
                echo $block['text'] . "\n";
            }
        }
        echo "\nImage tokens: {$response->usage->input_tokens}\n";
    } else {
        echo "Note: logo.png not found - skipping base64 example\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: URL-referenced image
echo "Example 2: URL-referenced Image\n";
echo "--------------------------------\n";
echo "Reference images by URL (no download/encoding needed)\n\n";

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
                        'text' => 'What species of ant is this?'
                    ]
                ]
            ]
        ]
    ]);

    echo "Image URL: {$imageUrl}\n";
    echo "Response: ";
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

// Example 3: Multiple images
echo "Example 3: Multiple Images in One Request\n";
echo "------------------------------------------\n";
echo "Analyze multiple images together\n\n";

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            [
                'role' => 'user',
                'content' => [
                    ['type' => 'text', 'text' => 'Compare these two images:'],
                    [
                        'type' => 'image',
                        'source' => [
                            'type' => 'url',
                            'url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/5b/Apis_mellifera_Western_honey_bee.jpg/440px-Apis_mellifera_Western_honey_bee.jpg'
                        ]
                    ],
                    [
                        'type' => 'image',
                        'source' => [
                            'type' => 'url',
                            'url' => 'https://upload.wikimedia.org/wikipedia/commons/a/a7/Camponotus_flavomarginatus_ant.jpg'
                        ]
                    ],
                    ['type' => 'text', 'text' => 'What are the main differences?']
                ]
            ]
        ]
    ]);

    echo "Sent 2 images for comparison\n";
    echo "Response: ";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            $text = $block['text'];
            if (strlen($text) > 250) {
                $text = substr($text, 0, 250) . '...';
            }
            echo $text . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 4: Best practices
echo "Example 4: Vision Best Practices\n";
echo "---------------------------------\n\n";

echo "✓ Image Size & Quality:\n";
echo "  • Max size: 5MB per image (base64), 100MB (URL)\n";
echo "  • Supported: JPEG, PNG, GIF, WebP\n";
echo "  • Resize large images to save tokens\n";
echo "  • Higher resolution = better detail recognition\n\n";

echo "✓ Token Usage:\n";
echo "  • Images count toward input tokens\n";
echo "  • Token count varies by image size and detail\n";
echo "  • Monitor usage to manage costs\n";
echo "  • Base64 increases request size\n\n";

echo "✓ Prompt Tips:\n";
echo "  • Be specific about what to analyze\n";
echo "  • Reference image position if multiple\n";
echo "  • Ask focused questions for better results\n";
echo "  • Combine with text context when helpful\n\n";

echo "✓ Multi-image Guidelines:\n";
echo "  • Order matters - reference by position\n";
echo "  • Mix base64 and URL sources\n";
echo "  • Watch total token count\n";
echo "  • Consider batch processing for many images\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "✓ Vision examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Two source types: base64 (embedded) and url (referenced)\n";
echo "• Supported formats: JPEG, PNG, GIF, WebP\n";
echo "• Max size: 5MB (base64), 100MB (URL)\n";
echo "• Multiple images supported in single request\n";
echo "• Images count as input tokens (varies by size)\n";
echo "• Use specific prompts for better analysis\n";
echo "• All Claude models support vision\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • examples/images.php - Basic vision example\n";
echo "  • examples/working_with_messages.php - Multiple images\n";
echo "  • examples/pdf_support.php - PDF vision capabilities\n";

