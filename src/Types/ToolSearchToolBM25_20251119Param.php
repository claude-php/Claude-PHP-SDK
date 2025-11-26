<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * BM25 search tool param (2025-11-19 version)
 *
 * A tool for searching using the BM25 algorithm.
 */
class ToolSearchToolBM25_20251119Param
{
    /**
     * @param string $type The tool type (tool_search_tool_bm25_20251119 or tool_search_tool_bm25)
     * @param string $name Name of the tool (tool_search_tool_bm25)
     * @param null|array<string> $allowed_callers List of allowed callers (direct, code_execution_20250825)
     * @param null|CacheControlEphemeralParam $cache_control Cache control configuration
     * @param null|bool $defer_loading If true, tool will not be included in initial system prompt
     * @param null|bool $strict Whether to use strict mode
     */
    public function __construct(
        public readonly string $type,
        public readonly string $name,
        public readonly ?array $allowed_callers = null,
        public readonly ?CacheControlEphemeralParam $cache_control = null,
        public readonly ?bool $defer_loading = null,
        public readonly ?bool $strict = null,
    ) {
    }
}
