<?php

declare(strict_types=1);

namespace ClaudePhp\Utils;

use ClaudePhp\Types\NotGiven;
use ClaudePhp\Types\Omit;

/**
 * Utilities for working with special SDK marker types.
 *
 * The SDK uses special types to distinguish between "not provided" and "null"
 * values in optional parameters.
 */
final class SpecialTypeUtils
{
    /**
     * Check if a value is the NotGiven marker.
     */
    public static function isNotGiven(mixed $value): bool
    {
        return $value instanceof NotGiven;
    }

    /**
     * Check if a value is the Omit marker.
     */
    public static function isOmit(mixed $value): bool
    {
        return $value instanceof Omit;
    }

    /**
     * Check if a value is given (not NotGiven and not Omit).
     *
     * @template T
     */
    public static function isGiven(mixed $value): bool
    {
        return !self::isNotGiven($value) && !self::isOmit($value);
    }

    /**
     * Strip NotGiven values from a mapping, keeping only provided values.
     *
     * @param null|array<string, mixed> $mapping
     *
     * @return null|array<string, mixed>
     */
    public static function stripNotGiven(?array $mapping): ?array
    {
        if (null === $mapping) {
            return null;
        }

        $result = [];
        foreach ($mapping as $key => $value) {
            if (!self::isNotGiven($value)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Strip both NotGiven and Omit values from a mapping.
     *
     * @param null|array<string, mixed> $mapping
     *
     * @return null|array<string, mixed>
     */
    public static function stripSpecialMarkers(?array $mapping): ?array
    {
        if (null === $mapping) {
            return null;
        }

        $result = [];
        foreach ($mapping as $key => $value) {
            if (self::isGiven($value)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
