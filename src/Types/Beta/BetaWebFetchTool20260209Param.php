<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta web fetch tool parameter (version 2026-02-09).
 *
 * Updated web fetch tool with enhanced capabilities including allowed_callers
 * for use with code execution tools.
 *
 * Use type "web_fetch_20260209" in your beta tool definition.
 *
 * @see https://docs.anthropic.com/en/docs/build-with-claude/tool-use/web-fetch
 */
class BetaWebFetchTool20260209Param
{
    /**
     * @param string        $name               Must be "web_fetch"
     * @param string        $type               Must be "web_fetch_20260209"
     * @param string[]      $allowed_callers    Callers allowed ("direct", "code_execution_20250825")
     * @param string[]|null $allowed_domains    If provided, only these domains will be fetched
     * @param string[]|null $blocked_domains    If provided, these domains will never be fetched
     * @param mixed|null    $cache_control      Cache control breakpoint configuration
     * @param mixed|null    $citations          Citations configuration for fetched documents
     * @param bool          $defer_loading      If true, tool is not included in initial system prompt
     * @param int|null      $max_content_tokens Maximum tokens for web page text content
     * @param int|null      $max_uses           Maximum number of times this tool can be used per request
     * @param bool          $strict             When true, guarantees schema validation
     */
    public function __construct(
        public readonly string $name = 'web_fetch',
        public readonly string $type = 'web_fetch_20260209',
        public readonly array $allowed_callers = [],
        public readonly ?array $allowed_domains = null,
        public readonly ?array $blocked_domains = null,
        public readonly mixed $cache_control = null,
        public readonly mixed $citations = null,
        public readonly bool $defer_loading = false,
        public readonly ?int $max_content_tokens = null,
        public readonly ?int $max_uses = null,
        public readonly bool $strict = false,
    ) {
    }
}
