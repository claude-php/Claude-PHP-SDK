#!/usr/bin/env php
<?php
/**
 * Web Fetch Tool Example
 *
 * Demonstrates the web_fetch tool (web_fetch_20250910) that allows Claude to
 * retrieve and read content from URLs. Useful for referencing live documentation,
 * news articles, or any publicly accessible web content.
 *
 * @see https://docs.anthropic.com/en/docs/build-with-claude/tool-use/web-fetch
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/helpers.php';

use ClaudePhp\ClaudePhp;
use ClaudePhp\Types\ModelParam;

loadEnv(__DIR__ . '/../.env');
$client = new ClaudePhp(apiKey: getApiKey());

echo "=== Web Fetch Tool Examples ===\n\n";

// ---------------------------------------------------------------------------
// Example 1: Basic web fetch
// ---------------------------------------------------------------------------
echo "Example 1: Basic Web Fetch\n";
echo "---------------------------\n\n";

try {
    $response = $client->messages()->create([
        'model'      => ModelParam::MODEL_CLAUDE_SONNET_4_5,
        'max_tokens' => 1024,
        'tools'      => [
            [
                'name' => 'web_fetch',
                'type' => 'web_fetch_20250910',
            ],
        ],
        'messages'   => [
            [
                'role'    => 'user',
                'content' => 'Fetch https://www.example.com and tell me the main heading on the page.',
            ],
        ],
    ]);

    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo "Response:\n{$block['text']}\n";
        } elseif ($block['type'] === 'tool_use' && $block['name'] === 'web_fetch') {
            echo "Tool call: web_fetch\n";
            echo "  URL: {$block['input']['url']}\n";
        }
    }

    echo "\nStop reason: {$response->stop_reason}\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// ---------------------------------------------------------------------------
// Example 2: Web fetch with domain restrictions
// ---------------------------------------------------------------------------
echo "Example 2: Web Fetch with Domain Restrictions\n";
echo "-----------------------------------------------\n\n";
echo "Restricting fetches to specific trusted domains.\n\n";

try {
    $response = $client->messages()->create([
        'model'      => ModelParam::MODEL_CLAUDE_SONNET_4_5,
        'max_tokens' => 1024,
        'tools'      => [
            [
                'name'             => 'web_fetch',
                'type'             => 'web_fetch_20250910',
                'allowed_domains'  => ['docs.anthropic.com', 'github.com'],
                'max_uses'         => 3,
            ],
        ],
        'messages'   => [
            [
                'role'    => 'user',
                'content' => 'Check the Anthropic docs at https://docs.anthropic.com/en/docs/welcome '
                    . 'and summarize the key capabilities listed.',
            ],
        ],
    ]);

    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo "Response:\n{$block['text']}\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// ---------------------------------------------------------------------------
// Example 3: Web fetch with citations
// ---------------------------------------------------------------------------
echo "Example 3: Web Fetch with Citations\n";
echo "-------------------------------------\n\n";
echo "Enabling citations so Claude cites the source of fetched content.\n\n";

try {
    $response = $client->messages()->create([
        'model'      => ModelParam::MODEL_CLAUDE_SONNET_4_5,
        'max_tokens' => 1024,
        'tools'      => [
            [
                'name'      => 'web_fetch',
                'type'      => 'web_fetch_20250910',
                'citations' => ['enabled' => true],
            ],
        ],
        'messages'   => [
            [
                'role'    => 'user',
                'content' => 'Fetch https://www.example.com and quote something from the page.',
            ],
        ],
    ]);

    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo "Response:\n{$block['text']}\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// ---------------------------------------------------------------------------
// Example 4: Beta web fetch (v2026-02-09) with allowed_callers
// ---------------------------------------------------------------------------
echo "Example 4: Beta Web Fetch v2026-02-09 with allowed_callers\n";
echo "------------------------------------------------------------\n\n";
echo "The 2026 version supports being called by code execution tools.\n\n";

try {
    $response = $client->beta()->messages()->create([
        'model'      => ModelParam::MODEL_CLAUDE_SONNET_4_5,
        'max_tokens' => 1024,
        'tools'      => [
            [
                'name'            => 'web_fetch',
                'type'            => 'web_fetch_20260209',
                'allowed_callers' => ['direct'],
                'max_uses'        => 5,
            ],
        ],
        'messages'   => [
            [
                'role'    => 'user',
                'content' => 'Fetch https://www.example.com and tell me the page title.',
            ],
        ],
    ]);

    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            echo "Response:\n{$block['text']}\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// ---------------------------------------------------------------------------
// Example 5: Configuration reference
// ---------------------------------------------------------------------------
echo "Example 5: Web Fetch Tool Configuration Reference\n";
echo "----------------------------------------------------\n\n";

echo "Tool versions:\n";
echo "  • web_fetch_20250910 — GA, via messages()->create()\n";
echo "  • web_fetch_20260209 — Beta, via beta()->messages()->create()\n\n";

echo "Key parameters:\n";
echo "  • allowed_domains  — Whitelist of domains Claude may fetch\n";
echo "  • blocked_domains  — Blacklist of domains Claude may never fetch\n";
echo "  • max_uses         — Limit total fetch calls per request\n";
echo "  • max_content_tokens — Limit tokens used by fetched content\n";
echo "  • citations        — Enable citations from fetched content\n";
echo "  • defer_loading    — Only load tool when referenced via tool_search\n\n";

echo "Error codes (WebFetchToolResultErrorCode):\n";
echo "  • invalid_tool_input    — Malformed URL\n";
echo "  • url_too_long          — URL exceeds length limit\n";
echo "  • url_not_allowed       — Domain is blocked or not in allowed_domains\n";
echo "  • url_not_accessible    — Page returned an error (4xx/5xx)\n";
echo "  • unsupported_content_type — Cannot parse the content type\n";
echo "  • too_many_requests     — Rate limited by the target server\n";
echo "  • max_uses_exceeded     — max_uses limit reached\n";
echo "  • unavailable           — Service temporarily unavailable\n";
