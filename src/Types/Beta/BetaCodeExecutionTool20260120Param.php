<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta code execution tool parameter (version 2026-01-20).
 *
 * Code execution tool with REPL state persistence (daemon mode + gVisor checkpoint).
 * State is maintained across multiple tool calls within a single API request.
 *
 * Use type "code_execution_20260120" in your tool definition.
 *
 * @see https://docs.anthropic.com/en/docs/build-with-claude/tool-use/code-execution
 */
class BetaCodeExecutionTool20260120Param
{
    /**
     * @param string      $name             Must be "code_execution"
     * @param string      $type             Must be "code_execution_20260120"
     * @param string[]    $allowed_callers  Callers allowed to invoke this tool ("direct", "code_execution_20250825")
     * @param mixed|null  $cache_control    Cache control breakpoint configuration
     * @param bool        $defer_loading    If true, tool is not included in initial system prompt
     * @param bool        $strict           When true, guarantees schema validation on tool names and inputs
     */
    public function __construct(
        public readonly string $name = 'code_execution',
        public readonly string $type = 'code_execution_20260120',
        public readonly array $allowed_callers = [],
        public readonly mixed $cache_control = null,
        public readonly bool $defer_loading = false,
        public readonly bool $strict = false,
    ) {
    }
}
