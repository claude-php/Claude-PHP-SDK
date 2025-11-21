# Tutorial 8: Tree of Thoughts (ToT)

**Time: 60 minutes** | **Difficulty: Advanced**

Tree of Thoughts (ToT) is an advanced reasoning pattern that explores multiple reasoning paths simultaneously, evaluates each path, and can backtrack when needed. Think of it as "exploring a maze" rather than following a single path.

## 🎯 Learning Objectives

By the end of this tutorial, you'll be able to:

- Understand the Tree of Thoughts pattern and its advantages
- Implement multi-path exploration strategies
- Evaluate and score different reasoning paths
- Implement backtracking when paths lead to dead ends
- Choose between breadth-first and depth-first exploration
- Apply ToT to complex problems like puzzles and optimization

## 🏗️ What We're Building

We'll implement Tree of Thoughts agents that:

1. **Generate multiple solution paths** - Explore different approaches
2. **Evaluate each path** - Score and rank possibilities
3. **Select best paths** - Choose most promising directions
4. **Backtrack when needed** - Abandon dead ends
5. **Combine insights** - Synthesize the best solution

## 📋 Prerequisites

Make sure you have:

- Completed [Tutorial 7: Chain of Thought](../07-chain-of-thought/)
- Understanding of recursive algorithms helpful
- PHP 8.1+ installed
- Claude PHP SDK configured

## 🌳 What is Tree of Thoughts?

Unlike Chain of Thought which follows a linear path, Tree of Thoughts explores multiple reasoning paths in parallel, creating a tree structure.

### Chain of Thought (Linear)

```
Problem → Step 1 → Step 2 → Step 3 → Answer
```

### Tree of Thoughts (Branching)

```
Problem
  ├─ Approach A → Step A1 → Step A2 → Solution A
  ├─ Approach B → Step B1 ✗ (Dead end)
  └─ Approach C → Step C1 → Step C2 → Solution C ✓ (Best)
```

## 🔑 Key Concepts

### 1. Thought Generation

Generate multiple possible next steps:

```php
"Generate 3 different approaches to solve this problem.
For each approach, explain the strategy and first step."
```

### 2. Thought Evaluation

Score each possibility:

```php
"Rate each approach from 1-10 based on:
- Likelihood of success
- Simplicity
- Efficiency"
```

### 3. Thought Selection

Choose the best path(s) to explore:

```php
// Select top N paths
$selectedPaths = array_slice($rankedPaths, 0, 2);
```

### 4. Backtracking

Abandon unsuccessful paths:

```php
if ($pathScore < $threshold) {
    echo "Path unsuccessful, backtracking...\n";
    continue; // Try different branch
}
```

## 📊 ToT Algorithm

The basic Tree of Thoughts loop:

```
1. Generate N possible next thoughts
2. Evaluate each thought (score 1-10)
3. Select top K thoughts
4. For each selected thought:
   a. If goal reached: return solution
   b. If dead end: backtrack
   c. Otherwise: goto step 1 (recurse)
```

## 🎮 Classic Example: Game of 24

Use 4 numbers and basic operations (+, -, ×, ÷) to make 24.

**Problem**: Use 4, 6, 7, 8 to make 24

**ToT Exploration**:

```
Initial: [4, 6, 7, 8]

Branch 1: Try (8 - 6) × 7 + 4
  Step 1: 8 - 6 = 2, remaining [2, 7, 4]
  Step 2: 2 × 7 = 14, remaining [14, 4]
  Step 3: 14 + 4 = 18 ✗ (Not 24, backtrack)

Branch 2: Try (7 + 6) × 8 - 4
  Step 1: 7 + 6 = 13, remaining [13, 8, 4]
  Step 2: 13 × 8 = 104, remaining [104, 4]
  Step 3: 104 - 4 = 100 ✗ (Not 24, backtrack)

Branch 3: Try (8 / 4) × (7 - 6)
  Step 1: 8 / 4 = 2, remaining [2, 7, 6]
  Step 2: 7 - 6 = 1, remaining [2, 1]
  Step 3: 2 × 1 = 2 ✗ (Not 24, backtrack)

Branch 4: Try (6 - 4) × (8 + 7)
  Step 1: 6 - 4 = 2, remaining [2, 8, 7]
  Step 2: 8 + 7 = 15, remaining [2, 15]
  Step 3: 2 × 15 = 30 ✗ (Close! Try variations)

Branch 5: Try 6 × (8 - 4) + 7
  Step 1: 8 - 4 = 4, remaining [6, 4, 7]
  Step 2: 6 × 4 = 24, remaining [24, 7]
  Wait! We have 24, ignore 7? ✗

Branch 6: Try (6 + 7 - 8) × 4
  Step 1: 6 + 7 = 13, remaining [13, 8, 4]
  Step 2: 13 - 8 = 5, remaining [5, 4]
  Step 3: 5 × 4 = 20 ✗

Branch 7: Try 6 × (7 - 4) + 8
  Step 1: 7 - 4 = 3, remaining [6, 3, 8]
  Step 2: 6 × 3 = 18, remaining [18, 8]
  Step 3: 18 + 8 = 26 ✗ (Very close)

Branch 8: Try 8 × (7 - 4) + 6
  Step 1: 7 - 4 = 3, remaining [8, 3, 6]
  Step 2: 8 × 3 = 24, remaining [24, 6]
  Need to use all numbers...

Branch 9: Try (8 - 4) × (7 - 6)
  This gives 4, not helpful

Branch 10: Try 6 ÷ (8 - 7) × 4
  Step 1: 8 - 7 = 1, remaining [6, 1, 4]
  Step 2: 6 ÷ 1 = 6, remaining [6, 4]
  Step 3: 6 × 4 = 24 ✓ Success!

Solution: 6 ÷ (8 - 7) × 4 = 24
```

## 🔀 Search Strategies

### Breadth-First Search (BFS)

Explore all paths at each level before going deeper:

```php
$queue = [$initialState];

while (!empty($queue)) {
    $state = array_shift($queue); // FIFO

    $nextStates = generateThoughts($state);

    foreach ($nextStates as $next) {
        if (isGoal($next)) return $next;
        $queue[] = $next;
    }
}
```

**Pros**: Finds shortest solution
**Cons**: High memory usage

### Depth-First Search (DFS)

Explore one path fully before trying others:

```php
$stack = [$initialState];

while (!empty($stack)) {
    $state = array_pop($stack); // LIFO

    $nextStates = generateThoughts($state);

    foreach ($nextStates as $next) {
        if (isGoal($next)) return $next;
        $stack[] = $next;
    }
}
```

**Pros**: Low memory usage
**Cons**: May find suboptimal solution

### Best-First Search

Explore most promising paths first:

```php
$priorityQueue = new SplPriorityQueue();
$priorityQueue->insert($initialState, evaluate($initialState));

while (!$priorityQueue->isEmpty()) {
    $state = $priorityQueue->extract();

    if (isGoal($state)) return $state;

    $nextStates = generateThoughts($state);
    foreach ($nextStates as $next) {
        $score = evaluate($next);
        $priorityQueue->insert($next, $score);
    }
}
```

**Pros**: More efficient, finds good solutions faster
**Cons**: Requires good evaluation function

## 💡 ToT Implementation Pattern

```php
function treeOfThoughts($problem, $maxDepth = 5, $branchingFactor = 3) {
    // Generate initial thoughts
    $thoughts = generateInitialThoughts($problem, $branchingFactor);

    // Evaluate each thought
    $evaluated = [];
    foreach ($thoughts as $thought) {
        $score = evaluateThought($thought);
        $evaluated[] = ['thought' => $thought, 'score' => $score];
    }

    // Sort by score
    usort($evaluated, fn($a, $b) => $b['score'] <=> $a['score']);

    // Explore best paths
    foreach ($evaluated as $item) {
        if ($item['score'] < $threshold) {
            continue; // Backtrack
        }

        if (isComplete($item['thought'])) {
            return $item['thought']; // Success!
        }

        if ($depth < $maxDepth) {
            // Recurse deeper
            $result = treeOfThoughts(
                $item['thought'],
                $maxDepth,
                $branchingFactor
            );
            if ($result) return $result;
        }
    }

    return null; // No solution found
}
```

## 🎨 ToT with Claude

### Generate Thoughts

```php
$prompt = "Problem: {$problem}\n\n" .
          "Generate {$n} different approaches to solve this. " .
          "For each approach, briefly describe the strategy.";

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 2048,
    'messages' => [['role' => 'user', 'content' => $prompt]]
]);
```

### Evaluate Thoughts

```php
$prompt = "Evaluate this approach: {$approach}\n\n" .
          "Rate from 1-10 based on:\n" .
          "- Likelihood of success (1-5)\n" .
          "- Efficiency (1-5)\n" .
          "Provide: Score (X/10) and brief reasoning.";
```

### Expand Promising Paths

```php
$prompt = "Current state: {$state}\n\n" .
          "What are the next {$n} best steps to take? " .
          "Consider what you've tried and avoid dead ends.";
```

## 🧪 When to Use ToT

**Perfect For:**

- ✅ Puzzles and games
- ✅ Optimization problems
- ✅ Creative writing with options
- ✅ Strategic planning
- ✅ Path-finding problems
- ✅ When multiple valid solutions exist

**Not Ideal For:**

- ❌ Simple linear problems (use CoT)
- ❌ When first solution is good enough
- ❌ Strict time constraints
- ❌ Problems with unique deterministic solutions

## 📈 ToT vs Other Patterns

| Aspect           | CoT       | ToT        | ReAct          |
| ---------------- | --------- | ---------- | -------------- |
| **Paths**        | Single    | Multiple   | Single + tools |
| **Backtracking** | No        | Yes        | Limited        |
| **Exploration**  | Linear    | Tree/graph | Linear         |
| **Complexity**   | Low       | High       | Medium         |
| **Best For**     | Reasoning | Puzzles    | Actions        |
| **Cost**         | Low       | High       | Medium         |

## ⚡ Optimizing ToT

### 1. Limit Branching Factor

```php
// Generate fewer options per step
$branchingFactor = 3; // Instead of 5+
```

### 2. Prune Aggressively

```php
// Only explore high-scoring paths
if ($score < 7) continue; // Skip low-scoring branches
```

### 3. Limit Depth

```php
// Prevent infinite exploration
$maxDepth = 5;
```

### 4. Use Caching

```php
// Cache evaluated states to avoid redundant work
$cache = [];
if (isset($cache[$stateHash])) {
    return $cache[$stateHash];
}
```

### 5. Early Termination

```php
// Stop when good enough solution found
if ($score >= $targetScore) {
    return $solution; // Don't keep searching
}
```

## 🎯 Real-World Applications

### Creative Writing

Generate multiple story directions, evaluate each, pursue the best:

```php
$prompt = "Story so far: {$story}\n\n" .
          "Generate 3 different ways the story could continue. " .
          "Evaluate each for drama, coherence, and interest.";
```

### Code Optimization

Explore different refactoring approaches:

```php
$prompt = "Current code: {$code}\n\n" .
          "Generate 3 different optimization strategies. " .
          "Evaluate based on performance gain and maintainability.";
```

### Business Strategy

Explore strategic options:

```php
$prompt = "Situation: {$context}\n\n" .
          "Generate 3 strategic approaches. " .
          "Evaluate risk vs reward for each.";
```

## ✅ Checkpoint

Before moving on, make sure you understand:

- [ ] How ToT differs from CoT (multiple paths vs single)
- [ ] The generate → evaluate → select → expand cycle
- [ ] When and how to backtrack
- [ ] Different search strategies (BFS, DFS, best-first)
- [ ] How to implement ToT with Claude
- [ ] When ToT is worth the extra complexity

## 🚀 Next Steps

You've mastered multi-path exploration with Tree of Thoughts! But what if we want to explicitly separate planning from execution?

**[Tutorial 9: Plan-and-Execute →](../09-plan-and-execute/)**

Learn how to implement agents that plan entire strategies before taking action!

## 💻 Try It Yourself

Run the complete working example:

```bash
php tutorials/08-tree-of-thoughts/tot_agent.php
```

The script demonstrates:

- ✅ Multi-path exploration (Game of 24)
- ✅ Path evaluation and scoring
- ✅ Backtracking when paths fail
- ✅ Creative problem solving (story writing)
- ✅ Logic puzzle solving (Knights and Knaves)
- ✅ Best-first search strategy

## 💡 Key Takeaways

1. **ToT explores options** - Don't commit to first path
2. **Evaluation is critical** - Need good scoring functions
3. **Backtracking saves time** - Abandon bad paths early
4. **Balance exploration depth** - More isn't always better
5. **ToT is expensive** - Multiple API calls per step
6. **Best for complex problems** - Simple problems don't need it
7. **Combine with other patterns** - ToT + tools, ToT + planning

## 📚 Further Reading

- [Tree of Thoughts Paper](https://arxiv.org/abs/2305.10601) - Yao et al., 2023
- [Graph of Thoughts](https://arxiv.org/abs/2308.09687) - Extension of ToT
- [Self-Consistency](https://arxiv.org/abs/2203.11171) - Related technique
- [Tutorial 7: Chain of Thought](../07-chain-of-thought/)

## 🎓 Advanced Topics

### Pruning Strategies

- **Alpha-Beta Pruning**: Borrow from game tree search
- **Beam Search**: Keep only top K branches
- **Monte Carlo Tree Search**: Statistical sampling of paths

### Parallel Exploration

```php
// Explore multiple branches concurrently
$promises = [];
foreach ($branches as $branch) {
    $promises[] = exploreAsync($branch);
}
$results = await($promises);
```

### Adaptive Branching

```php
// Vary branching factor based on progress
$branchingFactor = $progress < 0.5 ? 5 : 2;
```

### Memory of Explored States

```php
// Track what's been tried to avoid cycles
$visited = new Set();
if ($visited->contains($stateHash)) {
    continue; // Skip already explored state
}
```
