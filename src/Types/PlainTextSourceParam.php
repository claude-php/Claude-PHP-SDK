<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Request parameter for PlainTextSource
 *
 * @readonly
 */
class PlainTextSourceParam
{
    /**
     * @param string $type The type identifier ("text")
     * @param string $text The plain text content
     * @param null|string $title Optional title for the text
     */
    public function __construct(
        public readonly string $type,
        public readonly string $text,
        public readonly ?string $title = null,
    ) {
    }
}
