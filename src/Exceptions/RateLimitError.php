<?php

declare(strict_types=1);

namespace ClaudePhp\Exceptions;

/**
 * Exception thrown for 429 rate limit errors
 */
class RateLimitError extends APIStatusError
{
}
