<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Thinking block param for request
 */
class ThinkingBlockParam
{
    public function __construct(
        public readonly string $type,
        public readonly string $thinking,
    ) {}
}
