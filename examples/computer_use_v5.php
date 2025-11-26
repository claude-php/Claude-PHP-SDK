#!/usr/bin/env php
<?php
/**
 * Computer Use V5 - PHP examples for the latest computer use tool
 * 
 * Computer Use V5 (2025-11-24) introduces enhanced features including:
 * - Zoom capability for detailed screen inspection
 * - Allowed callers for security control
 * - Deferred loading for performance optimization
 * - Strict mode for validation
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Computer Use V5 - Enhanced Desktop Automation ===\n\n";
echo "The latest computer use tool (2025-11-24) with zoom, security controls,\n";
echo "and performance optimizations.\n\n";
echo "⚠️  EXPERIMENTAL FEATURE - Use only in isolated environments!\n\n";

// Example 1: V5 tool configuration
echo "Example 1: Computer Use V5 Configuration\n";
echo "------------------------------------------\n\n";

$computerToolV5 = [
    'type' => 'computer_20251124',
    'name' => 'computer',
    'display_width_px' => 1920,
    'display_height_px' => 1080,
    'enable_zoom' => true,
    'allowed_callers' => ['direct', 'code_execution_20250825'],
    'defer_loading' => false,
    'display_number' => 0,
    'strict' => false,
];

echo "V5 Tool Configuration:\n";
echo json_encode($computerToolV5, JSON_PRETTY_PRINT) . "\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 2: New V5 features
echo "Example 2: New V5 Features\n";
echo "---------------------------\n\n";

echo "1. ZOOM CAPABILITY (enable_zoom):\n";
echo "   When enabled, Claude can request zoomed-in screenshots\n";
echo "   for detailed UI element inspection.\n\n";

echo "   Use cases:\n";
echo "   • Reading small text\n";
echo "   • Identifying UI controls\n";
echo "   • Precise click targeting\n";
echo "   • Accessibility verification\n\n";

echo "2. ALLOWED CALLERS:\n";
echo "   Control who can invoke the tool:\n";
echo "   • 'direct' - Direct calls from Claude\n";
echo "   • 'code_execution_20250825' - From code execution tool\n\n";

echo "3. DEFERRED LOADING:\n";
echo "   When true, tool not included in initial system prompt.\n";
echo "   Loaded only when returned via tool search.\n\n";

echo "4. DISPLAY NUMBER:\n";
echo "   For multi-display setups, specify X11 display number.\n\n";

echo "5. STRICT MODE:\n";
echo "   Enables stricter input validation.\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 3: Comparison with previous versions
echo "Example 3: Version Comparison\n";
echo "------------------------------\n\n";

echo "┌─────────────────────────┬──────────────────┬──────────────────┐\n";
echo "│ Feature                 │ V4 (20251022)    │ V5 (20251124)    │\n";
echo "├─────────────────────────┼──────────────────┼──────────────────┤\n";
echo "│ Zoom capability         │ No               │ Yes              │\n";
echo "│ Allowed callers         │ No               │ Yes              │\n";
echo "│ Deferred loading        │ No               │ Yes              │\n";
echo "│ Display number          │ No               │ Yes              │\n";
echo "│ Strict mode             │ No               │ Yes              │\n";
echo "│ Input examples          │ No               │ Yes              │\n";
echo "└─────────────────────────┴──────────────────┴──────────────────┘\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 4: Zoom action
echo "Example 4: Zoom Screenshot Action\n";
echo "-----------------------------------\n\n";

echo "When enable_zoom is true, Claude can request zoomed screenshots:\n\n";

echo "```json\n";
echo "{\n";
echo "  \"action\": \"screenshot_zoom\",\n";
echo "  \"coordinate\": [800, 600],\n";
echo "  \"zoom_level\": 2.0\n";
echo "}\n";
echo "```\n\n";

echo "Implementation example:\n";
echo "```php\n";
echo "function handleComputerAction(\$action, \$params) {\n";
echo "    switch (\$action) {\n";
echo "        case 'screenshot_zoom':\n";
echo "            [\$x, \$y] = \$params['coordinate'];\n";
echo "            \$zoom = \$params['zoom_level'] ?? 2.0;\n";
echo "            \n";
echo "            // Take full screenshot\n";
echo "            \$screenshot = takeScreenshot();\n";
echo "            \n";
echo "            // Crop and scale region around coordinate\n";
echo "            \$region = cropRegion(\$screenshot, \$x, \$y, 200, 200);\n";
echo "            \$zoomed = scaleImage(\$region, \$zoom);\n";
echo "            \n";
echo "            return base64_encode(\$zoomed);\n";
echo "        // ... other actions\n";
echo "    }\n";
echo "}\n";
echo "```\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 5: Security with allowed callers
echo "Example 5: Security with Allowed Callers\n";
echo "------------------------------------------\n\n";

echo "Control who can invoke computer actions:\n\n";

$secureConfig = [
    'type' => 'computer_20251124',
    'name' => 'computer',
    'display_width_px' => 1920,
    'display_height_px' => 1080,
    'allowed_callers' => ['direct'], // Only direct calls, no code execution
];

echo "Restrictive configuration:\n";
echo json_encode($secureConfig, JSON_PRETTY_PRINT) . "\n\n";

echo "Security considerations:\n";
echo "  • Use 'direct' only: Claude invokes directly\n";
echo "  • Add 'code_execution_20250825': Allow programmatic access\n";
echo "  • Empty array: No callers allowed (tool disabled)\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 6: Performance optimization
echo "Example 6: Performance Optimization\n";
echo "-------------------------------------\n\n";

echo "Use defer_loading for large tool sets:\n\n";

echo "```php\n";
echo "// When you have many tools, defer loading computer use\n";
echo "\$computerTool = [\n";
echo "    'type' => 'computer_20251124',\n";
echo "    'name' => 'computer',\n";
echo "    'display_width_px' => 1920,\n";
echo "    'display_height_px' => 1080,\n";
echo "    'defer_loading' => true, // Not in initial prompt\n";
echo "];\n\n";
echo "// Tool loaded only when needed via tool search\n";
echo "```\n\n";

echo "Benefits:\n";
echo "  • Smaller initial system prompt\n";
echo "  • Faster first response\n";
echo "  • Lower token usage\n";
echo "  • On-demand tool availability\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 7: Multi-display support
echo "Example 7: Multi-Display Support\n";
echo "----------------------------------\n\n";

echo "For systems with multiple displays:\n\n";

$multiDisplay = [
    ['display_number' => 0, 'description' => 'Primary display'],
    ['display_number' => 1, 'description' => 'Secondary display'],
];

echo "Display configurations:\n";
foreach ($multiDisplay as $display) {
    $tool = [
        'type' => 'computer_20251124',
        'name' => "computer_display_{$display['display_number']}",
        'display_width_px' => 1920,
        'display_height_px' => 1080,
        'display_number' => $display['display_number'],
    ];
    echo "\n{$display['description']}:\n";
    echo json_encode($tool, JSON_PRETTY_PRINT) . "\n";
}
echo "\n";

echo str_repeat("=", 80) . "\n\n";

// Example 8: Input examples
echo "Example 8: Input Examples for Training\n";
echo "----------------------------------------\n\n";

echo "Provide input examples to guide Claude's behavior:\n\n";

$toolWithExamples = [
    'type' => 'computer_20251124',
    'name' => 'computer',
    'display_width_px' => 1920,
    'display_height_px' => 1080,
    'input_examples' => [
        [
            'action' => 'mouse_move',
            'coordinate' => [500, 300],
        ],
        [
            'action' => 'left_click',
        ],
        [
            'action' => 'type',
            'text' => 'Hello World',
        ],
    ],
];

echo "Configuration with examples:\n";
echo json_encode($toolWithExamples, JSON_PRETTY_PRINT) . "\n\n";

echo str_repeat("=", 80) . "\n\n";

// Example 9: Best practices
echo "Example 9: V5 Best Practices\n";
echo "-----------------------------\n\n";

echo "✓ Security:\n";
echo "  • Run in isolated VM/container\n";
echo "  • Use allowed_callers to restrict access\n";
echo "  • Enable strict mode for validation\n";
echo "  • Log all actions for audit trail\n\n";

echo "✓ Performance:\n";
echo "  • Use defer_loading for large tool sets\n";
echo "  • Enable zoom only when needed\n";
echo "  • Optimize screenshot resolution\n\n";

echo "✓ Multi-Display:\n";
echo "  • Specify display_number for each screen\n";
echo "  • Use descriptive tool names\n";
echo "  • Coordinate coordinate systems\n\n";

echo "✓ Zoom Usage:\n";
echo "  • Enable for detailed UI inspection\n";
echo "  • Implement efficient cropping/scaling\n";
echo "  • Cache zoomed regions when possible\n\n";

echo str_repeat("=", 80) . "\n\n";

echo "✓ Computer Use V5 examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Tool type: 'computer_20251124' (V5)\n";
echo "• New features: zoom, allowed_callers, defer_loading, display_number\n";
echo "• enable_zoom: Request zoomed screenshots\n";
echo "• allowed_callers: Security control for tool access\n";
echo "• defer_loading: Performance optimization for large tool sets\n";
echo "• display_number: Multi-display support\n";
echo "• strict: Enhanced input validation\n";
echo "• input_examples: Guide Claude's behavior\n";
echo "• ⚠️ EXPERIMENTAL - Use in isolated environments only\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • examples/computer_use_tool.php - Original computer use\n";
echo "  • examples/bash_tool.php - Command execution\n";
echo "  • examples/text_editor_tool.php - File editing\n";
echo "  • examples/tool_search.php - Tool discovery\n";
