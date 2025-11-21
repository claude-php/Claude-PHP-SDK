<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta URL image source
 */
class BetaURLImageSource
{
    public function __construct(
        public readonly string $type,
        public readonly string $url,
    ) {
    }
}
