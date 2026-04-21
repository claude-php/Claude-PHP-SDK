<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

class BetaTextEditorCodeExecutionStrReplaceResultBlockParam
{
    public function __construct(
        public readonly string $type = 'str_replace_result',
        public readonly ?int $occurrences_replaced = null,
    ) {
    }
}
