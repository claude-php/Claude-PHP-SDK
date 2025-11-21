<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Message count tokens tool
 */
class MessageCountTokensTool
{
    public function __construct(
        public readonly string $type,
        public readonly int $input_tokens,
    ) {
    }
}
