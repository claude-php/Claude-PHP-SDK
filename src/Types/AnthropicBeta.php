<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Shared;

class AnthropicBeta
{
    public function __construct(
        public readonly string $value,
    ) {
    }
}
