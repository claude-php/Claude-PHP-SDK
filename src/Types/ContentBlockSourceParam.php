<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Request parameter for ContentBlockSource
 *
 * @readonly
 */
class ContentBlockSourceParam
{
    /**
     * @param string $type The type of source
     * @param string|null $id The source identifier
     * @param array<string, mixed>|null $citation_info Citation information
     */
    public function __construct(
        public readonly string $type,
        public readonly ?string $id = null,
        public readonly ?array $citation_info = null,
    ) {}
}
