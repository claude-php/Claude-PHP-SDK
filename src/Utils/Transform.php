<?php

declare(strict_types=1);

namespace ClaudePhp\Utils;

use DateTimeInterface;

/**
 * Data transformation utilities for request/response processing.
 *
 * Transforms data based on type information and metadata annotations,
 * including field aliasing and custom formatting.
 */
final class Transform
{
    /**
     * Transform data based on expected type information.
     *
     * Handles field aliasing (snake_case to camelCase), custom formatting,
     * and recursive transformation of nested structures.
     *
     * @template T
     *
     * @param T $data The data to transform
     * @param array<string, mixed> $typeHints Type information for fields
     *
     * @return T The transformed data
     */
    public static function transform(mixed $data, array $typeHints): mixed
    {
        if (null === $data) {
            return null;
        }

        if (!Utils::isMapping($data)) {
            return $data;
        }

        $result = [];
        foreach ($data as $key => $value) {
            $typeInfo = $typeHints[$key] ?? null;

            // Transform the key if there's an alias
            $transformedKey = self::transformKey($key, $typeInfo);

            // Transform the value
            $result[$transformedKey] = self::transformValue($value, $typeInfo);
        }

        return $result;
    }

    /**
     * Format data according to the specified format type.
     *
     * @param mixed $data The data to format
     * @param string $format Format type: 'iso8601', 'base64', or 'custom'
     * @param null|string $formatTemplate Custom format template for dates
     *
     * @return mixed The formatted data
     */
    public static function formatData(mixed $data, string $format, ?string $formatTemplate = null): mixed
    {
        if ($data instanceof DateTimeInterface) {
            if ('iso8601' === $format) {
                return $data->format(DateTimeInterface::ATOM);
            }

            if ('custom' === $format && null !== $formatTemplate) {
                return $data->format($formatTemplate);
            }
        }

        if ('base64' === $format && is_string($data)) {
            return base64_encode($data);
        }

        if ('base64' === $format && is_resource($data)) {
            $content = stream_get_contents($data);

            return is_string($content) ? base64_encode($content) : $data;
        }

        return $data;
    }

    /**
     * Strip NotGiven markers and null values from request parameters.
     *
     * @param array<string, mixed> $params Request parameters
     *
     * @return array<string, mixed> Cleaned parameters
     */
    public static function cleanRequestParams(array $params): array
    {
        return SpecialTypeUtils::stripNotGiven($params) ?? [];
    }

    /**
     * Merge additional parameters into a request.
     *
     * @param array<string, mixed> $base Base parameters
     * @param array<string, mixed> $additional Additional parameters to merge
     * @param array<string, mixed> $typeHints Type information for transformation
     *
     * @return array<string, mixed> Merged and transformed parameters
     */
    public static function mergeParams(
        array $base,
        array $additional,
        array $typeHints = [],
    ): array {
        $merged = array_merge($base, $additional);

        return self::transform($merged, $typeHints);
    }

    /**
     * Transform a single key based on PropertyInfo metadata.
     *
     * @param string $key The original key
     * @param mixed $typeInfo Type information (PropertyInfo or null)
     *
     * @return string The transformed key
     */
    private static function transformKey(string $key, mixed $typeInfo): string
    {
        if ($typeInfo instanceof PropertyInfo && null !== $typeInfo->alias) {
            return $typeInfo->alias;
        }

        return $key;
    }

    /**
     * Transform a single value based on type information and formatting.
     *
     * @param mixed $value The value to transform
     * @param mixed $typeInfo Type information (PropertyInfo or null)
     *
     * @return mixed The transformed value
     */
    private static function transformValue(mixed $value, mixed $typeInfo): mixed
    {
        if (null === $value) {
            return null;
        }

        // Apply formatting if specified (before recursive transformation)
        if ($typeInfo instanceof PropertyInfo && null !== $typeInfo->format) {
            return self::formatData($value, $typeInfo->format, $typeInfo->formatTemplate);
        }

        // Handle nested structures
        if (Utils::isMapping($value)) {
            // Recursively transform nested objects
            return self::transform($value, []);
        }

        if (Utils::isList($value)) {
            return array_map(static fn ($item) => self::transformValue($item, null), $value);
        }

        return $value;
    }
}
