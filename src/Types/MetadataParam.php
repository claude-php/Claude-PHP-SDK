<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Metadata parameter for API requests
 *
 * @readonly
 */
class MetadataParam
{
    /**
     * @param array<string, string> $metadata Custom metadata key-value pairs
     */
    public function __construct(
        public readonly array $metadata,
    ) {}
}
