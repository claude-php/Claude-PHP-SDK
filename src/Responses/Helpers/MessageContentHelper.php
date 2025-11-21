<?php

declare(strict_types=1);

namespace ClaudePhp\Responses\Helpers;

use ClaudePhp\Responses\Message;
use ClaudePhp\Responses\TextContent;
use ClaudePhp\Responses\ToolResultContent;
use ClaudePhp\Responses\ToolUseContent;

/**
 * Utility helpers for working with message content blocks.
 */
final class MessageContentHelper
{
    private function __construct()
    {
    }

    /**
     * Concatenate all text blocks into a single string.
     */
    public static function text(array|Message $message, string $glue = "\n\n"): string
    {
        $segments = array_map(
            static fn (TextContent $block): string => $block->text,
            self::textBlocks($message),
        );

        return implode($glue, $segments);
    }

    /**
     * @return list<TextContent>
     */
    public static function textBlocks(array|Message $message): array
    {
        return array_values(array_filter(
            self::hydratedBlocks($message),
            static fn ($block): bool => $block instanceof TextContent,
        ));
    }

    /**
     * @return list<ToolUseContent>
     */
    public static function toolUses(array|Message $message): array
    {
        return array_values(array_filter(
            self::hydratedBlocks($message),
            static fn ($block): bool => $block instanceof ToolUseContent,
        ));
    }

    /**
     * @return list<ToolResultContent>
     */
    public static function toolResults(array|Message $message): array
    {
        return array_values(array_filter(
            self::hydratedBlocks($message),
            static fn ($block): bool => $block instanceof ToolResultContent,
        ));
    }

    /**
     * @return list<TextContent|ToolResultContent|ToolUseContent>
     */
    private static function hydratedBlocks(array|Message $message): array
    {
        $content = self::rawContent($message);

        $hydrated = [];
        foreach ($content as $block) {
            $valueObject = self::toValueObject($block);
            if (null !== $valueObject) {
                $hydrated[] = $valueObject;
            }
        }

        return $hydrated;
    }

    /**
     * @param array<string, mixed>|Message $message
     *
     * @return array<int, mixed>
     */
    private static function rawContent(array|Message $message): array
    {
        if ($message instanceof Message) {
            return $message->content;
        }

        $content = $message['content'] ?? [];

        return \is_array($content) ? $content : [];
    }

    private static function toValueObject(mixed $block): TextContent|ToolResultContent|ToolUseContent|null
    {
        if (
            $block instanceof TextContent
            || $block instanceof ToolUseContent
            || $block instanceof ToolResultContent
        ) {
            return $block;
        }

        if (!\is_array($block)) {
            return null;
        }

        return match ($block['type'] ?? null) {
            'text' => new TextContent(
                text: (string) ($block['text'] ?? ''),
            ),
            'tool_use' => new ToolUseContent(
                id: (string) ($block['id'] ?? ''),
                name: (string) ($block['name'] ?? ''),
                input: \is_array($block['input'] ?? null) ? $block['input'] : [],
            ),
            'tool_result' => new ToolResultContent(
                tool_use_id: (string) ($block['tool_use_id'] ?? ''),
                content: isset($block['content']) ? (string) $block['content'] : null,
                is_error: (bool) ($block['is_error'] ?? false),
            ),
            default => null,
        };
    }
}
