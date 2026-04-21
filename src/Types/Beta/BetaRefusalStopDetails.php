<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta structured stop details when the model refuses to complete.
 */
class BetaRefusalStopDetails
{
    public function __construct(
        public readonly string $type = 'refusal',
        public readonly ?string $category = null,
        public readonly ?string $explanation = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] ?? 'refusal',
            category: $data['category'] ?? null,
            explanation: $data['explanation'] ?? null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'category' => $this->category,
            'explanation' => $this->explanation,
        ], static fn ($v) => null !== $v);
    }
}
