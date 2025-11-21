<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Citations delta during streaming
 */
class CitationsDelta
{
    public function __construct(
        public readonly string $type,
        public readonly array $citations,
    ) {
    }
}
