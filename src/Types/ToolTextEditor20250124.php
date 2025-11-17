<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Text editor tool
 */
class ToolTextEditor20250124
{
    public function __construct(
        public readonly string $type,
        public readonly string $name,
    ) {}
}
