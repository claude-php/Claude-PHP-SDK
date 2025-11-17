<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Parse;

use ClaudePhp\Responses\Message;

/**
 * Parser for structured output responses.
 *
 * Handles parsing and validating responses with structured output schemas.
 * Supports both flat JSON and nested object structures.
 */
class ResponseParser
{
    /**
     * Parse and validate a structured output response.
     *
     * @param Message $message The message response from Claude
     * @param array<string, mixed> $schema The response schema (JSON Schema format)
     * @return array<string, mixed> Parsed and validated response data
     * @throws \RuntimeException If parsing fails or validation fails
     */
    public static function parse(Message $message, array $schema): array
    {
        // Extract the text content
        $textContent = self::extractTextContent($message);

        if (empty($textContent)) {
            throw new \RuntimeException('No text content in message to parse');
        }

        return self::parseText($textContent, $schema);
    }

    /**
     * Parse an arbitrary JSON string against a schema.
     *
     * @param string $text JSON text
     * @param array<string, mixed> $schema JSON schema
     * @return array<string, mixed>
     */
    public static function parseText(string $text, array $schema): array
    {
        try {
            $data = \json_decode($text, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException('Failed to parse JSON response: ' . $e->getMessage(), 0, $e);
        }

        if (!\is_array($data)) {
            throw new \RuntimeException('Response must be a JSON object');
        }

        self::validateAgainstSchema($data, $schema);

        return $data;
    }

    /**
     * Attempt to parse JSON text, returning null if the payload is incomplete.
     *
     * @param string $text JSON text (possibly partial)
     * @param array<string, mixed> $schema JSON schema
     * @return array<string, mixed>|null
     */
    public static function tryParseText(string $text, array $schema): ?array
    {
        try {
            return self::parseText($text, $schema);
        } catch (\RuntimeException $e) {
            return null;
        }
    }

    /**
     * Extract all text content from message.
     *
     * @param Message $message
     * @return string Concatenated text content
     */
    private static function extractTextContent(Message $message): string
    {
        $text = '';
        foreach ($message->content ?? [] as $block) {
            if (isset($block['type']) && $block['type'] === 'text') {
                $text .= $block['text'] ?? '';
            }
        }
        return $text;
    }

    /**
     * Validate data against a JSON Schema.
     *
     * @param mixed $data The data to validate
     * @param array<string, mixed> $schema The JSON schema
     * @throws \RuntimeException If validation fails
     */
    private static function validateAgainstSchema(mixed $data, array $schema): void
    {
        $type = $schema['type'] ?? null;

        // Validate type
        if ($type && !self::validateType($data, $type)) {
            throw new \RuntimeException(
                "Type mismatch: expected $type, got " . \gettype($data)
            );
        }

        // Validate object properties
        if ($type === 'object' && \is_array($data)) {
            $properties = $schema['properties'] ?? [];
            $required = $schema['required'] ?? [];

            // Check required properties
            foreach ($required as $requiredProp) {
                if (!isset($data[$requiredProp])) {
                    throw new \RuntimeException("Missing required property: $requiredProp");
                }
            }

            // Validate each property
            foreach ($properties as $propName => $propSchema) {
                if (isset($data[$propName])) {
                    self::validateAgainstSchema($data[$propName], $propSchema);
                }
            }
        }

        // Validate array items
        if ($type === 'array' && \is_array($data)) {
            $itemsSchema = $schema['items'] ?? [];
            foreach ($data as $item) {
                if (!empty($itemsSchema)) {
                    self::validateAgainstSchema($item, $itemsSchema);
                }
            }
        }
    }

    /**
     * Validate data type against schema type specification.
     *
     * @param mixed $data
     * @param string|array<string> $type
     * @return bool
     */
    private static function validateType(mixed $data, string|array $type): bool
    {
        if (\is_string($type)) {
            return match ($type) {
                'string' => \is_string($data),
                'number' => \is_numeric($data),
                'integer' => \is_int($data),
                'boolean' => \is_bool($data),
                'array' => \is_array($data),
                'object' => \is_array($data),
                'null' => $data === null,
                default => true,
            };
        }

        // Handle union types (array of types)
        foreach ($type as $t) {
            if (self::validateType($data, $t)) {
                return true;
            }
        }

        return false;
    }
}
