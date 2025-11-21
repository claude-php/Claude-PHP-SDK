<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Redacted thinking content block
 */
class RedactedThinkingBlock
{
    public function __construct(
        public readonly string $type,
        public readonly string $data,
    ) {
    }
}
