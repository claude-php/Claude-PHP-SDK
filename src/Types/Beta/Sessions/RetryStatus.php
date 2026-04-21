<?php

declare(strict_types=1);

namespace ClaudePhp\Types\Beta\Sessions;

/**
 * Retry status discriminator constants.
 */
final class RetryStatus
{
    public const RETRYING = 'retrying';
    public const EXHAUSTED = 'exhausted';
    public const TERMINAL = 'terminal';
}
