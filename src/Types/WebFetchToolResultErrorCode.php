<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Error codes for web fetch tool result errors.
 */
class WebFetchToolResultErrorCode
{
    public const INVALID_TOOL_INPUT = 'invalid_tool_input';
    public const URL_TOO_LONG = 'url_too_long';
    public const URL_NOT_ALLOWED = 'url_not_allowed';
    public const URL_NOT_ACCESSIBLE = 'url_not_accessible';
    public const UNSUPPORTED_CONTENT_TYPE = 'unsupported_content_type';
    public const TOO_MANY_REQUESTS = 'too_many_requests';
    public const MAX_USES_EXCEEDED = 'max_uses_exceeded';
    public const UNAVAILABLE = 'unavailable';

    public function __construct(
        public readonly string $value,
    ) {
    }
}
