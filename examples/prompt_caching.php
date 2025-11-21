#!/usr/bin/env php
<?php
/**
 * Prompt Caching - PHP examples from:
 * https://docs.claude.com/en/docs/build-with-claude/prompt-caching
 * 
 * Reduces costs and latency by caching frequently used context between API calls.
 * Cache lifetime: 5 minutes (can extend to 1 hour with continued use)
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Prompt Caching - Reduce Costs and Latency ===\n\n";

// Example 1: Basic prompt caching with system message
echo "Example 1: Basic Prompt Caching\n";
echo "--------------------------------\n";
echo "Cache frequently used system prompts to save costs and reduce latency.\n";
echo "Cache lifetime: 5 minutes (extends to 1 hour with continued use)\n\n";

try {
    $systemPrompt = "You are an AI assistant specialized in analyzing Python code. " .
        "You have deep knowledge of Python best practices, common patterns, " .
        "and potential issues. When analyzing code, provide detailed feedback " .
        "on code quality, potential bugs, performance issues, and suggestions " .
        "for improvement.";
    
    // First request - creates cache
    echo "First request (creates cache):\n";
    $response1 = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'system' => [
            [
                'type' => 'text',
                'text' => $systemPrompt,
                'cache_control' => ['type' => 'ephemeral']  // Mark for caching
            ]
        ],
        'messages' => [
            ['role' => 'user', 'content' => 'Review this code: def add(a, b): return a+b']
        ]
    ]);

    echo "Response: ";
    foreach ($response1->content as $block) {
        if ($block['type'] === 'text') {
            $text = substr($block['text'], 0, 150);
            echo $text . "...\n";
        }
    }
    
    echo "\nUsage (first request - cache creation):\n";
    echo "  Input tokens:              {$response1->usage->input_tokens}\n";
    echo "  Cache creation tokens:     " . ($response1->usage->cache_creation_input_tokens ?? 0) . "\n";
    echo "  Cache read tokens:         " . ($response1->usage->cache_read_input_tokens ?? 0) . "\n";
    echo "  Output tokens:             {$response1->usage->output_tokens}\n";
    
    // Second request - uses cache (within 5 minutes)
    echo "\nSecond request (reads from cache - within 5 minutes):\n";
    sleep(1); // Small delay to simulate real usage
    
    $response2 = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'system' => [
            [
                'type' => 'text',
                'text' => $systemPrompt,
                'cache_control' => ['type' => 'ephemeral']
            ]
        ],
        'messages' => [
            ['role' => 'user', 'content' => 'Review this code: def multiply(x, y): return x*y']
        ]
    ]);
    
    echo "Response received (different question, same cached context)\n";
    
    echo "\nUsage (second request - cache hit):\n";
    echo "  Input tokens:              {$response2->usage->input_tokens}\n";
    echo "  Cache creation tokens:     " . ($response2->usage->cache_creation_input_tokens ?? 0) . "\n";
    echo "  Cache read tokens:         " . ($response2->usage->cache_read_input_tokens ?? 0) . " (90% discount!)\n";
    echo "  Output tokens:             {$response2->usage->output_tokens}\n";
    
    echo "\n💰 Cost savings: Cache reads are 90% cheaper than regular input tokens!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: Caching large documents
echo "Example 2: Caching Large Documents\n";
echo "-----------------------------------\n";
echo "Cache large documents (>1024 tokens) for analysis, Q&A, or summarization.\n\n";

try {
    // Simulate a large document
    $largeDocument = str_repeat(
        "This is a comprehensive guide to machine learning algorithms. " .
        "It covers supervised learning, unsupervised learning, and reinforcement learning. " .
        "Each section provides detailed explanations and practical examples. ",
        50
    );
    
    echo "Document size: ~" . strlen($largeDocument) / 4 . " tokens (estimated)\n";
    echo "Caching document for multiple queries...\n\n";
    
    // First query
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 512,
        'system' => [
            [
                'type' => 'text',
                'text' => 'You are a helpful assistant that answers questions about documents.',
            ],
            [
                'type' => 'text',
                'text' => "Here is the document to reference:\n\n{$largeDocument}",
                'cache_control' => ['type' => 'ephemeral']  // Cache the document
            ]
        ],
        'messages' => [
            ['role' => 'user', 'content' => 'What topics does this document cover?']
        ]
    ]);

    echo "First query: 'What topics does this document cover?'\n";
    echo "Cache created for document\n";
    echo "Cache creation tokens: " . ($response->usage->cache_creation_input_tokens ?? 0) . "\n\n";
    
    // Subsequent queries use cached document
    echo "Subsequent queries will reuse the cached document (90% discount on those tokens)\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 3: Caching tool definitions
echo "Example 3: Caching Tool Definitions\n";
echo "------------------------------------\n";
echo "Cache tool definitions when using the same tools across multiple requests.\n\n";

try {
    $tools = [
        [
            'name' => 'get_weather',
            'description' => 'Get the current weather in a given location',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'location' => [
                        'type' => 'string',
                        'description' => 'The city and state, e.g. San Francisco, CA'
                    ],
                    'unit' => [
                        'type' => 'string',
                        'enum' => ['celsius', 'fahrenheit'],
                        'description' => 'The unit of temperature'
                    ]
                ],
                'required' => ['location']
            ],
            'cache_control' => ['type' => 'ephemeral']  // Cache tool definition
        ],
        [
            'name' => 'get_time',
            'description' => 'Get the current time in a given timezone',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'timezone' => [
                        'type' => 'string',
                        'description' => 'The timezone, e.g. America/New_York'
                    ]
                ],
                'required' => ['timezone']
            ]
        ]
    ];
    
    echo "Caching tool definitions (get_weather and get_time)\n";
    echo "First request creates cache, subsequent requests use it\n\n";
    
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 512,
        'tools' => $tools,
        'messages' => [
            ['role' => 'user', 'content' => 'What is the weather in San Francisco?']
        ]
    ]);

    echo "Tool definitions cached. Subsequent tool use requests will benefit from caching.\n";
    echo "Cache creation tokens: " . ($response->usage->cache_creation_input_tokens ?? 0) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 4: Multi-turn conversations with caching
echo "Example 4: Multi-turn Conversations with Caching\n";
echo "-------------------------------------------------\n";
echo "Cache conversation history for long-running conversations.\n\n";

try {
    $messages = [
        ['role' => 'user', 'content' => 'Hello, I need help with Python.']
    ];
    
    // Turn 1
    $response1 = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 256,
        'system' => [
            [
                'type' => 'text',
                'text' => 'You are a Python expert assistant.',
                'cache_control' => ['type' => 'ephemeral']
            ]
        ],
        'messages' => $messages
    ]);
    
    echo "Turn 1: User asks for help with Python\n";
    echo "Cache created for system prompt\n\n";
    
    // Turn 2 - add previous response and new message
    $messages[] = ['role' => 'assistant', 'content' => $response1->content];
    $messages[] = ['role' => 'user', 'content' => 'Explain list comprehensions.'];
    
    $response2 = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 256,
        'system' => [
            [
                'type' => 'text',
                'text' => 'You are a Python expert assistant.',
                'cache_control' => ['type' => 'ephemeral']
            ]
        ],
        'messages' => $messages
    ]);
    
    echo "Turn 2: User asks about list comprehensions\n";
    echo "System prompt read from cache (90% discount)\n";
    echo "Cache read tokens: " . ($response2->usage->cache_read_input_tokens ?? 0) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 5: Best practices and optimization tips
echo "Example 5: Best Practices & Optimization\n";
echo "-----------------------------------------\n\n";

echo "✓ Cache Placement Strategy:\n";
echo "  • Put static content (system prompts, docs) at the beginning\n";
echo "  • Place cache_control on the last block you want cached\n";
echo "  • Everything before and including that block gets cached\n\n";

echo "✓ Minimum Cache Size:\n";
echo "  • Only content >1024 tokens gets cached (about 4 paragraphs)\n";
echo "  • Smaller content won't benefit from caching\n\n";

echo "✓ Cache Lifetime:\n";
echo "  • 5 minutes initially\n";
echo "  • Extends to 1 hour with continued use (refreshes on each use)\n";
echo "  • Monitor cache_creation vs cache_read tokens to track hits\n\n";

echo "✓ Cost Savings:\n";
echo "  • Cache writes: Same as input tokens\n";
echo "  • Cache reads: 90% discount!\n";
echo "  • Example: 1000 token cache = \$0.003 to create, \$0.0003 to read\n\n";

echo "✓ What to Cache:\n";
echo "  • System prompts and instructions\n";
echo "  • Large documents for analysis\n";
echo "  • Tool definitions\n";
echo "  • Conversation history in long sessions\n";
echo "  • Few-shot examples\n\n";

echo "✓ Cache with Other Features:\n";
echo "  • ✓ Works with extended thinking\n";
echo "  • ✓ Works with tool use\n";
echo "  • ✓ Works with vision (images)\n";
echo "  • ✓ Works with all Claude models\n\n";

// Example configuration
echo "Example: Optimal cache configuration\n";
echo "```php\n";
echo "\$response = \$client->messages()->create([\n";
echo "    'model' => 'claude-sonnet-4-5',\n";
echo "    'max_tokens' => 1024,\n";
echo "    'system' => [\n";
echo "        [\n";
echo "            'type' => 'text',\n";
echo "            'text' => \$systemInstructions\n";
echo "        ],\n";
echo "        [\n";
echo "            'type' => 'text',\n";
echo "            'text' => \$largeDocument,\n";
echo "            'cache_control' => ['type' => 'ephemeral']  // Cache up to here\n";
echo "        ]\n";
echo "    ],\n";
echo "    'messages' => [\n";
echo "        ['role' => 'user', 'content' => \$question]\n";
echo "    ]\n";
echo "]);\n";
echo "```\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "✓ Prompt caching examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Prompt caching is now generally available (no beta prefix needed)\n";
echo "• 90% cost reduction for cached content\n";
echo "• 5-minute cache lifetime (extends to 1 hour with use)\n";
echo "• Minimum 1024 tokens to cache\n";
echo "• Use cache_control: ['type' => 'ephemeral'] to mark content\n";
echo "• Monitor cache_creation_input_tokens and cache_read_input_tokens\n";
echo "• Works with all Claude features (thinking, tools, vision, etc.)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • examples/context_windows.php - Token management\n";
echo "  • examples/tools.php - Tool use with caching\n";
echo "  • examples/thinking.php - Extended thinking with caching\n";
