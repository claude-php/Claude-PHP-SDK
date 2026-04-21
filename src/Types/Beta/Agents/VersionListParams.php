<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\Agents;

class VersionListParams
{
    public function __construct(
        public readonly ?int $limit = null,
        public readonly ?string $before_id = null,
        public readonly ?string $after_id = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'limit' => $this->limit,
            'before_id' => $this->before_id,
            'after_id' => $this->after_id,
        ], static fn ($v) => null !== $v);
    }
}
