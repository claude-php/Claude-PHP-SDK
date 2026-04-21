<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

class CommitCheckout
{
    public function __construct(
        public readonly string $type = 'commit',
        public readonly string $commit = '',
    ) {
    }
}
