<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta server tool use block
 */
class BetaServerToolUseBlock
{
    public function __construct(
        public readonly string $type,
        public readonly int $id,
        public readonly string $name,
        public readonly mixed $input,
    ) {}
}
