<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta base64 PDF source parameter
 *
 * @readonly
 */
class BetaBase64PDFSourceParam
{
    /**
     * @param string $type Source type ("base64")
     * @param string $media_type The media type of the PDF
     * @param string $data Base64-encoded PDF data
     */
    public function __construct(
        public readonly string $type,
        public readonly string $media_type,
        public readonly string $data,
    ) {
    }
}
