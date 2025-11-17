<?php

declare(strict_types=1);

namespace ClaudePhp\Utils;

use ClaudePhp\Types\NotGiven;

/**
 * File extraction utilities for multipart requests.
 *
 * Extracts file objects from request parameters based on specified paths,
 * allowing files to be separated from other data for multipart encoding.
 */
final class FileExtraction
{
    /**
     * Recursively extract files from a query/data structure based on paths.
     *
     * Mutates the input array by removing extracted files.
     *
     * @param array<string, mixed> $query The data structure to extract from
     * @param string[][] $paths Paths to extract files from (e.g., [['files', '<array>'], ['avatar']])
     * @return array<array<string, mixed>> Array of [path, file_data] tuples
     */
    public static function extractFiles(array &$query, array $paths): array
    {
        $files = [];

        foreach ($paths as $path) {
            $extracted = self::extractPath($query, $path);
            $files = array_merge($files, $extracted);
        }

        return $files;
    }

    /**
     * Extract files following a single path.
     *
     * @param mixed $obj The object to extract from (modified by reference)
     * @param string[] $path The path to follow
     * @return array<array<string, mixed>>
     */
    private static function extractPath(mixed &$obj, array $path): array
    {
        if (empty($path)) {
            return [];
        }

        if (!Utils::isMapping($obj)) {
            return [];
        }

        return self::extractPathRecursive($obj, $path, 0, '');
    }

    /**
     * Recursive implementation of path following.
     *
     * @param mixed $obj Current object being processed (modified by reference)
     * @param string[] $path Full path to follow
     * @param int $index Current index in path
     * @param string $prefix Key prefix for flattened names
     * @return array<array<string, mixed>>
     */
    private static function extractPathRecursive(
        mixed &$obj,
        array $path,
        int $index,
        string $prefix
    ): array {
        if (!Utils::isMapping($obj)) {
            return [];
        }

        $key = $path[$index] ?? null;

        if ($key === null) {
            // Reached end of path
            return [];
        }

        if ($key === '<array>') {
            // Handle array iteration
            if (!Utils::isList($obj)) {
                return [];
            }

            $files = [];
            $newPrefix = $prefix ? "{$prefix}[]" : '[]';

            foreach ($obj as $i => $item) {
                $extracted = self::extractPathRecursive($item, $path, $index + 1, $newPrefix);
                $files = array_merge($files, $extracted);
            }

            return $files;
        }

        // Regular key access
        if (!isset($obj[$key])) {
            return [];
        }

        $newPrefix = $prefix ? "{$prefix}[{$key}]" : $key;
        $isLastKey = ($index === count($path) - 1);

        if ($isLastKey) {
            // This is the final key - extract the value
            $value = &$obj[$key];

            if (Utils::isList($value)) {
                $files = [];
                foreach ($value as $item) {
                    if ($item instanceof NotGiven || $item === NotGiven::getInstance()) {
                        continue;
                    }
                    $files[] = ["{$newPrefix}[]", $item];
                }
                unset($obj[$key]);
                return $files;
            }

            if ($value instanceof NotGiven || $value === NotGiven::getInstance()) {
                return [];
            }

            $files = [[$newPrefix, $value]];
            unset($obj[$key]);
            return $files;
        }

        // Intermediate key - recurse deeper
        $value = &$obj[$key];
        return self::extractPathRecursive($value, $path, $index + 1, $newPrefix);
    }
}
