<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

class BetaContainerUploadBlockParam
{
    public function __construct(
        public readonly string $type = 'container_upload',
        public readonly string $file_id = '',
    ) {
    }
}
