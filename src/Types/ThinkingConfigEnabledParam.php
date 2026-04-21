<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Thinking config enabled param
 */
class ThinkingConfigEnabledParam
{
    /**
     * @param string $type Must be "enabled"
     * @param int|null $budget_tokens Token budget for thinking
     * @param string|null $display Display mode: "summarized" or "omitted"
     */
    public function __construct(
        public readonly string $type,
        public readonly ?int $budget_tokens = null,
        public readonly ?string $display = null,
    ) {
    }
}
