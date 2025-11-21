<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Thinking config enabled
 */
class ThinkingConfigEnabled
{
    public function __construct(
        public readonly string $type,
        public readonly ?int $budget_tokens = null,
    ) {
    }
}
