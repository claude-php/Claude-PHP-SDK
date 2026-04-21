<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta;

class BetaTextEditorCodeExecutionToolResultError
{
    public function __construct(
        public readonly string $type = 'text_editor_code_execution_tool_result_error',
        public readonly ?string $error_code = null,
        public readonly ?string $message = null,
    ) {
    }
}
