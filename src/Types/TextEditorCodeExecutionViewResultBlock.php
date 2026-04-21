<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

class TextEditorCodeExecutionViewResultBlock
{
    public function __construct(
        public readonly string $type = 'view_result',
        public readonly ?string $content = null,
        public readonly ?int $line_count = null,
    ) {
    }
}
