<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta Output Config param
 *
 * Configuration for model output including effort level.
 */
class BetaOutputConfigParam
{
    /**
     * @param null|string $effort Effort level for model output (low, medium, high)
     */
    public function __construct(
        public readonly ?string $effort = null,
    ) {
    }
}
