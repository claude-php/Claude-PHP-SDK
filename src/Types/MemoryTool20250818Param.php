<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Memory tool parameter (version 2025-08-18).
 *
 * Enables the model to persist and retrieve information across conversations
 * using a file-based memory system.
 *
 * Use type "memory_20250818" in your tool definition.
 *
 * @see https://docs.anthropic.com/en/docs/build-with-claude/tool-use/memory-tool
 */
class MemoryTool20250818Param
{
    /**
     * @param string      $name             Must be "memory"
     * @param string      $type             Must be "memory_20250818"
     * @param string[]    $allowed_callers  Callers allowed to invoke this tool ("direct", "code_execution_20250825")
     * @param mixed|null  $cache_control    Cache control breakpoint configuration
     * @param bool        $defer_loading    If true, tool is not included in initial system prompt
     * @param array[]     $input_examples   Example inputs to guide the model
     * @param bool        $strict           When true, guarantees schema validation on tool names and inputs
     */
    public function __construct(
        public readonly string $name = 'memory',
        public readonly string $type = 'memory_20250818',
        public readonly array $allowed_callers = [],
        public readonly mixed $cache_control = null,
        public readonly bool $defer_loading = false,
        public readonly array $input_examples = [],
        public readonly bool $strict = false,
    ) {
    }
}
