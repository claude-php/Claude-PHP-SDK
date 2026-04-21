<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents\Params;

class VaultUpdateParams
{
    public function __construct(
        public readonly ?string $display_name = null,
        public readonly ?array $metadata = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'display_name' => $this->display_name,
            'metadata' => $this->metadata,
        ], static fn ($v) => null !== $v);
    }
}
