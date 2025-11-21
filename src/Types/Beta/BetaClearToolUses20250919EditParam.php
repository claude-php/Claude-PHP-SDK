<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta clear tool uses edit parameter for the 2025-09-19 version
 *
 * @readonly
 */
class BetaClearToolUses20250919EditParam
{
    /**
     * @param string $type Parameter type ("clear_tool_uses")
     * @param bool $enabled Whether clear tool uses is enabled
     * @param null|array<string> $tool_names Optional list of specific tools to clear
     */
    public function __construct(
        public readonly string $type,
        public readonly bool $enabled,
        public readonly ?array $tool_names = null,
    ) {
    }
}
