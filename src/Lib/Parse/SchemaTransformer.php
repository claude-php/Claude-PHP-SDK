<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Parse;

/**
 * Schema transformer for converting PHP types to JSON Schema format.
 *
 * Helps convert PHP class/interface definitions to JSON Schema
 * suitable for use with Claude's structured output feature.
 */
class SchemaTransformer
{
    /**
     * Transform a PHP class or reflection type to JSON Schema.
     *
     * @param \ReflectionClass<object>|string $class Class name or ReflectionClass
     *
     * @return array<string, mixed> JSON Schema representation
     */
    public static function fromClass(\ReflectionClass|string $class): array
    {
        if (\is_string($class)) {
            $class = new \ReflectionClass($class);
        }

        $schema = [
            'type' => 'object',
            'title' => $class->getShortName(),
            'properties' => [],
            'required' => [],
        ];

        // Extract properties from constructor parameters if available
        $constructor = $class->getConstructor();
        if (null !== $constructor) {
            foreach ($constructor->getParameters() as $param) {
                $propName = $param->getName();
                $propType = $param->getType();

                $schema['properties'][$propName] = self::typeToSchema($propType);

                if (!$param->isOptional()) {
                    $schema['required'][] = $propName;
                }
            }
        }

        return $schema;
    }

    /**
     * Build a simple object schema from property definitions.
     *
     * @param array<string, string> $properties Map of property name to type
     * @param array<string> $required Required property names
     * @param null|string $title Schema title
     *
     * @return array<string, mixed> JSON Schema
     */
    public static function buildObjectSchema(
        array $properties,
        array $required = [],
        ?string $title = null,
    ): array {
        $schema = [
            'type' => 'object',
            'properties' => [],
            'required' => $required,
        ];

        if (null !== $title) {
            $schema['title'] = $title;
        }

        foreach ($properties as $name => $type) {
            $schema['properties'][$name] = self::parseType($type);
        }

        return $schema;
    }

    /**
     * Parse a type string and return JSON Schema type.
     *
     * @param string $type Type string (e.g., 'string', 'array[string]', 'int', etc.)
     *
     * @return array<string, mixed> JSON Schema type definition
     */
    public static function parseType(string $type): array
    {
        $type = \trim($type);

        // Handle array types
        if (\str_ends_with($type, ']')) {
            \preg_match('/^array\[(.+)\]$/i', $type, $matches);
            if (!empty($matches[1])) {
                return [
                    'type' => 'array',
                    'items' => self::parseType($matches[1]),
                ];
            }

            return ['type' => 'array'];
        }

        // Handle basic types
        return match (\strtolower($type)) {
            'string', 'str' => ['type' => 'string'],
            'int', 'integer' => ['type' => 'integer'],
            'float', 'double', 'number' => ['type' => 'number'],
            'bool', 'boolean' => ['type' => 'boolean'],
            'array' => ['type' => 'array'],
            'object' => ['type' => 'object'],
            'null' => ['type' => 'null'],
            default => ['type' => 'string'],
        };
    }

    /**
     * Convert a PHP type to JSON Schema.
     *
     * @param null|\ReflectionType $type The PHP reflection type
     *
     * @return array<string, mixed> JSON Schema
     */
    private static function typeToSchema(?\ReflectionType $type): array
    {
        if (null === $type) {
            return ['type' => 'string']; // Default to string
        }

        // Handle union types (e.g., string|int)
        if ($type instanceof \ReflectionUnionType) {
            $types = [];
            foreach ($type->getTypes() as $t) {
                $types[] = self::getJsonSchemaType($t);
            }

            return ['type' => $types];
        }

        // Handle named types
        if ($type instanceof \ReflectionNamedType) {
            return self::getJsonSchemaTypeForNamedType($type);
        }

        return ['type' => 'string'];
    }

    /**
     * Get JSON Schema type for a named reflection type.
     *
     * @return array<string, mixed>
     */
    private static function getJsonSchemaTypeForNamedType(\ReflectionNamedType $type): array
    {
        $typeName = $type->getName();
        $builtinType = self::getJsonSchemaType($type);

        // If it's a class, include it
        if (!$type->isBuiltin()) {
            try {
                $class = new \ReflectionClass($typeName);

                return [
                    'type' => 'object',
                    'title' => $class->getShortName(),
                    'properties' => [],
                ];
            } catch (\ReflectionException) {
                // Return as string type if class not found
                return ['type' => $builtinType];
            }
        }

        return ['type' => $builtinType];
    }

    /**
     * Get JSON Schema type string for a reflection type.
     */
    private static function getJsonSchemaType(\ReflectionType $type): string
    {
        if ($type instanceof \ReflectionNamedType) {
            return match ($type->getName()) {
                'string' => 'string',
                'int', 'integer' => 'integer',
                'float', 'double' => 'number',
                'bool', 'boolean' => 'boolean',
                'array' => 'array',
                'object' => 'object',
                default => 'object',
            };
        }

        return 'string';
    }
}
