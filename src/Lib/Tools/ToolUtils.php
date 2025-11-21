<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Tools;

/**
 * Utility functions for tool/function calling.
 */
class ToolUtils
{
    /**
     * Convert a PHP callable to a tool definition suitable for the API.
     *
     * @param string $name Tool name
     * @param string $description Tool description
     * @param array<string, mixed> $inputSchema JSON schema for tool input parameters
     *
     * @return array<string, mixed> Tool definition
     */
    public static function defineTool(
        string $name,
        string $description,
        array $inputSchema,
    ): array {
        return [
            'name' => $name,
            'description' => $description,
            'input_schema' => $inputSchema,
        ];
    }

    /**
     * Create a simple tool definition with string parameter.
     *
     * @param string $name Tool name
     * @param string $description Tool description
     *
     * @return array<string, mixed> Tool definition
     */
    public static function simpleStringTool(string $name, string $description): array
    {
        return self::defineTool($name, $description, [
            'type' => 'object',
            'properties' => [
                'input' => [
                    'type' => 'string',
                    'description' => 'Input text',
                ],
            ],
            'required' => ['input'],
        ]);
    }

    /**
     * Extract tool use from response content blocks.
     *
     * @param array<array<string, mixed>> $content Content blocks from Message
     *
     * @return array<array<string, mixed>> Tool use blocks only
     */
    public static function extractToolUses(array $content): array
    {
        $toolUses = [];
        foreach ($content as $block) {
            if (isset($block['type']) && 'tool_use' === $block['type']) {
                $toolUses[] = $block;
            }
        }

        return $toolUses;
    }

    /**
     * Check if response contains tool use.
     *
     * @param array<array<string, mixed>> $content Content blocks from Message
     *
     * @return bool True if any tool_use blocks present
     */
    public static function hasToolUse(array $content): bool
    {
        return !empty(self::extractToolUses($content));
    }

    /**
     * Build tool result content block.
     *
     * @param string $toolUseId The tool_use ID
     * @param string $result The tool execution result
     * @param bool $isError Whether this is an error result
     *
     * @return array<string, mixed> Tool result content block
     */
    public static function buildToolResult(
        string $toolUseId,
        string $result,
        bool $isError = false,
    ): array {
        return [
            'type' => 'tool_result',
            'tool_use_id' => $toolUseId,
            'content' => $result,
            'is_error' => $isError,
        ];
    }

    /**
     * Build a user message with tool results.
     *
     * @param array<array<string, mixed>> $toolResults Tool result blocks
     *
     * @return array<string, mixed> User message
     */
    public static function buildToolResultMessage(array $toolResults): array
    {
        return [
            'role' => 'user',
            'content' => $toolResults,
        ];
    }
}
