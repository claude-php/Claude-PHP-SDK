<?php

declare(strict_types=1);

namespace ClaudePhp\Responses;

/**
 * Represents usage information from an API response
 */
class Usage
{
    /**
     * @param int $input_tokens Number of input tokens used
     * @param int $output_tokens Number of output tokens used
     * @param null|int $cache_creation_input_tokens Tokens used for cache creation (if applicable)
     * @param null|int $cache_read_input_tokens Tokens read from cache (if applicable)
     * @param null|array<string, mixed> $server_tool_use Server tool usage metadata (e.g. web search counters)
     */
    public function __construct(
        public readonly int $input_tokens,
        public readonly int $output_tokens,
        public readonly ?int $cache_creation_input_tokens = null,
        public readonly ?int $cache_read_input_tokens = null,
        public readonly ?array $server_tool_use = null,
    ) {
    }
}
