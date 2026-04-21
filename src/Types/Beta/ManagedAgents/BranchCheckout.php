<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

class BranchCheckout
{
    public function __construct(
        public readonly string $type = 'branch',
        public readonly string $branch = '',
    ) {
    }
}
