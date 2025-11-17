<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Text editor tool (2025-04-29 version)
 */
class ToolTextEditor20250429
{
    public function __construct(
        public readonly string $type,
        public readonly string $name,
    ) {}
}
