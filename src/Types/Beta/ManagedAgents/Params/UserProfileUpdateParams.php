<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents\Params;

class UserProfileUpdateParams
{
    public function __construct(
        public readonly ?string $display_name = null,
        public readonly ?string $description = null,
        public readonly ?string $external_id = null,
        public readonly ?array $metadata = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'display_name' => $this->display_name,
            'description' => $this->description,
            'external_id' => $this->external_id,
            'metadata' => $this->metadata,
        ], static fn ($v) => null !== $v);
    }
}
