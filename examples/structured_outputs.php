#!/usr/bin/env php
<?php
/**
 * Structured Outputs - PHP examples from:
 * https://docs.claude.com/en/docs/build-with-claude/structured-outputs
 * 
 * Get JSON responses that conform to your schema with guaranteed structure.
 * Requires 'structured-outputs-2025-09-17' beta header (auto-added by parse()).
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Structured Outputs - Guaranteed JSON Schema ===\n\n";

// Example 1: Basic structured output
echo "Example 1: Basic Structured Output\n";
echo "-----------------------------------\n";
echo "Extract structured data with guaranteed schema compliance\n\n";

try {
    $schema = [
        'type' => 'object',
        'properties' => [
            'name' => ['type' => 'string'],
            'age' => ['type' => 'integer'],
            'email' => ['type' => 'string']
        ],
        'required' => ['name', 'age']
    ];
    
    $result = $client->beta()->messages()->parse([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Extract information: John Smith is 30 years old. Contact: john@example.com'
            ]
        ],
        'output_format' => $schema
    ]);
    
    echo "Input: 'John Smith is 30 years old. Contact: john@example.com'\n";
    echo "Schema: name (string), age (integer), email (string)\n\n";
    echo "Parsed output:\n";
    print_r($result);
    echo "\nNote: parse() automatically adds 'structured-outputs-2025-09-17' beta header\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: Complex nested schema
echo "Example 2: Complex Nested Schema\n";
echo "---------------------------------\n";
echo "Extract structured data with nested objects and arrays\n\n";

try {
    $schema = [
        'type' => 'object',
        'properties' => [
            'order_id' => ['type' => 'string'],
            'customer' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string'],
                    'email' => ['type' => 'string']
                ],
                'required' => ['name']
            ],
            'items' => [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'product' => ['type' => 'string'],
                        'quantity' => ['type' => 'integer'],
                        'price' => ['type' => 'number']
                    ],
                    'required' => ['product', 'quantity', 'price']
                ]
            ],
            'total' => ['type' => 'number']
        ],
        'required' => ['order_id', 'customer', 'items', 'total']
    ];
    
    $result = $client->beta()->messages()->parse([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 2048,
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Parse this order: Order #12345 for Jane Doe (jane@example.com). ' .
                    'Items: 2x Widget ($10 each), 1x Gadget ($25). Total: $45'
            ]
        ],
        'output_format' => $schema
    ]);
    
    echo "Complex order extraction with nested structure\n\n";
    echo "Parsed order:\n";
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 3: Streaming structured outputs
echo "Example 3: Streaming Structured Outputs\n";
echo "----------------------------------------\n";
echo "Stream structured data as it's generated\n\n";

try {
    $schema = [
        'type' => 'object',
        'properties' => [
            'summary' => ['type' => 'string'],
            'key_points' => [
                'type' => 'array',
                'items' => ['type' => 'string']
            ]
        ],
        'required' => ['summary', 'key_points']
    ];
    
    $rawStream = $client->beta()->messages()->streamStructured([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 2048,
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Summarize with key points: Machine learning revolutionizes data analysis.'
            ]
        ],
        'output_format' => $schema
    ]);
    
    echo "Streaming structured output:\n";
    
    foreach ($rawStream as $event) {
        if (isset($event['parsed_output'])) {
            echo "\nParsed output:\n";
            echo json_encode($event['parsed_output'], JSON_PRETTY_PRINT) . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 4: Schema design tips
echo "Example 4: Schema Design Tips\n";
echo "------------------------------\n\n";

echo "✓ Start Simple:\n";
echo "  • Begin with basic object structure\n";
echo "  • Add complexity incrementally\n";
echo "  • Test with real data\n\n";

echo "✓ Be Specific:\n";
echo "  • Use appropriate types (string, integer, number, boolean)\n";
echo "  • Mark required fields\n";
echo "  • Add descriptions for clarity\n";
echo "  • Use enums for fixed values\n\n";

echo "✓ Handle Arrays:\n";
echo "  • Define item schema clearly\n";
echo "  • Consider min/max items\n";
echo "  • Nested arrays work but add complexity\n\n";

echo "✓ Validation:\n";
echo "  • Schema is enforced by Claude\n";
echo "  • Output always matches schema\n";
echo "  • No need for manual validation\n";
echo "  • Type safety guaranteed\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 5: Common use cases
echo "Example 5: Common Use Cases\n";
echo "---------------------------\n\n";

echo "✓ Data Extraction:\n";
echo "  • Parse invoices, receipts, forms\n";
echo "  • Extract entities from text\n";
echo "  • Structured information retrieval\n\n";

echo "✓ API Integration:\n";
echo "  • Generate API request payloads\n";
echo "  • Parse API responses\n";
echo "  • Data transformation\n\n";

echo "✓ Classification:\n";
echo "  • Sentiment analysis with structured output\n";
echo "  • Multi-label classification\n";
echo "  • Confidence scores\n\n";

echo "✓ Workflow Automation:\n";
echo "  • Consistent data format for pipelines\n";
echo "  • Reliable parsing for downstream systems\n";
echo "  • No post-processing needed\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "✓ Structured outputs examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Use \$client->beta()->messages()->parse() for structured outputs\n";
echo "• Requires 'output_format' parameter with JSON schema\n";
echo "• Beta header 'structured-outputs-2025-09-17' auto-added\n";
echo "• Guaranteed schema compliance (no manual validation needed)\n";
echo "• Supports streaming with streamStructured()\n";
echo "• Works with complex nested schemas\n";
echo "• Ideal for: Data extraction, API integration, classification\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • examples/beta_features.php - Beta features usage\n";
echo "  • examples/tools.php - Function calling (similar structured outputs)\n";

