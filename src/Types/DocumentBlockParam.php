<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Document block param for request
 */
class DocumentBlockParam
{
    public function __construct(
        public readonly string $type,
        public readonly mixed $source,
        public readonly ?string $title = null,
        public readonly ?array $citations = null,
        public readonly ?string $context = null,
    ) {}
}
