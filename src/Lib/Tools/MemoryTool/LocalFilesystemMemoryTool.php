<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Tools\MemoryTool;

/**
 * Local filesystem-backed memory tool.
 *
 * Stores memory as files in a configurable base directory with restrictive
 * file permissions (0600) and path-traversal rejection.
 */
class LocalFilesystemMemoryTool extends AbstractMemoryTool
{
    private const FILE_MODE = 0600;

    private string $baseDir;

    public function __construct(string $baseDir)
    {
        $baseDir = rtrim($baseDir, '/');

        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0700, true);
        }

        $resolved = realpath($baseDir);
        $this->baseDir = false !== $resolved ? $resolved : $baseDir;
    }

    public function getBaseDir(): string
    {
        return $this->baseDir;
    }

    protected function view(array $command): array
    {
        $path = $this->validatePath($command['path'] ?? '');

        if (!file_exists($path)) {
            return ['error' => 'File not found: ' . ($command['path'] ?? '')];
        }

        $content = file_get_contents($path);

        return ['content' => $content !== false ? $content : ''];
    }

    protected function create(array $command): array
    {
        $path = $this->validatePath($command['path'] ?? '');
        $content = $command['content'] ?? '';

        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        file_put_contents($path, $content);
        chmod($path, self::FILE_MODE);

        return ['status' => 'created', 'path' => $command['path'] ?? ''];
    }

    protected function strReplace(array $command): array
    {
        $path = $this->validatePath($command['path'] ?? '');

        if (!file_exists($path)) {
            return ['error' => 'File not found: ' . ($command['path'] ?? '')];
        }

        $content = file_get_contents($path);
        if (false === $content) {
            return ['error' => 'Cannot read file'];
        }

        $oldStr = $command['old_str'] ?? '';
        $newStr = $command['new_str'] ?? '';

        if ('' === $oldStr) {
            return ['error' => 'old_str is required'];
        }

        $count = substr_count($content, $oldStr);
        if (0 === $count) {
            return ['error' => 'old_str not found in file'];
        }
        if ($count > 1) {
            return ['error' => 'old_str is ambiguous — found multiple matches'];
        }

        $content = str_replace($oldStr, $newStr, $content);
        file_put_contents($path, $content);
        chmod($path, self::FILE_MODE);

        return ['status' => 'replaced'];
    }

    protected function insert(array $command): array
    {
        $path = $this->validatePath($command['path'] ?? '');

        if (!file_exists($path)) {
            return ['error' => 'File not found: ' . ($command['path'] ?? '')];
        }

        $content = file_get_contents($path);
        if (false === $content) {
            return ['error' => 'Cannot read file'];
        }

        $insertAfterLine = $command['insert_after_line'] ?? 0;
        $newStr = $command['new_str'] ?? '';

        $lines = explode("\n", $content);
        $insertPos = min($insertAfterLine, count($lines));

        array_splice($lines, $insertPos, 0, $newStr);
        file_put_contents($path, implode("\n", $lines));
        chmod($path, self::FILE_MODE);

        return ['status' => 'inserted'];
    }

    protected function delete(array $command): array
    {
        $path = $this->validatePath($command['path'] ?? '');

        if (!file_exists($path)) {
            return ['error' => 'File not found: ' . ($command['path'] ?? '')];
        }

        unlink($path);

        return ['status' => 'deleted'];
    }

    protected function rename(array $command): array
    {
        $oldPath = $this->validatePath($command['old_path'] ?? '');
        $newPath = $this->validatePath($command['new_path'] ?? '');

        if (!file_exists($oldPath)) {
            return ['error' => 'Source file not found: ' . ($command['old_path'] ?? '')];
        }

        $dir = dirname($newPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        rename($oldPath, $newPath);

        return ['status' => 'renamed'];
    }

    /**
     * Validate and resolve a path within the base directory.
     *
     * Rejects path-traversal attempts including encoded dot-segments.
     *
     * @throws \InvalidArgumentException
     */
    private function validatePath(string $relativePath): string
    {
        if ('' === $relativePath) {
            throw new \InvalidArgumentException('Path cannot be empty');
        }

        $decoded = rawurldecode($relativePath);

        $segments = explode('/', $decoded);
        foreach ($segments as $segment) {
            if ('.' === $segment || '..' === $segment) {
                throw new \InvalidArgumentException(
                    "Path contains traversal segment: {$relativePath}"
                );
            }
        }

        $fullPath = $this->baseDir . '/' . ltrim($decoded, '/');

        $resolved = realpath(dirname($fullPath));
        if (false !== $resolved && !str_starts_with($resolved, $this->baseDir)) {
            throw new \InvalidArgumentException(
                "Path escapes base directory: {$relativePath}"
            );
        }

        return $fullPath;
    }
}
