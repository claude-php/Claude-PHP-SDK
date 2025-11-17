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
     * @param array<MessageParam|array<string, mixed>> $messages The message history
     * @param array<string, mixed>|null $tools Optional tools to count
     * @param array<string, mixed>|null $thinking Optional thinking configuration to count
     * @param array<string, mixed>|null $system Optional system prompt to count
     */
    public function __construct(
        public readonly string $model,
        public readonly array $messages,
        public readonly ?array $tools = null,
        public readonly ?array $thinking = null,
        public readonly ?array $system = null,
    ) {}
}
