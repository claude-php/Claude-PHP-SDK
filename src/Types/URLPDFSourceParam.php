<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Request parameter for URLPDFSource
 *
 * @readonly
 */
class URLPDFSourceParam
{
    /**
     * @param string $type The type identifier ("url")
     * @param string $url The PDF URL
     */
    public function __construct(
        public readonly string $type,
        public readonly string $url,
    ) {
    }
}
