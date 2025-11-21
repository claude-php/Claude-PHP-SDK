#!/usr/bin/env php
<?php
/**
 * Tutorial 10: Reflection & Self-Critique - Working Example
 * 
 * Demonstrates the Generate-Reflect-Refine pattern for iterative improvement
 * through self-evaluation and targeted refinement.
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../examples/helpers.php';
require_once __DIR__ . '/../helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║        Tutorial 10: Reflection & Self-Critique - Iterative Improvement    ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Helper Functions
// ============================================================================

/**
 * Extract quality score from reflection text
 */
function extractScore($text) {
    // Look for patterns like "Score: 7/10" or "Quality: 8"
    if (preg_match('/(?:score|quality|rating)[:\s]+(\d+)(?:\/10)?/i', $text, $matches)) {
        return (int)$matches[1];
    }
    return 5; // Default if no score found
}

/**
 * Simple reflection and refinement function
 */
function reflectAndRefine($client, $task, $output, $criteria = null) {
    $criteriaText = $criteria ?? "correctness, completeness, clarity, and quality";
    
    $reflectionPrompt = "Task: {$task}\n\n" .
                       "Current output:\n{$output}\n\n" .
                       "Evaluate this output on {$criteriaText}:\n" .
                       "1. What's working well?\n" .
                       "2. What issues or problems exist?\n" .
                       "3. How can it be improved?\n" .
                       "4. Overall quality score (1-10)";
    
    try {
        $reflection = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => [['role' => 'user', 'content' => $reflectionPrompt]]
        ]);
        
        $reflectionText = extractTextContent($reflection);
        $score = extractScore($reflectionText);
        
        // Refine if needed
        if ($score < 9) {
            $refinementPrompt = "Task: {$task}\n\n" .
                               "Current output:\n{$output}\n\n" .
                               "Reflection:\n{$reflectionText}\n\n" .
                               "Improve the output by addressing the issues identified in the reflection.";
            
            $refined = $client->messages()->create([
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 2048,
                'messages' => [['role' => 'user', 'content' => $refinementPrompt]]
            ]);
            
            return [
                'output' => extractTextContent($refined),
                'reflection' => $reflectionText,
                'score' => $score,
                'improved' => true
            ];
        }
        
        return [
            'output' => $output,
            'reflection' => $reflectionText,
            'score' => $score,
            'improved' => false
        ];
        
    } catch (Exception $e) {
        return [
            'output' => $output,
            'error' => $e->getMessage(),
            'improved' => false
        ];
    }
}

// ============================================================================
// Example 1: Code Generation with Reflection
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 1: Code Review with Self-Reflection\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$codeTask = "Write a PHP function to check if a string is a palindrome";

echo "Task: {$codeTask}\n\n";
echo "╔════ Round 1: Initial Generation ════╗\n\n";

try {
    $generate = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [['role' => 'user', 'content' => $codeTask]]
    ]);
    
    $code = extractTextContent($generate);
    echo $code . "\n\n";
    
    echo "╔════ Round 2: Reflection ════╗\n\n";
    
    $reflect = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'system' => 'You are an expert code reviewer focusing on security, quality, and best practices.',
        'messages' => [[
            'role' => 'user',
            'content' => "Review this code:\n\n{$code}\n\n" .
                        "Evaluate:\n" .
                        "1. Correctness - Does it work?\n" .
                        "2. Edge cases - What about empty strings, special characters?\n" .
                        "3. Code quality - Type hints, documentation, naming?\n" .
                        "4. Best practices - Modern PHP standards?\n" .
                        "5. Security - Any vulnerabilities?\n\n" .
                        "Score: X/10 and list improvements needed."
        ]]
    ]);
    
    $reflectionText = extractTextContent($reflect);
    echo $reflectionText . "\n\n";
    
    $score = extractScore($reflectionText);
    echo "📊 Score: {$score}/10\n\n";
    
    if ($score < 9) {
        echo "╔════ Round 3: Refinement ════╗\n\n";
        
        $refine = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1536,
            'messages' => [[
                'role' => 'user',
                'content' => "Original code:\n{$code}\n\n" .
                            "Review feedback:\n{$reflectionText}\n\n" .
                            "Improve the code by addressing ALL the issues raised in the review. " .
                            "Add proper documentation, type hints, and edge case handling."
            ]]
        ]);
        
        echo extractTextContent($refine) . "\n\n";
    }
    
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

echo "💡 Reflection catches issues that initial generation misses!\n";
echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 2: Essay Writing with Multiple Refinements
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 2: Iterative Writing Improvement\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$topic = "The benefits and challenges of remote work";
echo "Topic: {$topic}\n\n";

// Start with a basic draft
$draft = "Remote work is good because people don't have to commute. " .
         "They can work from home. This saves time and money. " .
         "But remote work can be lonely. People miss their coworkers.";

echo "╔════ Initial Draft ════╗\n\n";
echo $draft . "\n\n";
echo "Characters: " . strlen($draft) . "\n\n";

$maxIterations = 3;

for ($i = 1; $i <= $maxIterations; $i++) {
    echo "╔════ Iteration {$i}: Critique & Improve ════╗\n\n";
    
    try {
        // Critique
        $critique = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'system' => 'You are a writing instructor. Be constructive but critical.',
            'messages' => [[
                'role' => 'user',
                'content' => "Evaluate this paragraph about remote work:\n\n{$draft}\n\n" .
                            "Critique:\n" .
                            "- Argument strength and depth\n" .
                            "- Use of evidence and examples\n" .
                            "- Structure and transitions\n" .
                            "- Language and style\n" .
                            "- Specific improvements needed\n\n" .
                            "Score: X/10"
            ]]
        ]);
        
        $feedback = extractTextContent($critique);
        $score = extractScore($feedback);
        
        echo "Critique (Score: {$score}/10):\n";
        echo $feedback . "\n\n";
        
        if ($score >= 9) {
            echo "✅ Quality threshold reached!\n\n";
            break;
        }
        
        // Revise
        $revision = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1536,
            'messages' => [[
                'role' => 'user',
                'content' => "Current version:\n{$draft}\n\n" .
                            "Feedback:\n{$feedback}\n\n" .
                            "Rewrite and improve this paragraph based on the feedback. " .
                            "Keep it concise but make it more sophisticated and well-argued."
            ]]
        ]);
        
        $draft = extractTextContent($revision);
        
        echo "Revised version:\n{$draft}\n\n";
        echo "Characters: " . strlen($draft) . "\n\n";
        
    } catch (Exception $e) {
        echo "Error: {$e->getMessage()}\n\n";
        break;
    }
}

echo "💡 Each iteration improves quality through targeted feedback!\n";
echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 3: Decision Making with Pros/Cons Analysis
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 3: Decision Making with Reflection\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$decision = "Should our team adopt microservices architecture or stick with a monolith?";
echo "Decision: {$decision}\n\n";

try {
    // Initial recommendation
    echo "Step 1: Initial Recommendation\n";
    echo str_repeat("-", 80) . "\n";
    
    $initial = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [[
            'role' => 'user',
            'content' => "Question: {$decision}\n\n" .
                        "Provide a recommendation with reasoning."
        ]]
    ]);
    
    $recommendation = extractTextContent($initial);
    echo $recommendation . "\n\n";
    
    // Critical reflection
    echo "Step 2: Critical Reflection (Devil's Advocate)\n";
    echo str_repeat("-", 80) . "\n";
    
    $reflection = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1536,
        'system' => "You are a critical thinker who questions assumptions and identifies risks.",
        'messages' => [[
            'role' => 'user',
            'content' => "Decision: {$decision}\n\n" .
                        "Recommendation:\n{$recommendation}\n\n" .
                        "Play devil's advocate:\n" .
                        "- What risks were overlooked?\n" .
                        "- What assumptions might be wrong?\n" .
                        "- What could go wrong?\n" .
                        "- What alternative perspectives exist?\n" .
                        "- What questions weren't asked?"
        ]]
    ]);
    
    $critique = extractTextContent($reflection);
    echo $critique . "\n\n";
    
    // Balanced final recommendation
    echo "Step 3: Balanced Final Recommendation\n";
    echo str_repeat("-", 80) . "\n";
    
    $final = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1536,
        'messages' => [[
            'role' => 'user',
            'content' => "Decision: {$decision}\n\n" .
                        "Initial recommendation:\n{$recommendation}\n\n" .
                        "Critical analysis:\n{$critique}\n\n" .
                        "Provide a final, balanced recommendation that:\n" .
                        "1. Addresses the critiques\n" .
                        "2. Acknowledges risks\n" .
                        "3. Provides mitigation strategies\n" .
                        "4. Gives clear guidance"
        ]]
    ]);
    
    echo extractTextContent($final) . "\n\n";
    
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

echo "💡 Reflection leads to more balanced, thoughtful decisions!\n";
echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 4: Multi-Aspect Reflection
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 4: Multi-Aspect Quality Assessment\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$testOutput = "PHP 8 introduced named arguments, which let you pass parameters to functions by name rather than position. This is useful.";

echo "Output to evaluate:\n\"{$testOutput}\"\n\n";

$aspects = [
    'technical_accuracy' => 'Is the technical information correct?',
    'completeness' => 'Does it fully explain the topic?',
    'clarity' => 'Is it easy to understand for the target audience?',
    'examples' => 'Are there helpful examples?',
    'depth' => 'Does it provide sufficient detail?'
];

echo "Evaluating multiple aspects:\n";
echo str_repeat("-", 80) . "\n\n";

$scores = [];
foreach ($aspects as $aspect => $question) {
    try {
        $evaluation = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 512,
            'messages' => [[
                'role' => 'user',
                'content' => "Text: \"{$testOutput}\"\n\n" .
                            "Question: {$question}\n\n" .
                            "Rate 1-10 with brief reasoning."
            ]]
        ]);
        
        $text = extractTextContent($evaluation);
        $score = extractScore($text);
        $scores[$aspect] = $score;
        
        echo "• " . str_replace('_', ' ', ucfirst($aspect)) . ": {$score}/10\n";
        
    } catch (Exception $e) {
        $scores[$aspect] = 5;
        echo "• " . str_replace('_', ' ', ucfirst($aspect)) . ": Error\n";
    }
}

$avgScore = round(array_sum($scores) / count($scores), 1);
echo "\n📊 Overall Score: {$avgScore}/10\n\n";

if ($avgScore < 8) {
    echo "Aspects needing improvement:\n";
    foreach ($scores as $aspect => $score) {
        if ($score < 8) {
            echo "  ⚠️  " . str_replace('_', ' ', ucfirst($aspect)) . " ({$score}/10)\n";
        }
    }
    echo "\n";
}

echo "💡 Multi-aspect evaluation provides detailed quality insights!\n";
echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 5: Comparative Reflection
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 5: Comparing Multiple Approaches\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$problem = "Calculate the factorial of a number in PHP";
echo "Problem: {$problem}\n\n";

// Generate 3 different approaches
$approaches = [];
echo "Generating 3 different approaches...\n\n";

for ($i = 1; $i <= 3; $i++) {
    try {
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 512,
            'messages' => [[
                'role' => 'user',
                'content' => "{$problem}. Provide approach #{$i} (make each different)."
            ]]
        ]);
        
        $approaches[$i] = extractTextContent($response);
        echo "Approach {$i}:\n{$approaches[$i]}\n\n";
        
    } catch (Exception $e) {
        echo "Error generating approach {$i}: {$e->getMessage()}\n\n";
    }
}

// Compare approaches
if (count($approaches) >= 2) {
    echo "Comparing approaches...\n";
    echo str_repeat("-", 80) . "\n";
    
    try {
        $comparison = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1536,
            'messages' => [[
                'role' => 'user',
                'content' => "Problem: {$problem}\n\n" .
                            "Approach 1:\n{$approaches[1]}\n\n" .
                            "Approach 2:\n{$approaches[2]}\n\n" .
                            (isset($approaches[3]) ? "Approach 3:\n{$approaches[3]}\n\n" : "") .
                            "Compare these approaches:\n" .
                            "- Which is best for performance?\n" .
                            "- Which is most readable?\n" .
                            "- Which is most maintainable?\n" .
                            "- What are the trade-offs?\n" .
                            "- Overall recommendation?"
            ]]
        ]);
        
        echo extractTextContent($comparison) . "\n\n";
        
    } catch (Exception $e) {
        echo "Error comparing: {$e->getMessage()}\n\n";
    }
}

echo "💡 Comparing alternatives reveals trade-offs and best choices!\n";
echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Summary
// ============================================================================

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                           Tutorial Summary                                 ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "✅ Reflection Techniques Demonstrated:\n\n";

echo "1️⃣  Generate-Reflect-Refine Loop\n";
echo "   • Create initial output\n";
echo "   • Evaluate quality and issues\n";
echo "   • Improve based on reflection\n\n";

echo "2️⃣  Code Review Reflection\n";
echo "   • Security and quality checks\n";
echo "   • Edge case identification\n";
echo "   • Best practices enforcement\n\n";

echo "3️⃣  Iterative Writing\n";
echo "   • Multiple refinement rounds\n";
echo "   • Targeted improvements\n";
echo "   • Quality threshold stopping\n\n";

echo "4️⃣  Decision Analysis\n";
echo "   • Devil's advocate critique\n";
echo "   • Risk identification\n";
echo "   • Balanced recommendations\n\n";

echo "5️⃣  Multi-Aspect Evaluation\n";
echo "   • Separate dimension scoring\n";
echo "   • Comprehensive assessment\n";
echo "   • Targeted improvement areas\n\n";

echo "6️⃣  Comparative Analysis\n";
echo "   • Multiple approach generation\n";
echo "   • Trade-off evaluation\n";
echo "   • Best option selection\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🎯 When to Use Reflection:\n\n";

echo "  ✓ Quality is critical\n";
echo "  ✓ Errors are costly\n";
echo "  ✓ Output for others\n";
echo "  ✓ Complex requirements\n";
echo "  ✓ Learning/improvement valued\n\n";

echo "⚠️  When to Skip Reflection:\n\n";

echo "  • Simple tasks\n";
echo "  • Exploratory work\n";
echo "  • Time/cost constrained\n";
echo "  • Temporary outputs\n";
echo "  • Low quality bar\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "💡 Key Insights:\n\n";

echo "  • Reflection catches what generation misses\n";
echo "  • Multiple rounds compound improvements\n";
echo "  • Clear criteria enable better evaluation\n";
echo "  • Target specific issues, don't regenerate blindly\n";
echo "  • Balance quality gains vs cost/time\n";
echo "  • Combine with other patterns for power\n\n";

echo "🚀 Reflection enables self-improving agents!\n\n";
echo "Next: Tutorial 11 - Hierarchical Agents for complex tasks\n";
echo "→ tutorials/11-hierarchical-agents/\n\n";
