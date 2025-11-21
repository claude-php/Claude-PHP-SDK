<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Base64 encoded image source
 */
class Base64ImageSource
{
    public function __construct(
        public readonly string $type,
        public readonly string $media_type,
        public readonly string $data,
    ) {
    }
}
