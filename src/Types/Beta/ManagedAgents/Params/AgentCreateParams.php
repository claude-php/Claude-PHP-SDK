<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents\Params;

/**
 * Parameters for creating a managed agent.
 *
 * Mirrors Python `agent_create_params.py`.
 */
class AgentCreateParams
{
    /**
     * @param string|array<string, mixed> $model Model identifier or model_config object
     * @param string $name Human-readable name (1-256 characters)
     * @param string|null $description Description (up to 2048 characters)
     * @param list<array<string, mixed>>|null $mcp_servers MCP servers (max 20)
     * @param array<string, string>|null $metadata Key-value metadata (max 16 pairs)
     * @param list<array<string, mixed>>|null $skills Skills available to the agent (max 20)
     * @param string|null $system System prompt (up to 100,000 characters)
     * @param list<array<string, mixed>>|null $tools Tool configurations (max 128 total)
     * @param list<string>|null $betas Beta header values
     */
    public function __construct(
        public readonly string|array $model,
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly ?array $mcp_servers = null,
        public readonly ?array $metadata = null,
        public readonly ?array $skills = null,
        public readonly ?string $system = null,
        public readonly ?array $tools = null,
        public readonly ?array $betas = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'model' => $this->model,
            'name' => $this->name,
            'description' => $this->description,
            'mcp_servers' => $this->mcp_servers,
            'metadata' => $this->metadata,
            'skills' => $this->skills,
            'system' => $this->system,
            'tools' => $this->tools,
        ], static fn ($v) => null !== $v);
    }
}
