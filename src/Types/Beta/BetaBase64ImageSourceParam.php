<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta base64 image source parameter
 *
 * @readonly
 */
class BetaBase64ImageSourceParam
{
    /**
     * @param string $type Source type ("base64")
     * @param string $media_type The media type of the image
     * @param string $data Base64-encoded image data
     */
    public function __construct(
        public readonly string $type,
        public readonly string $media_type,
        public readonly string $data,
    ) {
    }
}
