<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Parameter type for refusal stop details in requests/stream events.
 */
class RefusalStopDetailsParam
{
    public function __construct(
        public readonly string $type = 'refusal',
        public readonly ?string $category = null,
        public readonly ?string $explanation = null,
    ) {
    }
}
