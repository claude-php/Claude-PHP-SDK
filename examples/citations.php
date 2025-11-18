#!/usr/bin/env php
<?php
/**
 * Citations - PHP examples from:
 * https://docs.claude.com/en/docs/build-with-claude/citations
 * 
 * Enable Claude to cite sources when answering questions about documents.
 * Requires 'citations-2024-11-12' beta header.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Citations - Source Attribution ===\n\n";
echo "⚠️  Citations is currently in BETA\n";
echo "Requires beta header: 'citations-2024-11-12'\n\n";

// Example 1: Basic citations
echo "Example 1: Basic Citations\n";
echo "--------------------------\n";
echo "Claude cites sources when answering questions about documents\n\n";

try {
    $document = "The Eiffel Tower was completed in 1889. It stands 330 meters tall. " .
        "The tower was designed by Gustave Eiffel for the 1889 World's Fair in Paris. " .
        "It took 2 years, 2 months and 5 days to build.";
    
    $response = $client->beta()->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'betas' => ['citations-2024-11-12'],
        'messages' => [
            [
                'role' => 'user',
                'content' => "Here is a document:\n\n{$document}\n\nWhen was the Eiffel Tower completed?"
            ]
        ]
    ]);

    echo "Document provided about Eiffel Tower\n";
    echo "Question: When was the Eiffel Tower completed?\n\n";
    
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo "Answer: {$block['text']}\n";
            
            if (isset($block['citations'])) {
                echo "\nCitations:\n";
                foreach ($block['citations'] as $citation) {
                    echo "  • Type: {$citation['type']}\n";
                    echo "    Cited text: \"{$citation['cited_text']}\"\n";
                    echo "    Document index: {$citation['document_index']}\n\n";
                }
            }
        }
    }
    
    echo "Note: Citations show which parts of the document were used\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Note: Citations requires beta access\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: Citations response structure
echo "Example 2: Citations Response Structure\n";
echo "----------------------------------------\n\n";

echo "When citations are enabled, text blocks include a 'citations' array:\n\n";
echo "{\n";
echo "  \"type\": \"text\",\n";
echo "  \"text\": \"The Eiffel Tower was completed in 1889.\",\n";
echo "  \"citations\": [\n";
echo "    {\n";
echo "      \"type\": \"document_citation\",\n";
echo "      \"cited_text\": \"The Eiffel Tower was completed in 1889\",\n";
echo "      \"document_index\": 0,\n";
echo "      \"start_index\": 0,\n";
echo "      \"end_index\": 41\n";
echo "    }\n";
echo "  ]\n";
echo "}\n\n";

echo "Citation fields:\n";
echo "  • type: 'document_citation'\n";
echo "  • cited_text: The specific text cited from source\n";
echo "  • document_index: Which document (when multiple provided)\n";
echo "  • start_index: Starting position in source text\n";
echo "  • end_index: Ending position in source text\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 3: Multiple documents
echo "Example 3: Multiple Documents with Citations\n";
echo "---------------------------------------------\n";
echo "Use document_index to track which document was cited\n\n";

echo "```php\n";
echo "\$response = \$client->beta()->messages()->create([\n";
echo "    'model' => 'claude-sonnet-4-5',\n";
echo "    'max_tokens' => 1024,\n";
echo "    'betas' => ['citations-2024-11-12'],\n";
echo "    'messages' => [\n";
echo "        [\n";
echo "            'role' => 'user',\n";
echo "            'content' => [\n";
echo "                ['type' => 'document', 'source' => ['type' => 'text', 'text' => \$doc1]],\n";
echo "                ['type' => 'document', 'source' => ['type' => 'text', 'text' => \$doc2]],\n";
echo "                ['type' => 'text', 'text' => 'Summarize key points from both documents']\n";
echo "            ]\n";
echo "        ]\n";
echo "    ]\n";
echo "]);\n";
echo "```\n\n";

echo "Each citation will indicate which document (document_index: 0 or 1)\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 4: Use cases
echo "Example 4: Use Cases for Citations\n";
echo "-----------------------------------\n\n";

echo "✓ Document Analysis:\n";
echo "  • Verify Claude's answers are grounded in source material\n";
echo "  • Track which parts of documents are being referenced\n";
echo "  • Ensure factual accuracy\n\n";

echo "✓ Research & Summarization:\n";
echo "  • Attribute information to specific sources\n";
echo "  • Build trust with verifiable citations\n";
echo "  • Easy fact-checking\n\n";

echo "✓ Compliance & Auditing:\n";
echo "  • Maintain audit trail of information sources\n";
echo "  • Demonstrate due diligence\n";
echo "  • Support regulatory requirements\n\n";

echo "✓ RAG (Retrieval-Augmented Generation):\n";
echo "  • Show which retrieved documents were used\n";
echo "  • Improve transparency in RAG systems\n";
echo "  • Help users verify information\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "✓ Citations examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Citations is BETA - requires 'citations-2024-11-12' header\n";
echo "• Use \$client->beta()->messages()->create() with betas parameter\n";
echo "• Text blocks include 'citations' array with source attribution\n";
echo "• Each citation shows cited_text, document_index, start/end positions\n";
echo "• Supports multiple documents with document_index tracking\n";
echo "• Improves transparency and verifiability\n";
echo "• Ideal for: Document Q&A, research, compliance, RAG systems\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

