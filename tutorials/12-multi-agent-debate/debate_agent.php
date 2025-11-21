#!/usr/bin/env php
<?php
/**
 * Tutorial 12: Multi-Agent Debate - Working Example
 * 
 * Demonstrates multiple agents debating to reach better decisions through
 * diverse perspectives and structured argumentation.
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../examples/helpers.php';
require_once __DIR__ . '/../helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║           Tutorial 12: Multi-Agent Debate - Collaborative Reasoning        ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Debate Agent Classes
// ============================================================================

/**
 * Individual debate agent with specific perspective
 */
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
    
    public function speak($topic, $context = "", $instruction = "") {
        $prompt = "Topic: {$topic}\n\n";
        
        if ($context) {
            $prompt .= "Previous discussion:\n{$context}\n\n";
        }
        
        if ($instruction) {
            $prompt .= "{$instruction}\n\n";
        }
        
        $prompt .= "Provide your perspective.";
        
        try {
            $response = $this->client->messages()->create([
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 1024,
                'system' => $this->systemPrompt,
                'messages' => [['role' => 'user', 'content' => $prompt]]
            ]);
            
            return extractTextContent($response);
        } catch (Exception $e) {
            return "Error from {$this->name}: {$e->getMessage()}";
        }
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getPerspective() {
        return $this->perspective;
    }
}

/**
 * Debate moderator that synthesizes agent inputs
 */
class DebateModerator {
    private $client;
    
    public function __construct($client) {
        $this->client = $client;
    }
    
    public function synthesize($topic, $debate_history) {
        $transcript = "";
        foreach ($debate_history as $round => $statements) {
            $transcript .= "=== Round " . ($round + 1) . " ===\n";
            foreach ($statements as $agent => $statement) {
                $transcript .= "\n{$agent}:\n{$statement}\n";
            }
            $transcript .= "\n";
        }
        
        $prompt = "Topic: {$topic}\n\n" .
                 "Debate transcript:\n{$transcript}\n\n" .
                 "Synthesize this debate into a balanced conclusion:\n" .
                 "1. Key areas of agreement\n" .
                 "2. Valid concerns from all sides\n" .
                 "3. Recommended decision with rationale\n" .
                 "4. Potential risks and mitigations";
        
        try {
            $response = $this->client->messages()->create([
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 2048,
                'system' => 'You synthesize multi-agent debates into clear, balanced conclusions.',
                'messages' => [['role' => 'user', 'content' => $prompt]]
            ]);
            
            return extractTextContent($response);
        } catch (Exception $e) {
            return "Synthesis error: {$e->getMessage()}";
        }
    }
    
    public function measureAgreement($statements) {
        // Simple heuristic: look for agreement keywords
        $agreementWords = ['agree', 'correct', 'yes', 'indeed', 'support', 'affirm'];
        $disagreementWords = ['disagree', 'however', 'but', 'concern', 'risk', 'problem'];
        
        $agreementCount = 0;
        $disagreementCount = 0;
        
        foreach ($statements as $statement) {
            $lower = strtolower($statement);
            foreach ($agreementWords as $word) {
                $agreementCount += substr_count($lower, $word);
            }
            foreach ($disagreementWords as $word) {
                $disagreementCount += substr_count($lower, $word);
            }
        }
        
        $total = $agreementCount + $disagreementCount;
        return $total > 0 ? $agreementCount / $total : 0.5;
    }
}

// ============================================================================
// Example 1: Simple Two-Agent Debate
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 1: Pro vs Con Debate\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$topic1 = "Should our team adopt a 4-day work week?";
echo "📋 Topic: {$topic1}\n\n";

// Create pro and con agents
$proAgent = new DebateAgent(
    $client,
    'Proponent',
    'support',
    'You advocate for the proposal. Present benefits, opportunities, and positive outcomes. Be persuasive.'
);

$conAgent = new DebateAgent(
    $client,
    'Opponent',
    'oppose',
    'You challenge the proposal. Identify risks, drawbacks, and potential problems. Be critical.'
);

$moderator = new DebateModerator($client);

// Round 1: Opening statements
echo "╔════ Round 1: Opening Statements ════╗\n\n";

$proStatement = $proAgent->speak($topic1, "", "Present your opening argument in favor.");
echo "✅ {$proAgent->getName()}:\n{$proStatement}\n\n";

$conStatement = $conAgent->speak($topic1, "", "Present your opening argument against.");
echo "❌ {$conAgent->getName()}:\n{$conStatement}\n\n";

// Round 2: Rebuttals
echo "╔════ Round 2: Rebuttals ════╗\n\n";

$context1 = "Opponent said: {$conStatement}";
$proRebuttal = $proAgent->speak($topic1, $context1, "Rebut the opponent's arguments.");
echo "✅ {$proAgent->getName()} (Rebuttal):\n{$proRebuttal}\n\n";

$context2 = "Proponent said: {$proStatement}\nProponent's rebuttal: {$proRebuttal}";
$conRebuttal = $conAgent->speak($topic1, $context2, "Respond and defend your position.");
echo "❌ {$conAgent->getName()} (Rebuttal):\n{$conRebuttal}\n\n";

// Synthesis
echo "╔════ Final: Moderator Synthesis ════╗\n\n";

$debate1History = [
    [
        'Proponent' => $proStatement,
        'Opponent' => $conStatement
    ],
    [
        'Proponent' => $proRebuttal,
        'Opponent' => $conRebuttal
    ]
];

$conclusion1 = $moderator->synthesize($topic1, $debate1History);
echo "⚖️  Moderator:\n{$conclusion1}\n\n";

echo "💡 Two perspectives reveal trade-offs and lead to balanced decision!\n";
echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 2: Multi-Agent Round Table
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 2: Four-Agent Round Table Discussion\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$topic2 = "Should we build a mobile app or focus on improving the website?";
echo "📋 Topic: {$topic2}\n\n";

// Create diverse agents
$userAdvocate = new DebateAgent(
    $client,
    'User Advocate',
    'user-focused',
    'You represent user needs and experience. Prioritize what users want and need.'
);

$engineer = new DebateAgent(
    $client,
    'Engineer',
    'technical',
    'You assess technical feasibility, complexity, and maintainability. Be pragmatic.'
);

$businessAnalyst = new DebateAgent(
    $client,
    'Business Analyst',
    'business',
    'You analyze ROI, market fit, and business impact. Focus on bottom-line results.'
);

$designer = new DebateAgent(
    $client,
    'Designer',
    'design',
    'You consider UX, design consistency, and platform capabilities. Think about experience.'
);

$agents = [$userAdvocate, $engineer, $businessAnalyst, $designer];

echo "Participants:\n";
foreach ($agents as $agent) {
    echo "  • {$agent->getName()} ({$agent->getPerspective()})\n";
}
echo "\n";

// Conduct multi-round discussion
$debateHistory = [];
$sharedContext = "";

for ($round = 1; $round <= 2; $round++) {
    echo "╔════ Round {$round} ════╗\n\n";
    
    $roundStatements = [];
    
    foreach ($agents as $agent) {
        $instruction = $round === 1 
            ? "Share your initial perspective on this decision."
            : "Respond to others' points and add new insights.";
        
        $statement = $agent->speak($topic2, $sharedContext, $instruction);
        $roundStatements[$agent->getName()] = $statement;
        
        echo "{$agent->getName()}:\n{$statement}\n\n";
        
        $sharedContext .= "\n{$agent->getName()}: {$statement}\n";
    }
    
    $debateHistory[] = $roundStatements;
}

// Check agreement level
$allStatements = [];
foreach ($debateHistory as $round) {
    $allStatements = array_merge($allStatements, array_values($round));
}
$agreementLevel = $moderator->measureAgreement($allStatements);
echo "📊 Agreement Level: " . round($agreementLevel * 100) . "%\n\n";

// Final synthesis
echo "╔════ Moderator Synthesis ════╗\n\n";

$conclusion2 = $moderator->synthesize($topic2, $debateHistory);
echo $conclusion2 . "\n\n";

echo "💡 Multiple perspectives create comprehensive understanding!\n";
echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 3: Devil's Advocate Pattern
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 3: Devil's Advocate Challenge\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$proposal = "We should migrate all our services to serverless architecture.";
echo "📋 Proposal: {$proposal}\n\n";

$proposer = new DebateAgent(
    $client,
    'Proposer',
    'advocate',
    'You advocate for the proposal. Explain benefits and why it should be adopted.'
);

$devilsAdvocate = new DebateAgent(
    $client,
    "Devil's Advocate",
    'challenger',
    'You challenge everything. Find flaws, identify risks, question assumptions. ' .
    'Your job is to stress-test ideas by being highly skeptical.'
);

// Initial proposal
echo "Step 1: Initial Proposal\n";
echo str_repeat("-", 80) . "\n";

$initialProposal = $proposer->speak($proposal, "", "Explain why this proposal should be adopted.");
echo "Proposer:\n{$initialProposal}\n\n";

// Devil's advocate challenge
echo "Step 2: Devil's Advocate Challenge\n";
echo str_repeat("-", 80) . "\n";

$challenge = $devilsAdvocate->speak(
    $proposal,
    "Proposal: {$initialProposal}",
    "Challenge this proposal. What could go wrong? What's overlooked?"
);
echo "Devil's Advocate:\n{$challenge}\n\n";

// Response to challenge
echo "Step 3: Response to Challenge\n";
echo str_repeat("-", 80) . "\n";

$response = $proposer->speak(
    $proposal,
    "Challenge: {$challenge}",
    "Address these concerns and strengthen your proposal."
);
echo "Proposer (Response):\n{$response}\n\n";

// Final assessment
echo "Step 4: Final Assessment\n";
echo str_repeat("-", 80) . "\n";

$assessment = $devilsAdvocate->speak(
    $proposal,
    "Original: {$initialProposal}\nRevised: {$response}",
    "Has the proposal adequately addressed the concerns? Remaining issues?"
);
echo "Devil's Advocate (Assessment):\n{$assessment}\n\n";

echo "💡 Devil's advocate strengthens proposals by exposing weaknesses!\n";
echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 4: Consensus Building
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 4: Reaching Consensus\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$decision = "Which programming language should we use for our new microservice?";
echo "📋 Decision: {$decision}\n\n";

$engineer1 = new DebateAgent(
    $client,
    'Engineer A',
    'performance',
    'You prioritize performance and efficiency. You favor proven, fast technologies.'
);

$engineer2 = new DebateAgent(
    $client,
    'Engineer B',
    'productivity',
    'You prioritize developer productivity and maintainability. You favor modern, expressive languages.'
);

$engineer3 = new DebateAgent(
    $client,
    'Engineer C',
    'pragmatic',
    'You balance multiple concerns. You look for practical solutions that work well enough.'
);

$consensusAgents = [$engineer1, $engineer2, $engineer3];
$consensusContext = "";
$maxRounds = 3;

echo "Goal: Reach consensus on best choice\n";
echo "Participants: 3 engineers with different priorities\n\n";

for ($round = 1; $round <= $maxRounds; $round++) {
    echo "╔════ Round {$round} ════╗\n\n";
    
    $statements = [];
    foreach ($consensusAgents as $agent) {
        $instruction = $round === 1 
            ? "What language do you recommend and why?"
            : "Consider others' views. Can you find common ground?";
        
        $statement = $agent->speak($decision, $consensusContext, $instruction);
        $statements[] = $statement;
        echo "{$agent->getName()}: " . substr($statement, 0, 100) . "...\n";
        $consensusContext .= "\n{$agent->getName()}: {$statement}\n";
    }
    
    $agreement = $moderator->measureAgreement($statements);
    echo "\n📊 Agreement: " . round($agreement * 100) . "%\n\n";
    
    if ($agreement > 0.7) {
        echo "✅ Consensus threshold reached!\n\n";
        break;
    }
}

echo "💡 Iterative discussion moves toward consensus!\n";
echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 5: Debate Visualization
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 5: Debate Flow Visualization\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Multi-Agent Debate Architecture:\n\n";

echo "                 ┌─────────────┐\n";
echo "                 │   Topic     │\n";
echo "                 └──────┬──────┘\n";
echo "                        │\n";
echo "         ┌──────────────┼──────────────┐\n";
echo "         │              │              │\n";
echo "    ┌────▼────┐    ┌────▼────┐    ┌────▼────┐\n";
echo "    │Agent A  │    │Agent B  │    │Agent C  │\n";
echo "    │(Pro)    │    │(Con)    │    │(Neutral)│\n";
echo "    └────┬────┘    └────┬────┘    └────┬────┘\n";
echo "         │              │              │\n";
echo "         └──────────────┼──────────────┘\n";
echo "                        │\n";
echo "                   ┌────▼────┐\n";
echo "                   │Moderator│\n";
echo "                   │Synthesis│\n";
echo "                   └─────────┘\n\n";

echo "Debate Protocol:\n";
echo "  1. Opening Statements - Each agent presents position\n";
echo "  2. Cross-Examination - Agents challenge each other\n";
echo "  3. Rebuttals - Address counterarguments\n";
echo "  4. Synthesis - Moderator creates balanced conclusion\n\n";

echo "Benefits:\n";
echo "  ✓ Multiple perspectives prevent bias\n";
echo "  ✓ Critical thinking identifies flaws\n";
echo "  ✓ Diverse viewpoints reveal trade-offs\n";
echo "  ✓ Structured process ensures thoroughness\n";
echo "  ✓ Synthesis produces balanced decisions\n\n";

echo "Challenges:\n";
echo "  ⚠️  Higher cost (multiple agent calls)\n";
echo "  ⚠️  Longer time to decision\n";
echo "  ⚠️  Requires good synthesis\n";
echo "  ⚠️  May not converge to consensus\n\n";

echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Summary
// ============================================================================

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                           Tutorial Summary                                 ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "✅ Multi-Agent Debate Patterns Demonstrated:\n\n";

echo "1️⃣  Pro/Con Debate\n";
echo "   • Two opposing viewpoints\n";
echo "   • Structured argumentation\n";
echo "   • Balanced synthesis\n\n";

echo "2️⃣  Multi-Agent Round Table\n";
echo "   • Multiple diverse perspectives\n";
echo "   • Iterative discussion rounds\n";
echo "   • Agreement measurement\n\n";

echo "3️⃣  Devil's Advocate\n";
echo "   • Challenge assumptions\n";
echo "   • Stress-test proposals\n";
echo "   • Strengthen through critique\n\n";

echo "4️⃣  Consensus Building\n";
echo "   • Iterative toward agreement\n";
echo "   • Find common ground\n";
echo "   • Practical compromises\n\n";

echo "5️⃣  Structured Protocols\n";
echo "   • Opening statements\n";
echo "   • Rebuttals and responses\n";
echo "   • Moderator synthesis\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🎯 When to Use Multi-Agent Debate:\n\n";

echo "  ✓ Complex decisions with trade-offs\n";
echo "  ✓ Multiple valid perspectives exist\n";
echo "  ✓ High stakes warrant thoroughness\n";
echo "  ✓ Bias prevention is important\n";
echo "  ✓ Quality over speed priority\n\n";

echo "⚠️  When Simpler Approaches Suffice:\n\n";

echo "  • Clear-cut decisions\n";
echo "  • Time/cost constrained\n";
echo "  • Single perspective adequate\n";
echo "  • Low-stakes choices\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "💡 Key Insights:\n\n";

echo "  • Debate reveals blind spots and assumptions\n";
echo "  • Multiple perspectives improve decision quality\n";
echo "  • Structure prevents chaos, enables synthesis\n";
echo "  • Diverse roles create comprehensive coverage\n";
echo "  • Synthesis converts debate to actionable conclusion\n";
echo "  • Cost scales with agents and rounds\n\n";

echo "🚀 Multi-agent debate enables robust, well-reasoned decisions!\n\n";
echo "Next: Tutorial 13 - RAG Pattern for knowledge-grounded agents\n";
echo "→ tutorials/13-rag-pattern/\n\n";
