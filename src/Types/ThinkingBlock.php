<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Thinking content block
 */
class ThinkingBlock
{
    public function __construct(
        public readonly string $type,
        public readonly string $thinking,
    ) {
    }
}
