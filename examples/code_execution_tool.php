#!/usr/bin/env php
<?php
/**
 * Code Execution Tool - PHP examples from:
 * https://docs.claude.com/en/docs/agents-and-tools/tool-use/code-execution-tool
 * 
 * Enable Claude to write and execute Python code in a sandboxed environment.
 * Requires 'code_execution_20250514' type - client-side implementation.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Code Execution Tool - Sandboxed Python ===\n\n";

// Example 1: Basic code execution setup
echo "Example 1: Code Execution Tool Setup\n";
echo "------------------------------------\n\n";

$tools = [
    [
        'type' => 'code_execution_20250514',
        'name' => 'code_execution'
    ]
];

echo "Tool definition:\n";
echo json_encode($tools[0], JSON_PRETTY_PRINT) . "\n\n";

echo "Claude can now:\n";
echo "  • Write Python code\n";
echo "  • Execute in sandboxed environment\n";
echo "  • Handle data analysis tasks\n";
echo "  • Perform calculations\n";
echo "  • Generate visualizations\n\n";

echo "Note: Requires sandboxed Python environment on your side\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: Safe code execution implementation
echo "Example 2: Safe Code Execution Pattern\n";
echo "---------------------------------------\n\n";

echo "```php\n";
echo "function executePythonCode(\$code) {\n";
echo "    // Create temporary file\n";
echo "    \$tempFile = tempnam(sys_get_temp_dir(), 'claude_code_');\n";
echo "    file_put_contents(\$tempFile, \$code);\n";
echo "    \n";
echo "    try {\n";
echo "        // Execute with timeout and restricted environment\n";
echo "        \$command = sprintf(\n";
echo "            'timeout 30 python3 -u %s 2>&1',\n";
echo "            escapeshellarg(\$tempFile)\n";
echo "        );\n";
echo "        \n";
echo "        \$output = [];\n";
echo "        \$returnCode = 0;\n";
echo "        exec(\$command, \$output, \$returnCode);\n";
echo "        \n";
echo "        return [\n";
echo "            'success' => \$returnCode === 0,\n";
echo "            'output' => implode(\"\\n\", \$output),\n";
echo "            'exit_code' => \$returnCode\n";
echo "        ];\n";
echo "    } finally {\n";
echo "        unlink(\$tempFile);\n";
echo "    }\n";
echo "}\n";
echo "```\n\n";

echo "Safety measures:\n";
echo "  ✓ Timeout (30 seconds)\n";
echo "  ✓ Temporary file cleanup\n";
echo "  ✓ Error capture (2>&1)\n";
echo "  ✓ Sandboxed execution\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 3: Use cases
echo "Example 3: Code Execution Use Cases\n";
echo "------------------------------------\n\n";

echo "✓ Data Analysis:\n";
echo "  • Process CSV/JSON data\n";
echo "  • Statistical calculations\n";
echo "  • Data transformation\n";
echo "  • Generate summaries\n\n";

echo "✓ Mathematical Computing:\n";
echo "  • Complex calculations\n";
echo "  • Numerical analysis\n";
echo "  • Algorithm implementation\n";
echo "  • Scientific computing\n\n";

echo "✓ Visualization:\n";
echo "  • Generate charts with matplotlib\n";
echo "  • Create plots and graphs\n";
echo "  • Data visualization\n\n";

echo "✓ Testing & Validation:\n";
echo "  • Test code snippets\n";
echo "  • Validate algorithms\n";
echo "  • Run unit tests\n";
echo "  • Verify outputs\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 4: Sandbox recommendations
echo "Example 4: Sandbox Recommendations\n";
echo "-----------------------------------\n\n";

echo "🐳 Docker Sandbox (Recommended):\n";
echo "```bash\n";
echo "docker run --rm -i \\\n";
echo "  --network none \\\n";
echo "  --memory=\"256m\" \\\n";
echo "  --cpus=\"0.5\" \\\n";
echo "  --read-only \\\n";
echo "  python:3.11-slim \\\n";
echo "  python3 -c \"\$CODE\"\n";
echo "```\n\n";

echo "Benefits:\n";
echo "  • Complete isolation\n";
echo "  • No network access\n";
echo "  • Resource limits\n";
echo "  • Read-only filesystem\n";
echo "  • Easy cleanup\n\n";

echo "Alternative: RestrictedPython library\n";
echo "  • In-process sandboxing\n";
echo "  • Restricts dangerous operations\n";
echo "  • Faster than Docker\n";
echo "  • Python-specific\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 5: Security best practices
echo "Example 5: Security Best Practices\n";
echo "-----------------------------------\n\n";

echo "🔒 Critical Security Measures:\n\n";

echo "1. Sandboxing (REQUIRED):\n";
echo "   • Docker containers (recommended)\n";
echo "   • Virtual machines\n";
echo "   • RestrictedPython\n";
echo "   • No direct system access\n\n";

echo "2. Network Isolation:\n";
echo "   • Disable network in sandbox\n";
echo "   • Block internet access\n";
echo "   • No external connections\n\n";

echo "3. Resource Limits:\n";
echo "   • CPU limits\n";
echo "   • Memory limits\n";
echo "   • Execution timeouts\n";
echo "   • Disk usage limits\n\n";

echo "4. Code Review:\n";
echo "   • Scan for dangerous patterns\n";
echo "   • Block file system access\n";
echo "   • Prevent subprocess spawning\n";
echo "   • Check imports\n\n";

echo "5. Monitoring:\n";
echo "   • Log all executions\n";
echo "   • Track resource usage\n";
echo "   • Alert on anomalies\n";
echo "   • Audit trail\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "✓ Bash tool examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Tool type: 'bash_tool_20250124'\n";
echo "• CLIENT-SIDE implementation required\n";
echo "• ⚠️  CRITICAL: Must run in sandboxed environment\n";
echo "• Use Docker for isolation (recommended)\n";
echo "• Implement command whitelisting\n";
echo "• Block dangerous operations\n";
echo "• Apply resource limits and timeouts\n";
echo "• Only use in controlled, trusted environments\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • examples/code_execution_tool.php - Python code execution\n";
echo "  • examples/computer_use_tool.php - Desktop automation\n";

