<?php

declare(strict_types=1);

namespace ClaudePhp\Types;

/**
 * Marker class used to distinguish between "not provided" and null values.
 *
 * This allows SDK functions to differentiate between:
 * - A parameter not being provided at all (NotGiven)
 * - A parameter being explicitly set to null (null)
 *
 * @internal
 */
final class NotGiven
{
    private static ?self $instance = null;

    private function __construct()
    {
    }

    /**
     * Get the singleton instance of NotGiven.
     */
    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
