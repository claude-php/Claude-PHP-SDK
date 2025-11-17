<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta usage
 */
class BetaUsage
{
    public function __construct(
        public readonly int $input_tokens,
        public readonly int $output_tokens,
        public readonly ?int $cache_creation_input_tokens = null,
        public readonly ?int $cache_read_input_tokens = null,
    ) {}
}
