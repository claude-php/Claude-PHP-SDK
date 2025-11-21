<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Thinking config disabled param
 */
class ThinkingConfigDisabledParam
{
    public function __construct(
        public readonly string $type,
    ) {
    }
}
