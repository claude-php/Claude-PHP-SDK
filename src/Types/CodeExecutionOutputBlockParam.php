<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Code execution output block parameter (for constructing tool results).
 */
class CodeExecutionOutputBlockParam
{
    /**
     * @param string $file_id  The ID of the output file
     * @param string $type     Always "code_execution_output"
     */
    public function __construct(
        public readonly string $file_id,
        public readonly string $type = 'code_execution_output',
    ) {
    }
}
