<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\Vaults;

class StaticBearerUpdateParams
{
    public function __construct(
        public readonly ?string $token = null,
        public readonly ?string $display_name = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'token' => $this->token,
            'display_name' => $this->display_name,
        ], static fn ($v) => null !== $v);
    }
}
