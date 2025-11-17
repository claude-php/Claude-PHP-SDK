<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Files;

/**
 * File utilities for uploading and managing files with Claude API.
 *
 * Provides helpers for collecting files from directories and
 * formatting them for upload to the Files API.
 */
class FilesUtils
{
    /**
     * Collect files from a directory recursively.
     *
     * @param string $directory Directory path
     * @param string|null $skillPath Optional path to SKILL.md file (auto-detected if null)
     * @param array<string> $excludeExtensions File extensions to exclude
     * @return array<array<string, mixed>> Array of file definitions
     */
    public static function filesFromDir(
        string $directory,
        ?string $skillPath = null,
        array $excludeExtensions = []
    ): array {
        if (!\is_dir($directory)) {
            throw new \RuntimeException("Directory not found: $directory");
        }

        $files = [];
        $directory = \rtrim($directory, '/');

        // Look for SKILL.md if not provided
        if ($skillPath === null) {
            $skillPath = self::findSkillFile($directory);
        }

        // Recursively collect files
        foreach (self::iterateDirectory($directory) as $filePath) {
            if (self::shouldExcludeFile($filePath, $excludeExtensions)) {
                continue;
            }

            $files[] = self::createFileDefinition($filePath, $directory);
        }

        return $files;
    }

    /**
     * Collect files from a directory asynchronously.
     *
     * @param string $directory Directory path
     * @param string|null $skillPath Optional path to SKILL.md file
     * @param array<string> $excludeExtensions File extensions to exclude
     * @return \Amp\Future<array<array<string, mixed>>>
     */
    public static function asyncFilesFromDir(
        string $directory,
        ?string $skillPath = null,
        array $excludeExtensions = []
    ): \Amp\Future {
        return \Amp\async(function () use ($directory, $skillPath, $excludeExtensions) {
            return self::filesFromDir($directory, $skillPath, $excludeExtensions);
        });
    }

    /**
     * Find SKILL.md file in directory tree.
     *
     * @param string $directory Starting directory
     * @return string|null Path to SKILL.md or null if not found
     */
    private static function findSkillFile(string $directory): ?string
    {
        $skillPath = $directory . '/SKILL.md';
        if (\is_file($skillPath)) {
            return $skillPath;
        }

        // Check parent directories up to 3 levels
        for ($i = 0; $i < 3; $i++) {
            $directory = \dirname($directory);
            $skillPath = $directory . '/SKILL.md';
            if (\is_file($skillPath)) {
                return $skillPath;
            }
        }

        return null;
    }

    /**
     * Check if file should be excluded.
     *
     * @param string $filePath
     * @param array<string> $excludeExtensions
     * @return bool
     */
    private static function shouldExcludeFile(
        string $filePath,
        array $excludeExtensions
    ): bool {
        // Common files to exclude
        $defaultExcludes = ['.DS_Store', '.git', '.gitignore', 'node_modules', '.env'];
        foreach ($defaultExcludes as $exclude) {
            if (\strpos($filePath, $exclude) !== false) {
                return true;
            }
        }

        // Check extension
        $ext = \pathinfo($filePath, PATHINFO_EXTENSION);
        if (\in_array($ext, $excludeExtensions, true)) {
            return true;
        }

        return false;
    }

    /**
     * Iterate directory recursively.
     *
     * @param string $directory
     * @return \Generator<string>
     */
    private static function iterateDirectory(string $directory): \Generator
    {
        $items = @\scandir($directory);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . '/' . $item;

            if (\is_dir($path)) {
                yield from self::iterateDirectory($path);
            } elseif (\is_file($path)) {
                yield $path;
            }
        }
    }

    /**
     * Create a file definition for API upload.
     *
     * @param string $filePath Path to file
     * @param string $baseDir Base directory for relative path
     * @return array<string, mixed>
     */
    private static function createFileDefinition(string $filePath, string $baseDir): array
    {
        $relativePath = \substr($filePath, \strlen($baseDir) + 1);
        $contents = @\file_get_contents($filePath);

        if ($contents === false) {
            throw new \RuntimeException("Cannot read file: $filePath");
        }

        return [
            'filename' => \basename($filePath),
            'relative_path' => $relativePath,
            'absolute_path' => $filePath,
            'contents' => $contents,
            'size' => \strlen($contents),
            'mime_type' => self::getMimeType($filePath),
        ];
    }

    /**
     * Get MIME type for file.
     *
     * @param string $filePath
     * @return string
     */
    private static function getMimeType(string $filePath): string
    {
        $ext = \strtolower(\pathinfo($filePath, PATHINFO_EXTENSION));

        return match ($ext) {
            'pdf' => 'application/pdf',
            'txt' => 'text/plain',
            'md' => 'text/markdown',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'html' => 'text/html',
            'csv' => 'text/csv',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'zip' => 'application/zip',
            'tar' => 'application/x-tar',
            default => 'application/octet-stream',
        };
    }

    /**
     * Upload file via Files API.
     *
     * @param mixed $client The API client
     * @param string $filePath Path to file
     * @param string|null $mimeType Optional MIME type override
     * @return array<string, mixed> File response from API
     */
    public static function uploadFile(
        mixed $client,
        string $filePath,
        ?string $mimeType = null
    ): array {
        if (!\is_file($filePath)) {
            throw new \RuntimeException("File not found: $filePath");
        }

        $mimeType ??= self::getMimeType($filePath);

        return $client->files()->create([
            'file' => [
                'path' => $filePath,
                'mime_type' => $mimeType,
            ],
        ]);
    }

    /**
     * Upload files from directory via Files API.
     *
     * @param mixed $client The API client
     * @param string $directory Directory path
     * @return array<array<string, mixed>> Array of uploaded file responses
     */
    public static function uploadDirectory(
        mixed $client,
        string $directory
    ): array {
        $files = self::filesFromDir($directory);
        $uploaded = [];

        foreach ($files as $file) {
            try {
                $response = self::uploadFile(
                    $client,
                    $file['absolute_path'],
                    $file['mime_type']
                );
                $uploaded[] = $response;
            } catch (\Throwable $e) {
                // Log error but continue with other files
                \error_log("Failed to upload {$file['filename']}: " . $e->getMessage());
            }
        }

        return $uploaded;
    }
}
