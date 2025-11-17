<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Raw content block delta event
 */
class RawContentBlockDeltaEvent
{
    public function __construct(
        public readonly string $type,
        public readonly int $index,
        public readonly mixed $delta,
    ) {}
}
