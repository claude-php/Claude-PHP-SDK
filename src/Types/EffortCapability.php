<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Effort (reasoning_effort) capability details for a model.
 *
 * Mirrors Python `EffortCapability` (`src/anthropic/types/effort_capability.py`).
 */
class EffortCapability
{
    /**
     * @param string $support One of CapabilitySupport::SUPPORTED|UNSUPPORTED|PREVIEW.
     * @param null|array<int, string> $allowed_values Allowed effort levels for this model.
     *                                                 Possible values: "low", "medium", "high", "xhigh", "max".
     */
    public function __construct(
        public readonly string $support = CapabilitySupport::UNSUPPORTED,
        public readonly ?array $allowed_values = null,
    ) {
    }
}
