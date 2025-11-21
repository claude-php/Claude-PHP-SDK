<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Usage statistics for a message
 */
class Usage
{
    public function __construct(
        public readonly int $input_tokens,
        public readonly int $output_tokens,
        public readonly ?int $cache_creation_input_tokens = null,
        public readonly ?int $cache_read_input_tokens = null,
        public readonly ?array $server_tool_use = null,
    ) {
    }
}
