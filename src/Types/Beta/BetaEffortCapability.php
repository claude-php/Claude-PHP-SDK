<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta Effort (reasoning_effort) capability details for a model.
 *
 * Mirrors Python `BetaEffortCapability`
 * (`src/anthropic/types/beta/beta_effort_capability.py`).
 */
class BetaEffortCapability
{
    /**
     * @param string $support One of BetaCapabilitySupport::SUPPORTED|UNSUPPORTED|PREVIEW.
     * @param null|array<int, string> $allowed_values Allowed effort levels for this model.
     *                                                 Possible values: "low", "medium", "high", "xhigh", "max".
     */
    public function __construct(
        public readonly string $support = BetaCapabilitySupport::UNSUPPORTED,
        public readonly ?array $allowed_values = null,
    ) {
    }
}
