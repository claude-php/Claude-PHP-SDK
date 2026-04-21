<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents\Params;

/**
 * Parameters for updating a managed agent.
 *
 * Mirrors Python `agent_update_params.py`.
 */
class AgentUpdateParams
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly string|array|null $model = null,
        public readonly ?array $mcp_servers = null,
        public readonly ?array $metadata = null,
        public readonly ?array $skills = null,
        public readonly ?string $system = null,
        public readonly ?array $tools = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'model' => $this->model,
            'mcp_servers' => $this->mcp_servers,
            'metadata' => $this->metadata,
            'skills' => $this->skills,
            'system' => $this->system,
            'tools' => $this->tools,
        ], static fn ($v) => null !== $v);
    }
}
