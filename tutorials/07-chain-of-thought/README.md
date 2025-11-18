# Tutorial 7: Chain of Thought (CoT)

**Time: 45 minutes** | **Difficulty: Intermediate**

Chain of Thought (CoT) prompting is a powerful technique that enables Claude to solve complex problems by breaking them down into explicit reasoning steps. Unlike ReAct which uses tools, CoT relies purely on reasoning to arrive at answers.

## 🎯 Learning Objectives

By the end of this tutorial, you'll be able to:

- Understand what Chain of Thought prompting is and when to use it
- Implement zero-shot CoT ("Let's think step by step")
- Use few-shot CoT with examples
- Compare CoT with ReAct patterns
- Apply CoT to mathematical reasoning, logic puzzles, and analysis
- Recognize when CoT is more appropriate than tool use

## 🏗️ What We're Building

We'll explore three types of Chain of Thought agents:

1. **Zero-Shot CoT** - Simple prompting for step-by-step reasoning
2. **Few-Shot CoT** - Providing examples to guide reasoning
3. **Complex CoT** - Multi-step logical reasoning without tools

## 📋 Prerequisites

Make sure you have:

- Completed [Tutorial 6: Agentic Framework](../06-agentic-framework/)
- Understanding of ReAct pattern from earlier tutorials
- PHP 8.1+ installed
- Claude PHP SDK configured

## 🤔 What is Chain of Thought?

Chain of Thought is a prompting technique where the model is encouraged to show its reasoning process explicitly before arriving at a final answer.

### Traditional Approach

```
User: "What is 15% of 80?"
Claude: "12"
```

### Chain of Thought Approach

```
User: "What is 15% of 80? Let's think step by step."
Claude: "Let me break this down:
1. First, I need to convert 15% to a decimal: 15% = 0.15
2. Then multiply by 80: 0.15 × 80
3. Calculating: 0.15 × 80 = 12
Therefore, 15% of 80 is 12."
```

## 🔑 Key Concepts

### 1. Zero-Shot CoT

Simply add "Let's think step by step" to your prompt:

```php
$prompt = "A farmer has 15 chickens. Each chicken lays 2 eggs per day. " .
          "How many eggs does the farmer collect in a week? " .
          "Let's think step by step.";
```

### 2. Few-Shot CoT

Provide examples showing the reasoning process:

```php
$systemPrompt = "You solve problems step by step. Here are examples:

Q: If a book costs $12 and is on 25% discount, what's the sale price?
A: Let me work through this:
1. Calculate the discount: 25% of $12 = $12 × 0.25 = $3
2. Subtract from original: $12 - $3 = $9
The sale price is $9.

Q: A train travels 60 km/h for 2.5 hours. How far does it travel?
A: Let me solve this step by step:
1. Use the formula: Distance = Speed × Time
2. Plug in values: Distance = 60 km/h × 2.5 h
3. Calculate: Distance = 150 km
The train travels 150 km.";
```

### 3. Benefits of CoT

**When to Use CoT:**
- ✅ Mathematical word problems
- ✅ Logical reasoning tasks
- ✅ Multi-step calculations
- ✅ Transparency and explainability required
- ✅ Educational contexts where showing work is important
- ✅ No external tools needed

**When to Use ReAct Instead:**
- ❌ Need to query external APIs or databases
- ❌ Require real-time information
- ❌ Task involves actual computations (use calculator tool)
- ❌ Need to manipulate files or systems

## 📊 CoT vs ReAct Comparison

| Aspect | Chain of Thought | ReAct |
|--------|-----------------|-------|
| **Primary Use** | Pure reasoning | Action + reasoning |
| **External Tools** | None | Required |
| **Transparency** | High (shows thinking) | Moderate |
| **Accuracy** | Good for reasoning | Exact for calculations |
| **Complexity** | Simple implementation | More complex setup |
| **Best For** | Logic, analysis | API calls, computations |

## 💡 Zero-Shot CoT Implementation

The simplest form - just add the magic phrase:

```php
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 2048,
    'messages' => [
        [
            'role' => 'user',
            'content' => $problem . "\n\nLet's think step by step."
        ]
    ]
]);
```

### Magic Phrases

Different phrasings work:
- "Let's think step by step."
- "Let's work this out step by step."
- "Let's approach this systematically."
- "Let's break this down."

## 🎓 Few-Shot CoT Implementation

Provide examples in the system prompt:

```php
$systemPrompt = "You are a logical reasoning expert. " .
                "Always show your reasoning step by step. " .
                "Here are examples of how to approach problems:\n\n" .
                $examples;

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 2048,
    'system' => $systemPrompt,
    'messages' => [
        ['role' => 'user', 'content' => $problem]
    ]
]);
```

## 🧩 Example: Math Word Problem

```php
$problem = "Sarah has 3 boxes. Each box contains 4 bags. " .
           "Each bag has 5 marbles. How many marbles does Sarah have in total?";

// Zero-shot CoT
$prompt = $problem . "\n\nLet's solve this step by step.";
```

Expected reasoning:
```
1. Calculate marbles per box: 4 bags × 5 marbles = 20 marbles
2. Calculate total marbles: 3 boxes × 20 marbles = 60 marbles
Therefore, Sarah has 60 marbles in total.
```

## 🎯 Example: Logic Puzzle

```php
$puzzle = "If all roses are flowers, and some flowers fade quickly, " .
          "can we conclude that some roses fade quickly?";

$prompt = $puzzle . "\n\nLet's think through this logically.";
```

Expected reasoning:
```
1. Premise 1: All roses are flowers (roses ⊂ flowers)
2. Premise 2: Some flowers fade quickly
3. Question: Do some roses fade quickly?

Analysis:
- We know roses are a subset of flowers
- We know some flowers fade quickly
- But we don't know if the flowers that fade quickly include roses
- The "some flowers" could be other types of flowers

Conclusion: No, we cannot conclude that some roses fade quickly.
This is a logic error - just because roses are flowers and some flowers
fade quickly doesn't mean those specific flowers are roses.
```

## 🔧 Advanced: Complex Reasoning

For multi-step problems, structure the prompt:

```php
$systemPrompt = "You are an expert problem solver. For each problem:
1. Identify what's being asked
2. List the given information
3. Determine the steps needed
4. Work through each step clearly
5. State the final answer";

$problem = "A store has a sale: Buy 2 items, get 30% off the cheaper one. " .
           "If Alice buys a $50 shirt and a $30 hat, how much does she pay?";
```

Expected structured reasoning:
```
Problem Analysis:
- Question: Total amount Alice pays
- Given: Shirt = $50, Hat = $30, Discount = 30% off cheaper item
- Cheaper item: Hat ($30)

Steps:
1. Calculate discount on hat: 30% of $30 = $9
2. Calculate discounted hat price: $30 - $9 = $21
3. Add shirt price (no discount): $50 + $21 = $71

Final Answer: Alice pays $71
```

## 🎨 CoT for Creative Tasks

CoT isn't just for math! It works for:

### Code Review

```php
$prompt = "Review this code for security issues:\n" .
          $codeSnippet . "\n\n" .
          "Analyze step by step, checking each line.";
```

### Decision Making

```php
$prompt = "Should we migrate from MySQL to PostgreSQL? " .
          "Consider: performance, cost, learning curve, features. " .
          "Analyze each factor step by step.";
```

### Essay Analysis

```php
$prompt = "Analyze the main argument in this essay:\n" .
          $essay . "\n\n" .
          "Break down the argument structure step by step.";
```

## ⚠️ CoT Limitations

**What CoT Cannot Do:**

1. **Exact Calculations** - May make arithmetic errors
   - Solution: Use calculator tools for precision
   
2. **Real-Time Data** - No access to current information
   - Solution: Use web search or APIs
   
3. **Complex Math** - Struggles with advanced formulas
   - Solution: Combine with computation tools

4. **Deterministic Logic** - May occasionally make logical leaps
   - Solution: Verify critical reasoning paths

## 🔀 Combining CoT with Tools

Best of both worlds:

```php
$systemPrompt = "You solve problems step by step. " .
                "When you need exact calculations, use the calculator tool. " .
                "When you need data, use the search tool. " .
                "Always explain your reasoning.";

// Hybrid approach: CoT reasoning + tools for precision
```

## 📈 Measuring CoT Quality

Evaluate CoT responses by:

1. **Completeness** - Are all steps shown?
2. **Correctness** - Is the logic sound?
3. **Clarity** - Is it easy to follow?
4. **Efficiency** - Are steps necessary, not redundant?

## 🧪 Try It Yourself

Run the complete working example:

```bash
php tutorials/07-chain-of-thought/cot_agent.php
```

The script demonstrates:

- ✅ Zero-shot CoT for math problems
- ✅ Few-shot CoT with examples
- ✅ Complex reasoning for logic puzzles
- ✅ Comparison with direct answering

## 🎯 Best Practices

### 1. Clear Instructions

```php
// Good
$prompt = $problem . "\n\nLet's solve this step by step, " .
          "showing all calculations.";

// Less effective
$prompt = $problem . "\n\nThink about it.";
```

### 2. Structure for Complex Problems

```php
$prompt = "Problem: {$problem}\n\n" .
          "Please:\n" .
          "1. Restate what's being asked\n" .
          "2. List known information\n" .
          "3. Identify steps needed\n" .
          "4. Work through each step\n" .
          "5. State the final answer";
```

### 3. Use Examples for Consistency

```php
// Provide 2-3 examples in system prompt for consistent formatting
$systemPrompt = "Solve problems using this format:\n\n" . $examples;
```

### 4. Extract Final Answer

```php
function extractFinalAnswer($response) {
    // Look for "Therefore", "Final answer", "Conclusion" patterns
    if (preg_match('/(?:Therefore|Thus|Final answer|Conclusion)[^.]*:\s*(.+)/', 
                   $response, $matches)) {
        return trim($matches[1]);
    }
    return $response;
}
```

## 🆚 When to Choose CoT Over ReAct

Choose **Chain of Thought** when:
- ✅ Problem requires logical reasoning
- ✅ Showing work is important
- ✅ No external data needed
- ✅ Transparency matters
- ✅ Educational context

Choose **ReAct** when:
- ✅ Need external APIs/tools
- ✅ Require real-time data
- ✅ Multi-step tool orchestration
- ✅ Precision is critical
- ✅ Complex state management

Choose **Hybrid** when:
- ✅ Reasoning + tools needed
- ✅ Complex analysis with data lookup
- ✅ Best of both worlds

## ✅ Checkpoint

Before moving on, make sure you understand:

- [ ] What Chain of Thought prompting is
- [ ] Difference between zero-shot and few-shot CoT
- [ ] When to use CoT vs ReAct vs Hybrid
- [ ] How to structure CoT prompts effectively
- [ ] Limitations of pure reasoning approaches
- [ ] How to extract and verify CoT reasoning

## 🚀 Next Steps

You've mastered Chain of Thought reasoning! But what if we need to explore multiple reasoning paths and backtrack when needed?

**[Tutorial 8: Tree of Thoughts →](../08-tree-of-thoughts/)**

Learn how to implement multi-path reasoning with the Tree of Thoughts pattern!

## 💡 Key Takeaways

1. **CoT is about transparency** - Making reasoning visible
2. **Simple trigger phrases work** - "Let's think step by step"
3. **Examples improve consistency** - Few-shot > zero-shot
4. **Not a replacement for tools** - Use tools for precision
5. **Powerful for pure reasoning** - Logic, analysis, explanation
6. **Combines well with other patterns** - Hybrid approaches

## 📚 Further Reading

- [Original CoT Paper](https://arxiv.org/abs/2201.11903) - Wei et al., 2022
- [Zero-Shot CoT](https://arxiv.org/abs/2205.11916) - Kojima et al., 2022
- [Claude Docs: Prompt Engineering](https://docs.anthropic.com/en/docs/prompt-engineering)
- [SDK Example: extended_thinking.php](../../examples/extended_thinking.php)

## 🎓 Real-World Applications

### Customer Support
Analyze customer issues step by step to provide better solutions.

### Code Review
Break down code analysis into systematic checks.

### Educational Tools
Show students how to approach problems methodically.

### Decision Support
Analyze business decisions with clear reasoning trails.

### Content Analysis
Break down articles, essays, or documents systematically.

