<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Model info
 */
class ModelInfo
{
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly ?string $display_name = null,
        public readonly ?int $max_tokens = null,
    ) {
    }
}
