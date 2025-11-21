<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Raw message delta event
 */
class RawMessageDeltaEvent
{
    public function __construct(
        public readonly string $type,
        public readonly mixed $delta,
        public readonly int $usage,
    ) {
    }
}
