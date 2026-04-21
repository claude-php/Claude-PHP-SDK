<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\Sessions;

/**
 * Token usage for a model request span within a session.
 */
class SpanModelUsage
{
    public function __construct(
        public readonly int $input_tokens = 0,
        public readonly int $output_tokens = 0,
        public readonly ?array $cache_creation = null,
        public readonly ?int $cache_read_input_tokens = null,
    ) {
    }
}
