<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Code execution tool parameter (version 2025-05-22).
 *
 * Enables the model to execute code in a sandboxed environment.
 * Use type "code_execution_20250522" in your tool definition.
 *
 * @see https://docs.anthropic.com/en/docs/build-with-claude/tool-use/code-execution
 */
class CodeExecutionTool20250522Param
{
    /**
     * @param string      $name             Must be "code_execution"
     * @param string      $type             Must be "code_execution_20250522"
     * @param string[]    $allowed_callers  Callers allowed to invoke this tool ("direct", "code_execution_20250825")
     * @param mixed|null  $cache_control    Cache control breakpoint configuration
     * @param bool        $defer_loading    If true, tool is not included in initial system prompt
     * @param bool        $strict           When true, guarantees schema validation on tool names and inputs
     */
    public function __construct(
        public readonly string $name = 'code_execution',
        public readonly string $type = 'code_execution_20250522',
        public readonly array $allowed_callers = [],
        public readonly mixed $cache_control = null,
        public readonly bool $defer_loading = false,
        public readonly bool $strict = false,
    ) {
    }
}
