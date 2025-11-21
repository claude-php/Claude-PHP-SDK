<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Raw message stop event
 */
class RawMessageStopEvent
{
    public function __construct(
        public readonly string $type,
    ) {
    }
}
