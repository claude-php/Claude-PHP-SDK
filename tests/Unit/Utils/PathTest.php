<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Utils;

use ClaudePhp\Utils\Path;
use PHPUnit\Framework\TestCase;

class PathTest extends TestCase
{
    public function testBasicInterpolation(): void
    {
        $result = Path::pathTemplate('/agents/{agent_id}', ['agent_id' => 'ag_123']);
        $this->assertSame('/agents/ag_123', $result);
    }

    public function testMultiplePlaceholders(): void
    {
        $result = Path::pathTemplate(
            '/vaults/{vault_id}/credentials/{cred_id}',
            ['vault_id' => 'v1', 'cred_id' => 'c2'],
        );
        $this->assertSame('/vaults/v1/credentials/c2', $result);
    }

    public function testPercentEncodesSpecialCharacters(): void
    {
        $result = Path::pathTemplate('/files/{id}', ['id' => 'hello world/file']);
        $this->assertSame('/files/hello%20world%2Ffile', $result);
    }

    public function testRejectsDotSegment(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('dot-segment traversal');
        Path::pathTemplate('/files/{id}', ['id' => '..']);
    }

    public function testRejectsSingleDot(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Path::pathTemplate('/files/{id}', ['id' => '.']);
    }

    public function testRejectsEncodedDotDot(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Path::pathTemplate('/files/{id}', ['id' => '%2e%2e']);
    }

    public function testRejectsMissingParam(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing path parameter');
        Path::pathTemplate('/files/{id}', []);
    }

    public function testNoPlaceholdersPassthrough(): void
    {
        $result = Path::pathTemplate('/messages', []);
        $this->assertSame('/messages', $result);
    }
}
