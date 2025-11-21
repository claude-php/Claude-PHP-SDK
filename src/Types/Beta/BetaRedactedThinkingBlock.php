<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta redacted thinking block
 */
class BetaRedactedThinkingBlock
{
    public function __construct(
        public readonly string $type,
        public readonly string $data,
    ) {
    }
}
