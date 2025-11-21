<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Citation web search result location
 *
 * @readonly
 */
class CitationsWebSearchResultLocation
{
    /**
     * @param int $result_index The index of the result in the web search results
     */
    public function __construct(
        public readonly int $result_index,
    ) {
    }
}
