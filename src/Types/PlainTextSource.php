<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Plain text source
 */
class PlainTextSource
{
    public function __construct(
        public readonly string $type,
        public readonly string $text,
    ) {}
}
