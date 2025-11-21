<?php

declare(strict_types=1);

namespace ClaudePhp\Utils;

use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

/**
 * Type checking and reflection utilities.
 *
 * Provides helpers for working with PHP types and type hints.
 */
final class TypeUtils
{
    /**
     * Check if a type is a union type.
     */
    public static function isUnionType(?ReflectionType $type): bool
    {
        return $type instanceof ReflectionUnionType;
    }

    /**
     * Check if a type is a named type (not union or intersection).
     */
    public static function isNamedType(?ReflectionType $type): bool
    {
        return $type instanceof ReflectionNamedType;
    }

    /**
     * Extract the class name from a named type.
     */
    public static function getTypeName(ReflectionNamedType $type): string
    {
        return $type->getName();
    }

    /**
     * Check if a type allows null (is nullable).
     */
    public static function isNullableType(?ReflectionType $type): bool
    {
        if (null === $type) {
            return true;
        }

        if ($type->allowsNull()) {
            return true;
        }

        return false;
    }

    /**
     * Get the type names from a union type.
     *
     * @return string[]
     */
    public static function getUnionTypeNames(ReflectionUnionType $type): array
    {
        $names = [];
        foreach ($type->getTypes() as $t) {
            if ($t instanceof ReflectionNamedType) {
                $names[] = $t->getName();
            }
        }

        return $names;
    }

    /**
     * Check if a variable matches a type string.
     *
     * Supports basic type strings like 'string', 'int', 'array', 'object', etc.
     */
    public static function matchesType(mixed $value, string $type): bool
    {
        return match ($type) {
            'string' => is_string($value),
            'int', 'integer' => is_int($value),
            'float', 'double' => is_float($value),
            'bool', 'boolean' => is_bool($value),
            'array' => is_array($value),
            'object' => is_object($value),
            'null' => null === $value,
            'scalar' => is_scalar($value),
            'callable' => is_callable($value),
            'iterable' => is_iterable($value),
            'numeric' => is_numeric($value),
            default => $value instanceof $type,
        };
    }

    /**
     * Check if a type is a list type.
     */
    public static function isListType(mixed $type): bool
    {
        if ('list' === $type || 'array' === $type) {
            return true;
        }

        if (is_string($type) && str_starts_with($type, 'array<')) {
            return true;
        }

        return false;
    }

    /**
     * Check if a type is a sequence type.
     */
    public static function isSequenceType(mixed $type): bool
    {
        return self::isListType($type);
    }

    /**
     * Check if a type is an iterable type.
     */
    public static function isIterableType(mixed $type): bool
    {
        if ('iterable' === $type) {
            return true;
        }

        return false;
    }

    /**
     * Extract type argument from generic type.
     *
     * For example, extracts 'string' from 'array<int, string>'.
     *
     * @param string $type The generic type string
     * @param int $index The index of the argument to extract
     *
     * @return null|string The type argument or null if not found
     */
    public static function extractTypeArg(string $type, int $index = 0): ?string
    {
        if (!preg_match('/^(\w+)<(.+)>$/', $type, $matches)) {
            return null;
        }

        $args = array_map('trim', explode(',', $matches[2]));

        return $args[$index] ?? null;
    }

    /**
     * Check if a type is a TypedDict (associative array with known keys).
     */
    public static function isTypeDict(mixed $type): bool
    {
        if (!is_array($type)) {
            return false;
        }

        // If it's an array of type hints, treat it as a TypedDict
        return true;
    }

    /**
     * Check if a type is annotated with metadata.
     */
    public static function isAnnotatedType(mixed $type): bool
    {
        if ($type instanceof PropertyInfo) {
            return true;
        }

        if (is_array($type) && isset($type['__metadata__'])) {
            return true;
        }

        return false;
    }

    /**
     * Strip annotation wrapper from a type, returning the underlying type.
     *
     * @return mixed The unwrapped type
     */
    public static function stripAnnotatedType(mixed $type): mixed
    {
        if ($type instanceof PropertyInfo) {
            return $type;
        }

        if (is_array($type) && isset($type['__type__'])) {
            return $type['__type__'];
        }

        return $type;
    }
}
