#!/usr/bin/env php
<?php
/**
 * Computer Use Tool - PHP examples from:
 * https://docs.claude.com/en/docs/agents-and-tools/tool-use/computer-use-tool
 * 
 * Enable Claude to interact with desktop environments (mouse, keyboard, screenshots).
 * Requires 'computer_use_20251022' type - client-side implementation with desktop automation.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Computer Use Tool - Desktop Automation ===\n\n";
echo "⚠️  EXPERIMENTAL FEATURE with significant security implications\n";
echo "Only use in isolated, controlled environments!\n\n";

// Example 1: Computer use tool setup
echo "Example 1: Computer Use Tool Setup\n";
echo "-----------------------------------\n\n";

$tools = [
    [
        'type' => 'computer_use_20251022',
        'name' => 'computer',
        'display_width_px' => 1920,
        'display_height_px' => 1080
    ]
];

echo "Tool definition:\n";
echo json_encode($tools[0], JSON_PRETTY_PRINT) . "\n\n";

echo "Claude can perform actions:\n";
echo "  • mouse_move - Move mouse to coordinates\n";
echo "  • left_click - Click at current position\n";
echo "  • right_click - Right-click context menu\n";
echo "  • double_click - Double-click\n";
echo "  • middle_click - Middle mouse button\n";
echo "  • type - Type text\n";
echo "  • key - Press keyboard keys\n";
echo "  • screenshot - Capture screen\n";
echo "  • cursor_position - Get cursor location\n\n";

echo "Requirements:\n";
echo "  • Desktop automation library (e.g., Python's pyautogui)\n";
echo "  • Display server (X11, Wayland, macOS, Windows)\n";
echo "  • Screenshot capability\n";
echo "  • Mouse/keyboard control\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: Action types
echo "Example 2: Computer Use Actions\n";
echo "--------------------------------\n\n";

echo "Mouse Actions:\n";
echo "```json\n";
echo "{\n";
echo "  \"action\": \"mouse_move\",\n";
echo "  \"coordinate\": [800, 600]\n";
echo "}\n\n";
echo "{\n";
echo "  \"action\": \"left_click\"\n";
echo "}\n";
echo "```\n\n";

echo "Keyboard Actions:\n";
echo "```json\n";
echo "{\n";
echo "  \"action\": \"type\",\n";
echo "  \"text\": \"Hello World\"\n";
echo "}\n\n";
echo "{\n";
echo "  \"action\": \"key\",\n";
echo "  \"text\": \"Return\"  // Or \"Escape\", \"Tab\", etc.\n";
echo "}\n";
echo "```\n\n";

echo "Screenshot Action:\n";
echo "```json\n";
echo "{\n";
echo "  \"action\": \"screenshot\"\n";
echo "}\n";
echo "```\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 3: Implementation pattern
echo "Example 3: Implementation Pattern\n";
echo "----------------------------------\n\n";

echo "```php\n";
echo "function executeComputerAction(\$action, \$params) {\n";
echo "    switch (\$action) {\n";
echo "        case 'screenshot':\n";
echo "            // Take screenshot, return base64\n";
echo "            \$screenshot = takeScreenshot();\n";
echo "            return base64_encode(\$screenshot);\n";
echo "        \n";
echo "        case 'mouse_move':\n";
echo "            [\$x, \$y] = \$params['coordinate'];\n";
echo "            moveMouse(\$x, \$y);\n";
echo "            return 'Mouse moved to ' . \$x . ',' . \$y;\n";
echo "        \n";
echo "        case 'left_click':\n";
echo "            click('left');\n";
echo "            return 'Clicked';\n";
echo "        \n";
echo "        case 'type':\n";
echo "            typeText(\$params['text']);\n";
echo "            return 'Typed: ' . \$params['text'];\n";
echo "        \n";
echo "        case 'key':\n";
echo "            pressKey(\$params['text']);\n";
echo "            return 'Pressed: ' . \$params['text'];\n";
echo "        \n";
echo "        default:\n";
echo "            return 'Unknown action';\n";
echo "    }\n";
echo "}\n";
echo "```\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 4: Use cases
echo "Example 4: Computer Use Cases\n";
echo "------------------------------\n\n";

echo "✓ Testing & QA:\n";
echo "  • Automated UI testing\n";
echo "  • Visual regression testing\n";
echo "  • Interaction testing\n";
echo "  • Screenshot comparison\n\n";

echo "✓ Task Automation:\n";
echo "  • Desktop workflow automation\n";
echo "  • Data entry tasks\n";
echo "  • Application interaction\n";
echo "  • Multi-step processes\n\n";

echo "✓ Research & Analysis:\n";
echo "  • Web scraping with browser\n";
echo "  • Application analysis\n";
echo "  • Interface exploration\n";
echo "  • Usability testing\n\n";

echo "✓ Accessibility:\n";
echo "  • Assist users with disabilities\n";
echo "  • Screen reader integration\n";
echo "  • Keyboard navigation\n";
echo "  • Voice control\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 5: Security considerations
echo "Example 5: Security Considerations\n";
echo "-----------------------------------\n\n";

echo "🔒 Critical Security Measures:\n\n";

echo "1. Isolation (MANDATORY):\n";
echo "   • Run in VM or container\n";
echo "   • No access to host system\n";
echo "   • Separate user account\n";
echo "   • Limited permissions\n\n";

echo "2. Network Restrictions:\n";
echo "   • Block internet access\n";
echo "   • No internal network\n";
echo "   • Whitelist specific sites if needed\n\n";

echo "3. File System:\n";
echo "   • Read-only where possible\n";
echo "   • Limited write access\n";
echo "   • No system directories\n";
echo "   • Temporary storage only\n\n";

echo "4. Monitoring:\n";
echo "   • Log all actions\n";
echo "   • Screenshot audit trail\n";
echo "   • Action replay capability\n";
echo "   • Anomaly detection\n\n";

echo "5. Rate Limiting:\n";
echo "   • Actions per minute\n";
echo "   • Screenshot frequency\n";
echo "   • Resource usage limits\n\n";

echo "⚠️  NOT RECOMMENDED for production environments\n";
echo "Use only for research, testing, and controlled automation\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "✓ Computer use tool examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Tool type: 'computer_use_20251022'\n";
echo "• CLIENT-SIDE implementation with desktop automation library\n";
echo "• ⚠️  EXPERIMENTAL - significant security implications\n";
echo "• Actions: mouse, keyboard, screenshot, cursor position\n";
echo "• MUST run in isolated VM/container\n";
echo "• Requires display server and automation library\n";
echo "• Use for: Testing, automation, accessibility (NOT production)\n";
echo "• Implement comprehensive logging and monitoring\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • examples/bash_tool.php - Command execution\n";
echo "  • examples/text_editor_tool.php - File editing\n";

