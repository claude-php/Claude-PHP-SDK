<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Tools;

/**
 * MCP (Model Context Protocol) conversion helpers.
 *
 * Converts MCP tool definitions, content, messages, and resources into
 * Anthropic Beta API shapes. MCP client integration is BYO — these helpers
 * only perform shape conversion.
 */
class Mcp
{
    /**
     * Convert an MCP tool definition to a Beta tool param array.
     *
     * @param array<string, mixed> $tool MCP tool definition (name, description, inputSchema)
     * @param array<string, mixed> $options Extra options (cache_control, defer_loading, allowed_callers, strict)
     * @return array<string, mixed> Beta tool param shape
     */
    public static function tool(array $tool, array $options = []): array
    {
        $result = [
            'name' => $tool['name'] ?? '',
            'description' => $tool['description'] ?? '',
            'input_schema' => $tool['inputSchema'] ?? $tool['input_schema'] ?? ['type' => 'object'],
        ];

        if (isset($options['cache_control'])) {
            $result['cache_control'] = $options['cache_control'];
        }
        if (isset($options['defer_loading'])) {
            $result['defer_loading'] = $options['defer_loading'];
        }
        if (isset($options['allowed_callers'])) {
            $result['allowed_callers'] = $options['allowed_callers'];
        }
        if (isset($options['strict'])) {
            $result['strict'] = $options['strict'];
        }

        return $result;
    }

    /**
     * Convert MCP content to a Beta content block array.
     *
     * @param array<string, mixed> $content MCP content block (type, text, etc.)
     * @param array<string, mixed>|null $cacheControl Optional cache control
     * @return array<string, mixed>
     */
    public static function content(array $content, ?array $cacheControl = null): array
    {
        $type = $content['type'] ?? 'text';

        $result = match ($type) {
            'text' => [
                'type' => 'text',
                'text' => $content['text'] ?? '',
            ],
            'image' => [
                'type' => 'image',
                'source' => [
                    'type' => 'base64',
                    'media_type' => $content['mimeType'] ?? 'image/png',
                    'data' => $content['data'] ?? '',
                ],
            ],
            'resource' => self::resourceContentToBlock($content),
            default => throw new UnsupportedMcpValueException(
                "Unsupported MCP content type: {$type}"
            ),
        };

        if (null !== $cacheControl) {
            $result['cache_control'] = $cacheControl;
        }

        return $result;
    }

    /**
     * Convert an MCP prompt message to an Anthropic message param array.
     *
     * @param array<string, mixed> $message MCP prompt message (role, content)
     * @param array<string, mixed>|null $cacheControl Optional cache control
     * @return array<string, mixed>
     */
    public static function message(array $message, ?array $cacheControl = null): array
    {
        $role = $message['role'] ?? 'user';
        $content = $message['content'] ?? [];

        if (\is_string($content)) {
            $contentBlocks = [['type' => 'text', 'text' => $content]];
        } elseif (\is_array($content) && isset($content['type'])) {
            $contentBlocks = [self::content($content, $cacheControl)];
        } elseif (\is_array($content)) {
            $contentBlocks = array_map(
                static fn ($c) => self::content($c, $cacheControl),
                $content,
            );
        } else {
            $contentBlocks = [];
        }

        return [
            'role' => $role,
            'content' => $contentBlocks,
        ];
    }

    /**
     * Convert an MCP resource read result to Beta content blocks.
     *
     * @param array<string, mixed> $result MCP ReadResourceResult (contents array)
     * @param array<string, mixed>|null $cacheControl Optional cache control
     * @return list<array<string, mixed>> Content blocks
     */
    public static function resourceToContent(array $result, ?array $cacheControl = null): array
    {
        $blocks = [];

        foreach ($result['contents'] ?? [] as $item) {
            $block = self::resourceContentToBlock($item);
            if (null !== $cacheControl) {
                $block['cache_control'] = $cacheControl;
            }
            $blocks[] = $block;
        }

        return $blocks;
    }

    /**
     * Convert an MCP resource read result to file-like tuple data.
     *
     * @param array<string, mixed> $result MCP ReadResourceResult
     * @return list<array{filename: string|null, data: string, mimeType: string|null}>
     */
    public static function resourceToFile(array $result): array
    {
        $files = [];

        foreach ($result['contents'] ?? [] as $item) {
            $uri = $item['uri'] ?? '';
            $filename = '' !== $uri ? basename($uri) : null;
            $mimeType = $item['mimeType'] ?? null;
            $data = $item['text'] ?? $item['blob'] ?? '';

            $files[] = [
                'filename' => $filename,
                'data' => $data,
                'mimeType' => $mimeType,
            ];
        }

        return $files;
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    private static function resourceContentToBlock(array $item): array
    {
        if (isset($item['blob'])) {
            return [
                'type' => 'image',
                'source' => [
                    'type' => 'base64',
                    'media_type' => $item['mimeType'] ?? 'application/octet-stream',
                    'data' => $item['blob'],
                ],
            ];
        }

        return [
            'type' => 'text',
            'text' => $item['text'] ?? '',
        ];
    }
}

/**
 * Exception for unsupported MCP values during conversion.
 */
class UnsupportedMcpValueException extends \RuntimeException
{
}
