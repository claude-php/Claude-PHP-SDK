<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Bash tool
 */
class ToolBash20250124
{
    public function __construct(
        public readonly string $type,
        public readonly string $name,
    ) {}
}
