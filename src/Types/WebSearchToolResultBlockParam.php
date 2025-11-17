<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Web search tool result block param for request
 */
class WebSearchToolResultBlockParam
{
    public function __construct(
        public readonly string $type,
        public readonly mixed $content = null,
    ) {}
}
