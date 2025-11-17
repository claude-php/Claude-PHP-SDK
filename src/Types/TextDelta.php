<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Text delta during streaming
 */
class TextDelta
{
    public function __construct(
        public readonly string $type,
        public readonly string $text,
    ) {}
}
