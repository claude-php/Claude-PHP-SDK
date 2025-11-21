#!/usr/bin/env php
<?php
/**
 * Tutorial 7: Chain of Thought (CoT) - Working Example
 * 
 * Demonstrates Chain of Thought prompting for step-by-step reasoning
 * without external tools. Shows zero-shot, few-shot, and complex reasoning.
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../examples/helpers.php';
require_once __DIR__ . '/../helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║             Tutorial 7: Chain of Thought (CoT) Reasoning                  ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Example 1: Zero-Shot CoT - Math Word Problem
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 1: Zero-Shot CoT - Math Word Problem\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$mathProblem = "A baker makes 24 cupcakes. She sells them in boxes of 6. " .
               "If each box costs \$12, how much money does she make in total?";

echo "Problem: {$mathProblem}\n\n";

// Test without CoT first
echo "📋 Without Chain of Thought:\n";
echo str_repeat("-", 80) . "\n";

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 512,
        'messages' => [
            ['role' => 'user', 'content' => $mathProblem]
        ]
    ]);
    
    echo extractTextContent($response) . "\n\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// Now with CoT
echo "🧠 With Chain of Thought (Zero-Shot):\n";
echo str_repeat("-", 80) . "\n";

try {
    $cotPrompt = $mathProblem . "\n\nLet's solve this step by step.";
    
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            ['role' => 'user', 'content' => $cotPrompt]
        ]
    ]);
    
    echo extractTextContent($response) . "\n\n";
    echo "💡 Notice: With CoT, we see the complete reasoning process!\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 2: Few-Shot CoT - Providing Examples
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 2: Few-Shot CoT - Learning from Examples\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Create a system prompt with examples
$fewShotSystem = "You are a math tutor who solves problems step by step. " .
                 "Here are examples of how to approach problems:\n\n" .
                 "Example 1:\n" .
                 "Q: If a book costs \$15 and is on 20% discount, what's the sale price?\n" .
                 "A: Let me work through this:\n" .
                 "   Step 1: Calculate the discount amount: 20% of \$15 = \$15 × 0.20 = \$3\n" .
                 "   Step 2: Subtract discount from original price: \$15 - \$3 = \$12\n" .
                 "   Final Answer: The sale price is \$12\n\n" .
                 "Example 2:\n" .
                 "Q: A car travels at 60 mph for 2.5 hours. How far does it go?\n" .
                 "A: Let me solve this step by step:\n" .
                 "   Step 1: Use the formula Distance = Speed × Time\n" .
                 "   Step 2: Plug in the values: Distance = 60 mph × 2.5 hours\n" .
                 "   Step 3: Calculate: Distance = 150 miles\n" .
                 "   Final Answer: The car travels 150 miles\n\n" .
                 "Now solve problems using this same step-by-step format.";

$newProblem = "A pizza is cut into 8 slices. If a family of 4 people each eats 2 slices, " .
              "what fraction of the pizza is left?";

echo "Problem: {$newProblem}\n\n";
echo "Using few-shot examples to guide reasoning format...\n\n";

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'system' => $fewShotSystem,
        'messages' => [
            ['role' => 'user', 'content' => $newProblem]
        ]
    ]);
    
    echo extractTextContent($response) . "\n\n";
    echo "💡 Notice: The response follows the same structured format as the examples!\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 3: Complex Reasoning - Logic Puzzle
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 3: Complex Reasoning - Logic Puzzle\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$logicPuzzle = "Three friends Alice, Bob, and Carol are sitting in a row. " .
               "Alice is not sitting next to Carol. " .
               "Bob is sitting to the right of Alice. " .
               "Who is sitting in the middle?";

echo "Puzzle: {$logicPuzzle}\n\n";
echo "Solving with structured reasoning...\n\n";

$structuredSystem = "You are a logic expert. For each problem:\n" .
                    "1. Identify the constraints\n" .
                    "2. List possible arrangements\n" .
                    "3. Eliminate invalid options\n" .
                    "4. Determine the solution\n" .
                    "5. Verify the answer";

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'system' => $structuredSystem,
        'messages' => [
            ['role' => 'user', 'content' => $logicPuzzle]
        ]
    ]);
    
    echo extractTextContent($response) . "\n\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 4: Multi-Step Problem with Verification
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 4: Multi-Step Problem with Self-Verification\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$complexProblem = "A store offers this deal: Buy 2 get 1 free on items priced at \$20 each. " .
                  "Sarah wants to buy 5 items. How much will she pay?";

echo "Problem: {$complexProblem}\n\n";

$verificationSystem = "You solve problems step by step, then verify your answer. " .
                      "Format:\n" .
                      "1. Understand the problem\n" .
                      "2. Plan the solution\n" .
                      "3. Execute step by step\n" .
                      "4. Verify by checking your work\n" .
                      "5. State final answer";

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'system' => $verificationSystem,
        'messages' => [
            ['role' => 'user', 'content' => $complexProblem]
        ]
    ]);
    
    echo extractTextContent($response) . "\n\n";
    echo "💡 Notice: Self-verification helps catch errors!\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 5: Comparing Different CoT Approaches
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 5: Comparing CoT Trigger Phrases\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$testProblem = "If you have 3 apples and buy 2 more, then give away half, how many do you have?";

echo "Problem: {$testProblem}\n\n";

$triggerPhrases = [
    "Let's think step by step.",
    "Let's work this out systematically.",
    "Let's break this down.",
    "Let's approach this logically."
];

foreach ($triggerPhrases as $i => $phrase) {
    echo "Trigger " . ($i + 1) . ": \"{$phrase}\"\n";
    echo str_repeat("-", 80) . "\n";
    
    try {
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 512,
            'messages' => [
                ['role' => 'user', 'content' => $testProblem . "\n\n" . $phrase]
            ]
        ]);
        
        $answer = extractTextContent($response);
        // Show first 150 characters
        echo substr($answer, 0, 150);
        if (strlen($answer) > 150) echo "...";
        echo "\n\n";
    } catch (Exception $e) {
        echo "Error: {$e->getMessage()}\n\n";
    }
}

echo "💡 All trigger phrases work, but some may produce more detailed reasoning.\n";
echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 6: CoT for Non-Mathematical Reasoning
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 6: CoT for Decision Making\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$decisionProblem = "Should a small business choose MySQL or PostgreSQL for their database? " .
                   "Consider: ease of use, performance, cost, community support, and scalability.";

echo "Decision: {$decisionProblem}\n\n";

$decisionSystem = "You are a technical consultant. Analyze decisions by:\n" .
                  "1. Identifying key factors\n" .
                  "2. Evaluating each option against each factor\n" .
                  "3. Weighing pros and cons\n" .
                  "4. Making a recommendation with reasoning";

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1536,
        'system' => $decisionSystem,
        'messages' => [
            ['role' => 'user', 'content' => $decisionProblem]
        ]
    ]);
    
    echo extractTextContent($response) . "\n\n";
    echo "💡 CoT works for qualitative reasoning, not just math!\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Summary
// ============================================================================

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                           Tutorial Summary                                 ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "✅ Chain of Thought Techniques Demonstrated:\n\n";

echo "1️⃣  Zero-Shot CoT\n";
echo "   • Simple trigger phrases\n";
echo "   • 'Let's think step by step'\n";
echo "   • Works without examples\n\n";

echo "2️⃣  Few-Shot CoT\n";
echo "   • Provide reasoning examples\n";
echo "   • Consistent output format\n";
echo "   • Better structured responses\n\n";

echo "3️⃣  Structured Reasoning\n";
echo "   • Define reasoning steps\n";
echo "   • System prompts guide process\n";
echo "   • Comprehensive analysis\n\n";

echo "4️⃣  Self-Verification\n";
echo "   • Check work after solving\n";
echo "   • Catch potential errors\n";
echo "   • Increase confidence\n\n";

echo "5️⃣  Flexible Applications\n";
echo "   • Math problems\n";
echo "   • Logic puzzles\n";
echo "   • Decision making\n";
echo "   • Analysis tasks\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🎯 When to Use Chain of Thought:\n\n";

echo "  ✓ Problems requiring logical reasoning\n";
echo "  ✓ When transparency is important\n";
echo "  ✓ Educational contexts\n";
echo "  ✓ No external tools needed\n";
echo "  ✓ Step-by-step explanation valuable\n\n";

echo "⚠️  When NOT to Use CoT Alone:\n\n";

echo "  ✗ Need exact calculations (use calculator tools)\n";
echo "  ✗ Require real-time data (use web search)\n";
echo "  ✗ Complex computations (use specialized tools)\n";
echo "  ✗ External API calls needed (use ReAct)\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🚀 CoT is powerful for pure reasoning tasks!\n\n";
echo "Next: Tutorial 8 - Tree of Thoughts for multi-path exploration\n";
echo "→ tutorials/08-tree-of-thoughts/\n\n";


