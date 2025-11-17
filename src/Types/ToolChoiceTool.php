<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Tool choice specific tool
 */
class ToolChoiceTool
{
    public function __construct(
        public readonly string $type,
        public readonly string $name,
    ) {}
}
