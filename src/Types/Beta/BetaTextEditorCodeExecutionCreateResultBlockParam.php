<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

class BetaTextEditorCodeExecutionCreateResultBlockParam
{
    public function __construct(
        public readonly string $type = 'create_result',
        public readonly ?string $path = null,
        public readonly ?bool $is_new_file = null,
    ) {
    }
}
