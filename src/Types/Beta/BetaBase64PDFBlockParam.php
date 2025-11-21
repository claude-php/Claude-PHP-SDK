<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta base64 PDF block parameter
 *
 * @readonly
 */
class BetaBase64PDFBlockParam
{
    /**
     * @param string $type Block type ("document")
     * @param array<string, mixed> $source PDF source configuration
     * @param null|string $cache_control Optional cache control settings
     */
    public function __construct(
        public readonly string $type,
        public readonly array $source,
        public readonly ?string $cache_control = null,
    ) {
    }
}
