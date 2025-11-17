<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Search result block parameter
 *
 * @readonly
 */
class SearchResultBlockParam
{
    /**
     * @param string $type The type identifier ("tool_result")
     * @param string $tool_use_id The ID of the search tool use
     * @param array<array<string, mixed>>|string $content The search result content
     * @param bool|null $is_error Whether the search resulted in an error
     */
    public function __construct(
        public readonly string $type,
        public readonly string $tool_use_id,
        public readonly array|string $content,
        public readonly ?bool $is_error = null,
    ) {}
}
