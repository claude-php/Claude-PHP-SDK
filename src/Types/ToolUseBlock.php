<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Tool use content block
 */
class ToolUseBlock
{
    public function __construct(
        public readonly string $type,
        public readonly string $id,
        public readonly string $name,
        public readonly mixed $input,
    ) {
    }
}
