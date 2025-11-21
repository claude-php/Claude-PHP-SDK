<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Server tool use block
 */
class ServerToolUseBlock
{
    public function __construct(
        public readonly string $type,
        public readonly int $id,
        public readonly string $name,
        public readonly mixed $input,
    ) {
    }
}
