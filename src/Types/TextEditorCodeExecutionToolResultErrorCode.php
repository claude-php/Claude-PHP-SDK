<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Error code constants for text editor code execution.
 */
final class TextEditorCodeExecutionToolResultErrorCode
{
    public const FILE_NOT_FOUND = 'file_not_found';
    public const PERMISSION_DENIED = 'permission_denied';
    public const INVALID_PATH = 'invalid_path';
    public const STR_NOT_FOUND = 'str_not_found';
    public const STR_AMBIGUOUS = 'str_ambiguous';
    public const UNKNOWN = 'unknown';
}
