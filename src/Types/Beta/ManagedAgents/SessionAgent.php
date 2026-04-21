<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

/**
 * Agent snapshot embedded in a session response.
 */
class SessionAgent
{
    public function __construct(
        public readonly string $id = '',
        public readonly ?string $name = null,
        public readonly ?int $version = null,
    ) {
    }
}
