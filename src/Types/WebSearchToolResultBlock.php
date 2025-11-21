<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Web search tool result block
 */
class WebSearchToolResultBlock
{
    public function __construct(
        public readonly string $type,
        public readonly ?string $url = null,
        public readonly ?array $content = null,
    ) {
    }
}
