#!/usr/bin/env php
<?php
/**
 * Bash Tool - PHP examples from:
 * https://docs.claude.com/en/docs/agents-and-tools/tool-use/bash-tool
 * 
 * Enable Claude to execute bash commands on your system.
 * Requires 'bash_tool_20250124' type - client-side implementation.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Bash Tool - Command Execution ===\n\n";
echo "⚠️  SECURITY WARNING: Bash tool allows arbitrary command execution\n";
echo "Only use in controlled environments with proper safety measures!\n\n";

// Example 1: Basic bash tool setup
echo "Example 1: Basic Bash Tool Setup\n";
echo "---------------------------------\n\n";

$tools = [
    [
        'type' => 'bash_tool_20250124',
        'name' => 'bash_tool'
    ]
];

echo "Tool definition:\n";
echo json_encode($tools[0], JSON_PRETTY_PRINT) . "\n\n";

echo "Claude can now request to run bash commands like:\n";
echo "  • ls -la\n";
echo "  • cat file.txt\n";
echo "  • grep 'pattern' *.php\n";
echo "  • find . -name '*.js'\n\n";

echo "Note: You must implement the execution logic on your side\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: Safe bash execution pattern
echo "Example 2: Safe Bash Execution Pattern\n";
echo "---------------------------------------\n\n";

echo "```php\n";
echo "function executeBashCommand(\$command, \$allowedCommands = []) {\n";
echo "    // Security: Whitelist of allowed commands\n";
echo "    \$commandBase = explode(' ', trim(\$command))[0];\n";
echo "    \n";
echo "    if (!empty(\$allowedCommands) && !in_array(\$commandBase, \$allowedCommands)) {\n";
echo "        return [\n";
echo "            'success' => false,\n";
echo "            'error' => \"Command not allowed: {\$commandBase}\"\n";
echo "        ];\n";
echo "    }\n";
echo "    \n";
echo "    // Additional safety checks\n";
echo "    \$dangerousPatterns = ['rm -rf', '> /dev/', 'dd if=', 'mkfs', 'fdisk'];\n";
echo "    foreach (\$dangerousPatterns as \$pattern) {\n";
echo "        if (stripos(\$command, \$pattern) !== false) {\n";
echo "            return [\n";
echo "                'success' => false,\n";
echo "                'error' => 'Dangerous command blocked'\n";
echo "            ];\n";
echo "        }\n";
echo "    }\n";
echo "    \n";
echo "    // Execute with timeout\n";
echo "    \$output = [];\n";
echo "    \$returnCode = 0;\n";
echo "    exec(\$command . ' 2>&1', \$output, \$returnCode);\n";
echo "    \n";
echo "    return [\n";
echo "        'success' => \$returnCode === 0,\n";
echo "        'output' => implode(\"\\n\", \$output),\n";
echo "        'exit_code' => \$returnCode\n";
echo "    ];\n";
echo "}\n";
echo "```\n\n";

echo "Safety measures:\n";
echo "  ✓ Whitelist allowed commands\n";
echo "  ✓ Block dangerous patterns (rm -rf, etc.)\n";
echo "  ✓ Use timeouts to prevent hanging\n";
echo "  ✓ Run in sandboxed environment\n";
echo "  ✓ Validate command structure\n";
echo "  ✓ Log all executions for audit\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 3: Use cases
echo "Example 3: Bash Tool Use Cases\n";
echo "-------------------------------\n\n";

echo "✓ File Operations:\n";
echo "  • List directory contents\n";
echo "  • Search for files\n";
echo "  • Read file contents\n";
echo "  • File manipulation (with safety limits)\n\n";

echo "✓ System Information:\n";
echo "  • Check disk space\n";
echo "  • View process list\n";
echo "  • Monitor system resources\n";
echo "  • Network status\n\n";

echo "✓ Development Tasks:\n";
echo "  • Run tests\n";
echo "  • Build projects\n";
echo "  • Search code\n";
echo "  • Git operations (read-only)\n\n";

echo "✓ Data Processing:\n";
echo "  • Text processing with grep/awk/sed\n";
echo "  • Log analysis\n";
echo "  • Data transformation\n";
echo "  • File format conversion\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 4: Security considerations
echo "Example 4: Security Considerations\n";
echo "-----------------------------------\n\n";

echo "🔒 Essential Security Measures:\n\n";

echo "1. Command Whitelisting:\n";
echo "   • Only allow specific safe commands\n";
echo "   • Block destructive operations\n";
echo "   • Validate command structure\n\n";

echo "2. Sandboxing:\n";
echo "   • Run in isolated environment (Docker, chroot)\n";
echo "   • Limit file system access\n";
echo "   • Network restrictions\n\n";

echo "3. User Context:\n";
echo "   • Run as limited user (not root)\n";
echo "   • Minimal permissions\n";
echo "   • Restrict PATH\n\n";

echo "4. Input Validation:\n";
echo "   • Escape shell arguments\n";
echo "   • Check for command injection\n";
echo "   • Validate parameter format\n\n";

echo "5. Monitoring & Logging:\n";
echo "   • Log all command executions\n";
echo "   • Alert on suspicious patterns\n";
echo "   • Audit trail for compliance\n\n";

echo "6. Rate Limiting:\n";
echo "   • Limit commands per session\n";
echo "   • Timeout for long-running commands\n";
echo "   • Resource usage limits\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "✓ Bash tool examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Bash tool type: 'bash_tool_20250124'\n";
echo "• CLIENT-SIDE tool - you implement execution\n";
echo "• ⚠️  HIGH SECURITY RISK - implement strict safety measures\n";
echo "• Whitelist commands, block dangerous patterns\n";
echo "• Run in sandboxed environment with minimal permissions\n";
echo "• Log all executions, implement timeouts\n";
echo "• Use for: File ops, system info, dev tasks, data processing\n";
echo "• NOT for production unless heavily restricted\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • examples/text_editor_tool.php - File editing tool\n";
echo "  • examples/code_execution_tool.php - Code execution\n";

