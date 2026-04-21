<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

class DeletedCredential
{
    public function __construct(
        public readonly string $id = '',
        public readonly string $type = 'credential_deleted',
    ) {
    }
}
