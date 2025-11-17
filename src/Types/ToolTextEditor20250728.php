<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Text editor tool (2025-07-28 version)
 */
class ToolTextEditor20250728
{
    public function __construct(
        public readonly string $type,
        public readonly string $name,
    ) {}
}
