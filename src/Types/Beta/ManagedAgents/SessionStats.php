<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

/**
 * Session statistics.
 */
class SessionStats
{
    public function __construct(
        public readonly int $total_events = 0,
        public readonly int $total_turns = 0,
        public readonly ?string $last_activity_at = null,
    ) {
    }
}
