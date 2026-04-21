<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

class AlwaysAllowPolicy
{
    public function __construct(
        public readonly string $type = 'always_allow',
    ) {
    }
}
