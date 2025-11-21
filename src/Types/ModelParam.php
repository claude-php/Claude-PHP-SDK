<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Model parameter for API requests
 *
 * @readonly
 */
class ModelParam
{
    /**
     * @param string $model The model identifier
     */
    public function __construct(
        public readonly string $model,
    ) {
    }
}
