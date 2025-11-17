<?php

declare(strict_types=1);

namespace ClaudePhp\Utils;

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
     *
     * @param mixed $value
     * @return bool
     */
    public static function isNotGiven(mixed $value): bool
    {
        return $value instanceof \ClaudePhp\Types\NotGiven;
    }

    /**
     * Check if a value is the Omit marker.
     *
     * @param mixed $value
     * @return bool
     */
    public static function isOmit(mixed $value): bool
    {
        return $value instanceof \ClaudePhp\Types\Omit;
    }

    /**
     * Check if a value is given (not NotGiven and not Omit).
     *
     * @template T
     * @param mixed $value
     * @return bool
     */
    public static function isGiven(mixed $value): bool
    {
        return !static::isNotGiven($value) && !static::isOmit($value);
    }

    /**
     * Strip NotGiven values from a mapping, keeping only provided values.
     *
     * @param array<string, mixed>|null $mapping
     * @return array<string, mixed>|null
     */
    public static function stripNotGiven(?array $mapping): ?array
    {
        if ($mapping === null) {
            return null;
        }

        $result = [];
        foreach ($mapping as $key => $value) {
            if (!static::isNotGiven($value)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Strip both NotGiven and Omit values from a mapping.
     *
     * @param array<string, mixed>|null $mapping
     * @return array<string, mixed>|null
     */
    public static function stripSpecialMarkers(?array $mapping): ?array
    {
        if ($mapping === null) {
            return null;
        }

        $result = [];
        foreach ($mapping as $key => $value) {
            if (static::isGiven($value)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
