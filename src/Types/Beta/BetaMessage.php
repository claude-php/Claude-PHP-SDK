<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta message response
 */
class BetaMessage
{
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly string $role,
        public readonly array $content,
        public readonly string $model,
        public readonly string $stop_reason,
        public readonly ?string $stop_sequence = null,
        public readonly ?array $usage = null,
    ) {}
}
