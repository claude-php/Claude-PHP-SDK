<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Message tokens count response
 */
class MessageTokensCount
{
    public function __construct(
        public readonly int $input_tokens,
        public readonly int $output_tokens = 0,
        public readonly ?int $cache_creation_input_tokens = null,
        public readonly ?int $cache_read_input_tokens = null,
    ) {}
}
