#!/usr/bin/env php
<?php
/**
 * Text Editor Tool - PHP examples from:
 * https://docs.claude.com/en/docs/agents-and-tools/tool-use/text-editor-tool
 * 
 * Enable Claude to read and edit files using search-and-replace operations.
 * Requires 'text_editor_20250728' type - client-side implementation.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Text Editor Tool - File Editing ===\n\n";

// Example 1: Text editor tool setup
echo "Example 1: Text Editor Tool Setup\n";
echo "----------------------------------\n\n";

$tools = [
    [
        'type' => 'text_editor_20250728',
        'name' => 'str_replace_based_edit_tool',
        'max_characters' => 100000  // Limit file size
    ]
];

echo "Tool definition:\n";
echo json_encode($tools[0], JSON_PRETTY_PRINT) . "\n\n";

echo "Operations supported:\n";
echo "  • view - Read file contents\n";
echo "  • str_replace - Replace text in file\n";
echo "  • create - Create new file\n";
echo "  • insert - Insert text at line\n";
echo "  • undo_edit - Revert last change\n\n";

echo "Parameters:\n";
echo "  • path - File path (required)\n";
echo "  • old_str - Text to replace (for str_replace)\n";
echo "  • new_str - Replacement text\n";
echo "  • insert_line - Line number (for insert)\n";
echo "  • max_characters - File size limit\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: File operations
echo "Example 2: File Operations\n";
echo "--------------------------\n\n";

echo "View file:\n";
echo "```json\n";
echo "{\n";
echo "  \"command\": \"view\",\n";
echo "  \"path\": \"example.txt\"\n";
echo "}\n";
echo "```\n\n";

echo "Create file:\n";
echo "```json\n";
echo "{\n";
echo "  \"command\": \"create\",\n";
echo "  \"path\": \"new_file.txt\",\n";
echo "  \"file_text\": \"Initial content\"\n";
echo "}\n";
echo "```\n\n";

echo "Search and replace:\n";
echo "```json\n";
echo "{\n";
echo "  \"command\": \"str_replace\",\n";
echo "  \"path\": \"example.txt\",\n";
echo "  \"old_str\": \"Hello\",\n";
echo "  \"new_str\": \"Goodbye\"\n";
echo "}\n";
echo "```\n\n";

echo "Insert at line:\n";
echo "```json\n";
echo "{\n";
echo "  \"command\": \"insert\",\n";
echo "  \"path\": \"example.txt\",\n";
echo "  \"insert_line\": 5,\n";
echo "  \"new_str\": \"New line content\"\n";
echo "}\n";
echo "```\n\n";

echo "Undo last edit:\n";
echo "```json\n";
echo "{\n";
echo "  \"command\": \"undo_edit\",\n";
echo "  \"path\": \"example.txt\"\n";
echo "}\n";
echo "```\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 3: Implementation pattern
echo "Example 3: Implementation Pattern\n";
echo "----------------------------------\n\n";

echo "```php\n";
echo "function executeTextEditor(\$command, \$path, \$params = []) {\n";
echo "    // Security: Validate path\n";
echo "    \$basePath = '/safe/workspace/';\n";
echo "    \$fullPath = realpath(\$basePath . \$path);\n";
echo "    \n";
echo "    if (!str_starts_with(\$fullPath, \$basePath)) {\n";
echo "        return ['error' => 'Access denied: Path outside workspace'];\n";
echo "    }\n";
echo "    \n";
echo "    switch (\$command) {\n";
echo "        case 'view':\n";
echo "            if (!file_exists(\$fullPath)) {\n";
echo "                return ['error' => 'File not found'];\n";
echo "            }\n";
echo "            \$content = file_get_contents(\$fullPath);\n";
echo "            \$lines = explode(\"\\n\", \$content);\n";
echo "            return [\n";
echo "                'content' => \$content,\n";
echo "                'line_count' => count(\$lines)\n";
echo "            ];\n";
echo "        \n";
echo "        case 'str_replace':\n";
echo "            \$content = file_get_contents(\$fullPath);\n";
echo "            \$oldStr = \$params['old_str'];\n";
echo "            \$newStr = \$params['new_str'];\n";
echo "            \n";
echo "            if (substr_count(\$content, \$oldStr) === 0) {\n";
echo "                return ['error' => 'String not found'];\n";
echo "            }\n";
echo "            \n";
echo "            \$newContent = str_replace(\$oldStr, \$newStr, \$content);\n";
echo "            file_put_contents(\$fullPath, \$newContent);\n";
echo "            return ['success' => true];\n";
echo "        \n";
echo "        case 'create':\n";
echo "            if (file_exists(\$fullPath)) {\n";
echo "                return ['error' => 'File already exists'];\n";
echo "            }\n";
echo "            file_put_contents(\$fullPath, \$params['file_text']);\n";
echo "            return ['success' => true];\n";
echo "    }\n";
echo "}\n";
echo "```\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 4: Use cases
echo "Example 4: Computer Use Cases\n";
echo "------------------------------\n\n";

echo "✓ Software Testing:\n";
echo "  • Automated UI testing\n";
echo "  • End-to-end test scenarios\n";
echo "  • Visual verification\n";
echo "  • Cross-platform testing\n\n";

echo "✓ Development Assistance:\n";
echo "  • Code debugging workflows\n";
echo "  • IDE interaction\n";
echo "  • Build process automation\n";
echo "  • Tool integration\n\n";

echo "✓ Research:\n";
echo "  • Application behavior analysis\n";
echo "  • UI/UX research\n";
echo "  • Accessibility testing\n";
echo "  • Performance analysis\n\n";

echo "✓ Data Collection:\n";
echo "  • Screenshot-based data extraction\n";
echo "  • Application output capture\n";
echo "  • Interface documentation\n";
echo "  • Visual cataloging\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 5: Security & safety
echo "Example 5: Security & Safety\n";
echo "-----------------------------\n\n";

echo "🔒 Essential Security Measures:\n\n";

echo "1. Virtual Environment (REQUIRED):\n";
echo "   • Dedicated VM for computer use\n";
echo "   • No production data\n";
echo "   • Network isolated\n";
echo "   • Regular snapshots for reset\n\n";

echo "2. Monitoring:\n";
echo "   • Record all actions\n";
echo "   • Screenshot history\n";
echo "   • Action logging\n";
echo "   • Audit trail\n\n";

echo "3. Restrictions:\n";
echo "   • No system administration\n";
echo "   • Limited file access\n";
echo "   • No credential access\n";
echo "   • Sandboxed applications only\n\n";

echo "4. Human Oversight:\n";
echo "   • Require approval for sensitive actions\n";
echo "   • Real-time monitoring\n";
echo "   • Emergency stop capability\n";
echo "   • Review sessions regularly\n\n";

echo "⚠️  This is an experimental feature\n";
echo "Use only in research/testing environments with proper isolation\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "✓ Computer use tool examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Tool type: 'computer_use_20251022'\n";
echo "• ⚠️  EXPERIMENTAL - desktop automation capability\n";
echo "• Actions: mouse, keyboard, screenshot, cursor_position\n";
echo "• MUST run in isolated VM with no production access\n";
echo "• Requires desktop automation library (pyautogui, etc.)\n";
echo "• Use for: Testing, research, automation (NOT production)\n";
echo "• Comprehensive logging and human oversight required\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • examples/text_editor_tool.php - File editing\n";
echo "  • examples/bash_tool.php - Command execution\n";

