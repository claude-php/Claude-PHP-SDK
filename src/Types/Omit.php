<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Marker class used to indicate that a field should be omitted from requests.
 *
 * Similar to NotGiven but with different semantics for API operations.
 *
 * @internal
 */
final class Omit
{
    private static ?self $instance = null;

    private function __construct()
    {
    }

    /**
     * Get the singleton instance of Omit.
     */
    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
