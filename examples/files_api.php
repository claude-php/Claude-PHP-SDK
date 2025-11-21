#!/usr/bin/env php
<?php
/**
 * Files API - PHP examples from:
 * https://docs.claude.com/en/docs/build-with-claude/files
 * 
 * Upload, manage, and use files with Claude (beta feature).
 * Requires 'files-2024-12-10' beta header.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Files API - Document Management ===\n\n";
echo "⚠️  Files API is currently in BETA\n";
echo "Requires beta header: 'files-2024-12-10'\n\n";

// Example 1: Uploading a file
echo "Example 1: Uploading a File\n";
echo "---------------------------\n";
echo "Upload documents to use across multiple requests\n\n";

echo "```php\n";
echo "\$file = \$client->beta()->files()->upload([\n";
echo "    'file' => [\n";
echo "        'name' => 'document.pdf',\n";
echo "        'content' => file_get_contents('document.pdf'),\n";
echo "        'mime_type' => 'application/pdf'\n";
echo "    ],\n";
echo "    'purpose' => 'user_message'\n";
echo "]);\n\n";
echo "echo \"File ID: {\$file->id}\";\n";
echo "echo \"Size: {\$file->size} bytes\";\n";
echo "```\n\n";

echo "Supported file types:\n";
echo "  • application/pdf\n";
echo "  • text/plain\n";
echo "  • text/csv\n";
echo "  • image/jpeg, image/png, image/gif, image/webp\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: Using uploaded files
echo "Example 2: Using Uploaded Files in Messages\n";
echo "--------------------------------------------\n";
echo "Reference uploaded files by their file_id\n\n";

echo "```php\n";
echo "// After uploading, use the file in a message\n";
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
echo "                        'type' => 'file',\n";
echo "                        'file_id' => \$file->id\n";
echo "                    ]\n";
echo "                ],\n";
echo "                [\n";
echo "                    'type' => 'text',\n";
echo "                    'text' => 'Summarize this document'\n";
echo "                ]\n";
echo "            ]\n";
echo "        ]\n";
echo "    ]\n";
echo "]);\n";
echo "```\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 3: Listing files
echo "Example 3: Listing Files\n";
echo "------------------------\n\n";

echo "```php\n";
echo "\$files = \$client->beta()->files()->list([\n";
echo "    'limit' => 10\n";
echo "]);\n\n";
echo "foreach (\$files->data as \$file) {\n";
echo "    echo \"File: {\$file->filename}\\n\";\n";
echo "    echo \"  ID: {\$file->id}\\n\";\n";
echo "    echo \"  Size: {\$file->size} bytes\\n\";\n";
echo "    echo \"  Created: {\$file->created_at}\\n\";\n";
echo "}\n";
echo "```\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 4: Retrieving file metadata
echo "Example 4: Retrieving File Metadata\n";
echo "------------------------------------\n\n";

echo "```php\n";
echo "\$file = \$client->beta()->files()->retrieve('file-abc123');\n\n";
echo "echo \"Filename: {\$file->filename}\\n\";\n";
echo "echo \"Type: {\$file->type}\\n\";\n";
echo "echo \"Size: {\$file->size} bytes\\n\";\n";
echo "echo \"Status: {\$file->status}\\n\";\n";
echo "```\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 5: Downloading file content
echo "Example 5: Downloading File Content\n";
echo "------------------------------------\n\n";

echo "```php\n";
echo "\$content = \$client->beta()->files()->content('file-abc123');\n";
echo "file_put_contents('downloaded.pdf', \$content);\n";
echo "```\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 6: Deleting files
echo "Example 6: Deleting Files\n";
echo "-------------------------\n\n";

echo "```php\n";
echo "\$result = \$client->beta()->files()->delete('file-abc123');\n";
echo "echo \$result->deleted ? 'File deleted' : 'Delete failed';\n";
echo "```\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 7: Benefits
echo "Example 7: Benefits of Files API\n";
echo "---------------------------------\n\n";

echo "✓ Reusability:\n";
echo "  • Upload once, use in multiple requests\n";
echo "  • Save bandwidth and time\n";
echo "  • Consistent file references\n\n";

echo "✓ Management:\n";
echo "  • List, retrieve, and delete files\n";
echo "  • Track file metadata\n";
echo "  • Organize document library\n\n";

echo "✓ Efficiency:\n";
echo "  • Avoid re-encoding large files\n";
echo "  • Reduce request payload size\n";
echo "  • Better for repeated document analysis\n\n";

echo "✓ Integration:\n";
echo "  • Works with vision capabilities\n";
echo "  • Supports PDF, images, text files\n";
echo "  • Combine with citations for attribution\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "✓ Files API examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Files API is BETA - requires 'files-2024-12-10' header\n";
echo "• Upload files once, reference by file_id in multiple requests\n";
echo "• Supported types: PDF, images, text, CSV\n";
echo "• Operations: upload, list, retrieve, content, delete\n";
echo "• Use 'type' => 'document', 'source' => ['type' => 'file', 'file_id' => ...]\n";
echo "• Files are retained for efficient reuse\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • examples/pdf_support.php - PDF document analysis\n";
echo "  • examples/vision_comprehensive.php - Image handling\n";

