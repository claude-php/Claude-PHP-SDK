<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * URL image source
 */
class URLImageSource
{
    public function __construct(
        public readonly string $type,
        public readonly string $url,
    ) {
    }
}
