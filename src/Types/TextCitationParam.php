<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Text citation param
 */
class TextCitationParam
{
    public function __construct(
        public readonly string $type,
        public readonly string $text,
    ) {}
}
