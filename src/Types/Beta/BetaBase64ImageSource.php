<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta base64 image source
 */
class BetaBase64ImageSource
{
    public function __construct(
        public readonly string $type,
        public readonly string $media_type,
        public readonly string $data,
    ) {
    }
}
