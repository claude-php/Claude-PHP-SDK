<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Message delta usage
 */
class MessageDeltaUsage
{
    public function __construct(
        public readonly string $type,
        public readonly int $output_tokens,
    ) {}
}
