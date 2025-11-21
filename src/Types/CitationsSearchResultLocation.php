<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Citation search result location
 *
 * @readonly
 */
class CitationsSearchResultLocation
{
    /**
     * @param int $result_index The index of the result in the search results
     */
    public function __construct(
        public readonly int $result_index,
    ) {
    }
}
