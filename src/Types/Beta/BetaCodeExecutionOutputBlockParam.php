<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta code execution output block parameter.
 */
class BetaCodeExecutionOutputBlockParam
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
