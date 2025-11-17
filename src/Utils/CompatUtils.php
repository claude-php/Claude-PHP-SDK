<?php

declare(strict_types=1);

namespace ClaudePhp\Utils;

use ReflectionClass;
use ReflectionNamedType;
use ReflectionUnionType;
use ReflectionIntersectionType;

/**
 * Compatibility utilities for runtime type introspection.
 *
 * Provides helpers for extracting type information from PHP type declarations,
 * similar to typing_extensions functions in Python SDK.
 */
final class CompatUtils
{
    /**
     * Extract type arguments from a generic/union type.
     *
     * For PHP, this works with ReflectionType objects to extract component types.
     *
     * @param ReflectionType|string|null $type The type to extract from
     * @return class-string[]
     */
    public static function getArgs(mixed $type): array
    {
        if ($type === null) {
            return [];
        }

        if (is_string($type)) {
            return self::getArgsFromString($type);
        }

        if ($type instanceof ReflectionUnionType) {
            $types = [];
            foreach ($type->getTypes() as $t) {
                $types[] = $t->getName();
            }
            return $types;
        }

        if ($type instanceof ReflectionIntersectionType) {
            $types = [];
            foreach ($type->getTypes() as $t) {
                $types[] = $t->getName();
            }
            return $types;
        }

        if ($type instanceof ReflectionNamedType) {
            return [$type->getName()];
        }

        return [];
    }

    /**
     * Extract the origin/base type from a generic type string.
     *
     * For example:
     * - "List[string]" → "list"
     * - "Dict[string, int]" → "dict"
     * - "Union[string, int]" → "union"
     * - "string" → "string"
     *
     * @param mixed $type The type to get origin from
     * @return string|null
     */
    public static function getOrigin(mixed $type): ?string
    {
        if ($type === null) {
            return null;
        }

        if (is_string($type)) {
            return self::getOriginFromString($type);
        }

        if ($type instanceof ReflectionUnionType) {
            return 'union';
        }

        if ($type instanceof ReflectionIntersectionType) {
            return 'intersection';
        }

        if ($type instanceof ReflectionNamedType) {
            $name = $type->getName();

            // Map PHP built-in types to their origins
            return match ($name) {
                'array' => 'array',
                'iterable' => 'iterable',
                'object' => 'object',
                'mixed' => 'mixed',
                default => $name,
            };
        }

        return null;
    }

    /**
     * Check if a type is a Union type.
     *
     * @param mixed $type The type to check
     * @return bool
     */
    public static function isUnion(mixed $type): bool
    {
        if ($type instanceof ReflectionUnionType) {
            return true;
        }

        if (is_string($type)) {
            return str_contains($type, '|');
        }

        return false;
    }

    /**
     * Check if a type is a TypedDict type.
     *
     * In PHP, TypedDict is typically represented as a class with a specific marker
     * or as a class that extends from a base class. Since PHP doesn't have true
     * TypedDict, we check for classes that might be used as typed dictionaries.
     *
     * @param mixed $type The type to check
     * @return bool
     */
    public static function isTypedDict(mixed $type): bool
    {
        if (is_string($type)) {
            try {
                $type = new ReflectionClass($type);
            } catch (\ReflectionException) {
                return false;
            }
        }

        if (!$type instanceof ReflectionClass) {
            return false;
        }

        // Check if class has a marker attribute or method indicating it's a TypedDict-like structure
        // For now, we check if it's a final class with a __toArray or similar method
        // or if it has the TypedDict marker attribute (if we add one)
        $attributes = $type->getAttributes();
        foreach ($attributes as $attr) {
            if (str_contains($attr->getName(), 'TypedDict')) {
                return true;
            }
        }

        // Check for common patterns that indicate TypedDict-like behavior
        return $type->isFinal() && count($type->getMethods()) === 0 && count($type->getProperties()) > 0;
    }

    /**
     * Check if a type is a Literal type.
     *
     * In PHP, literals are typically represented as specific string values in union types.
     * For example: 'pending'|'completed'|'failed'
     *
     * @param mixed $type The type to check
     * @return bool
     */
    public static function isLiteralType(mixed $type): bool
    {
        if (is_string($type)) {
            // Check if it looks like a literal union: 'value1'|'value2'
            $parts = explode('|', $type);
            if (count($parts) > 1) {
                foreach ($parts as $part) {
                    $part = trim($part);
                    // Check if part is quoted (string literal)
                    if ((str_starts_with($part, "'") && str_ends_with($part, "'")) ||
                        (str_starts_with($part, '"') && str_ends_with($part, '"'))
                    ) {
                        continue;
                    }
                    // If any part is not a literal, it's not a pure literal type
                    return false;
                }
                return true;
            }

            // Single quoted string could be a literal
            return (str_starts_with($type, "'") && str_ends_with($type, "'")) ||
                (str_starts_with($type, '"') && str_ends_with($type, '"'));
        }

        if ($type instanceof ReflectionUnionType) {
            // Check if all union members are string literals (represented as SingleQuotedString or similar)
            $types = $type->getTypes();
            return count($types) > 0 && count($types) < 5; // Heuristic: literal unions are small
        }

        return false;
    }

    /**
     * Extract type arguments from a string type declaration.
     *
     * @param string $typeString The type string to parse
     * @return class-string[]
     */
    private static function getArgsFromString(string $typeString): array
    {
        // Handle generic-like strings: "List<string>" or "Dict<string, int>"
        if (str_contains($typeString, '<')) {
            preg_match('/^([^<]+)<(.+)>$/', $typeString, $matches);
            if (isset($matches[2])) {
                $innerTypes = explode(',', $matches[2]);
                return array_map('trim', $innerTypes);
            }
        }

        // Handle union types: "string|int|null"
        if (str_contains($typeString, '|')) {
            $parts = explode('|', $typeString);
            return array_map('trim', $parts);
        }

        return [];
    }

    /**
     * Extract origin from a string type declaration.
     *
     * @param string $typeString The type string to parse
     * @return string|null
     */
    private static function getOriginFromString(string $typeString): ?string
    {
        // Handle generic-like strings: "List<string>"
        if (str_contains($typeString, '<')) {
            $base = explode('<', $typeString)[0];
            return strtolower(trim($base));
        }

        // Handle union types: return the first type as origin (typically used for variant discrimination)
        if (str_contains($typeString, '|')) {
            $parts = explode('|', $typeString);
            return trim($parts[0]);
        }

        return strtolower(trim($typeString));
    }
}
