<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents\Params;

class EnvironmentCreateParams
{
    public function __construct(
        public readonly string $display_name,
        public readonly ?string $description = null,
        public readonly ?array $packages = null,
        public readonly ?array $cloud_config = null,
        public readonly ?array $network = null,
        public readonly ?array $metadata = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'display_name' => $this->display_name,
            'description' => $this->description,
            'packages' => $this->packages,
            'cloud_config' => $this->cloud_config,
            'network' => $this->network,
            'metadata' => $this->metadata,
        ], static fn ($v) => null !== $v);
    }
}
