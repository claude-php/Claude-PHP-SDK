<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta bash code execution output block parameter
 *
 * @readonly
 */
class BetaBashCodeExecutionOutputBlockParam
{
    /**
     * @param string $type Block type ("bash_code_execution_output")
     * @param string $text The output text from the bash execution
     */
    public function __construct(
        public readonly string $type,
        public readonly string $text,
    ) {}
}