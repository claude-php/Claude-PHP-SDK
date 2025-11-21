<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Parameters for counting message tokens
 *
 * @readonly
 */
class MessageCountTokensParams
{
    /**
     * @param string $model The model to use for token counting
     * @param array<array<string, mixed>|MessageParam> $messages The message history
     * @param null|array<string, mixed> $tools Optional tools to count
     * @param null|array<string, mixed> $thinking Optional thinking configuration to count
     * @param null|array<string, mixed> $system Optional system prompt to count
     */
    public function __construct(
        public readonly string $model,
        public readonly array $messages,
        public readonly ?array $tools = null,
        public readonly ?array $thinking = null,
        public readonly ?array $system = null,
    ) {
    }
}
