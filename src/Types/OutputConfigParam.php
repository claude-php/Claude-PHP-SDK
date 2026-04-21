<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Output Config param.
 *
 * Configuration for model output including effort level.
 *
 * Mirrors Python `OutputConfigParam`
 * (`src/anthropic/types/output_config_param.py`).
 */
class OutputConfigParam
{
    /**
     * @param null|string $effort Effort level for model output. One of:
     *                            "low", "medium", "high", "xhigh", "max".
     */
    public function __construct(
        public readonly ?string $effort = null,
    ) {
    }
}
