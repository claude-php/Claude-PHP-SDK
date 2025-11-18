#!/usr/bin/env php
<?php
/**
 * Web Fetch Tool - PHP examples from:
 * https://docs.claude.com/en/docs/agents-and-tools/tool-use/web-fetch-tool
 * 
 * Enable Claude to fetch and analyze web page content.
 * Server-side tool - executes automatically on Anthropic's servers.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Web Fetch Tool - Retrieve Web Content ===\n\n";

// Example 1: Basic web fetch
echo "Example 1: Basic Web Fetch\n";
echo "--------------------------\n";
echo "Fetch and analyze web page content\n\n";

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 2048,
        'tools' => [
            [
                'type' => 'web_fetch_20250305',
                'name' => 'web_fetch',
                'max_uses' => 5
            ]
        ],
        'messages' => [
            ['role' => 'user', 'content' => 'Fetch and summarize https://www.anthropic.com']
        ]
    ]);

    echo "Request: Fetch and summarize https://www.anthropic.com\n\n";
    echo "Response: ";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            $text = $block['text'];
            if (strlen($text) > 300) {
                $text = substr($text, 0, 300) . '...';
            }
            echo $text . "\n";
        }
    }
    
    if (isset($response->usage->server_tool_use)) {
        echo "\nServer tool usage:\n";
        echo "  Web fetches: " . ($response->usage->server_tool_use['web_fetch_requests'] ?? 0) . "\n";
    }
    
    echo "\nNote: Web fetch executes on Anthropic's servers automatically\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: Multiple URL fetching
echo "Example 2: Multiple URLs\n";
echo "------------------------\n";
echo "Fetch and compare multiple web pages\n\n";

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 2048,
        'tools' => [
            [
                'type' => 'web_fetch_20250305',
                'name' => 'web_fetch',
                'max_uses' => 5
            ]
        ],
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Compare the content of https://www.anthropic.com and https://openai.com'
            ]
        ]
    ]);

    echo "Request: Compare two websites\n\n";
    echo "Claude will fetch both URLs and compare them\n";
    echo "max_uses: 5 allows multiple fetches in one request\n\n";
    
    if (isset($response->usage->server_tool_use)) {
        $fetches = $response->usage->server_tool_use['web_fetch_requests'] ?? 0;
        echo "Web fetches performed: {$fetches}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 3: Web fetch response structure
echo "Example 3: Web Fetch Response Structure\n";
echo "----------------------------------------\n\n";

echo "Content blocks in response:\n\n";
echo "1. text block - Claude's commentary\n";
echo "2. server_tool_use block - Fetch request\n";
echo "3. web_fetch_tool_result block - Fetched content\n";
echo "4. text block - Claude's analysis\n\n";

echo "Tool result structure:\n";
echo "{\n";
echo "  \"type\": \"web_fetch_tool_result\",\n";
echo "  \"tool_use_id\": \"srvtoolu_...\",\n";
echo "  \"content\": [\n";
echo "    {\n";
echo "      \"type\": \"web_fetch_result\",\n";
echo "      \"url\": \"https://example.com\",\n";
echo "      \"encrypted_content\": \"encrypted_data...\",\n";
echo "      \"page_age\": null,\n";
echo "      \"status\": \"success\"\n";
echo "    }\n";
echo "  ]\n";
echo "}\n\n";

echo "Note: Content is encrypted for privacy\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 4: Use cases
echo "Example 4: Web Fetch Use Cases\n";
echo "-------------------------------\n\n";

echo "✓ Content Analysis:\n";
echo "  • Summarize web pages\n";
echo "  • Extract key information\n";
echo "  • Compare websites\n";
echo "  • Monitor changes\n\n";

echo "✓ Research:\n";
echo "  • Gather information from URLs\n";
echo "  • Fact checking with sources\n";
echo "  • Competitive analysis\n";
echo "  • Market research\n\n";

echo "✓ Data Collection:\n";
echo "  • Extract structured data\n";
echo "  • Price monitoring\n";
echo "  • News aggregation\n";
echo "  • Content curation\n\n";

echo "✓ Verification:\n";
echo "  • Link validation\n";
echo "  • Content verification\n";
echo "  • Source attribution\n";
echo "  • Fact checking\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 5: Web fetch vs web search
echo "Example 5: Web Fetch vs Web Search\n";
echo "-----------------------------------\n\n";

echo "Web Fetch Tool:\n";
echo "  ✓ Fetch specific URLs\n";
echo "  ✓ Get full page content\n";
echo "  ✓ Analyze known sources\n";
echo "  ✓ Direct content access\n";
echo "  ✗ Can't discover URLs\n\n";

echo "Web Search Tool:\n";
echo "  ✓ Discover relevant URLs\n";
echo "  ✓ Find current information\n";
echo "  ✓ Explore topics\n";
echo "  ✗ Summary snippets only\n";
echo "  ✗ May not fetch full content\n\n";

echo "Combined Usage:\n";
echo "  1. Use web_search to find relevant URLs\n";
echo "  2. Use web_fetch to get full content from URLs\n";
echo "  3. Claude analyzes complete information\n\n";

echo "Best for web_fetch:\n";
echo "  • Known URLs to analyze\n";
echo "  • Specific page content needed\n";
echo "  • Documentation or article review\n";
echo "  • Detailed content extraction\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "✓ Web fetch tool examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Tool type: 'web_fetch_20250305'\n";
echo "• SERVER-SIDE tool - executes automatically\n";
echo "• Fetches full web page content from specific URLs\n";
echo "• Configure with max_uses parameter\n";
echo "• Content is encrypted in response\n";
echo "• Additional charges per fetch\n";
echo "• Use for: Content analysis, research, verification\n";
echo "• Combine with web_search for discovery + detailed analysis\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • examples/web_search.php - Web search tool\n";
echo "  • examples/search_results.php - Manual search results\n";

