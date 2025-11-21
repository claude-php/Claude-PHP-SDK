<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Thinking delta during streaming
 */
class ThinkingDelta
{
    public function __construct(
        public readonly string $type,
        public readonly string $thinking,
    ) {
    }
}
