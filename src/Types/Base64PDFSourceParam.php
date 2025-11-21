<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Request parameter for Base64PDFSource
 *
 * @readonly
 */
class Base64PDFSourceParam
{
    /**
     * @param string $type The type identifier ("base64")
     * @param string $media_type The PDF media type ("application/pdf")
     * @param string $data The base64-encoded PDF data
     */
    public function __construct(
        public readonly string $type,
        public readonly string $media_type,
        public readonly string $data,
    ) {
    }
}
