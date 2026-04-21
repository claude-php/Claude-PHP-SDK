<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta parameter type for refusal stop details.
 */
class BetaRefusalStopDetailsParam
{
    public function __construct(
        public readonly string $type = 'refusal',
        public readonly ?string $category = null,
        public readonly ?string $explanation = null,
    ) {
    }
}
