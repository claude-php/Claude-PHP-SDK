#!/usr/bin/env php
<?php
/**
 * Tutorial 8: Tree of Thoughts (ToT) - Working Example
 * 
 * Demonstrates the Tree of Thoughts pattern for exploring multiple
 * reasoning paths, evaluating them, and backtracking when needed.
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../examples/helpers.php';
require_once __DIR__ . '/../helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║           Tutorial 8: Tree of Thoughts (ToT) - Multi-Path Reasoning       ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Helper Functions
// ============================================================================

/**
 * Generate multiple thought branches
 */
function generateThoughts($client, $problem, $context = '', $count = 3) {
    $prompt = "";
    
    if ($context) {
        $prompt .= "Context so far: {$context}\n\n";
    }
    
    $prompt .= "Problem: {$problem}\n\n";
    $prompt .= "Generate {$count} different approaches or next steps. ";
    $prompt .= "For each, provide:\n";
    $prompt .= "1. The approach/step\n";
    $prompt .= "2. Brief reasoning\n\n";
    $prompt .= "Format as:\n";
    $prompt .= "Approach 1: [description]\n";
    $prompt .= "Reasoning: [why this might work]\n\n";
    
    try {
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1536,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);
        
        return extractTextContent($response);
    } catch (Exception $e) {
        return "Error generating thoughts: {$e->getMessage()}";
    }
}

/**
 * Evaluate a thought branch
 */
function evaluateThought($client, $thought, $problem) {
    $prompt = "Problem: {$problem}\n\n";
    $prompt .= "Proposed approach: {$thought}\n\n";
    $prompt .= "Evaluate this approach on a scale of 1-10, considering:\n";
    $prompt .= "- Likelihood of success (0-5 points)\n";
    $prompt .= "- Efficiency/simplicity (0-5 points)\n\n";
    $prompt .= "Provide:\n";
    $prompt .= "Score: X/10\n";
    $prompt .= "Reasoning: [brief explanation]";
    
    try {
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 512,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);
        
        $text = extractTextContent($response);
        
        // Extract score
        if (preg_match('/Score:\s*(\d+)/', $text, $matches)) {
            $score = (int)$matches[1];
        } else {
            $score = 5; // Default
        }
        
        return ['score' => $score, 'evaluation' => $text];
    } catch (Exception $e) {
        return ['score' => 0, 'evaluation' => "Error: {$e->getMessage()}"];
    }
}

/**
 * Visualize tree structure
 */
function visualizeTree($nodes, $indent = 0) {
    foreach ($nodes as $i => $node) {
        $prefix = str_repeat("  ", $indent);
        $branch = $indent > 0 ? "├─ " : "";
        
        echo $prefix . $branch . $node['label'];
        if (isset($node['score'])) {
            echo " [Score: {$node['score']}/10]";
        }
        echo "\n";
        
        if (isset($node['children'])) {
            visualizeTree($node['children'], $indent + 1);
        }
    }
}

// ============================================================================
// Example 1: Game of 24 Problem
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 1: Game of 24 - Classic ToT Problem\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$game24Problem = "Use the numbers 4, 6, 7, 8 exactly once with operations (+, -, ×, ÷) to make 24.";

echo "Problem: {$game24Problem}\n\n";

echo "Step 1: Generate initial approaches\n";
echo str_repeat("-", 80) . "\n";

$thoughts = generateThoughts($client, $game24Problem, '', 4);
echo $thoughts . "\n\n";

echo "Step 2: Let's evaluate one promising approach in detail\n";
echo str_repeat("-", 80) . "\n";

$approach = "Try: 6 ÷ (8 - 7) × 4";
echo "Evaluating: {$approach}\n\n";

$evaluation = evaluateThought($client, $approach, $game24Problem);
echo $evaluation['evaluation'] . "\n\n";

echo "Step 3: Execute the approach\n";
echo str_repeat("-", 80) . "\n";

$executePrompt = "Problem: {$game24Problem}\n\n";
$executePrompt .= "Approach: {$approach}\n\n";
$executePrompt .= "Execute this step by step and verify if it equals 24.";

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            ['role' => 'user', 'content' => $executePrompt]
        ]
    ]);
    
    echo extractTextContent($response) . "\n\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

echo "💡 ToT explores multiple paths and backtracks from unsuccessful ones!\n";
echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 2: Creative Writing with Branching
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 2: Creative Writing - Exploring Story Paths\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$storyStart = "A detective enters an abandoned mansion. " .
              "The door slams shut behind her. " .
              "She hears footsteps upstairs.";

echo "Story so far: {$storyStart}\n\n";

echo "Step 1: Generate possible continuations\n";
echo str_repeat("-", 80) . "\n";

$continuations = generateThoughts(
    $client, 
    "Continue this mystery story in an interesting way", 
    $storyStart,
    3
);
echo $continuations . "\n\n";

echo "Step 2: Evaluate continuations for drama and coherence\n";
echo str_repeat("-", 80) . "\n";

// For demo, evaluate one continuation
$sampleContinuation = "She draws her weapon and carefully climbs the stairs, " .
                      "noticing fresh muddy footprints that weren't there before.";

echo "Evaluating: \"{$sampleContinuation}\"\n\n";

$eval = evaluateThought(
    $client, 
    $sampleContinuation, 
    "Continuation should be dramatic, coherent, and advance the mystery"
);
echo $eval['evaluation'] . "\n\n";

echo "💡 ToT helps explore creative options before committing to one path!\n";
echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 3: Logic Puzzle with Backtracking
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 3: Logic Puzzle - Knights and Knaves\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$puzzle = "On an island, knights always tell the truth and knaves always lie. " .
          "You meet two people, A and B. " .
          "A says: 'We are both knaves.' " .
          "What are A and B?";

echo "Puzzle: {$puzzle}\n\n";

echo "Exploring multiple reasoning paths...\n";
echo str_repeat("-", 80) . "\n\n";

$systemPrompt = "You solve logic puzzles by exploring different possibilities. " .
                "For each possibility, check if it leads to a contradiction.";

// Path exploration prompt
$explorationPrompt = "Puzzle: {$puzzle}\n\n" .
                     "Explore these possibilities:\n" .
                     "1. Assume A is a knight\n" .
                     "2. Assume A is a knave\n\n" .
                     "For each assumption, check if it's consistent with A's statement. " .
                     "Show your reasoning for each path.";

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1536,
        'system' => $systemPrompt,
        'messages' => [
            ['role' => 'user', 'content' => $explorationPrompt]
        ]
    ]);
    
    echo extractTextContent($response) . "\n\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

echo "💡 ToT systematically explores logical possibilities!\n";
echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 4: Visualizing the Thought Tree
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 4: Visualizing the Thought Tree\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Problem: Find the best route for a delivery\n\n";

// Simulated tree structure for visualization
$thoughtTree = [
    [
        'label' => 'Problem: Deliver to 3 locations',
        'score' => null,
        'children' => [
            [
                'label' => 'Route A: Location 1 → 2 → 3',
                'score' => 7,
                'children' => [
                    ['label' => 'Distance: 15 km', 'score' => 6],
                    ['label' => 'Time: 45 min', 'score' => 7]
                ]
            ],
            [
                'label' => 'Route B: Location 2 → 1 → 3',
                'score' => 5,
                'children' => [
                    ['label' => 'Distance: 20 km', 'score' => 4],
                    ['label' => 'Time: 60 min ✗ (too long)', 'score' => 3]
                ]
            ],
            [
                'label' => 'Route C: Location 1 → 3 → 2',
                'score' => 9,
                'children' => [
                    ['label' => 'Distance: 12 km ✓', 'score' => 9],
                    ['label' => 'Time: 35 min ✓ (best)', 'score' => 9]
                ]
            ]
        ]
    ]
];

echo "Thought Tree Visualization:\n";
echo str_repeat("-", 80) . "\n";
visualizeTree($thoughtTree);
echo "\n";

echo "Selected Path: Route C (highest scores)\n";
echo "Decision: Location 1 → 3 → 2 (12km, 35min)\n\n";

echo "💡 Visualizing helps understand exploration process!\n";
echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 5: Comparison - CoT vs ToT
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 5: Comparing Chain of Thought vs Tree of Thoughts\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$testProblem = "You have 9 identical-looking coins, but one is counterfeit and lighter. " .
               "You have a balance scale. What's the minimum number of weighings needed?";

echo "Problem: {$testProblem}\n\n";

// CoT approach
echo "🔹 Chain of Thought (Single Path):\n";
echo str_repeat("-", 80) . "\n";

try {
    $cotResponse = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            ['role' => 'user', 'content' => $testProblem . "\n\nLet's solve this step by step."]
        ]
    ]);
    
    $cotAnswer = extractTextContent($cotResponse);
    echo substr($cotAnswer, 0, 300) . "...\n\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// ToT approach
echo "🌳 Tree of Thoughts (Multiple Paths):\n";
echo str_repeat("-", 80) . "\n";

$totPrompt = "Problem: {$testProblem}\n\n" .
             "Generate 3 different strategies for solving this. " .
             "Then evaluate which strategy is most efficient.";

try {
    $totResponse = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1536,
        'messages' => [
            ['role' => 'user', 'content' => $totPrompt]
        ]
    ]);
    
    echo extractTextContent($totResponse) . "\n\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

echo "💡 ToT explores alternatives before committing, often finding better solutions!\n";
echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 6: Practical Application - Code Optimization
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 6: Code Optimization Strategies\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$codeScenario = "We have a PHP function that processes 10,000 database records. " .
                "It currently takes 30 seconds. How can we optimize it?";

echo "Scenario: {$codeScenario}\n\n";

echo "Exploring optimization strategies with ToT...\n";
echo str_repeat("-", 80) . "\n\n";

$strategiesPrompt = "Scenario: {$codeScenario}\n\n" .
                    "Generate 3 different optimization strategies. " .
                    "For each, explain:\n" .
                    "1. The approach\n" .
                    "2. Expected performance gain\n" .
                    "3. Implementation complexity\n" .
                    "4. Potential risks";

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1536,
        'messages' => [
            ['role' => 'user', 'content' => $strategiesPrompt]
        ]
    ]);
    
    echo extractTextContent($response) . "\n\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

echo "💡 ToT helps evaluate trade-offs between different technical approaches!\n";
echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Summary
// ============================================================================

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                           Tutorial Summary                                 ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "✅ Tree of Thoughts Concepts Demonstrated:\n\n";

echo "1️⃣  Multi-Path Exploration\n";
echo "   • Generate multiple approaches\n";
echo "   • Don't commit to first idea\n";
echo "   • Explore alternatives\n\n";

echo "2️⃣  Evaluation & Scoring\n";
echo "   • Rate each approach\n";
echo "   • Compare options objectively\n";
echo "   • Select most promising paths\n\n";

echo "3️⃣  Backtracking\n";
echo "   • Recognize dead ends\n";
echo "   • Abandon unsuccessful paths\n";
echo "   • Try alternative approaches\n\n";

echo "4️⃣  Systematic Exploration\n";
echo "   • BFS, DFS, or best-first\n";
echo "   • Structured search\n";
echo "   • Complete coverage\n\n";

echo "5️⃣  Applications\n";
echo "   • Puzzles and games\n";
echo "   • Creative writing\n";
echo "   • Logic problems\n";
echo "   • Optimization tasks\n";
echo "   • Strategic planning\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🎯 When to Use Tree of Thoughts:\n\n";

echo "  ✓ Complex problems with multiple solutions\n";
echo "  ✓ When first idea might not be best\n";
echo "  ✓ Puzzles requiring exploration\n";
echo "  ✓ Strategic decisions with trade-offs\n";
echo "  ✓ Creative tasks needing options\n\n";

echo "⚠️  ToT Limitations:\n\n";

echo "  ✗ More expensive (multiple API calls)\n";
echo "  ✗ Takes longer than CoT\n";
echo "  ✗ Overkill for simple problems\n";
echo "  ✗ Requires good evaluation functions\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🚀 ToT enables sophisticated multi-path reasoning!\n\n";
echo "Next: Tutorial 9 - Plan-and-Execute for structured action\n";
echo "→ tutorials/09-plan-and-execute/\n\n";


