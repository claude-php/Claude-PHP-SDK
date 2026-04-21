<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

class BetaTextEditorCodeExecutionToolResultBlockParam
{
    public function __construct(
        public readonly string $type = 'text_editor_code_execution_tool_result',
        public readonly ?string $tool_use_id = null,
        public readonly ?array $content = null,
        public readonly ?bool $is_error = null,
    ) {
    }
}
