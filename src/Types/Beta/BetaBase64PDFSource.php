<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta base64 PDF source
 */
class BetaBase64PDFSource
{
    public function __construct(
        public readonly string $type,
        public readonly string $media_type,
        public readonly string $data,
    ) {
    }
}
