<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta tool use block
 */
class BetaToolUseBlock
{
    public function __construct(
        public readonly string $type,
        public readonly string $id,
        public readonly string $name,
        public readonly mixed $input,
    ) {}
}
