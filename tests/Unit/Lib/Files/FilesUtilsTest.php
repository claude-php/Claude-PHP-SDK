<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Lib\Files;

use ClaudePhp\Lib\Files\FilesUtils;
use PHPUnit\Framework\TestCase;

class FilesUtilsTest extends TestCase
{
    public function testGetMimeType(): void
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass(FilesUtils::class);
        $method = $reflection->getMethod('getMimeType');
        $method->setAccessible(true);

        $this->assertSame('application/pdf', $method->invoke(null, 'file.pdf'));
        $this->assertSame('text/plain', $method->invoke(null, 'file.txt'));
        $this->assertSame('application/json', $method->invoke(null, 'file.json'));
        $this->assertSame('image/jpeg', $method->invoke(null, 'photo.jpg'));
        $this->assertSame('image/png', $method->invoke(null, 'image.png'));
    }

    public function testFilesFromDirNotFound(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Directory not found');

        FilesUtils::filesFromDir('/nonexistent/directory');
    }

    public function testFilesFromDirExclusion(): void
    {
        // Use temp dir for testing
        $tmpdir = \sys_get_temp_dir() . '/claude-test-' . \uniqid();
        @\mkdir($tmpdir, 0o777, true);

        try {
            // Create test files
            \file_put_contents("{$tmpdir}/test.txt", 'content');
            \file_put_contents("{$tmpdir}/test.log", 'log content');
            \mkdir("{$tmpdir}/node_modules", 0o777, true);

            // Get files excluding .log
            $files = FilesUtils::filesFromDir($tmpdir, null, ['log']);

            // Should have test.txt but not test.log
            $filenames = \array_map(fn ($f) => $f['filename'], $files);
            $this->assertContains('test.txt', $filenames);
            $this->assertNotContains('test.log', $filenames);
        } finally {
            $this->removeDirectory($tmpdir);
        }
    }

    public function testRelativePathsUsePosixFormat(): void
    {
        // Test that relative_path always uses forward slashes, not backslashes
        // This ensures cross-platform compatibility (especially for Windows)
        $tmpdir = \sys_get_temp_dir() . '/claude-test-' . \uniqid();
        @\mkdir($tmpdir . '/subdir', 0o777, true);

        try {
            // Create nested file
            \file_put_contents("{$tmpdir}/subdir/nested.txt", 'nested content');

            $files = FilesUtils::filesFromDir($tmpdir);

            // Verify relative path uses forward slashes
            $this->assertCount(1, $files);
            $this->assertStringContainsString('/', $files[0]['relative_path']);
            $this->assertStringNotContainsString('\\', $files[0]['relative_path']);
            $this->assertSame('subdir/nested.txt', $files[0]['relative_path']);
        } finally {
            $this->removeDirectory($tmpdir);
        }
    }

    private function removeDirectory(string $path): void
    {
        if (!\is_dir($path)) {
            return;
        }

        $items = new \FilesystemIterator($path);
        foreach ($items as $item) {
            if ($item->isDir() && !$item->isLink()) {
                $this->removeDirectory($item->getPathname());

                continue;
            }

            @\unlink($item->getPathname());
        }

        @\rmdir($path);
    }
}
