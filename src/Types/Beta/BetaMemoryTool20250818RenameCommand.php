<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Memory tool "rename" command — rename or move a file or directory.
 */
class BetaMemoryTool20250818RenameCommand
{
    /**
     * @param string $old_path Current path of the file or directory
     * @param string $new_path New path for the file or directory
     * @param string $command  Always "rename"
     */
    public function __construct(
        public readonly string $old_path,
        public readonly string $new_path,
        public readonly string $command = 'rename',
    ) {
    }
}
