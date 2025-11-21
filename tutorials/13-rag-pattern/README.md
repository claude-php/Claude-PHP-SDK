# Tutorial 13: RAG Pattern (Retrieval-Augmented Generation)

**Time: 60 minutes** | **Difficulty: Advanced**

RAG (Retrieval-Augmented Generation) enhances AI agents with external knowledge by retrieving relevant information before generating responses. This grounds outputs in facts and extends agent capabilities beyond training data.

## 🎯 Learning Objectives

By the end of this tutorial, you'll be able to:

- Implement RAG pipelines for knowledge-grounded responses
- Build document retrieval systems
- Integrate external knowledge bases
- Chunk and embed documents effectively
- Combine retrieval with generation
- Handle citation and source attribution
- Optimize retrieval quality and performance

## 🏗️ What We're Building

A RAG system with:

1. **Document Store** - Knowledge base of documents
2. **Chunking System** - Break documents into retrievable pieces
3. **Retriever** - Find relevant chunks for queries
4. **Context Builder** - Format retrieved content
5. **Generator** - Claude with enhanced context
6. **Citation System** - Track and attribute sources

## 📋 Prerequisites

Make sure you have:

- Completed [Tutorial 12: Multi-Agent Debate](../12-multi-agent-debate/)
- Understanding of information retrieval concepts
- PHP 8.1+ installed
- Claude PHP SDK configured

## 🤔 What is RAG?

RAG combines retrieval and generation:

```
Without RAG:
Question → Claude → Answer (limited to training data)

With RAG:
Question → Retrieve Relevant Docs → Claude + Context → Grounded Answer
```

### Why RAG?

**Benefits:**

- ✅ **Current Information** - Beyond training cutoff
- ✅ **Domain Expertise** - Use private documents
- ✅ **Factual Grounding** - Reduce hallucinations
- ✅ **Citations** - Traceable sources
- ✅ **Dynamic Updates** - Add knowledge without retraining

**Challenges:**

- ❌ **Retrieval Quality** - Finding right documents
- ❌ **Context Length** - Fitting retrieved docs
- ❌ **Latency** - Extra retrieval step
- ❌ **Cost** - More tokens from context

## 🔑 Key Concepts

### 1. Document Chunking

Break documents into retrievable pieces:

```php
function chunkDocument($text, $chunkSize = 500, $overlap = 50) {
    $chunks = [];
    $words = explode(' ', $text);

    for ($i = 0; $i < count($words); $i += ($chunkSize - $overlap)) {
        $chunk = implode(' ', array_slice($words, $i, $chunkSize));
        if (!empty($chunk)) {
            $chunks[] = [
                'text' => $chunk,
                'start' => $i,
                'end' => min($i + $chunkSize, count($words))
            ];
        }
    }

    return $chunks;
}
```

### 2. Similarity Search

Find relevant chunks (simplified keyword matching):

```php
function searchChunks($query, $chunks, $topK = 3) {
    $queryTerms = array_map('strtolower', explode(' ', $query));
    $scored = [];

    foreach ($chunks as $i => $chunk) {
        $chunkText = strtolower($chunk['text']);
        $score = 0;

        foreach ($queryTerms as $term) {
            $score += substr_count($chunkText, $term);
        }

        $scored[] = ['index' => $i, 'score' => $score, 'chunk' => $chunk];
    }

    // Sort by score descending
    usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

    return array_slice($scored, 0, $topK);
}
```

### 3. Context Building

Format retrieved chunks for Claude:

```php
function buildContext($retrievedChunks) {
    $context = "Relevant information:\n\n";

    foreach ($retrievedChunks as $i => $item) {
        $source = $item['chunk']['source'] ?? 'Unknown';
        $text = $item['chunk']['text'];

        $context .= "[Source {$i}] {$source}:\n{$text}\n\n";
    }

    return $context;
}
```

### 4. RAG Query

Complete retrieval + generation:

```php
function ragQuery($client, $query, $documents) {
    // 1. Retrieve relevant chunks
    $allChunks = [];
    foreach ($documents as $doc) {
        $chunks = chunkDocument($doc['content']);
        foreach ($chunks as $chunk) {
            $chunk['source'] = $doc['title'];
            $allChunks[] = $chunk;
        }
    }

    $retrieved = searchChunks($query, $allChunks, 3);

    // 2. Build context
    $context = buildContext($retrieved);

    // 3. Generate with context
    $prompt = "{$context}\n\nQuestion: {$query}\n\n" .
              "Answer based on the provided sources. " .
              "Cite sources using [Source N] notation.";

    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 2048,
        'messages' => [['role' => 'user', 'content' => $prompt]]
    ]);

    return extractTextContent($response);
}
```

## 💡 RAG Implementation Patterns

### Basic RAG System

```php
class BasicRAG {
    private $client;
    private $documents = [];
    private $chunks = [];

    public function __construct($client) {
        $this->client = $client;
    }

    public function addDocument($title, $content) {
        $this->documents[] = ['title' => $title, 'content' => $content];

        // Chunk and store
        $chunks = $this->chunk($content);
        foreach ($chunks as $chunk) {
            $this->chunks[] = [
                'source' => $title,
                'text' => $chunk
            ];
        }
    }

    private function chunk($text, $size = 500) {
        $sentences = preg_split('/(?<=[.!?])\s+/', $text);
        $chunks = [];
        $current = '';

        foreach ($sentences as $sentence) {
            if (strlen($current . $sentence) > $size && !empty($current)) {
                $chunks[] = $current;
                $current = $sentence;
            } else {
                $current .= ($current ? ' ' : '') . $sentence;
            }
        }

        if (!empty($current)) {
            $chunks[] = $current;
        }

        return $chunks;
    }

    public function query($question) {
        // Retrieve
        $relevant = $this->retrieve($question, 3);

        // Build context
        $context = "Reference information:\n\n";
        foreach ($relevant as $i => $chunk) {
            $context .= "[{$i}] {$chunk['source']}:\n{$chunk['text']}\n\n";
        }

        // Generate
        $prompt = $context . "Question: {$question}\n\n" .
                 "Answer using the reference information. Cite sources.";

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1536,
            'messages' => [['role' => 'user', 'content' => $prompt]]
        ]);

        return extractTextContent($response);
    }

    private function retrieve($query, $k = 3) {
        $queryLower = strtolower($query);
        $scored = [];

        foreach ($this->chunks as $chunk) {
            $score = 0;
            $chunkLower = strtolower($chunk['text']);

            // Simple keyword matching
            $queryWords = explode(' ', $queryLower);
            foreach ($queryWords as $word) {
                if (strlen($word) > 3) {
                    $score += substr_count($chunkLower, $word);
                }
            }

            $scored[] = ['chunk' => $chunk, 'score' => $score];
        }

        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice(
            array_map(fn($x) => $x['chunk'], $scored),
            0,
            $k
        );
    }
}
```

## 🎯 Advanced RAG Techniques

### 1. Hybrid Search

Combine keyword and semantic search:

```php
function hybridSearch($query, $chunks, $alpha = 0.5) {
    $keywordScores = keywordSearch($query, $chunks);
    $semanticScores = semanticSearch($query, $chunks);

    $combined = [];
    foreach ($chunks as $i => $chunk) {
        $combined[$i] = $alpha * $keywordScores[$i] +
                       (1 - $alpha) * $semanticScores[$i];
    }

    arsort($combined);
    return array_slice(array_keys($combined), 0, 5);
}
```

### 2. Hierarchical Chunking

Maintain document structure:

```php
function hierarchicalChunk($document) {
    return [
        'summary' => extractSummary($document),
        'sections' => [
            [
                'title' => 'Introduction',
                'content' => '...',
                'chunks' => chunkText($content)
            ],
            // More sections...
        ]
    ];
}
```

### 3. Query Expansion

Improve retrieval with expanded queries:

```php
function expandQuery($client, $query) {
    $prompt = "Original query: {$query}\n\n" .
              "Generate 3 alternative phrasings that could help " .
              "find relevant information:";

    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 512,
        'messages' => [['role' => 'user', 'content' => $prompt]]
    ]);

    $alternatives = extractTextContent($response);
    return [$query] + parseAlternatives($alternatives);
}
```

### 4. Re-ranking

Refine initial retrieval:

```php
function rerank($client, $query, $candidates) {
    $ranked = [];

    foreach ($candidates as $candidate) {
        $prompt = "Query: {$query}\n\n" .
                 "Document: {$candidate['text']}\n\n" .
                 "Relevance score (0-10):";

        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 10,
            'messages' => [['role' => 'user', 'content' => $prompt]]
        ]);

        $score = extractScore(extractTextContent($response));
        $ranked[] = ['candidate' => $candidate, 'score' => $score];
    }

    usort($ranked, fn($a, $b) => $b['score'] <=> $a['score']);
    return array_map(fn($x) => $x['candidate'], $ranked);
}
```

### 5. Citation Extraction

Track which sources were used:

```php
function extractCitations($response) {
    preg_match_all('/\[Source (\d+)\]/', $response, $matches);
    return array_unique($matches[1]);
}
```

## 📊 RAG Optimization

### Chunk Size Optimization

```php
$chunkingStrategies = [
    'small' => ['size' => 200, 'overlap' => 50],   // Precise retrieval
    'medium' => ['size' => 500, 'overlap' => 100], // Balanced
    'large' => ['size' => 1000, 'overlap' => 200]  // More context
];
```

### Retrieval Count

```php
$retrievalConfigs = [
    'precise' => 1,     // Single best match
    'standard' => 3,    // Good coverage
    'comprehensive' => 10  // Maximum context
];
```

### Context Window Management

```php
function fitContext($chunks, $maxTokens = 4000) {
    $context = '';
    $tokenCount = 0;
    $fitted = [];

    foreach ($chunks as $chunk) {
        $chunkTokens = estimateTokens($chunk['text']);

        if ($tokenCount + $chunkTokens > $maxTokens) {
            break;
        }

        $context .= $chunk['text'] . "\n\n";
        $tokenCount += $chunkTokens;
        $fitted[] = $chunk;
    }

    return ['context' => $context, 'chunks' => $fitted, 'tokens' => $tokenCount];
}
```

## 🎨 RAG Use Cases

### 1. Documentation Q&A

```php
// Add product documentation
$rag->addDocument('User Guide', $userGuideContent);
$rag->addDocument('API Reference', $apiDocsContent);

// Answer questions
$answer = $rag->query("How do I authenticate API requests?");
```

### 2. Research Assistant

```php
// Index research papers
foreach ($papers as $paper) {
    $rag->addDocument($paper['title'], $paper['abstract'] . ' ' . $paper['content']);
}

// Ask research questions
$summary = $rag->query("What are the latest findings on topic X?");
```

### 3. Customer Support

```php
// Knowledge base
$rag->addDocument('FAQ', $faqContent);
$rag->addDocument('Troubleshooting', $troubleshootingContent);

// Answer customer queries
$response = $rag->query("How do I reset my password?");
```

### 4. Code Search

```php
// Index codebase
foreach ($codeFiles as $file) {
    $rag->addDocument($file['path'], $file['content']);
}

// Find relevant code
$examples = $rag->query("Show me examples of authentication middleware");
```

## ⚙️ Configuration

### Document Preprocessing

```php
$preprocessingConfig = [
    'remove_boilerplate' => true,
    'extract_metadata' => true,
    'normalize_whitespace' => true,
    'min_chunk_size' => 100,
    'max_chunk_size' => 1000
];
```

### Retrieval Settings

```php
$retrievalConfig = [
    'top_k' => 5,
    'min_score' => 0.3,
    'rerank' => true,
    'expand_query' => false
];
```

## 📈 Evaluation Metrics

Measure RAG quality:

```php
$ragMetrics = [
    'retrieval_precision' => 0.85,  // Relevant docs retrieved
    'retrieval_recall' => 0.75,     // All relevant docs found
    'answer_accuracy' => 0.90,      // Correct answers
    'citation_accuracy' => 0.95,    // Correct source attribution
    'avg_latency' => 1.2,          // seconds
    'avg_cost' => 0.05             // dollars per query
];
```

## ✅ Checkpoint

Before moving on, make sure you understand:

- [ ] RAG architecture (retrieve + generate)
- [ ] Document chunking strategies
- [ ] Similarity search basics
- [ ] Context building for Claude
- [ ] Citation and attribution
- [ ] Optimization trade-offs
- [ ] When RAG adds value
- [ ] RAG vs fine-tuning

## 🚀 Next Steps

You've mastered RAG! Ready for the ultimate challenge?

**[Tutorial 14: Autonomous Agents →](../14-autonomous-agents/)**

Learn to build self-directed agents that pursue goals independently!

## 💻 Try It Yourself

Run the complete working example:

```bash
php tutorials/13-rag-pattern/rag_agent.php
```

The script demonstrates:

- ✅ Knowledge base setup and ingestion
- ✅ Document retrieval by similarity
- ✅ Context injection into prompts
- ✅ Citation tracking and attribution
- ✅ Multi-source synthesis
- ✅ Handling missing information gracefully

## 💡 Key Takeaways

1. **RAG grounds responses** - External knowledge reduces hallucinations
2. **Retrieval is critical** - Quality depends on finding right docs
3. **Chunking matters** - Size affects precision and context
4. **Citations build trust** - Traceable sources increase confidence
5. **Optimize for use case** - Balance latency, cost, accuracy
6. **Update dynamically** - Add knowledge without retraining
7. **Measure performance** - Track retrieval and generation quality
8. **Combine with other patterns** - RAG + ReAct, RAG + Reflection

## 📚 Further Reading

### Research Papers

- **[Retrieval-Augmented Generation](https://arxiv.org/abs/2005.11401)** - Lewis et al., 2020
- **[REALM: Retrieval-Augmented Language Modeling](https://arxiv.org/abs/2002.08909)** - Guu et al., 2020
- **[Dense Passage Retrieval](https://arxiv.org/abs/2004.04906)** - Karpukhin et al., 2020

### Related Tutorials

- [Tutorial 3: Multi-Tool Agent](../03-multi-tool-agent/) - Tool integration basics
- [Tutorial 6: Agentic Framework](../06-agentic-framework/) - System design
- [Tutorial 14: Autonomous Agents](../14-autonomous-agents/) - Goal-directed agents

### Tools and Libraries

- **Vector Databases**: Pinecone, Weaviate, Qdrant, Milvus
- **Embedding Models**: OpenAI, Cohere, Sentence Transformers
- **Document Processing**: Apache Tika, PyPDF2, python-docx

## 🎓 Practice Exercises

Try building RAG systems for:

1. **Personal Knowledge Base** - Your notes and documents
2. **Code Documentation** - Project README and code comments
3. **News Summarization** - Recent articles on topics
4. **Legal Research** - Case law and statutes

## 🔧 Troubleshooting

**Issue**: Poor retrieval quality

- **Solution**: Improve chunking, expand queries, use better similarity metrics

**Issue**: Retrieved docs not relevant

- **Solution**: Increase top-k, improve document preprocessing, filter noise

**Issue**: Answer ignores retrieved context

- **Solution**: Strengthen prompt instructions, reduce context length, improve chunk quality

**Issue**: High latency

- **Solution**: Reduce retrieval count, optimize search algorithm, cache common queries

**Issue**: Citations missing or wrong

- **Solution**: Format sources clearly, instruct model explicitly, validate citations
