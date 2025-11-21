<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Text citation
 */
class TextCitation
{
    public function __construct(
        public readonly string $type,
        public readonly string $text,
    ) {
    }
}
