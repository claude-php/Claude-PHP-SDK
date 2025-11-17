<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta clear thinking edit parameter for the 2025-10-15 version
 *
 * @readonly
 */
class BetaClearThinking20251015EditParam
{
    /**
     * @param string $type Parameter type ("clear_thinking")
     * @param bool $enabled Whether clear thinking is enabled
     * @param array<string, mixed>|null $options Optional configuration options
     */
    public function __construct(
        public readonly string $type,
        public readonly bool $enabled,
        public readonly ?array $options = null,
    ) {}
}