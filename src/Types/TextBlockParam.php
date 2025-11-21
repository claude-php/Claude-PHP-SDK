<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Text block param for request
 */
class TextBlockParam
{
    public function __construct(
        public readonly string $type,
        public readonly string $text,
    ) {
    }
}
