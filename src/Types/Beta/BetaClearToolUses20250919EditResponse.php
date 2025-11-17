<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta clear tool uses edit response for the 2025-09-19 version
 *
 * @readonly
 */
class BetaClearToolUses20250919EditResponse
{
    /**
     * @param string $type Response type ("clear_tool_uses_response")
     * @param array<string> $cleared_tools List of tools that were cleared
     * @param bool $success Whether the operation was successful
     */
    public function __construct(
        public readonly string $type,
        public readonly array $cleared_tools,
        public readonly bool $success,
    ) {}
}