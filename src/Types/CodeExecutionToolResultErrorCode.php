<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Error codes for code execution tool result errors.
 */
class CodeExecutionToolResultErrorCode
{
    /** Code execution timed out */
    public const TIMEOUT = 'timeout';

    /** Execution was killed (e.g. out of memory) */
    public const EXECUTION_ERROR = 'execution_error';

    /** An unexpected internal error occurred */
    public const INTERNAL_ERROR = 'internal_error';

    public function __construct(
        public readonly string $value,
    ) {
    }
}
