<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Memory tool "str_replace" command — replace text in a file.
 */
class BetaMemoryTool20250818StrReplaceCommand
{
    /**
     * @param string $path     Path to the file where text should be replaced
     * @param string $old_str  Text to search for and replace
     * @param string $new_str  Text to replace with
     * @param string $command  Always "str_replace"
     */
    public function __construct(
        public readonly string $path,
        public readonly string $old_str,
        public readonly string $new_str,
        public readonly string $command = 'str_replace',
    ) {
    }
}
