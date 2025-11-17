<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta bash code execution tool result error
 *
 * @readonly
 */
class BetaBashCodeExecutionToolResultError
{
    /**
     * @param string $type Error type ("bash_code_execution_error")
     * @param string $message Error message
     * @param string|null $code Optional error code
     */
    public function __construct(
        public readonly string $type,
        public readonly string $message,
        public readonly ?string $code = null,
    ) {}
}