# Tutorial 12: Multi-Agent Debate

**Time: 60 minutes** | **Difficulty: Advanced**

Multi-agent debate systems leverage diverse perspectives and critical thinking by having multiple agents discuss, challenge, and refine ideas. This pattern produces more robust, well-reasoned outputs through collaborative argumentation.

## 🎯 Learning Objectives

By the end of this tutorial, you'll be able to:

- Implement multi-agent debate protocols
- Design agents with different perspectives
- Build consensus mechanisms
- Handle disagreements and conflicts
- Synthesize insights from debates
- Apply debate to decision-making
- Understand when debate improves outcomes

## 🏗️ What We're Building

A debate system featuring:

1. **Multiple Agents** - Different viewpoints and roles
2. **Debate Protocol** - Structured argumentation rounds
3. **Challenger Agents** - Devil's advocate and critics
4. **Moderator** - Manages flow and synthesizes
5. **Consensus Builder** - Finds common ground
6. **Decision Framework** - Converts debate to action

## 📋 Prerequisites

Make sure you have:

- Completed [Tutorial 11: Hierarchical Agents](../11-hierarchical-agents/)
- Understanding of argumentation and logic
- PHP 8.1+ installed
- Claude PHP SDK configured

## 🤔 What is Multi-Agent Debate?

Multi-agent debate involves multiple AI agents with different roles or perspectives discussing a topic to reach better conclusions.

### Debate vs Single Agent

**Single Agent:**
```
Question: Should we adopt technology X?
Agent: Yes, because... [one perspective]
Done.
```

**Multi-Agent Debate:**
```
Question: Should we adopt technology X?

Proponent: Yes, because benefits A, B, C...
Opponent: No, because risks X, Y, Z...
Analyst: Data shows...
Critic: Both sides overlook...
Moderator: Considering all views...

Result: Nuanced, well-reasoned decision
```

## 🔑 Key Concepts

### 1. Agent Roles

Different agents bring different perspectives:

```php
$roles = [
    'proponent' => [
        'perspective' => 'Support the proposal',
        'system' => 'You advocate for the proposal. Find benefits and opportunities.'
    ],
    'opponent' => [
        'perspective' => 'Challenge the proposal',
        'system' => 'You oppose the proposal. Identify risks and drawbacks.'
    ],
    'analyst' => [
        'perspective' => 'Objective analysis',
        'system' => 'You analyze facts objectively. Focus on data and evidence.'
    ],
    'critic' => [
        'perspective' => 'Critical thinking',
        'system' => 'You identify logical flaws and assumptions in arguments.'
    ],
    'moderator' => [
        'perspective' => 'Synthesis and balance',
        'system' => 'You synthesize viewpoints and find balanced conclusions.'
    ]
];
```

### 2. Debate Protocol

Structured rounds ensure comprehensive discussion:

```php
$protocol = [
    'round_1' => [
        'type' => 'opening_statements',
        'agents' => ['proponent', 'opponent'],
        'purpose' => 'Present initial positions'
    ],
    'round_2' => [
        'type' => 'rebuttals',
        'agents' => ['opponent', 'proponent'],
        'purpose' => 'Challenge opponent arguments'
    ],
    'round_3' => [
        'type' => 'analysis',
        'agents' => ['analyst', 'critic'],
        'purpose' => 'Objective evaluation'
    ],
    'final' => [
        'type' => 'synthesis',
        'agents' => ['moderator'],
        'purpose' => 'Unified conclusion'
    ]
];
```

### 3. Argument Quality

Evaluate argument strength:

```php
$argumentMetrics = [
    'logic' => 'Is reasoning sound?',
    'evidence' => 'Are claims supported?',
    'completeness' => 'Are counterarguments addressed?',
    'relevance' => 'Does it address the question?'
];
```

### 4. Consensus Building

Find common ground:

```php
function findConsensus($debateHistory) {
    $agreementPoints = [];
    $disagreementPoints = [];
    
    foreach ($debateHistory as $round) {
        // Identify where agents agree
        $commonalities = extractCommonPoints($round);
        $agreementPoints = array_merge($agreementPoints, $commonalities);
        
        // Track persistent disagreements
        $conflicts = extractConflicts($round);
        $disagreementPoints = array_merge($disagreementPoints, $conflicts);
    }
    
    return [
        'consensus' => $agreementPoints,
        'open_issues' => $disagreementPoints
    ];
}
```

## 💡 Implementation Patterns

### Basic Debate System

```php
class DebateSystem {
    private $client;
    private $agents = [];
    private $history = [];
    
    public function __construct($client) {
        $this->client = $client;
    }
    
    public function addAgent($name, $role, $systemPrompt) {
        $this->agents[$name] = [
            'role' => $role,
            'system' => $systemPrompt
        ];
    }
    
    public function debate($topic, $rounds = 3) {
        $context = "Topic: {$topic}\n\n";
        
        for ($round = 1; $round <= $rounds; $round++) {
            echo "Round {$round}:\n";
            
            foreach ($this->agents as $name => $config) {
                $prompt = $context . "As the {$config['role']}, provide your perspective.";
                
                $response = $this->client->messages()->create([
                    'model' => 'claude-sonnet-4-5',
                    'max_tokens' => 1024,
                    'system' => $config['system'],
                    'messages' => [['role' => 'user', 'content' => $prompt]]
                ]);
                
                $statement = extractTextContent($response);
                $this->history[] = [
                    'round' => $round,
                    'agent' => $name,
                    'statement' => $statement
                ];
                
                $context .= "\n{$name}: {$statement}\n";
                echo "{$name}: {$statement}\n\n";
            }
        }
        
        return $this->synthesize($topic);
    }
    
    private function synthesize($topic) {
        $debateText = "";
        foreach ($this->history as $entry) {
            $debateText .= "Round {$entry['round']} - {$entry['agent']}:\n";
            $debateText .= "{$entry['statement']}\n\n";
        }
        
        $prompt = "Topic: {$topic}\n\nDebate transcript:\n{$debateText}\n" .
                 "Synthesize this debate into a balanced conclusion that:\n" .
                 "1. Identifies key agreements\n" .
                 "2. Acknowledges valid concerns\n" .
                 "3. Provides actionable recommendation";
        
        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1536,
            'system' => 'You synthesize debates into clear, balanced conclusions.',
            'messages' => [['role' => 'user', 'content' => $prompt]]
        ]);
        
        return extractTextContent($response);
    }
}
```

### Structured Debate Agent

```php
class DebateAgent {
    private $client;
    private $name;
    private $perspective;
    private $systemPrompt;
    
    public function __construct($client, $name, $perspective, $systemPrompt) {
        $this->client = $client;
        $this->name = $name;
        $this->perspective = $perspective;
        $this->systemPrompt = $systemPrompt;
    }
    
    public function respond($topic, $context, $roundType) {
        $prompt = $this->buildPrompt($topic, $context, $roundType);
        
        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'system' => $this->systemPrompt,
            'messages' => [['role' => 'user', 'content' => $prompt]]
        ]);
        
        return extractTextContent($response);
    }
    
    private function buildPrompt($topic, $context, $roundType) {
        $prompts = [
            'opening' => "Topic: {$topic}\n\nProvide your opening position.",
            'rebuttal' => "Topic: {$topic}\n\nPrevious arguments:\n{$context}\n\n" .
                         "Rebut the opposing arguments.",
            'analysis' => "Topic: {$topic}\n\nDebate so far:\n{$context}\n\n" .
                         "Provide objective analysis.",
            'closing' => "Topic: {$topic}\n\nFull debate:\n{$context}\n\n" .
                        "Closing statement."
        ];
        
        return $prompts[$roundType] ?? $prompts['opening'];
    }
    
    public function getName() {
        return $this->name;
    }
}
```

## 🎯 Debate Strategies

### 1. Pro/Con Debate

Simple two-sided debate:

```php
$proAgent = new DebateAgent(
    $client,
    'Proponent',
    'support',
    'You advocate for the proposal. Present benefits and opportunities.'
);

$conAgent = new DebateAgent(
    $client,
    'Opponent',
    'oppose',
    'You challenge the proposal. Identify risks and drawbacks.'
);

$moderator = new DebateAgent(
    $client,
    'Moderator',
    'synthesize',
    'You create balanced conclusions from debates.'
);
```

### 2. Round-Robin Discussion

Each agent responds to all others:

```php
foreach ($agents as $speaker) {
    $otherStatements = array_filter(
        $statements,
        fn($s) => $s['agent'] !== $speaker->getName()
    );
    
    $context = implode("\n\n", array_map(
        fn($s) => "{$s['agent']}: {$s['text']}",
        $otherStatements
    ));
    
    $response = $speaker->respond($topic, $context, 'discussion');
    $statements[] = ['agent' => $speaker->getName(), 'text' => $response];
}
```

### 3. Socratic Method

Questioner challenges assumptions:

```php
$questioner = new DebateAgent(
    $client,
    'Questioner',
    'socratic',
    'You ask probing questions to reveal assumptions and test logic. ' .
    'Never make statements, only ask clarifying questions.'
);

$responder = new DebateAgent(
    $client,
    'Responder',
    'answer',
    'You answer questions thoughtfully and defend your position with evidence.'
);
```

### 4. Devil's Advocate

One agent challenges everything:

```php
$devilsAdvocate = new DebateAgent(
    $client,
    "Devil's Advocate",
    'challenge',
    'You challenge every assumption. Find flaws, identify risks, ' .
    'question evidence. Be skeptical of all claims.'
);
```

## 📊 Advanced Patterns

### Weighted Voting

Agents vote on final decision:

```php
function weightedVote($agents, $topic, $options) {
    $votes = [];
    
    foreach ($agents as $agent) {
        $vote = $agent->vote($topic, $options);
        $confidence = $agent->getConfidence();
        
        $votes[$vote] = ($votes[$vote] ?? 0) + $confidence;
    }
    
    arsort($votes);
    return array_key_first($votes);
}
```

### Iterative Refinement

Debate outcome feeds back for improvement:

```php
function iterativeDebate($topic, $agents, $iterations = 3) {
    $proposal = initialProposal($topic);
    
    for ($i = 0; $i < $iterations; $i++) {
        $feedback = debate($proposal, $agents);
        $issues = extractIssues($feedback);
        $proposal = refine($proposal, $issues);
    }
    
    return $proposal;
}
```

### Consensus Threshold

Debate continues until agreement reached:

```php
function debateUntilConsensus($topic, $agents, $threshold = 0.8) {
    $round = 1;
    $maxRounds = 10;
    
    while ($round <= $maxRounds) {
        $statements = collectStatements($topic, $agents);
        $agreement = measureAgreement($statements);
        
        if ($agreement >= $threshold) {
            return synthesize($statements);
        }
        
        $round++;
    }
    
    return "Consensus not reached";
}
```

### Dynamic Agent Addition

Add specialists as needed:

```php
function adaptiveDebate($topic, $baseAgents) {
    $debate = initializeDebate($topic, $baseAgents);
    
    // Identify gaps in discussion
    $gaps = analyzeGaps($debate);
    
    // Add specialists for gaps
    foreach ($gaps as $gap) {
        $specialist = createSpecialist($gap);
        $debate->addAgent($specialist);
    }
    
    return $debate->continue();
}
```

## 🎨 Use Cases

### 1. Technical Decision Making

```
Topic: Choose database technology
Agents: Performance expert, Cost analyst, Operations engineer, Developer
Result: Balanced decision considering all factors
```

### 2. Product Design

```
Topic: New feature design
Agents: User advocate, Engineer, Designer, Business analyst
Result: Feature balancing UX, feasibility, and business value
```

### 3. Risk Assessment

```
Topic: Project risks
Agents: Optimist, Pessimist, Realist, Risk manager
Result: Comprehensive risk identification and mitigation
```

### 4. Ethical Analysis

```
Topic: AI policy decision
Agents: Ethicist, Legal expert, Technologist, Public representative
Result: Ethically sound policy
```

## ⚙️ Configuration

### Debate Parameters

```php
$config = [
    'min_rounds' => 2,
    'max_rounds' => 5,
    'consensus_threshold' => 0.75,
    'max_turn_length' => 500,  // words
    'timeout_seconds' => 300
];
```

### Agent Diversity

Ensure varied perspectives:

```php
$perspectives = [
    'technical' => 0.3,   // 30% technical focus
    'business' => 0.3,    // 30% business focus
    'user' => 0.2,        // 20% user focus
    'ethical' => 0.2      // 20% ethical focus
];
```

## 📈 Measuring Debate Quality

Track effectiveness:

```php
$metrics = [
    'rounds_to_consensus' => 3,
    'unique_points_raised' => 15,
    'arguments_per_agent' => [
        'proponent' => 5,
        'opponent' => 6,
        'analyst' => 4
    ],
    'agreement_level' => 0.85,
    'decision_quality_score' => 8.5
];
```

## ✅ Checkpoint

Before moving on, make sure you understand:

- [ ] Multi-agent debate protocols
- [ ] Different agent roles and perspectives
- [ ] Structured argumentation rounds
- [ ] Consensus building mechanisms
- [ ] Synthesis of diverse viewpoints
- [ ] When debate improves decisions
- [ ] Cost-benefit of multi-agent systems

## 🚀 Next Steps

You've mastered Multi-Agent Debate! But what about integrating external knowledge?

**[Tutorial 13: RAG Pattern →](../13-rag-pattern/)**

Learn how to augment agents with retrieval for knowledge-grounded responses!

## 💻 Try It Yourself

Run the complete working example:

```bash
php tutorials/12-multi-agent-debate/debate_agent.php
```

The script demonstrates:

- ✅ Pro vs Con debate structure
- ✅ Multi-round argumentation
- ✅ Four-agent round table discussion
- ✅ Moderator synthesis
- ✅ Consensus building
- ✅ Structured debate protocols

## 💡 Key Takeaways

1. **Multiple perspectives improve decisions** - Diverse views catch blind spots
2. **Structure prevents chaos** - Clear protocols keep debates productive
3. **Roles create focus** - Each agent contributes uniquely
4. **Synthesis is critical** - Convert debate to actionable conclusion
5. **Balance cost and quality** - More agents = better but more expensive
6. **Consensus takes time** - Allow sufficient rounds
7. **Not always needed** - Simple decisions don't require debate
8. **Measure effectiveness** - Track if debate improves outcomes

## 📚 Further Reading

### Research Papers

- **[Improving Factuality via Multi-Agent Debate](https://arxiv.org/abs/2305.14325)** - Du et al., 2023
- **[Debating with More Persuasive LLMs](https://arxiv.org/abs/2402.01569)** - Khan et al., 2024
- **[Society of Mind](https://web.media.mit.edu/~minsky/som/)** - Minsky, 1986

### Related Tutorials

- [Tutorial 10: Reflection](../10-reflection/) - Self-critique
- [Tutorial 11: Hierarchical Agents](../11-hierarchical-agents/) - Agent coordination
- [Tutorial 14: Autonomous Agents](../14-autonomous-agents/) - Independent operation

## 🎓 Practice Exercises

Try multi-agent debate for:

1. **Technology Selection** - Agents with different priorities (cost, performance, maintainability)
2. **Feature Prioritization** - User advocate vs business vs engineering
3. **Risk Analysis** - Optimist vs pessimist vs realist
4. **Design Review** - Multiple design philosophies debate best approach

## 🔧 Troubleshooting

**Issue**: Agents agree too quickly
- **Solution**: Strengthen opposing viewpoints, reward finding issues

**Issue**: Debate never converges
- **Solution**: Lower consensus threshold, add moderator with tie-breaking

**Issue**: Repetitive arguments
- **Solution**: Track previous points, penalize repetition

**Issue**: One perspective dominates
- **Solution**: Balance turn lengths, require all agents to participate

**Issue**: High cost with marginal benefit
- **Solution**: Reduce rounds, use fewer agents, simpler debate structure
