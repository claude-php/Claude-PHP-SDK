<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Memory tool "delete" command — delete a file or directory.
 */
class BetaMemoryTool20250818DeleteCommand
{
    /**
     * @param string $path    Path to the file or directory to delete
     * @param string $command Always "delete"
     */
    public function __construct(
        public readonly string $path,
        public readonly string $command = 'delete',
    ) {
    }
}
