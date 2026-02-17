<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * The result of a code execution tool call.
 *
 * Contains the stdout, stderr, return code, and any file outputs produced
 * by the code execution run.
 */
class CodeExecutionResultBlock
{
    /**
     * @param CodeExecutionOutputBlock[] $content     File outputs produced during execution
     * @param int                        $return_code Exit code of the executed process
     * @param string                     $stderr      Standard error output
     * @param string                     $stdout      Standard output
     * @param string                     $type        Always "code_execution_result"
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
