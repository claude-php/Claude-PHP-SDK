<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Tool result block param for request
 */
class ToolResultBlockParam
{
    public function __construct(
        public readonly string $type,
        public readonly string $tool_use_id,
        public readonly mixed $content,
    ) {}
}
