<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta bash code execution result block parameter
 *
 * @readonly
 */
class BetaBashCodeExecutionResultBlockParam
{
    /**
     * @param string $type Block type ("bash_code_execution_result")
     * @param int $exit_code The exit code from the bash execution
     * @param string $stdout Standard output from the bash execution
     * @param string $stderr Standard error from the bash execution
     */
    public function __construct(
        public readonly string $type,
        public readonly int $exit_code,
        public readonly string $stdout,
        public readonly string $stderr,
    ) {
    }
}
