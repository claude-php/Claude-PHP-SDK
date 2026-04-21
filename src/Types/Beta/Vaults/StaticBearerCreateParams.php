<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\Vaults;

class StaticBearerCreateParams
{
    public function __construct(
        public readonly string $type = 'static_bearer',
        public readonly string $token = '',
        public readonly ?string $display_name = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'token' => $this->token,
            'display_name' => $this->display_name,
        ], static fn ($v) => null !== $v);
    }
}
