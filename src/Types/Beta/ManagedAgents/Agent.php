<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents;

/**
 * Managed agent response object.
 */
class Agent
{
    public function __construct(
        public readonly string $id = '',
        public readonly string $type = 'agent',
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?string $model = null,
        public readonly ?array $model_config = null,
        public readonly ?array $skills = null,
        public readonly ?array $tools = null,
        public readonly ?array $mcp_servers = null,
        public readonly ?string $system_prompt = null,
        public readonly ?string $status = null,
        public readonly ?string $created_at = null,
        public readonly ?string $updated_at = null,
        public readonly ?string $archived_at = null,
        public readonly ?int $version = null,
        public readonly ?array $cloud_config = null,
        public readonly ?array $packages = null,
        public readonly ?array $environment = null,
        public readonly ?array $metadata = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(...array_intersect_key($data, array_flip([
            'id', 'type', 'name', 'description', 'model', 'model_config',
            'skills', 'tools', 'mcp_servers', 'system_prompt', 'status',
            'created_at', 'updated_at', 'archived_at', 'version',
            'cloud_config', 'packages', 'environment', 'metadata',
        ])));
    }
}
