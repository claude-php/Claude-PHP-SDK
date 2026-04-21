<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta Output Config param.
 *
 * Configuration for model output including effort level and an optional
 * user-configurable total token budget across contexts.
 *
 * Mirrors Python `BetaOutputConfigParam`
 * (`src/anthropic/types/beta/beta_output_config_param.py`).
 */
class BetaOutputConfigParam
{
    /**
     * @param null|string $effort Effort level for model output. One of:
     *                            "low", "medium", "high", "xhigh", "max".
     * @param null|array<string, mixed> $task_budget Token task budget
     *                            (BetaTokenTaskBudgetParam shape) — user-configurable
     *                            total token budget across contexts.
     */
    public function __construct(
        public readonly ?string $effort = null,
        public readonly ?array $task_budget = null,
    ) {
    }
}
