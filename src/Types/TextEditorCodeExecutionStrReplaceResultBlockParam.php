<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

class TextEditorCodeExecutionStrReplaceResultBlockParam
{
    public function __construct(
        public readonly string $type = 'str_replace_result',
        public readonly ?int $occurrences_replaced = null,
    ) {
    }
}
