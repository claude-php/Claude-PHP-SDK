<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Message tokens count response
 */
class MessageTokensCount
{
    /**
     * @param int $input_tokens Count of tokens that would be sent to the model
     * @param int $output_tokens Estimated response tokens (if provided)
     * @param null|int $cache_creation_input_tokens Tokens used creating cache
     * @param null|int $cache_read_input_tokens Tokens saved via cache reads
     * @param null|array<string, mixed> $context_management Extra metadata when context edits are applied
     */
    public function __construct(
        public readonly int $input_tokens,
        public readonly int $output_tokens = 0,
        public readonly ?int $cache_creation_input_tokens = null,
        public readonly ?int $cache_read_input_tokens = null,
        public readonly ?array $context_management = null,
    ) {
    }
}
