<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta thinking delta
 */
class BetaThinkingDelta
{
    public function __construct(
        public readonly string $type,
        public readonly string $thinking,
    ) {
    }
}
