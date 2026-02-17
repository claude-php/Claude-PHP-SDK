<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta tool result block for code execution.
 */
class BetaCodeExecutionToolResultBlock
{
    /**
     * @param mixed  $content     Code execution result content
     * @param string $tool_use_id The ID of the tool_use block this result corresponds to
     * @param string $type        Always "code_execution_tool_result"
     */
    public function __construct(
        public readonly mixed $content,
        public readonly string $tool_use_id,
        public readonly string $type = 'code_execution_tool_result',
    ) {
    }
}
