#!/usr/bin/env php
<?php
/**
 * Tutorial 13: RAG Pattern - Working Example
 * 
 * Demonstrates Retrieval-Augmented Generation for grounding AI responses
 * in external knowledge and reducing hallucinations.
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../examples/helpers.php';
require_once __DIR__ . '/../helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║        Tutorial 13: RAG Pattern - Knowledge-Grounded Responses             ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// RAG System Classes
// ============================================================================

/**
 * Simple RAG system with document storage and retrieval
 */
class SimpleRAG
{
    private $client;
    private $documents = [];
    private $chunks = [];

    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * Add a document to the knowledge base
     */
    public function addDocument($title, $content, $metadata = [])
    {
        $docId = count($this->documents);
        $this->documents[$docId] = [
            'id' => $docId,
            'title' => $title,
            'content' => $content,
            'metadata' => $metadata
        ];

        // Chunk and index
        $chunks = $this->chunkText($content);
        foreach ($chunks as $i => $chunkText) {
            $this->chunks[] = [
                'doc_id' => $docId,
                'chunk_id' => $i,
                'source' => $title,
                'text' => $chunkText
            ];
        }

        return $docId;
    }

    /**
     * Chunk text into smaller pieces
     */
    private function chunkText($text, $chunkSize = 500, $overlap = 50)
    {
        // Split by sentences
        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        $chunks = [];
        $currentChunk = '';
        $wordCount = 0;

        foreach ($sentences as $sentence) {
            $sentenceWords = str_word_count($sentence);

            if ($wordCount + $sentenceWords > $chunkSize && !empty($currentChunk)) {
                $chunks[] = trim($currentChunk);

                // Keep overlap
                $words = explode(' ', $currentChunk);
                $overlapWords = array_slice($words, -$overlap);
                $currentChunk = implode(' ', $overlapWords) . ' ' . $sentence;
                $wordCount = count($overlapWords) + $sentenceWords;
            } else {
                $currentChunk .= ' ' . $sentence;
                $wordCount += $sentenceWords;
            }
        }

        if (!empty(trim($currentChunk))) {
            $chunks[] = trim($currentChunk);
        }

        return $chunks;
    }

    /**
     * Retrieve relevant chunks for a query
     */
    public function retrieve($query, $topK = 3)
    {
        $queryLower = strtolower($query);
        $queryWords = array_filter(
            explode(' ', $queryLower),
            fn($w) => strlen($w) > 3
        );

        $scored = [];

        foreach ($this->chunks as $chunk) {
            $chunkLower = strtolower($chunk['text']);
            $score = 0;

            // Keyword matching score
            foreach ($queryWords as $word) {
                $count = substr_count($chunkLower, $word);
                $score += $count * strlen($word); // Weight by word length
            }

            // Bonus for exact phrase
            if (strpos($chunkLower, $queryLower) !== false) {
                $score += 50;
            }

            $scored[] = [
                'chunk' => $chunk,
                'score' => $score
            ];
        }

        // Sort by score descending
        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        // Return top K
        return array_slice(
            array_map(fn($x) => $x['chunk'], $scored),
            0,
            $topK
        );
    }

    /**
     * Query with RAG
     */
    public function query($question, $topK = 3)
    {
        // 1. Retrieve relevant chunks
        $relevant = $this->retrieve($question, $topK);

        if (empty($relevant)) {
            return "No relevant information found in knowledge base.";
        }

        // 2. Build context
        $context = "Reference Information:\n\n";
        foreach ($relevant as $i => $chunk) {
            $context .= "[Source {$i}] {$chunk['source']}:\n";
            $context .= $chunk['text'] . "\n\n";
        }

        // 3. Generate answer with context
        $prompt = $context .
            "Question: {$question}\n\n" .
            "Answer the question based on the reference information provided. " .
            "Cite sources using [Source N] notation. " .
            "If the information is not in the references, say so.";

        try {
            $response = $this->client->messages()->create([
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 1536,
                'messages' => [['role' => 'user', 'content' => $prompt]]
            ]);

            return [
                'answer' => extractTextContent($response),
                'sources' => $relevant
            ];
        } catch (Exception $e) {
            return [
                'answer' => "Error: {$e->getMessage()}",
                'sources' => []
            ];
        }
    }

    /**
     * Get document count
     */
    public function getDocumentCount()
    {
        return count($this->documents);
    }

    /**
     * Get chunk count
     */
    public function getChunkCount()
    {
        return count($this->chunks);
    }
}

// ============================================================================
// Sample Documents for Testing
// ============================================================================

$sampleDocs = [
    [
        'title' => 'PHP 8 Features',
        'content' => 'PHP 8 was released in November 2020 and introduced several major features. ' .
            'Named arguments allow you to pass parameters to functions by specifying the parameter name. ' .
            'Union types enable a value to be of multiple types, declared with Type1|Type2 syntax. ' .
            'The match expression is a more powerful alternative to switch statements with strict type comparisons. ' .
            'The nullsafe operator ?-> allows chaining on potentially null values without errors. ' .
            'Constructor property promotion reduces boilerplate by declaring and initializing properties in one line. ' .
            'JIT (Just-In-Time) compilation improves performance for certain workloads. ' .
            'Attributes provide a way to add metadata to classes and functions, replacing docblock annotations.'
    ],
    [
        'title' => 'Claude API Guide',
        'content' => 'The Claude API allows you to integrate Claude into your applications. ' .
            'Authentication requires an API key passed in the x-api-key header. ' .
            'The Messages API is the primary endpoint for sending prompts and receiving responses. ' .
            'You can specify the model, such as claude-sonnet-4-5 or claude-opus-4-20250514. ' .
            'Max tokens controls the maximum length of the response. ' .
            'Temperature affects randomness, from 0.0 (deterministic) to 1.0 (creative). ' .
            'System prompts set the behavior and role of Claude for the conversation. ' .
            'Tools enable function calling, allowing Claude to use external capabilities. ' .
            'Streaming responses provide real-time token delivery for better user experience.'
    ],
    [
        'title' => 'Agentic AI Concepts',
        'content' => 'Agentic AI refers to AI systems that can pursue goals autonomously. ' .
            'The ReAct pattern combines reasoning and action in iterative loops. ' .
            'Tools are external functions that agents can call to interact with the world. ' .
            'Multi-step reasoning allows agents to break down complex tasks into manageable subtasks. ' .
            'Memory enables agents to maintain context across interactions. ' .
            'Planning involves creating action sequences before execution. ' .
            'Reflection allows agents to evaluate and improve their own outputs. ' .
            'Hierarchical agents organize work through master-worker relationships. ' .
            'RAG (Retrieval-Augmented Generation) grounds agent responses in external knowledge.'
    ]
];

// ============================================================================
// Example 1: Basic RAG Query
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 1: Basic RAG - Knowledge Base Query\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$rag = new SimpleRAG($client);

echo "Building knowledge base...\n";
foreach ($sampleDocs as $doc) {
    $docId = $rag->addDocument($doc['title'], $doc['content']);
    echo "  ✓ Added: {$doc['title']} (ID: {$docId})\n";
}

echo "\nKnowledge Base Stats:\n";
echo "  • Documents: {$rag->getDocumentCount()}\n";
echo "  • Chunks: {$rag->getChunkCount()}\n\n";

$question1 = "What is the nullsafe operator in PHP 8?";
echo "Question: {$question1}\n";
echo str_repeat("-", 80) . "\n";

$result1 = $rag->query($question1, 2);
echo "\nAnswer:\n{$result1['answer']}\n\n";

echo "Sources Used:\n";
foreach ($result1['sources'] as $i => $source) {
    echo "  [{$i}] {$source['source']}\n";
}

echo "\n💡 RAG retrieved relevant context and generated grounded answer!\n";
echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 2: Multiple Source Integration
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 2: Multi-Source Query\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$question2 = "How do tools work in agentic AI systems?";
echo "Question: {$question2}\n";
echo str_repeat("-", 80) . "\n";

$result2 = $rag->query($question2, 3);
echo "\nAnswer:\n{$result2['answer']}\n\n";

if (!empty($result2['sources'])) {
    echo "Information drawn from " . count($result2['sources']) . " sources:\n";
    $sourceTitles = array_unique(array_map(fn($s) => $s['source'], $result2['sources']));
    foreach ($sourceTitles as $title) {
        echo "  • {$title}\n";
    }
}

echo "\n💡 RAG synthesized information from multiple documents!\n";
echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 3: Comparing With and Without RAG
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 3: RAG vs No RAG Comparison\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$question3 = "What are the key features of PHP 8?";
echo "Question: {$question3}\n\n";

// Without RAG
echo "╔════ Without RAG (Direct Query) ════╗\n\n";

try {
    $noRag = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [['role' => 'user', 'content' => $question3]]
    ]);

    $noRagAnswer = extractTextContent($noRag);
    echo substr($noRagAnswer, 0, 300) . "...\n\n";
    echo "Note: Answer based on training data\n\n";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// With RAG
echo "╔════ With RAG (Knowledge-Grounded) ════╗\n\n";

$result3 = $rag->query($question3, 2);
echo substr($result3['answer'], 0, 300) . "...\n\n";
echo "Note: Answer grounded in provided documents with citations\n\n";

echo "💡 RAG provides verifiable, source-attributed answers!\n";
echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 4: Retrieval Quality Analysis
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 4: Analyzing Retrieval Quality\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$testQueries = [
    "What is match expression?",
    "How to authenticate Claude API?",
    "What is the ReAct pattern?",
    "Tell me about constructor property promotion"
];

echo "Testing retrieval for multiple queries...\n\n";

foreach ($testQueries as $query) {
    echo "Query: \"{$query}\"\n";

    $retrieved = $rag->retrieve($query, 1);

    if (!empty($retrieved)) {
        $topChunk = $retrieved[0];
        echo "  → Top match: {$topChunk['source']}\n";
        echo "  → Preview: " . substr($topChunk['text'], 0, 80) . "...\n";
    } else {
        echo "  → No matches found\n";
    }

    echo "\n";
}

echo "💡 Good retrieval is critical for RAG quality!\n";
echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 5: Handling No Relevant Information
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 5: Graceful Handling of Missing Information\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$offTopicQuestion = "What is the capital of France?";
echo "Question: {$offTopicQuestion}\n";
echo "(Not in knowledge base)\n";
echo str_repeat("-", 80) . "\n";

$result5 = $rag->query($offTopicQuestion, 3);
echo "\nAnswer:\n{$result5['answer']}\n\n";

echo "💡 RAG agents should acknowledge when information is unavailable!\n";
echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 6: Chunk Size Impact
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 6: Document Chunking Strategy\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$longDoc = str_repeat($sampleDocs[0]['content'] . ' ', 3);

echo "Testing different chunking strategies on long document...\n\n";

// Create test RAG systems with different chunk sizes
$strategies = [
    'Small Chunks' => 200,
    'Medium Chunks' => 500,
    'Large Chunks' => 1000
];

foreach ($strategies as $name => $chunkSize) {
    $testRag = new SimpleRAG($client);
    $testRag->addDocument('Test Doc', $longDoc);

    echo "{$name} ({$chunkSize} words):\n";
    echo "  • Total chunks: {$testRag->getChunkCount()}\n";
    echo "  • Avg chunk size: ~{$chunkSize} words\n";
    echo "  • Trade-off: " . ($chunkSize < 500 ? "Precision > Context" : "Context > Precision") . "\n\n";
}

echo "💡 Chunk size affects retrieval precision and context!\n";
echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Example 7: RAG System Architecture
// ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 7: RAG Architecture Overview\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "RAG Pipeline:\n\n";

echo "  1. INDEXING (Offline)\n";
echo "     ┌──────────┐\n";
echo "     │Documents │\n";
echo "     └────┬─────┘\n";
echo "          │\n";
echo "     ┌────▼──────┐\n";
echo "     │  Chunk    │\n";
echo "     └────┬──────┘\n";
echo "          │\n";
echo "     ┌────▼──────┐\n";
echo "     │  Index    │\n";
echo "     └───────────┘\n\n";

echo "  2. RETRIEVAL (Online)\n";
echo "     ┌──────────┐\n";
echo "     │  Query   │\n";
echo "     └────┬─────┘\n";
echo "          │\n";
echo "     ┌────▼──────┐\n";
echo "     │  Search   │\n";
echo "     └────┬──────┘\n";
echo "          │\n";
echo "     ┌────▼──────┐\n";
echo "     │Top K Docs │\n";
echo "     └───────────┘\n\n";

echo "  3. GENERATION\n";
echo "     ┌──────────┐     ┌──────────┐\n";
echo "     │  Query   │ +   │Retrieved │\n";
echo "     └────┬─────┘     │  Docs    │\n";
echo "          │           └────┬─────┘\n";
echo "          └────────┬────────┘\n";
echo "                   │\n";
echo "              ┌────▼────┐\n";
echo "              │ Claude  │\n";
echo "              └────┬────┘\n";
echo "                   │\n";
echo "              ┌────▼────┐\n";
echo "              │ Answer  │\n";
echo "              │+ Sources│\n";
echo "              └─────────┘\n\n";

echo "Key Components:\n";
echo "  • Document Store - Knowledge repository\n";
echo "  • Chunker - Breaks docs into retrievable units\n";
echo "  • Retriever - Finds relevant chunks (keyword, semantic)\n";
echo "  • Context Builder - Formats retrieved content\n";
echo "  • Generator (Claude) - Produces grounded answers\n";
echo "  • Citation System - Tracks source attribution\n\n";

echo str_repeat("═", 80) . "\n\n";

// ============================================================================
// Summary
// ============================================================================

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                           Tutorial Summary                                 ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "✅ RAG Pattern Components Demonstrated:\n\n";

echo "1️⃣  Document Indexing\n";
echo "   • Add documents to knowledge base\n";
echo "   • Chunk into retrievable pieces\n";
echo "   • Maintain source metadata\n\n";

echo "2️⃣  Retrieval\n";
echo "   • Keyword-based search\n";
echo "   • Similarity scoring\n";
echo "   • Top-K selection\n\n";

echo "3️⃣  Context Building\n";
echo "   • Format retrieved chunks\n";
echo "   • Include source attribution\n";
echo "   • Manage context length\n\n";

echo "4️⃣  Grounded Generation\n";
echo "   • Claude with retrieved context\n";
echo "   • Source citation\n";
echo "   • Factual grounding\n\n";

echo "5️⃣  Quality Considerations\n";
echo "   • Chunk size optimization\n";
echo "   • Retrieval accuracy\n";
echo "   • Missing information handling\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🎯 When to Use RAG:\n\n";

echo "  ✓ Need current information\n";
echo "  ✓ Domain-specific knowledge\n";
echo "  ✓ Private/proprietary data\n";
echo "  ✓ Reduce hallucinations\n";
echo "  ✓ Require source attribution\n";
echo "  ✓ Dynamic knowledge updates\n\n";

echo "⚠️  When RAG May Not Help:\n\n";

echo "  • General knowledge queries\n";
echo "  • Creative generation tasks\n";
echo "  • Small, static knowledge sets\n";
echo "  • Latency-critical applications\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "💡 Key Insights:\n\n";

echo "  • RAG grounds responses in facts\n";
echo "  • Retrieval quality determines answer quality\n";
echo "  • Chunking strategy impacts precision vs context\n";
echo "  • Citations build trust and verifiability\n";
echo "  • Can update knowledge without retraining\n";
echo "  • Combine with other patterns (RAG + ReAct)\n\n";

echo "🚀 RAG enables knowledge-grounded, trustworthy AI agents!\n\n";
echo "Next: Tutorial 14 - Autonomous Agents for goal-directed behavior\n";
echo "→ tutorials/14-autonomous-agents/\n\n";
