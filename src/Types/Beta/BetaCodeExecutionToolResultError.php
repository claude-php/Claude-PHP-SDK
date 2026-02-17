<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta code execution tool result error.
 */
class BetaCodeExecutionToolResultError
{
    /**
     * @param string $error_code Error code (see BetaCodeExecutionToolResultErrorCode constants)
     * @param string $type       Always "code_execution_tool_result_error"
     */
    public function __construct(
        public readonly string $error_code,
        public readonly string $type = 'code_execution_tool_result_error',
    ) {
    }
}
