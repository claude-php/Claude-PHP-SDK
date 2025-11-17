<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Redacted thinking block param for request
 */
class RedactedThinkingBlockParam
{
    public function __construct(
        public readonly string $type,
        public readonly string $data,
    ) {}
}
