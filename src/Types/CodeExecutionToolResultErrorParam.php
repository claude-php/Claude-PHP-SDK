<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Code execution tool result error parameter (for constructing error tool results).
 */
class CodeExecutionToolResultErrorParam
{
    /**
     * @param string $error_code Error code (see CodeExecutionToolResultErrorCode constants)
     * @param string $type       Always "code_execution_tool_result_error"
     */
    public function __construct(
        public readonly string $error_code,
        public readonly string $type = 'code_execution_tool_result_error',
    ) {
    }
}
