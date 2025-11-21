<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Signature delta
 */
class SignatureDelta
{
    public function __construct(
        public readonly string $type,
        public readonly string $signature_prefix,
    ) {
    }
}
