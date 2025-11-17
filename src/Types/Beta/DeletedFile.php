<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

/**
 * Beta deleted file
 */
class DeletedFile
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $type = null,
    ) {}
}
