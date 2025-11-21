<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta input JSON delta
 */
class BetaInputJSONDelta
{
    public function __construct(
        public readonly string $type,
        public readonly string $partial_json,
    ) {
    }
}
