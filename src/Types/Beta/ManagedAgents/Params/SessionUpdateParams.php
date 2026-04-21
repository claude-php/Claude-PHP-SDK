<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\ManagedAgents\Params;

class SessionUpdateParams
{
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?array $metadata = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'metadata' => $this->metadata,
        ], static fn ($v) => null !== $v);
    }
}
