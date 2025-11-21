<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * URL PDF source
 */
class URLPDFSource
{
    public function __construct(
        public readonly string $type,
        public readonly string $url,
    ) {
    }
}
