<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Text content block
 */
class TextBlock
{
    public function __construct(
        public readonly string $type,
        public readonly string $text,
        public readonly ?array $citations = null,
    ) {
    }
}
