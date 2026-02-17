<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * A file output produced by a code execution run.
 *
 * Contains a reference to a file (e.g. an image or data file) created
 * during code execution.
 */
class CodeExecutionOutputBlock
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
