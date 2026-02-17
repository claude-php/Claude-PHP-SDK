<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta web search tool parameter (version 2026-02-09).
 *
 * Enhanced web search tool with allowed_callers support for multi-agent
 * and code execution workflows.
 *
 * Use type "web_search_20260209" in your beta tool definition.
 *
 * @see https://docs.anthropic.com/en/docs/build-with-claude/tool-use/web-search
 */
class BetaWebSearchTool20260209Param
{
    /**
     * @param string        $name               Must be "web_search"
     * @param string        $type               Must be "web_search_20260209"
     * @param string[]      $allowed_callers    Callers allowed ("direct", "code_execution_20250825")
     * @param string[]|null $allowed_domains    If provided, only these domains will appear in results
     * @param string[]|null $blocked_domains    If provided, these domains will never appear in results
     * @param mixed|null    $cache_control      Cache control breakpoint configuration
     * @param bool          $defer_loading      If true, tool is not included in initial system prompt
     * @param int|null      $max_uses           Maximum number of times this tool can be used per request
     * @param bool          $strict             When true, guarantees schema validation
     * @param mixed|null    $user_location      User location for more relevant search results
     */
    public function __construct(
        public readonly string $name = 'web_search',
        public readonly string $type = 'web_search_20260209',
        public readonly array $allowed_callers = [],
        public readonly ?array $allowed_domains = null,
        public readonly ?array $blocked_domains = null,
        public readonly mixed $cache_control = null,
        public readonly bool $defer_loading = false,
        public readonly ?int $max_uses = null,
        public readonly bool $strict = false,
        public readonly mixed $user_location = null,
    ) {
    }
}
