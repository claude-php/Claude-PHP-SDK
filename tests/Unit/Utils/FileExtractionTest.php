<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Utils;

use ClaudePhp\Types\NotGiven;
use ClaudePhp\Utils\FileExtraction;
use PHPUnit\Framework\TestCase;

class FileExtractionTest extends TestCase
{
    public function testExtractSingleFile(): void
    {
        $data = [
            'name' => 'test',
            'file' => ['content' => 'data'],
        ];

        $files = FileExtraction::extractFiles($data, [['file']]);

        $this->assertCount(1, $files);
        $this->assertSame('file', $files[0][0]);
        $this->assertSame(['content' => 'data'], $files[0][1]);
        $this->assertArrayNotHasKey('file', $data);
        $this->assertArrayHasKey('name', $data);
    }

    public function testExtractNestedFile(): void
    {
        $data = [
            'document' => [
                'content' => 'text',
                'attachment' => ['bytes' => 'base64'],
            ],
        ];

        $files = FileExtraction::extractFiles($data, [['document', 'attachment']]);

        $this->assertCount(1, $files);
        $this->assertSame('document[attachment]', $files[0][0]);
        $this->assertSame(['bytes' => 'base64'], $files[0][1]);
        $this->assertArrayNotHasKey('attachment', $data['document']);
        $this->assertArrayHasKey('content', $data['document']);
    }

    public function testExtractIgnoresNotGiven(): void
    {
        $data = [
            'file' => NotGiven::getInstance(),
            'name' => 'test',
        ];

        $files = FileExtraction::extractFiles($data, [['file']]);

        $this->assertCount(0, $files);
        $this->assertArrayHasKey('file', $data);
    }

    public function testExtractNonexistentPath(): void
    {
        $data = ['name' => 'test'];

        $files = FileExtraction::extractFiles($data, [['file']]);

        $this->assertCount(0, $files);
        $this->assertArrayHasKey('name', $data);
    }

    public function testExtractWithInvalidPath(): void
    {
        $data = [
            'nested' => [
                'files' => 'not_an_array',
            ],
        ];

        $files = FileExtraction::extractFiles($data, [['nested', 'files', '<array>']]);

        $this->assertCount(0, $files);
        $this->assertSame('not_an_array', $data['nested']['files']);
    }

    public function testExtractPreservesOtherData(): void
    {
        $data = [
            'name' => 'test',
            'email' => 'test@example.com',
            'file' => ['data' => 'content'],
            'extra' => ['nested' => 'value'],
        ];

        $files = FileExtraction::extractFiles($data, [['file']]);

        $this->assertCount(1, $files);
        $this->assertArrayNotHasKey('file', $data);
        $this->assertSame('test', $data['name']);
        $this->assertSame('test@example.com', $data['email']);
        $this->assertSame(['nested' => 'value'], $data['extra']);
    }

    public function testExtractMultiplePaths(): void
    {
        $data = [
            'avatar' => ['type' => 'image'],
            'extra' => 'data',
        ];

        $files = FileExtraction::extractFiles($data, [['avatar']]);

        $this->assertCount(1, $files);
        $this->assertSame('avatar', $files[0][0]);
        $this->assertArrayNotHasKey('avatar', $data);
        $this->assertArrayHasKey('extra', $data);
    }
}
