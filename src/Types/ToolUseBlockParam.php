<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Tool use block param for request
 */
class ToolUseBlockParam
{
    public function __construct(
        public readonly string $type,
        public readonly string $id,
        public readonly string $name,
        public readonly mixed $input,
    ) {}
}
