<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents\Params;

class SessionListParams
{
    public function __construct(
        public readonly ?int $limit = null,
        public readonly ?string $before_id = null,
        public readonly ?string $after_id = null,
        public readonly ?string $agent_id = null,
        public readonly ?string $environment_id = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'limit' => $this->limit,
            'before_id' => $this->before_id,
            'after_id' => $this->after_id,
            'agent_id' => $this->agent_id,
            'environment_id' => $this->environment_id,
        ], static fn ($v) => null !== $v);
    }
}
