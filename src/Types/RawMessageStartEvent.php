<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Raw message start event
 */
class RawMessageStartEvent
{
    public function __construct(
        public readonly string $type,
        public readonly mixed $message,
    ) {}
}
