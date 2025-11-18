#!/usr/bin/env php
<?php
/**
 * Embeddings - PHP examples from:
 * https://docs.claude.com/en/docs/build-with-claude/embeddings
 * 
 * Generate text embeddings using Voyage AI models via Anthropic's API.
 * Useful for semantic search, RAG, and similarity comparisons.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Embeddings - Semantic Search & RAG ===\n\n";

// Example 1: Basic embedding generation
echo "Example 1: Generate Embeddings\n";
echo "-------------------------------\n";
echo "Convert text into dense vector representations\n\n";

echo "Note: The PHP SDK currently focuses on the Messages API.\n";
echo "For embeddings, use the HTTP API directly or a dedicated embeddings library.\n\n";

echo "Example cURL command:\n";
echo "```bash\n";
echo "curl https://api.anthropic.com/v1/embeddings \\\n";
echo "  --header \"x-api-key: \$ANTHROPIC_API_KEY\" \\\n";
echo "  --header \"anthropic-version: 2023-06-01\" \\\n";
echo "  --header \"content-type: application/json\" \\\n";
echo "  --data '{\n";
echo "    \"model\": \"voyage-3\",\n";
echo "    \"input\": [\"Hello world\", \"Goodbye world\"]\n";
echo "  }'\n";
echo "```\n\n";

echo "Response format:\n";
echo "{\n";
echo "  \"object\": \"list\",\n";
echo "  \"data\": [\n";
echo "    {\n";
echo "      \"object\": \"embedding\",\n";
echo "      \"embedding\": [0.123, -0.456, ...],\n";
echo "      \"index\": 0\n";
echo "    },\n";
echo "    {...}\n";
echo "  ],\n";
echo "  \"model\": \"voyage-3\",\n";
echo "  \"usage\": {\"total_tokens\": 4}\n";
echo "}\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: Available models
echo "Example 2: Available Embedding Models\n";
echo "--------------------------------------\n\n";

echo "Voyage AI models via Anthropic:\n\n";
echo "• voyage-3\n";
echo "  - Latest general-purpose model\n";
echo "  - Dimensions: 1024\n";
echo "  - Best for: Most applications\n\n";

echo "• voyage-3-lite\n";
echo "  - Faster, more efficient\n";
echo "  - Dimensions: 512\n";
echo "  - Best for: High-throughput scenarios\n\n";

echo "• voyage-code-3\n";
echo "  - Optimized for code\n";
echo "  - Dimensions: 1024\n";
echo "  - Best for: Code search, similarity\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 3: Use cases
echo "Example 3: Common Use Cases\n";
echo "---------------------------\n\n";

echo "✓ Semantic Search:\n";
echo "  1. Embed all documents in your corpus\n";
echo "  2. Embed user query\n";
echo "  3. Find nearest neighbors using cosine similarity\n";
echo "  4. Return most relevant documents\n\n";

echo "✓ RAG (Retrieval-Augmented Generation):\n";
echo "  1. Embed knowledge base documents\n";
echo "  2. For each query, embed and find similar documents\n";
echo "  3. Pass retrieved documents to Claude with citations\n";
echo "  4. Claude generates grounded, cited responses\n\n";

echo "✓ Clustering & Classification:\n";
echo "  • Group similar documents together\n";
echo "  • Classify new content based on embeddings\n";
echo "  • Detect duplicate or near-duplicate content\n\n";

echo "✓ Recommendation Systems:\n";
echo "  • Find similar items based on embeddings\n";
echo "  • Content-based filtering\n";
echo "  • Personalization engines\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "✓ Embeddings examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Embeddings convert text to dense vectors\n";
echo "• Available models: voyage-3, voyage-3-lite, voyage-code-3\n";
echo "• Use for: Semantic search, RAG, clustering, recommendations\n";
echo "• Combine with Citations for verifiable RAG systems\n";
echo "• Access via HTTP API (not yet in PHP SDK Messages API)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • examples/citations.php - Source attribution\n";
echo "  • examples/search_results.php - Web search integration\n";

