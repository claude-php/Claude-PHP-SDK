<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta clear thinking edit response for the 2025-10-15 version
 *
 * @readonly
 */
class BetaClearThinking20251015EditResponse
{
    /**
     * @param string $type Response type ("clear_thinking_response")
     * @param string $content The clear thinking content
     * @param array<string, mixed>|null $metadata Optional metadata
     */
    public function __construct(
        public readonly string $type,
        public readonly string $content,
        public readonly ?array $metadata = null,
    ) {}
}