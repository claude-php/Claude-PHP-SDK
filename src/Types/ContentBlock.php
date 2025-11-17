<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Base class for content blocks
 *
 * @readonly
 */
abstract class ContentBlock
{
    /**
     * @param string $type The type of content block
     */
    public function __construct(
        public readonly string $type,
    ) {}
}