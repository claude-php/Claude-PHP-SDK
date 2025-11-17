<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Metadata type
 */
class Metadata
{
    public function __construct(
        public readonly array $data = [],
    ) {}
}
