<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Tool definition
 */
class Tool
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly array $input_schema,
    ) {}
}
