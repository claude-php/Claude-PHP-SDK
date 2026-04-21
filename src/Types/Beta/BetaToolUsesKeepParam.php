<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Tool uses keep parameter for compaction controls.
 */
class BetaToolUsesKeepParam
{
    public function __construct(
        public readonly string $type = 'tool_uses',
        public readonly int $value = 0,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['type' => $this->type, 'value' => $this->value];
    }
}
