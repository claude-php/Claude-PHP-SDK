<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Memory tool "view" command — view a file or directory.
 */
class BetaMemoryTool20250818ViewCommand
{
    /**
     * @param string    $path        Path to directory or file to view
     * @param string    $command     Always "view"
     * @param int[]|null $view_range Optional line range [start, end] for viewing specific lines
     */
    public function __construct(
        public readonly string $path,
        public readonly string $command = 'view',
        public readonly ?array $view_range = null,
    ) {
    }
}
