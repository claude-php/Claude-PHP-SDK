#!/usr/bin/env php
<?php
/**
 * Fine-grained Tool Streaming - PHP examples from:
 * https://docs.claude.com/en/docs/agents-and-tools/tool-use/fine-grained-tool-streaming
 * 
 * Stream tool use parameters as they're generated for real-time responsiveness.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;
use ClaudePhp\Lib\Streaming\MessageStream;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Fine-grained Tool Streaming - Real-time Tool Parameters ===\n\n";

// Example 1: Streaming tool parameters
echo "Example 1: Streaming Tool Parameters\n";
echo "-------------------------------------\n";
echo "Receive tool parameters as they're generated (partial JSON)\n\n";

try {
    $rawStream = $client->messages()->stream([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'tools' => [
            [
                'name' => 'send_email',
                'description' => 'Send an email',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'to' => ['type' => 'string'],
                        'subject' => ['type' => 'string'],
                        'body' => ['type' => 'string']
                    ],
                    'required' => ['to', 'subject', 'body']
                ]
            ]
        ],
        'messages' => [
            ['role' => 'user', 'content' => 'Send an email to john@example.com about meeting tomorrow']
        ]
    ]);

    $stream = new MessageStream($rawStream);
    
    echo "Streaming tool parameter construction:\n";
    $accumulatedJson = '';
    
    foreach ($stream as $event) {
        $type = $event['type'] ?? null;
        
        if ($type === 'content_block_start') {
            $blockType = $event['content_block']['type'] ?? 'unknown';
            if ($blockType === 'tool_use') {
                $toolName = $event['content_block']['name'] ?? 'unknown';
                echo "\n→ Tool: {$toolName}\n";
                echo "  Building parameters...\n";
            }
        } elseif ($type === 'content_block_delta') {
            if (isset($event['delta']['partial_json'])) {
                $partial = $event['delta']['partial_json'];
                $accumulatedJson .= $partial;
                echo "  + \"{$partial}\"\n";
                flush();
            }
        } elseif ($type === 'content_block_stop') {
            if (!empty($accumulatedJson)) {
                echo "\n  Complete JSON: {$accumulatedJson}\n";
                $accumulatedJson = '';
            }
        }
    }
    
    echo "\nNote: Parameters stream as partial JSON chunks\n";
    echo "SDKs handle accumulation and parsing automatically\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: Input JSON delta events
echo "Example 2: Input JSON Delta Events\n";
echo "-----------------------------------\n\n";

echo "Event structure:\n";
echo "```json\n";
echo "{\n";
echo "  \"type\": \"content_block_delta\",\n";
echo "  \"index\": 1,\n";
echo "  \"delta\": {\n";
echo "    \"type\": \"input_json_delta\",\n";
echo "    \"partial_json\": \"{\\\"location\\\": \\\"San Fra\"\n";
echo "  }\n";
echo "}\n";
echo "```\n\n";

echo "Streaming behavior:\n";
echo "  • Models emit one complete key-value pair at a time\n";
echo "  • Multiple chunks per key-value (fine-grained)\n";
echo "  • May have delays between keys while thinking\n";
echo "  • Parse complete JSON on content_block_stop\n\n";

echo "SDK handling:\n";
echo "  • MessageStream accumulates chunks\n";
echo "  • Automatic JSON parsing\n";
echo "  • Access via getFinalMessage()\n";
echo "  • Error handling for invalid JSON\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 3: Early execution opportunity
echo "Example 3: Early Execution Opportunity\n";
echo "---------------------------------------\n";
echo "Start executing tools before full response is complete\n\n";

echo "Pattern:\n";
echo "```php\n";
echo "foreach (\$stream as \$event) {\n";
echo "    if (\$event['type'] === 'content_block_start') {\n";
echo "        if (\$event['content_block']['type'] === 'tool_use') {\n";
echo "            // Tool use starting - prepare resources\n";
echo "            \$toolName = \$event['content_block']['name'];\n";
echo "            prepareToolExecution(\$toolName);\n";
echo "        }\n";
echo "    }\n";
echo "    \n";
echo "    if (\$event['type'] === 'content_block_delta') {\n";
echo "        if (isset(\$event['delta']['partial_json'])) {\n";
echo "            // Accumulate parameters as they stream\n";
echo "            \$jsonChunks[] = \$event['delta']['partial_json'];\n";
echo "        }\n";
echo "    }\n";
echo "    \n";
echo "    if (\$event['type'] === 'content_block_stop') {\n";
echo "        // Parse complete JSON and execute immediately\n";
echo "        \$fullJson = implode('', \$jsonChunks);\n";
echo "        \$params = json_decode(\$fullJson, true);\n";
echo "        executeTool(\$toolName, \$params);\n";
echo "    }\n";
echo "}\n";
echo "```\n\n";

echo "Benefits:\n";
echo "  • Reduced latency (execute before full response)\n";
echo "  • Better user experience\n";
echo "  • Parallel processing opportunity\n";
echo "  • Faster overall workflow\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 4: Handling partial JSON
echo "Example 4: Handling Partial JSON\n";
echo "---------------------------------\n\n";

echo "Challenges:\n";
echo "  • Partial JSON is not valid JSON\n";
echo "  • Cannot parse until complete\n";
echo "  • Need to accumulate chunks\n\n";

echo "Solutions:\n";
echo "  ✓ Use SDK helpers (recommended)\n";
echo "  ✓ Accumulate strings manually\n";
echo "  ✓ Parse on content_block_stop\n";
echo "  ✓ Use partial JSON parser libraries\n\n";

echo "SDK approach:\n";
echo "```php\n";
echo "use ClaudePhp\\Lib\\Streaming\\MessageStream;\n";
echo "\n";
echo "\$stream = new MessageStream(\$rawStream);\n";
echo "foreach (\$stream as \$event) {\n";
echo "    // Handle events\n";
echo "}\n";
echo "\n";
echo "// Get complete message with parsed tool use\n";
echo "\$finalMessage = \$stream->getFinalMessage();\n";
echo "foreach (\$finalMessage->content as \$block) {\n";
echo "    if (\$block['type'] === 'tool_use') {\n";
echo "        // \$block['input'] is already parsed!\n";
echo "        executeTool(\$block['name'], \$block['input']);\n";
echo "    }\n";
echo "}\n";
echo "```\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 5: Best practices
echo "Example 5: Best Practices\n";
echo "-------------------------\n\n";

echo "✓ Use SDK Helpers:\n";
echo "  • MessageStream handles accumulation\n";
echo "  • Automatic JSON parsing\n";
echo "  • Error handling built-in\n";
echo "  • Less code, fewer bugs\n\n";

echo "✓ Progress Indicators:\n";
echo "  • Show partial parameters as they stream\n";
echo "  • Update UI in real-time\n";
echo "  • Better user experience\n";
echo "  • Perceived performance boost\n\n";

echo "✓ Error Handling:\n";
echo "  • Handle incomplete streams\n";
echo "  • Validate JSON after parsing\n";
echo "  • Provide fallbacks\n";
echo "  • Log streaming errors\n\n";

echo "✓ Performance:\n";
echo "  • Start tool prep early\n";
echo "  • Execute as soon as parameters complete\n";
echo "  • Parallel processing where possible\n";
echo "  • Monitor streaming latency\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "✓ Fine-grained tool streaming examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Tool parameters stream as partial JSON (input_json_delta)\n";
echo "• One key-value pair at a time (granular streaming)\n";
echo "• Use MessageStream for automatic accumulation and parsing\n";
echo "• Opportunity for early execution (reduced latency)\n";
echo "• Models may pause between keys while thinking\n";
echo "• Parse complete JSON on content_block_stop\n";
echo "• Perfect for real-time UI updates and responsiveness\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • examples/streaming_comprehensive.php - All streaming patterns\n";
echo "  • examples/tool_use_implementation.php - Tool implementation\n";

