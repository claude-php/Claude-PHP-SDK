#!/usr/bin/env php
<?php
/**
 * Search Results - PHP examples from:
 * https://docs.claude.com/en/docs/build-with-claude/search-results
 * 
 * Provide web search results to Claude for grounded responses.
 * Alternative to using the web_search tool.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Search Results - Grounded Web Responses ===\n\n";

// Example 1: Providing search results to Claude
echo "Example 1: Providing Search Results\n";
echo "------------------------------------\n";
echo "Give Claude pre-fetched search results for analysis\n\n";

try {
    $searchResults = [
        [
            'title' => 'Introduction to Machine Learning',
            'url' => 'https://example.com/ml-intro',
            'snippet' => 'Machine learning is a subset of artificial intelligence that enables systems to learn and improve from experience without being explicitly programmed.'
        ],
        [
            'title' => 'Types of Machine Learning',
            'url' => 'https://example.com/ml-types',
            'snippet' => 'The three main types of machine learning are supervised learning, unsupervised learning, and reinforcement learning.'
        ],
        [
            'title' => 'ML Applications',
            'url' => 'https://example.com/ml-apps',
            'snippet' => 'Machine learning is used in recommendation systems, image recognition, natural language processing, and autonomous vehicles.'
        ]
    ];
    
    // Format search results for Claude
    $formattedResults = "Here are web search results:\n\n";
    foreach ($searchResults as $i => $result) {
        $formattedResults .= ($i + 1) . ". {$result['title']}\n";
        $formattedResults .= "   URL: {$result['url']}\n";
        $formattedResults .= "   {$result['snippet']}\n\n";
    }
    
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
        'messages' => [
            [
                'role' => 'user',
                'content' => $formattedResults . "Based on these search results, what is machine learning?"
            ]
        ]
    ]);

    echo "Provided 3 search results about machine learning\n";
    echo "Question: What is machine learning?\n\n";
    echo "Claude's response: ";
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            $text = $block['text'];
            if (strlen($text) > 300) {
                $text = substr($text, 0, 300) . '...';
            }
            echo $text . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 2: Search results vs web_search tool
echo "Example 2: Search Results vs Web Search Tool\n";
echo "---------------------------------------------\n\n";

echo "Manual Search Results:\n";
echo "  ✓ You control which results to include\n";
echo "  ✓ Can use any search API (Google, Bing, etc.)\n";
echo "  ✓ Pre-filter and curate results\n";
echo "  ✓ Combine with your own data\n";
echo "  ✗ Requires external search API\n";
echo "  ✗ More code to implement\n\n";

echo "Web Search Tool (built-in):\n";
echo "  ✓ Fully automated - Claude searches directly\n";
echo "  ✓ No external API needed\n";
echo "  ✓ Real-time results\n";
echo "  ✗ Less control over results\n";
echo "  ✗ Additional usage charges\n\n";

echo "Choose based on:\n";
echo "  • Control needs (manual = more control)\n";
echo "  • Existing infrastructure (have search API?)\n";
echo "  • Cost considerations\n";
echo "  • Real-time requirements\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

// Example 3: Best practices for formatting results
echo "Example 3: Best Practices for Formatting\n";
echo "-----------------------------------------\n\n";

echo "✓ Clear Structure:\n";
echo "  • Number each result\n";
echo "  • Include title, URL, and snippet\n";
echo "  • Use consistent formatting\n";
echo "  • Separate results clearly\n\n";

echo "✓ Quality Control:\n";
echo "  • Filter low-quality results\n";
echo "  • Remove duplicates\n";
echo "  • Rank by relevance\n";
echo "  • Limit to top 5-10 results\n\n";

echo "✓ Context Optimization:\n";
echo "  • Extract key snippets only\n";
echo "  • Keep snippets concise (2-3 sentences)\n";
echo "  • Remove irrelevant metadata\n";
echo "  • Watch token usage\n\n";

echo "✓ Attribution:\n";
echo "  • Always include source URLs\n";
echo "  • Combine with Citations feature\n";
echo "  • Enable fact-checking\n";
echo "  • Build user trust\n";

echo "\n" . str_repeat("=", 80) . "\n\n";

echo "✓ Search results examples completed!\n\n";

echo "Key Takeaways:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "• Provide pre-fetched search results in user message\n";
echo "• Format clearly: number, title, URL, snippet\n";
echo "• Alternative to built-in web_search tool\n";
echo "• More control but requires external search API\n";
echo "• Combine with Citations for attribution\n";
echo "• Ideal for: Custom search, filtered results, existing search infrastructure\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Related examples:\n";
echo "  • examples/web_search.php - Built-in web search tool\n";
echo "  • examples/citations.php - Source attribution\n";

