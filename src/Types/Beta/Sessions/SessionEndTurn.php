<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\Sessions;

class SessionEndTurn
{
    public function __construct(
        public readonly string $type = 'session_end_turn',
        public readonly ?string $session_id = null,
        public readonly ?string $created_at = null,
    ) {
    }
}
