<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Raw content block stop event
 */
class RawContentBlockStopEvent
{
    public function __construct(
        public readonly string $type,
        public readonly int $index,
    ) {
    }
}
