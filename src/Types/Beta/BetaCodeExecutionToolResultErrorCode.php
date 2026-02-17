<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Error codes for beta code execution tool result errors.
 */
class BetaCodeExecutionToolResultErrorCode
{
    public const TIMEOUT = 'timeout';
    public const EXECUTION_ERROR = 'execution_error';
    public const INTERNAL_ERROR = 'internal_error';

    public function __construct(
        public readonly string $value,
    ) {
    }
}
