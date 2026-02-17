<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta web fetch tool result block parameter.
 */
class BetaWebFetchToolResultBlockParam
{
    /**
     * @param mixed       $content     Web fetch result or error block
     * @param string      $tool_use_id The ID of the tool_use block this result corresponds to
     * @param mixed|null  $caller      Information about what invoked this tool
     * @param string      $type        Always "web_fetch_tool_result"
     */
    public function __construct(
        public readonly mixed $content,
        public readonly string $tool_use_id,
        public readonly mixed $caller = null,
        public readonly string $type = 'web_fetch_tool_result',
    ) {
    }
}
