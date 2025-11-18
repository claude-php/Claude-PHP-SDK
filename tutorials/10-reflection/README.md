# Tutorial 10: Reflection & Self-Critique

**Time: 45 minutes** | **Difficulty: Intermediate**

Reflection enables agents to evaluate their own outputs, identify issues, and iteratively improve results. This meta-cognitive capability is key to building high-quality, self-correcting AI systems.

## 🎯 Learning Objectives

By the end of this tutorial, you'll be able to:

- Implement reflection loops for self-evaluation
- Build agents that critique their own work
- Use iterative refinement to improve outputs
- Define quality criteria for different tasks
- Apply reflection to code, writing, and decisions
- Combine reflection with other patterns
- Understand when reflection adds value vs overhead

## 🏗️ What We're Building

We'll implement reflection agents that:

1. **Generate** - Create initial output
2. **Reflect** - Evaluate quality and identify issues  
3. **Refine** - Improve based on reflection
4. **Iterate** - Repeat until quality threshold met
5. **Compare** - Show before/after improvements

## 📋 Prerequisites

Make sure you have:

- Completed [Tutorial 9: Plan-and-Execute](../09-plan-and-execute/)
- Understanding of quality assessment
- PHP 8.1+ installed
- Claude PHP SDK configured

## 🤔 What is Reflection?

Reflection is the ability to examine and evaluate one's own outputs, thoughts, and processes. In AI agents, reflection enables:

- **Self-evaluation** - Assess quality of outputs
- **Error detection** - Find mistakes and issues
- **Iterative improvement** - Refine through multiple passes
- **Learning** - Understand what works and what doesn't

### Simple Example

**Without Reflection:**
```
Task: Write a function to reverse a string
Output: function reverse($s) { return strrev($s); }
Done!
```

**With Reflection:**
```
Task: Write a function to reverse a string

Generate:
function reverse($s) { return strrev($s); }

Reflect:
- Uses built-in function (good)
- No input validation (issue)
- No documentation (issue)
- No edge case handling (issue)

Refine:
/**
 * Reverses a string safely
 * @param string|null $s Input string
 * @return string Reversed string
 */
function reverse(?string $s): string {
    if ($s === null || $s === '') {
        return '';
    }
    return strrev($s);
}

Better!
```

## 🔑 Key Concepts

### 1. Generate-Reflect-Refine Loop

The core pattern:

```php
$output = generate($task);

for ($iteration = 1; $iteration <= $maxIterations; $iteration++) {
    $reflection = reflect($output, $criteria);
    
    $score = extractScore($reflection);
    
    if ($score >= $qualityThreshold) {
        echo "Quality threshold reached!\n";
        break;
    }
    
    $issues = extractIssues($reflection);
    $output = refine($output, $issues);
}

return $output;
```

### 2. Quality Criteria

Define what "good" means for your task:

```php
$criteria = [
    'correctness' => [
        'weight' => 0.4,
        'description' => 'Is the solution correct and accurate?'
    ],
    'completeness' => [
        'weight' => 0.3,
        'description' => 'Are all requirements addressed?'
    ],
    'clarity' => [
        'weight' => 0.2,
        'description' => 'Is it easy to understand?'
    ],
    'efficiency' => [
        'weight' => 0.1,
        'description' => 'Is it reasonably optimal?'
    ]
];
```

### 3. Reflection Prompts

Different types of reflection questions:

**Quality Assessment:**
```
"Evaluate this output on a scale of 1-10 for:
- Correctness (1-10)
- Completeness (1-10)
- Clarity (1-10)
Overall score and reasoning?"
```

**Issue Identification:**
```
"Review this carefully and identify:
1. Errors or mistakes
2. Missing information
3. Unclear explanations
4. Potential improvements"
```

**Comparative Analysis:**
```
"Compare this output to best practices:
- What aligns with standards?
- What deviates from best practices?
- What could be better?"
```

### 4. Targeted Refinement

Fix specific issues:

```php
$refinementPrompt = "Improve this output by:\n";
foreach ($issues as $issue) {
    $refinementPrompt .= "- {$issue['type']}: {$issue['description']}\n";
}
$refinementPrompt .= "\nOriginal output:\n{$output}";
```

## 💡 Reflection Implementations

### Basic Reflection Function

```php
function reflectAndRefine($client, $task, $initialOutput, $maxIterations = 3) {
    $output = $initialOutput;
    $history = [];
    
    for ($i = 0; $i < $maxIterations; $i++) {
        echo "Iteration " . ($i + 1) . "\n";
        echo str_repeat("-", 60) . "\n";
        
        // Reflect
        $reflectionPrompt = "Task: {$task}\n\n" .
                           "Current output:\n{$output}\n\n" .
                           "Evaluate this output:\n" .
                           "1. What's working well?\n" .
                           "2. What issues exist?\n" .
                           "3. How can it be improved?\n" .
                           "4. Overall quality score (1-10)";
        
        $reflection = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => [[
                'role' => 'user',
                'content' => $reflectionPrompt
            ]]
        ]);
        
        $reflectionText = extractTextContent($reflection);
        echo "Reflection:\n{$reflectionText}\n\n";
        
        // Extract score
        preg_match('/(?:score|quality)[:\s]+(\d+)/i', $reflectionText, $matches);
        $score = isset($matches[1]) ? (int)$matches[1] : 5;
        
        $history[] = [
            'iteration' => $i + 1,
            'output' => $output,
            'reflection' => $reflectionText,
            'score' => $score
        ];
        
        if ($score >= 9) {
            echo "Quality threshold reached (score: {$score}/10)!\n";
            break;
        }
        
        // Refine
        $refinementPrompt = "Task: {$task}\n\n" .
                           "Current output:\n{$output}\n\n" .
                           "Reflection:\n{$reflectionText}\n\n" .
                           "Improve the output based on the reflection. " .
                           "Address the identified issues.";
        
        $refined = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 2048,
            'messages' => [[
                'role' => 'user',
                'content' => $refinementPrompt
            ]]
        ]);
        
        $output = extractTextContent($refined);
        echo "Refined output:\n{$output}\n\n";
    }
    
    return ['final_output' => $output, 'history' => $history];
}
```

## 🎯 Application Examples

### 1. Code Generation with Reflection

```php
function generateCodeWithReflection($client, $requirement) {
    // Generate
    $code = generateCode($client, $requirement);
    
    // Reflect
    $review = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'system' => 'You are an expert code reviewer.',
        'messages' => [[
            'role' => 'user',
            'content' => "Review this code:\n\n{$code}\n\n" .
                        "Check for:\n" .
                        "- Security issues\n" .
                        "- Performance problems\n" .
                        "- Code quality\n" .
                        "- Best practices\n" .
                        "- Edge cases"
        ]]
    ]);
    
    // Refine if issues found
    $reviewText = extractTextContent($review);
    if (containsIssues($reviewText)) {
        $improved = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 2048,
            'messages' => [[
                'role' => 'user',
                'content' => "Original code:\n{$code}\n\n" .
                            "Review:\n{$reviewText}\n\n" .
                            "Fix the identified issues."
            ]]
        ]);
        $code = extractTextContent($improved);
    }
    
    return $code;
}
```

### 2. Essay Writing with Multiple Refinements

```php
function writeEssayWithReflection($client, $topic, $iterations = 3) {
    // Initial draft
    $essay = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 2048,
        'messages' => [[
            'role' => 'user',
            'content' => "Write a short essay about: {$topic}"
        ]]
    ]);
    
    $draft = extractTextContent($essay);
    
    // Iterative refinement
    for ($i = 0; $i < $iterations; $i++) {
        // Critique
        $critique = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'system' => 'You are a writing instructor.',
            'messages' => [[
                'role' => 'user',
                'content' => "Critique this essay:\n\n{$draft}\n\n" .
                            "Evaluate:\n" .
                            "- Argument strength\n" .
                            "- Evidence quality\n" .
                            "- Structure and flow\n" .
                            "- Clarity and style\n" .
                            "- Specific improvements needed"
            ]]
        ]);
        
        $feedback = extractTextContent($critique);
        
        // Revise
        $revision = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 2048,
            'messages' => [[
                'role' => 'user',
                'content' => "Essay:\n{$draft}\n\n" .
                            "Feedback:\n{$feedback}\n\n" .
                            "Revise the essay to address the feedback."
            ]]
        ]);
        
        $draft = extractTextContent($revision);
    }
    
    return $draft;
}
```

### 3. Decision Making with Pros/Cons Analysis

```php
function makeDecisionWithReflection($client, $question, $options) {
    // Initial decision
    $optionsList = implode("\n", array_map(
        fn($o) => "- {$o}",
        $options
    ));
    
    $decision = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [[
            'role' => 'user',
            'content' => "Decision: {$question}\n\n" .
                        "Options:\n{$optionsList}\n\n" .
                        "Make a recommendation with reasoning."
        ]]
    ]);
    
    $recommendation = extractTextContent($decision);
    
    // Reflect on decision
    $reflection = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1536,
        'system' => "You are a devil's advocate. Question decisions critically.",
        'messages' => [[
            'role' => 'user',
            'content' => "Decision question: {$question}\n\n" .
                        "Recommendation: {$recommendation}\n\n" .
                        "Analyze:\n" .
                        "- What are the risks?\n" .
                        "- What was overlooked?\n" .
                        "- What are alternative views?\n" .
                        "- Could this backfire?"
        ]]
    ]);
    
    $critique = extractTextContent($reflection);
    
    // Revise decision
    $final = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1536,
        'messages' => [[
            'role' => 'user',
            'content' => "Original recommendation: {$recommendation}\n\n" .
                        "Critical analysis: {$critique}\n\n" .
                        "Provide a final, balanced recommendation " .
                        "that addresses the critiques."
        ]]
    ]);
    
    return extractTextContent($final);
}
```

## 📊 Advanced Reflection Patterns

### Multi-Aspect Reflection

Evaluate different dimensions separately:

```php
function multiAspectReflection($client, $output) {
    $aspects = [
        'technical' => 'Evaluate technical correctness and accuracy',
        'clarity' => 'Evaluate clarity and understandability',
        'completeness' => 'Evaluate whether all parts are addressed',
        'style' => 'Evaluate adherence to style guidelines'
    ];
    
    $scores = [];
    foreach ($aspects as $aspect => $criteria) {
        $evaluation = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 512,
            'messages' => [[
                'role' => 'user',
                'content' => "Output: {$output}\n\n{$criteria}\n\nScore 1-10:"
            ]]
        ]);
        
        $text = extractTextContent($evaluation);
        preg_match('/(\d+)/', $text, $matches);
        $scores[$aspect] = isset($matches[1]) ? (int)$matches[1] : 5;
    }
    
    return $scores;
}
```

### Comparative Reflection

Generate multiple variants and compare:

```php
function comparativeReflection($client, $task) {
    // Generate 3 variants
    $variants = [];
    for ($i = 0; $i < 3; $i++) {
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => [['role' => 'user', 'content' => $task]]
        ]);
        $variants[] = extractTextContent($response);
    }
    
    // Compare variants
    $comparison = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 2048,
        'messages' => [[
            'role' => 'user',
            'content' => "Task: {$task}\n\n" .
                        "Variant 1:\n{$variants[0]}\n\n" .
                        "Variant 2:\n{$variants[1]}\n\n" .
                        "Variant 3:\n{$variants[2]}\n\n" .
                        "Compare these variants. " .
                        "Which is best and why? " .
                        "How can the best be improved further?"
        ]]
    ]);
    
    $analysis = extractTextContent($comparison);
    
    // Synthesize best version
    $best = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 2048,
        'messages' => [[
            'role' => 'user',
            'content' => "Analysis: {$analysis}\n\n" .
                        "Create the best possible version " .
                        "incorporating insights from all variants."
        ]]
        ]);
    
    return extractTextContent($best);
}
```

### Iterative Depth Reflection

Increase critique depth each iteration:

```php
function deepReflection($client, $output, $task) {
    $levels = [
        1 => 'Quick surface-level review',
        2 => 'Detailed analysis of key aspects',
        3 => 'Expert-level deep critique'
    ];
    
    $refined = $output;
    foreach ($levels as $level => $instruction) {
        $reflection = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024 * $level,
            'messages' => [[
                'role' => 'user',
                'content' => "Task: {$task}\n\n" .
                            "Output: {$refined}\n\n" .
                            "Level {$level} review: {$instruction}\n\n" .
                            "Provide critique and improved version."
            ]]
        ]);
        
        $refined = extractTextContent($reflection);
    }
    
    return $refined;
}
```

## ⚙️ Reflection Configuration

### Quality Thresholds

```php
$thresholds = [
    'minimum' => 6,      // Below this = major issues
    'acceptable' => 7,   // Okay to use
    'good' => 8,         // High quality
    'excellent' => 9     // Outstanding
];
```

### Iteration Limits

```php
$config = [
    'max_iterations' => 3,           // Hard limit
    'target_score' => 8,             // Stop if reached
    'min_improvement' => 0.5,        // Stop if progress stalls
    'timeout_seconds' => 300         // Time limit
];
```

### Cost Management

```php
function managedReflection($client, $task, $budget) {
    $cost = 0;
    $iterations = 0;
    $output = generate($client, $task);
    
    while ($cost < $budget && $iterations < 5) {
        $reflection = reflect($client, $output);
        $cost += estimateCost($reflection);
        
        if ($cost >= $budget) {
            break;
        }
        
        $output = refine($client, $output, $reflection);
        $cost += estimateCost($output);
        $iterations++;
    }
    
    return ['output' => $output, 'cost' => $cost, 'iterations' => $iterations];
}
```

## 🎨 Real-World Applications

### 1. API Design Review

```php
// Generate API design
// Reflect on consistency, REST principles, security
// Refine based on best practices
```

### 2. Test Case Generation

```php
// Generate test cases
// Reflect on coverage, edge cases, maintainability
// Add missing tests
```

### 3. Documentation Writing

```php
// Write documentation
// Reflect on clarity, completeness, examples
// Improve based on feedback
```

### 4. SQL Query Optimization

```php
// Write query
// Reflect on performance, indexes, complexity
// Optimize based on analysis
```

## ⚠️ When to Use Reflection

**Good Use Cases:**

✅ Code quality is critical (production systems)
✅ Output will be used by others (documentation, APIs)
✅ Errors are costly (financial, safety-critical)
✅ Learning/improvement over time is valuable
✅ Multiple quality dimensions matter

**Poor Use Cases:**

❌ Simple, straightforward tasks
❌ First-pass exploratory work
❌ Time/cost very constrained
❌ Output is temporary/disposable
❌ Quality bar is low

## 📈 Measuring Reflection Effectiveness

Track improvement metrics:

```php
$metrics = [
    'initial_score' => 6,
    'final_score' => 9,
    'improvement' => 3,
    'iterations' => 2,
    'time_seconds' => 45,
    'cost_dollars' => 0.08,
    'value_gained' => 'high'
];
```

## ✅ Checkpoint

Before moving on, make sure you understand:

- [ ] Generate-Reflect-Refine loop structure
- [ ] How to define quality criteria
- [ ] Different types of reflection prompts
- [ ] Targeted refinement techniques
- [ ] When reflection adds value vs overhead
- [ ] How to set iteration limits and thresholds
- [ ] Multi-aspect and comparative reflection
- [ ] Cost-benefit trade-offs

## 🚀 Next Steps

You've mastered Reflection and Self-Critique! But what if we need multiple specialized agents working together?

**[Tutorial 11: Hierarchical Agents →](../11-hierarchical-agents/)**

Learn how to build master-worker agent hierarchies for complex tasks!

## 💻 Try It Yourself

Run the complete working example:

```bash
php tutorials/10-reflection/reflection_agent.php
```

The script demonstrates:

- ✅ Generate-Reflect-Refine loops
- ✅ Quality assessment with scoring
- ✅ Iterative improvement cycles
- ✅ Code review with reflection
- ✅ Convergence detection
- ✅ Multi-round refinement

## 💡 Key Takeaways

1. **Reflection improves quality** - Self-evaluation catches issues early
2. **Iterate to perfection** - Multiple rounds often better than one pass
3. **Define clear criteria** - Know what "good" looks like
4. **Target improvements** - Fix specific issues, don't regenerate blindly
5. **Balance cost vs quality** - More iterations = better output but higher cost
6. **Combine with other patterns** - Reflection + ReAct, Reflection + Planning
7. **Not always needed** - Simple tasks don't benefit from reflection
8. **Measure improvement** - Track before/after to validate value

## 📚 Further Reading

### Research Papers

- **[Reflexion: Language Agents with Verbal Reinforcement Learning](https://arxiv.org/abs/2303.11366)** - Shinn et al., 2023
- **[Self-Refine: Iterative Refinement with Self-Feedback](https://arxiv.org/abs/2303.17651)** - Madaan et al., 2023
- **[Constitutional AI](https://arxiv.org/abs/2212.08073)** - Bai et al., 2022

### Related Tutorials

- [Tutorial 5: Advanced ReAct](../05-advanced-react/) - Combines reflection with ReAct
- [Tutorial 8: Tree of Thoughts](../08-tree-of-thoughts/) - Explores alternatives
- [Tutorial 9: Plan-and-Execute](../09-plan-and-execute/) - Systematic execution

### Claude Documentation

- [Prompt Engineering Guide](https://docs.anthropic.com/en/docs/prompt-engineering)
- [Best Practices](https://docs.anthropic.com/en/docs/build-with-claude/prompt-engineering/overview)

## 🎓 Practice Exercises

Try implementing reflection for:

1. **Code Review** - Generate, review security/performance, improve
2. **Writing** - Draft → Critique → Revise (3 rounds)
3. **Design** - Propose solution → Challenge assumptions → Refine
4. **Testing** - Generate tests → Check coverage → Add missing cases

## 🔧 Troubleshooting

**Issue**: Reflection doesn't improve output
- **Solution**: Make criteria more specific, provide examples of good/bad

**Issue**: Too many iterations without convergence
- **Solution**: Set stricter thresholds, limit iterations, check criteria validity

**Issue**: High cost for marginal improvement
- **Solution**: Reduce iterations, increase score threshold, use cheaper model

**Issue**: Reflection identifies same issues repeatedly
- **Solution**: Be more explicit in refinement prompts, provide templates
