<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Configuration for thinking turn budgets.
 */
class BetaThinkingTurnsParam
{
    /**
     * @param int|null $max_turns Maximum number of thinking turns
     * @param int|null $budget_tokens Token budget per thinking turn
     */
    public function __construct(
        public readonly ?int $max_turns = null,
        public readonly ?int $budget_tokens = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'max_turns' => $this->max_turns,
            'budget_tokens' => $this->budget_tokens,
        ], static fn ($v) => null !== $v);
    }
}
