<?php

declare(strict_types=1);

namespace ClaudePhp\Utils;

// Export all utility classes and functions for convenient access
// Use: use ClaudePhp\Utils\{ Utils, TypeUtils, DateTimeUtils, SpecialTypeUtils };

/**
 * Utility module index.
 *
 * This module provides helper functions and classes for:
 * - Type checking and narrowing (isDict, isList, isMapping, etc.)
 * - Data transformation (deepcopyMinimal, jsonSafe, transform, etc.)
 * - String manipulation (removePrefix, removeSuffix, humanJoin, etc.)
 * - Type coercion (coerceInteger, coerceFloat, coerceBoolean, etc.)
 * - DateTime parsing and formatting
 * - Special SDK type handling (NotGiven, Omit markers)
 * - Reflection and function inspection
 * - Stream consumption and iteration
 * - Request parameter transformation with PropertyInfo metadata
 * - File extraction for multipart requests
 * - Function argument validation
 * - Runtime type introspection (getArgs, getOrigin, isUnion, etc.)
 *
 * @example
 * ```php
 * use ClaudePhp\Utils\Utils;
 * use ClaudePhp\Utils\Transform;
 * use ClaudePhp\Utils\PropertyInfo;
 *
 * // Type checking
 * if (Utils::isMapping($data)) {
 *     // $data is an associative array
 * }
 *
 * // String manipulation
 * $joined = Utils::humanJoin(['a', 'b', 'c'], ', ', 'or');
 * // => "a, b, or c"
 *
 * // Data transformation
 * $safe = Utils::jsonSafe($data);
 *
 * // Request parameter transformation
 * $params = ['user_id' => 123];
 * $typeHints = ['user_id' => new PropertyInfo(alias: 'userId')];
 * $transformed = Transform::transform($params, $typeHints);
 * // => ['userId' => 123]
 * ```
 */
final class Index
{
    /**
     * Get all available utility classes.
     *
     * @return array<string, class-string>
     */
    public static function getUtilityClasses(): array
    {
        return [
            'Utils' => Utils::class,
            'TypeUtils' => TypeUtils::class,
            'DateTimeUtils' => DateTimeUtils::class,
            'SpecialTypeUtils' => SpecialTypeUtils::class,
            'AsyncUtils' => AsyncUtils::class,
            'LazyProxy' => LazyProxy::class,
            'Transform' => Transform::class,
            'PropertyInfo' => PropertyInfo::class,
            'Reflection' => Reflection::class,
            'Streams' => Streams::class,
            'FileExtraction' => FileExtraction::class,
            'RequiredArgs' => RequiredArgs::class,
            'CompatUtils' => CompatUtils::class,
        ];
    }
}
