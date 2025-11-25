#!/usr/bin/env php
<?php
/**
 * Tutorial 0: Claude PHP SDK & Introduction to Agentic AI - Code Examples
 * 
 * This file demonstrates SDK fundamentals and core concepts of agentic AI 
 * through working examples. Run this to see both the SDK basics and 
 * the difference between chatbot-style and agent-style interactions.
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../examples/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║        Tutorial 0: Claude PHP SDK & Introduction to Agentic AI            ║\n";
echo "║                                                                            ║\n";
echo "║  This tutorial demonstrates SDK fundamentals and agentic AI concepts       ║\n";
echo "║  through working code examples. Follow along to learn both!                ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "⏱️  Estimated runtime: 2-3 minutes\n";
echo "💰 Estimated cost: ~$0.05-0.10 (depending on models)\n\n";

// ============================================================================
// PART 1: SDK FUNDAMENTALS
// ============================================================================

echo "\n";
echo "┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓\n";
echo "┃                        PART 1: SDK FUNDAMENTALS                         ┃\n";
echo "┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛\n";

// ============================================================================
// Example 1: Basic Message Creation
// ============================================================================

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 1: Basic Message Creation (Hello World)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

try {
    echo "📤 Sending: 'Hello Claude, what can you do?'\n\n";

    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 500,
        'messages' => [
            ['role' => 'user', 'content' => 'Hello Claude, what can you do?']
        ]
    ]);

    echo "✓ Response received!\n\n";
    echo "📥 Claude's response:\n";
    echo "   " . str_repeat("─", 70) . "\n";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            // Truncate for display
            $text = $block['text'];
            if (strlen($text) > 300) {
                $text = substr($text, 0, 300) . "...[truncated]";
            }
            echo "   " . str_replace("\n", "\n   ", $text) . "\n";
        }
    }
    echo "   " . str_repeat("─", 70) . "\n";

    echo "\n💡 Notice how we accessed the response content through the content array.\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}

echo "\n" . str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 2: Response Structure & Token Usage
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 2: Understanding Response Structure\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

try {
    echo "📋 Response Properties:\n\n";

    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 200,
        'messages' => [
            ['role' => 'user', 'content' => 'What is PHP?']
        ]
    ]);

    echo "  ID: {$response->id}\n";
    echo "  Model: {$response->model}\n";
    echo "  Stop Reason: {$response->stop_reason}\n";
    echo "  Content Blocks: " . count($response->content) . "\n";

    echo "\n💰 Token Usage:\n";
    echo "  Input tokens: {$response->usage->input_tokens}\n";
    echo "  Output tokens: {$response->usage->output_tokens}\n";
    echo "  Total tokens: " . ($response->usage->input_tokens + $response->usage->output_tokens) . "\n";

    echo "\n📄 Content:\n";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            $text = $block['text'];
            if (strlen($text) > 150) {
                $text = substr($text, 0, 150) . "...";
            }
            echo "   " . str_replace("\n", "\n   ", $text) . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}

echo "\n" . str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 3: Model Comparison
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 3: Comparing Different Models\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$testQuestion = "Summarize machine learning in one sentence.";

$models = [
    [
        'name' => 'Claude Sonnet 4.5',
        'id' => 'claude-sonnet-4-5',
        'characteristics' => 'Balanced - best for most tasks'
    ],
    [
        'name' => 'Claude Haiku 4.5',
        'id' => 'claude-haiku-4-5',
        'characteristics' => 'Fast & cost-effective - best for simple tasks'
    ]
];

foreach ($models as $model) {
    echo "🤖 {$model['name']}\n";
    echo "   Characteristics: {$model['characteristics']}\n";
    echo "   Question: \"{$testQuestion}\"\n";

    try {
        $start = microtime(true);
        $response = $client->messages()->create([
            'model' => $model['id'],
            'max_tokens' => 200,
            'messages' => [
                ['role' => 'user', 'content' => $testQuestion]
            ]
        ]);
        $elapsed = round((microtime(true) - $start) * 1000);

        echo "   Response: ";
        foreach ($response->content as $block) {
            if ($block['type'] === 'text') {
                echo $block['text'];
            }
        }
        echo "\n";
        echo "   Tokens: {$response->usage->input_tokens} input, {$response->usage->output_tokens} output\n";
        echo "   Time: ~{$elapsed}ms\n\n";
    } catch (Exception $e) {
        echo "   Error: {$e->getMessage()}\n\n";
    }
}

echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 4: Multi-Turn Conversations
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 4: Multi-Turn Conversations (Conversation History)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "💬 Building a conversation with context...\n\n";

try {
    // First turn
    echo "User: Hello! What's a good programming language to learn?\n";

    $response1 = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 300,
        'messages' => [
            ['role' => 'user', 'content' => 'Hello! What\'s a good programming language to learn?']
        ]
    ]);

    $assistantMessage1 = '';
    foreach ($response1->content as $block) {
        if ($block['type'] === 'text') {
            $text = $block['text'];
            if (strlen($text) > 150) {
                $text = substr($text, 0, 150) . "...";
            }
            echo "Claude: " . $text . "\n\n";
            $assistantMessage1 = $block['text'];
        }
    }

    // Second turn (building on first)
    echo "User: Why would you recommend that one?\n";

    $response2 = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 300,
        'messages' => [
            ['role' => 'user', 'content' => 'Hello! What\'s a good programming language to learn?'],
            ['role' => 'assistant', 'content' => $assistantMessage1],
            ['role' => 'user', 'content' => 'Why would you recommend that one?']
        ]
    ]);

    foreach ($response2->content as $block) {
        if ($block['type'] === 'text') {
            $text = $block['text'];
            if (strlen($text) > 150) {
                $text = substr($text, 0, 150) . "...";
            }
            echo "Claude: " . $text . "\n\n";
        }
    }

    echo "✓ Conversation completed. Claude remembered the context!\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}

echo "\n" . str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 5: System Prompts
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 5: Using System Prompts to Set Personality\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "📋 System prompts guide Claude's behavior...\n\n";

try {
    // Without system prompt
    echo "1️⃣ Without system prompt:\n";
    echo "   Question: 'Explain quantum computing'\n";

    $response1 = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 200,
        'messages' => [
            ['role' => 'user', 'content' => 'Explain quantum computing in a single sentence.']
        ]
    ]);

    foreach ($response1->content as $block) {
        if ($block['type'] === 'text') {
            $text = $block['text'];
            if (strlen($text) > 100) {
                $text = substr($text, 0, 100) . "...";
            }
            echo "   Response: " . $text . "\n\n";
        }
    }

    // With system prompt
    echo "2️⃣ With system prompt (ELI5 - Explain Like I'm 5):\n";
    echo "   Question: 'Explain quantum computing'\n";

    $response2 = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 200,
        'system' => 'You are explaining technical concepts to a 5-year-old. Use simple words and analogies.',
        'messages' => [
            ['role' => 'user', 'content' => 'Explain quantum computing in a single sentence.']
        ]
    ]);

    foreach ($response2->content as $block) {
        if ($block['type'] === 'text') {
            $text = $block['text'];
            if (strlen($text) > 100) {
                $text = substr($text, 0, 100) . "...";
            }
            echo "   Response: " . $text . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}

echo "\n" . str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 6: Temperature Control
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 6: Temperature - Controlling Randomness\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🎚️  Temperature controls how creative/random responses are:\n";
echo "   0.0 = Deterministic (always the same answer)\n";
echo "   1.0 = Creative (different each time)\n\n";

try {
    // Deterministic response
    echo "1️⃣ Temperature = 0.0 (Deterministic):\n";
    echo "   Question: 'List three colors'\n";

    $responses_deterministic = [];
    for ($i = 0; $i < 2; $i++) {
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 100,
            'temperature' => 0.0,
            'messages' => [
                ['role' => 'user', 'content' => 'List exactly three colors, nothing else.']
            ]
        ]);

        foreach ($response->content as $block) {
            if ($block['type'] === 'text') {
                $responses_deterministic[] = trim($block['text']);
            }
        }
    }

    echo "   Try 1: " . $responses_deterministic[0] . "\n";
    echo "   Try 2: " . $responses_deterministic[1] . "\n";
    echo "   ✓ Same every time (deterministic)\n\n";
} catch (Exception $e) {
    echo "   Error: {$e->getMessage()}\n\n";
}

echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 7: Error Handling
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 7: Error Handling & Exception Types\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🛡️ Proper error handling is critical...\n\n";

try {
    echo "1️⃣ Handling validation errors:\n";

    // This will fail because max_tokens is required
    try {
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!']
            ]
            // Missing max_tokens
        ]);
    } catch (InvalidArgumentException $e) {
        echo "   ✓ Caught validation error: max_tokens is required\n";
        echo "   Error: " . $e->getMessage() . "\n\n";
    }

    echo "2️⃣ Handling successful request with try-catch:\n";
    try {
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 100,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello!']
            ]
        ]);
        echo "   ✓ Request succeeded\n";
        echo "   Response ID: " . $response->id . "\n";
    } catch (Exception $e) {
        echo "   Error: " . $e->getMessage() . "\n";
    }
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}

echo "\n" . str_repeat("═", 80) . "\n\n";

// ============================================================================
// PART 2: AGENTIC AI CONCEPTS
// ============================================================================

echo "\n";
echo "┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓\n";
echo "┃                   PART 2: AGENTIC AI CONCEPTS                            ┃\n";
echo "┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛\n";

// ============================================================================
// Example 8: Traditional Chatbot (No Tools)
// ============================================================================

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 8: Traditional Chatbot Behavior (No Tools)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Question: What is 1,234 × 5,678?\n\n";

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            ['role' => 'user', 'content' => 'What is 1,234 × 5,678?']
        ]
    ]);

    echo "🤖 Chatbot Response:\n";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            $text = $block['text'];
            if (strlen($text) > 200) {
                $text = substr($text, 0, 200) . "...";
            }
            echo "   {$text}\n";
        }
    }

    echo "\n💡 Observation: The chatbot tries to calculate mentally but may be approximate.\n";
    echo "   It's limited to its training and reasoning capabilities.\n";
    echo "   Let's see how an agent with a calculator tool does better...\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}

echo "\n" . str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 9: Agent with Tool (Calculator)
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 9: Agent Behavior (With Calculator Tool)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Same question: What is 1,234 × 5,678?\n\n";

// Define a calculator tool
$calculatorTool = [
    'name' => 'calculate',
    'description' => 'Perform precise mathematical calculations. Supports +, -, *, /, and parentheses.',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'expression' => [
                'type' => 'string',
                'description' => 'Mathematical expression to evaluate, e.g., "1234 * 5678"'
            ]
        ],
        'required' => ['expression']
    ]
];

try {
    // Step 1: Send request with tool
    echo "🧠 Agent Reasoning Phase:\n";
    $response1 = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'tools' => [$calculatorTool],
        'messages' => [
            ['role' => 'user', 'content' => 'What is 1,234 × 5,678?']
        ]
    ]);

    // Check if agent wants to use the tool
    $toolUse = null;
    foreach ($response1->content as $block) {
        if ($block['type'] === 'tool_use') {
            $toolUse = $block;
            echo "   ✓ Agent decided to use tool: '{$block['name']}'\n";
            echo "   ✓ With parameters: " . json_encode($block['input']) . "\n";
        }
    }

    if ($toolUse) {
        // Step 2: Execute the tool (our code)
        echo "\n🔧 Tool Execution Phase:\n";
        $expression = $toolUse['input']['expression'];
        echo "   ✓ Evaluating: {$expression}\n";

        // Simple eval (in production, use a proper math parser!)
        // For demo purposes only - eval() is dangerous with untrusted input
        $result = eval("return {$expression};");
        echo "   ✓ Result: {$result}\n";

        // Step 3: Return result to agent
        echo "\n🧠 Agent Response Phase:\n";
        $response2 = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'tools' => [$calculatorTool],
            'messages' => [
                ['role' => 'user', 'content' => 'What is 1,234 × 5,678?'],
                ['role' => 'assistant', 'content' => $response1->content],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'tool_result',
                            'tool_use_id' => $toolUse['id'],
                            'content' => (string)$result
                        ]
                    ]
                ]
            ]
        ]);

        echo "   Agent Final Response:\n";
        foreach ($response2->content as $block) {
            if ($block['type'] === 'text') {
                echo "   {$block['text']}\n";
            }
        }

        echo "\n💡 Observation: The agent used a tool to get the EXACT answer.\n";
        echo "   This is the power of agentic behavior!\n";
    }
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}

echo "\n" . str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 10: Demonstrating the ReAct Loop
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 10: The ReAct Loop in Action\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Task: Calculate (100 × 50) + (25 × 30)\n";
echo "This requires TWO tool calls - let's see the agent figure it out!\n\n";

$messages = [
    ['role' => 'user', 'content' => 'What is (100 × 50) + (25 × 30)?']
];

$iteration = 0;
$maxIterations = 5;

while ($iteration < $maxIterations) {
    $iteration++;

    echo "╔════ Iteration {$iteration} ════╗\n\n";

    // Call Claude
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'tools' => [$calculatorTool],
        'messages' => $messages
    ]);

    echo "🧠 REASON: Agent is thinking...\n";
    echo "🛑 Stop Reason: {$response->stop_reason}\n\n";

    // Add assistant response to conversation
    $messages[] = ['role' => 'assistant', 'content' => $response->content];

    // Check what the agent wants to do
    if ($response->stop_reason === 'end_turn') {
        echo "✅ COMPLETE: Agent finished!\n\n";
        echo "📝 Final Answer:\n";
        foreach ($response->content as $block) {
            if ($block['type'] === 'text') {
                echo "   {$block['text']}\n";
            }
        }
        break;
    }

    if ($response->stop_reason === 'tool_use') {
        $toolResults = [];

        foreach ($response->content as $block) {
            if ($block['type'] === 'tool_use') {
                echo "🔧 ACT: Using tool '{$block['name']}'\n";
                echo "   Input: {$block['input']['expression']}\n";

                // Execute tool
                $expression = $block['input']['expression'];
                $result = eval("return {$expression};");

                echo "👁️  OBSERVE: Tool returned: {$result}\n\n";

                $toolResults[] = [
                    'type' => 'tool_result',
                    'tool_use_id' => $block['id'],
                    'content' => (string)$result
                ];
            }
        }

        // Add tool results to conversation
        if (!empty($toolResults)) {
            $messages[] = ['role' => 'user', 'content' => $toolResults];
        }
    }
}

if ($iteration >= $maxIterations) {
    echo "⚠️  Max iterations reached!\n";
}

echo "\n💡 Observation: The agent executed multiple tool calls in sequence,\n";
echo "   using the results to solve the complete problem. This is ReAct!\n";

echo "\n" . str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 11: Understanding Stop Reasons
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 11: Understanding Stop Reasons\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$testCases = [
    [
        'name' => 'Direct Answer (No Tool)',
        'message' => 'What is the capital of France?',
        'expected' => 'end_turn'
    ],
    [
        'name' => 'Tool Use Needed',
        'message' => 'What is 987 × 654?',
        'expected' => 'tool_use'
    ]
];

foreach ($testCases as $test) {
    echo "Test: {$test['name']}\n";
    echo "Question: \"{$test['message']}\"\n";

    try {
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'tools' => [$calculatorTool],
            'messages' => [
                ['role' => 'user', 'content' => $test['message']]
            ]
        ]);

        $stopReason = $response->stop_reason;
        $match = $stopReason === $test['expected'] ? '✓' : '✗';

        echo "  Stop Reason: {$stopReason} {$match}\n";
        echo "  Expected: {$test['expected']}\n\n";
    } catch (Exception $e) {
        echo "  Error: {$e->getMessage()}\n\n";
    }
}

echo "💡 Key Insight: The 'stop_reason' tells you what the agent wants to do next:\n";
echo "   • 'end_turn': Agent has completed its response\n";
echo "   • 'tool_use': Agent wants to execute a tool\n";
echo "   • 'max_tokens': Response was cut off (increase max_tokens)\n\n";

echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Summary
// ============================================================================

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                              Key Takeaways                                 ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "SDK FUNDAMENTALS:\n";
echo "1️⃣  Installation & Configuration\n";
echo "   The SDK is easy to set up with Composer and environment variables.\n\n";

echo "2️⃣  Basic API Usage\n";
echo "   Create a client, call messages().create(), extract content.\n\n";

echo "3️⃣  Models & When to Use Them\n";
echo "   Sonnet for quality, Haiku for speed and cost, Opus for specialized tasks.\n\n";

echo "4️⃣  Message Patterns\n";
echo "   Single-turn, multi-turn, system prompts, and response prefilling.\n\n";

echo "5️⃣  Error Handling\n";
echo "   Always use try-catch and handle specific exception types.\n\n";

echo "AGENTIC AI CONCEPTS:\n";
echo "1️⃣  Chatbots respond → Agents take action\n";
echo "   Chatbots are limited to their training. Agents use tools to extend capabilities.\n\n";

echo "2️⃣  ReAct Loop = Reason → Act → Observe → Repeat\n";
echo "   Agents iterate until the task is complete or max iterations reached.\n\n";

echo "3️⃣  Tools are the agent's superpowers\n";
echo "   They enable getting real-time data, performing calculations, taking actions.\n\n";

echo "4️⃣  Stop reasons guide the loop\n";
echo "   'tool_use' = needs to execute a tool\n";
echo "   'end_turn' = task complete\n\n";

echo "5️⃣  Iteration limits prevent infinite loops\n";
echo "   Always set a maximum to avoid runaway execution.\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🎓 Ready to build your first real agent?\n";
echo "   Continue to Tutorial 1: Your First Agent\n";
echo "   → tutorials/01-first-agent/\n\n";

echo "📚 Concepts covered:\n";
echo "   ✓ SDK Installation & Configuration\n";
echo "   ✓ Basic API Usage & Response Handling\n";
echo "   ✓ Available Models & Selection\n";
echo "   ✓ Message Patterns & Conversation History\n";
echo "   ✓ System Prompts & Advanced Parameters\n";
echo "   ✓ Error Handling & Exception Types\n";
echo "   ✓ Chatbot vs Agent behavior\n";
echo "   ✓ The ReAct loop pattern\n";
echo "   ✓ Tool use and agent capabilities\n";
echo "   ✓ Stop reasons and their meanings\n";
echo "   ✓ Multi-step problem solving\n\n";
