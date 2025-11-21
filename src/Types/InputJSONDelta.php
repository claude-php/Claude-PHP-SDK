<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Input JSON delta during streaming
 */
class InputJSONDelta
{
    public function __construct(
        public readonly string $type,
        public readonly string $partial_json,
    ) {
    }
}
