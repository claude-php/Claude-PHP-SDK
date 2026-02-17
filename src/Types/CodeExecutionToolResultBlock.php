<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * A tool result block containing code execution output.
 *
 * Returned in the API response when the model invokes a code_execution tool.
 */
class CodeExecutionToolResultBlock
{
    /**
     * @param mixed  $content     Code execution result or encrypted result content
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
