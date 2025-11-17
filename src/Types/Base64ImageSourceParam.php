<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Request parameter for Base64ImageSource
 *
 * @readonly
 */
class Base64ImageSourceParam
{
    /**
     * @param string $type The type identifier ("base64")
     * @param string $media_type The image media type (e.g., "image/jpeg")
     * @param string $data The base64-encoded image data
     */
    public function __construct(
        public readonly string $type,
        public readonly string $media_type,
        public readonly string $data,
    ) {}
}
