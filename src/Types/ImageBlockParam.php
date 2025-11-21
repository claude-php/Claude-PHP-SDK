<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Image block param for request
 */
class ImageBlockParam
{
    public function __construct(
        public readonly string $type,
        public readonly mixed $source,
    ) {
    }
}
