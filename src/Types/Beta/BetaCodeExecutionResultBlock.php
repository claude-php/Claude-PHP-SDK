<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta code execution result block.
 *
 * Represents the result of a code_execution tool call in a beta API response.
 */
class BetaCodeExecutionResultBlock
{
    /**
     * @param BetaCodeExecutionOutputBlock[] $content     File outputs produced during execution
     * @param int                            $return_code Exit code of the executed process
     * @param string                         $stderr      Standard error output
     * @param string                         $stdout      Standard output
     * @param string                         $type        Always "code_execution_result"
     */
    public function __construct(
        public readonly array $content,
        public readonly int $return_code,
        public readonly string $stderr,
        public readonly string $stdout,
        public readonly string $type = 'code_execution_result',
    ) {
    }
}
