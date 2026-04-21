<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents\Params;

class SkillCreateParams
{
    public function __construct(
        public readonly string $display_name,
        public readonly string $instructions,
        public readonly ?string $description = null,
        public readonly ?array $allowed_tools = null,
        public readonly ?array $metadata = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'display_name' => $this->display_name,
            'instructions' => $this->instructions,
            'description' => $this->description,
            'allowed_tools' => $this->allowed_tools,
            'metadata' => $this->metadata,
        ], static fn ($v) => null !== $v);
    }
}
