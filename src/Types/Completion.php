<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Completion response from the Anthropic API (legacy/text completion)
 *
 * @readonly
 */
class Completion
{
    /**
     * @param string $id The unique identifier for the completion
     * @param string $type The type of object ("completion")
     * @param string $completion The completed text
     * @param string $stop_reason Why the model stopped generating text
     * @param null|string $model The model that generated the completion
     */
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly string $completion,
        public readonly string $stop_reason,
        public readonly ?string $model = null,
    ) {
    }
}
