<?php

declare(strict_types=1);

namespace ClaudePhp\Utils;

use DateTime;
use DateTimeInterface;
use Traversable;

/**
 * Collection of utility functions for the Claude PHP SDK.
 *
 * Provides type checking, data transformation, and validation helpers
 * that mirror functionality from the Python SDK.
 */
final class Utils
{
    /**
     * Flatten a multi-level iterable into a single level list.
     *
     * @param iterable<int, iterable<int, mixed>> $items
     *
     * @return list<mixed>
     */
    public static function flatten(iterable $items): array
    {
        $result = [];
        foreach ($items as $sublist) {
            foreach ($sublist as $item) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * Check if a value is a mapping/associative array.
     */
    public static function isMapping(mixed $obj): bool
    {
        return is_array($obj) && (empty($obj) || array_keys($obj) !== range(0, count($obj) - 1));
    }

    /**
     * Check if a value is a list/sequential array.
     */
    public static function isList(mixed $obj): bool
    {
        return is_array($obj) && (empty($obj) || array_keys($obj) === range(0, count($obj) - 1));
    }

    /**
     * Check if a value is a dict (associative array).
     */
    public static function isDict(mixed $obj): bool
    {
        return is_array($obj);
    }

    /**
     * Check if a value is a tuple-like structure.
     */
    public static function isTuple(mixed $obj): bool
    {
        return is_array($obj) && self::isList($obj);
    }

    /**
     * Check if a value is iterable.
     */
    public static function isIterable(mixed $obj): bool
    {
        return is_iterable($obj);
    }

    /**
     * Check if a value is a sequence (array or Traversable).
     */
    public static function isSequence(mixed $obj): bool
    {
        return is_array($obj) || $obj instanceof Traversable;
    }

    /**
     * Perform a minimal deep copy of a value.
     *
     * Only copies mappings and lists, leaves other types as-is.
     * This is more performant than full deepcopy.
     *
     * @template T
     *
     * @param T $item
     *
     * @return T
     */
    public static function deepcopyMinimal(mixed $item): mixed
    {
        if (self::isMapping($item)) {
            $result = [];
            foreach ($item as $k => $v) {
                $result[$k] = self::deepcopyMinimal($v);
            }

            return $result;
        }

        if (self::isList($item)) {
            $result = [];
            foreach ($item as $entry) {
                $result[] = self::deepcopyMinimal($entry);
            }

            return $result;
        }

        return $item;
    }

    /**
     * Join a sequence of strings with a delimiter and final connector.
     *
     * Example: humanJoin(['a', 'b', 'c'], ', ', 'or') => "a, b, or c"
     *
     * @param string[] $seq
     * @param string $delim The delimiter between items
     * @param string $final The connector before the last item
     */
    public static function humanJoin(array $seq, string $delim = ', ', string $final = 'or'): string
    {
        $size = count($seq);
        if (0 === $size) {
            return '';
        }

        if (1 === $size) {
            return $seq[0];
        }

        if (2 === $size) {
            return "{$seq[0]} {$final} {$seq[1]}";
        }

        $last = array_pop($seq);

        return implode($delim, $seq) . "{$delim}{$final} {$last}";
    }

    /**
     * Add single quotation marks around a string.
     *
     * Does not perform any escaping.
     */
    public static function quote(string $string): string
    {
        return "'{$string}'";
    }

    /**
     * Remove a prefix from a string.
     */
    public static function removePrefix(string $string, string $prefix): string
    {
        if (str_starts_with($string, $prefix)) {
            return substr($string, strlen($prefix));
        }

        return $string;
    }

    /**
     * Remove a suffix from a string.
     */
    public static function removeSuffix(string $string, string $suffix): string
    {
        if (str_ends_with($string, $suffix)) {
            return substr($string, 0, -strlen($suffix));
        }

        return $string;
    }

    /**
     * Coerce a string value to an integer.
     *
     * @throws \ValueError If the string cannot be converted to an integer
     */
    public static function coerceInteger(string $val): int
    {
        return (int) $val;
    }

    /**
     * Coerce a string value to a float.
     *
     * @throws \ValueError If the string cannot be converted to a float
     */
    public static function coerceFloat(string $val): float
    {
        return (float) $val;
    }

    /**
     * Coerce a string value to a boolean.
     *
     * Recognizes 'true', '1', 'on' as true values.
     */
    public static function coerceBoolean(string $val): bool
    {
        $lower = strtolower($val);

        return 'true' === $lower || '1' === $lower || 'on' === $lower;
    }

    /**
     * Coerce a nullable string value to a nullable integer.
     */
    public static function maybeCoerceInteger(?string $val): ?int
    {
        return null === $val ? null : self::coerceInteger($val);
    }

    /**
     * Coerce a nullable string value to a nullable float.
     */
    public static function maybeCoerceFloat(?string $val): ?float
    {
        return null === $val ? null : self::coerceFloat($val);
    }

    /**
     * Coerce a nullable string value to a nullable boolean.
     */
    public static function maybeCoerceBoolean(?string $val): ?bool
    {
        return null === $val ? null : self::coerceBoolean($val);
    }

    /**
     * Make a value JSON-safe by converting objects and special types to serializable format.
     *
     * Recursively processes mappings, sequences, DateTime objects, etc.
     */
    public static function jsonSafe(mixed $data): mixed
    {
        if (self::isMapping($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                $result[self::jsonSafe($key)] = self::jsonSafe($value);
            }

            return $result;
        }

        if (self::isIterable($data) && !is_string($data) && !$data instanceof DateTimeInterface) {
            $result = [];
            foreach ($data as $item) {
                $result[] = self::jsonSafe($item);
            }

            return $result;
        }

        if ($data instanceof DateTimeInterface) {
            return $data->format(DateTimeInterface::ATOM);
        }

        return $data;
    }

    /**
     * Get a required header from a headers mapping.
     *
     * Performs case-insensitive lookup with various formatting attempts.
     *
     * @param array<string, string> $headers
     * @param string $header The header name to find
     *
     * @throws \ValueError If the header is not found
     *
     * @return string The header value
     */
    public static function getRequiredHeader(array $headers, string $header): string
    {
        $lowerHeader = strtolower($header);

        // Try exact match first
        foreach ($headers as $k => $v) {
            if (strtolower($k) === $lowerHeader) {
                return (string) $v;
            }
        }

        // Try various transformations
        $transformations = [
            $header,
            $lowerHeader,
            strtoupper($header),
            ucfirst(strtolower($header)),
        ];

        foreach ($transformations as $normalized) {
            if (isset($headers[$normalized])) {
                return (string) $headers[$normalized];
            }
        }

        throw new \ValueError("Could not find {$header} header");
    }

    /**
     * Read a file from disk and return its contents.
     *
     * @param string $path The file path
     *
     * @return string The file contents
     */
    public static function fileFromPath(string $path): string
    {
        $contents = @file_get_contents($path);
        if (false === $contents) {
            throw new \RuntimeException("Could not read file at path: {$path}");
        }

        return $contents;
    }
}
