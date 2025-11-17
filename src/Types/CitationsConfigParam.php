<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Citations config param
 */
class CitationsConfigParam
{
    public function __construct(
        public readonly string $type,
        public readonly ?bool $enabled = null,
    ) {}
}
