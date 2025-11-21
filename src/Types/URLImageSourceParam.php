<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Request parameter for URLImageSource
 *
 * @readonly
 */
class URLImageSourceParam
{
    /**
     * @param string $type The type identifier ("url")
     * @param string $url The image URL
     */
    public function __construct(
        public readonly string $type,
        public readonly string $url,
    ) {
    }
}
