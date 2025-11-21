<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Raw content block delta (alias for streaming)
 */
class RawContentBlockDelta
{
    public function __construct(
        public readonly string $type,
        public readonly int $index,
        public readonly mixed $delta,
    ) {
    }
}
