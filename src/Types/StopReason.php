<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Stop reason enum
 */
class StopReason
{
    public function __construct(
        public readonly string $value,
    ) {}
}
