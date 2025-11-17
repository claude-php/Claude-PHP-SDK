<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta bash code execution tool result block parameter
 *
 * @readonly
 */
class BetaBashCodeExecutionToolResultBlockParam
{
    /**
     * @param string $type Block type ("bash_code_execution_tool_result")
     * @param string $tool_use_id ID of the tool use that generated this result
     * @param bool $is_error Whether this represents an error result
     * @param array<string, mixed> $content The result content
     */
    public function __construct(
        public readonly string $type,
        public readonly string $tool_use_id,
        public readonly bool $is_error,
        public readonly array $content,
    ) {}
}