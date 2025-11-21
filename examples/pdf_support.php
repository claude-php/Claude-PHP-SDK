#!/usr/bin/env php
<?php
/**
 * PDF Support - PHP examples from:
 * https://docs.claude.com/en/docs/build-with-claude/pdf-support
 * 
 * Analyze PDF documents using Claude's vision and document understanding.
 * PDFs are converted to images for processing.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== PDF Support - Document Analysis ===\n\n";

// Example 1: PDF via base64
echo "Example 1: PDF Document via Base64\n";
echo "-----------------------------------\n";
echo "Send PDF documents as base64-encoded content\n\n";

echo "```php\n";
echo "\$pdfData = base64_encode(file_get_contents('document.pdf'));\n\n";
echo "\$response = \$client->messages()->create([\n";
echo "    'model' => 'claude-sonnet-4-5',\n";
echo "    'max_tokens' => 4096,\n";
echo "    'messages' => [\n";
echo "        [\n";
echo "            'role' => 'user',\n";
echo "            'content' => [\n";
echo "                [\n";
echo "                    'type' => 'document',\n";
echo "                    'source' => [\n";
echo "                        'type' => 'base64',\n";
echo "                        'media_type' => 'application/pdf',\n";
echo "                        'data' => \$pdfData\n";
echo "                    ]\n";
echo "                ],\n";
echo "                [\n";
echo "                    'type' => 'text',\n";
echo "                    'text' => 'Summarize this PDF document'\n";
echo "                ]\n";
echo "            ]\n";
echo "        ]\n";
echo "    ]\n";
echo "]);\n";
echo "```\n\n";

echo "Note: PDFs are converted to images internally\n";
echo "Each page becomes an image for Claude to analyze\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: PDF limitations and considerations
echo "Example 2: PDF Limitations & Considerations\n";
echo "-------------------------------------------\n\n";

echo "✓ Size Limits:\n";
echo "  • Max file size: 32MB per PDF\n";
echo "  • Max pages: Varies by resolution\n";
echo "  • Token cost: Each page counts as image tokens\n\n";

echo "✓ Processing:\n";
echo "  • PDFs converted to images internally\n";
echo "  • Text extraction not guaranteed\n";
echo "  • Visual layout preserved\n";
echo "  • Complex layouts may be challenging\n\n";

echo "✓ Best Practices:\n";
echo "  • High-quality PDFs work best\n";
echo "  • Simple layouts are easier to process\n";
echo "  • Monitor token usage (each page = image)\n";
echo "  • Consider extracting text if PDF is text-heavy\n\n";

echo "✓ Use Cases:\n";
echo "  • Document summarization\n";
echo "  • Form extraction\n";
echo "  • Invoice processing\n";
echo "  • Contract analysis\n";
echo "  • Report understanding\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 3: Alternative approaches
echo "Example 3: Alternative Approaches for Text-Heavy PDFs\n";
echo "------------------------------------------------------\n\n";

echo "For text-heavy PDFs, consider extracting text first:\n\n";

echo "```php\n";
echo "// Option 1: Extract text with a library\n";
echo "// (e.g., using Smalot\\PdfParser or similar)\n";
echo "use Smalot\\PdfParser\\Parser;\n\n";
echo "\$parser = new Parser();\n";
echo "\$pdf = \$parser->parseFile('document.pdf');\n";
echo "\$text = \$pdf->getText();\n\n";
echo "// Then send as text (much more token-efficient)\n";
echo "\$response = \$client->messages()->create([\n";
echo "    'model' => 'claude-sonnet-4-5',\n";
echo "    'max_tokens' => 4096,\n";
echo "    'messages' => [\n";
echo "        [\n";
echo "            'role' => 'user',\n";
echo "            'content' => \"Summarize this document:\\n\\n{\$text}\"\n";
echo "        ]\n";
echo "    ]\n";
echo "]);\n";
echo "```\n\n";

echo "Benefits:\n";
echo "  • More token-efficient\n";
echo "  • Faster processing\n";
echo "  • Lower costs\n";
echo "  • Better for text-only content\n\n";

echo "When to use PDF vision:\n";
echo "  • Layout matters (forms, tables, diagrams)\n";
echo "  • Mixed text and images\n";
echo "  • Visual elements are important\n";
echo "  • Scanned documents\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "✓ PDF support examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Use 'type' => 'document' with 'media_type' => 'application/pdf'\n";
echo "• Max size: 32MB per PDF\n";
echo "• PDFs converted to images internally\n";
echo "• Each page counts as image tokens\n";
echo "• For text-heavy PDFs, consider text extraction\n";
echo "• Best for: Forms, invoices, mixed content, scanned documents\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • examples/vision_comprehensive.php - General vision capabilities\n";
echo "  • examples/files_api.php - Files API for document management\n";

