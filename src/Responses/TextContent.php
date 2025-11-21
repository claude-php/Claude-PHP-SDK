<?php

declare(strict_types=1);

namespace ClaudePhp\Responses;

/**
 * Represents a text content block in a message
 */
class TextContent
{
    /**
     * @param string $text The text content
     */
    public function __construct(
        public readonly string $text,
    ) {
    }
}
