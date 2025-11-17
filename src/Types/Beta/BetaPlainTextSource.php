<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta plain text source
 */
class BetaPlainTextSource
{
    public function __construct(
        public readonly string $type,
        public readonly string $text,
    ) {}
}
