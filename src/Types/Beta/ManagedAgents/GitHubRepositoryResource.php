<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

class GitHubRepositoryResource
{
    public function __construct(
        public readonly string $type = 'github_repository',
        public readonly string $repository = '',
        public readonly ?string $vault_credential_id = null,
        public readonly ?string $mount_path = null,
        public readonly ?array $checkout = null,
    ) {
    }
}
