<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Web search tool
 */
class WebSearchTool20250305
{
    public function __construct(
        public readonly string $type,
        public readonly string $name,
    ) {}
}
