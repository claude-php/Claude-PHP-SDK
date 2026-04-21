<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Lib\Tools;

use ClaudePhp\Lib\Tools\MemoryTool\LocalFilesystemMemoryTool;
use PHPUnit\Framework\TestCase;

class MemoryToolTest extends TestCase
{
    private string $tmpDir;
    private LocalFilesystemMemoryTool $tool;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/claude_php_memory_test_' . uniqid();
        mkdir($this->tmpDir, 0700, true);
        $this->tool = new LocalFilesystemMemoryTool($this->tmpDir);
    }

    protected function tearDown(): void
    {
        $this->recursiveDelete($this->tmpDir);
    }

    public function testCreateAndView(): void
    {
        $result = $this->tool->execute([
            'command' => 'create',
            'path' => 'notes.md',
            'content' => '# Notes',
        ]);
        $this->assertSame('created', $result['status']);

        $view = $this->tool->execute(['command' => 'view', 'path' => 'notes.md']);
        $this->assertSame('# Notes', $view['content']);
    }

    public function testStrReplace(): void
    {
        $this->tool->execute(['command' => 'create', 'path' => 'test.txt', 'content' => 'hello world']);
        $result = $this->tool->execute([
            'command' => 'str_replace',
            'path' => 'test.txt',
            'old_str' => 'world',
            'new_str' => 'PHP',
        ]);
        $this->assertSame('replaced', $result['status']);

        $view = $this->tool->execute(['command' => 'view', 'path' => 'test.txt']);
        $this->assertSame('hello PHP', $view['content']);
    }

    public function testDelete(): void
    {
        $this->tool->execute(['command' => 'create', 'path' => 'temp.txt', 'content' => 'x']);
        $result = $this->tool->execute(['command' => 'delete', 'path' => 'temp.txt']);
        $this->assertSame('deleted', $result['status']);
        $this->assertFileDoesNotExist($this->tmpDir . '/temp.txt');
    }

    public function testRename(): void
    {
        $this->tool->execute(['command' => 'create', 'path' => 'a.txt', 'content' => 'data']);
        $result = $this->tool->execute([
            'command' => 'rename',
            'old_path' => 'a.txt',
            'new_path' => 'b.txt',
        ]);
        $this->assertSame('renamed', $result['status']);
        $this->assertFileDoesNotExist($this->tmpDir . '/a.txt');
        $this->assertFileExists($this->tmpDir . '/b.txt');
    }

    public function testFilePermissions(): void
    {
        $this->tool->execute(['command' => 'create', 'path' => 'secret.txt', 'content' => 'x']);
        $perms = fileperms($this->tmpDir . '/secret.txt') & 0777;
        $this->assertSame(0600, $perms, 'File should have restrictive 0600 permissions');
    }

    public function testRejectsPathTraversal(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('traversal');
        $this->tool->execute(['command' => 'view', 'path' => '../etc/passwd']);
    }

    public function testRejectsEncodedTraversal(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->tool->execute(['command' => 'view', 'path' => '%2e%2e/etc/passwd']);
    }

    public function testToDict(): void
    {
        $dict = $this->tool->toDict();
        $this->assertSame('memory_20250818', $dict['type']);
        $this->assertSame('memory', $dict['name']);
    }

    public function testViewMissingFile(): void
    {
        $result = $this->tool->execute(['command' => 'view', 'path' => 'nonexistent.txt']);
        $this->assertArrayHasKey('error', $result);
    }

    private function recursiveDelete(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($dir);
    }
}
