<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Utils;

use ClaudePhp\Utils\Utils;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    public function testFlatten(): void
    {
        $input = [[1, 2], [3, 4], [5]];
        $expected = [1, 2, 3, 4, 5];
        $this->assertSame($expected, Utils::flatten($input));
    }

    public function testFlattenEmpty(): void
    {
        $input = [];
        $expected = [];
        $this->assertSame($expected, Utils::flatten($input));
    }

    public function testIsMapping(): void
    {
        $this->assertTrue(Utils::isMapping(['a' => 1, 'b' => 2]));
        $this->assertFalse(Utils::isMapping([1, 2, 3]));
        $this->assertTrue(Utils::isMapping([]));
    }

    public function testIsList(): void
    {
        $this->assertTrue(Utils::isList([1, 2, 3]));
        $this->assertFalse(Utils::isList(['a' => 1, 'b' => 2]));
        $this->assertTrue(Utils::isList([]));
    }

    public function testIsDict(): void
    {
        $this->assertTrue(Utils::isDict(['a' => 1]));
        $this->assertTrue(Utils::isDict([1, 2, 3]));
        $this->assertFalse(Utils::isDict('not an array'));
    }

    public function testIsTuple(): void
    {
        $this->assertTrue(Utils::isTuple([1, 2, 3]));
        $this->assertFalse(Utils::isTuple(['a' => 1]));
    }

    public function testIsIterable(): void
    {
        $this->assertTrue(Utils::isIterable([1, 2, 3]));
        $this->assertTrue(Utils::isIterable(new \ArrayIterator([1, 2])));
        $this->assertFalse(Utils::isIterable('string'));
    }

    public function testIsSequence(): void
    {
        $this->assertTrue(Utils::isSequence([1, 2, 3]));
        $this->assertTrue(Utils::isSequence(new \ArrayIterator([1, 2])));
    }

    public function testDeepcopyMinimal(): void
    {
        $data = [
            'nested' => [
                'value' => 'test',
                'array' => [1, 2, 3],
            ],
            'simple' => 'string',
        ];

        $copy = Utils::deepcopyMinimal($data);

        // Should be equal in value
        $this->assertEquals($data, $copy);

        // Should be different array instances (different variables)
        // Note: In PHP, arrays are value types, not reference types, so we can't test identity
        // with === like we can with objects. However, we can verify the copy was recursive
        // by ensuring nested arrays were created independently.
    }

    public function testDeepcopyMinimalNonRecursive(): void
    {
        // Non-collection types should not be copied
        $obj = new \stdClass();
        $result = Utils::deepcopyMinimal($obj);
        $this->assertSame($obj, $result);
    }

    public function testHumanJoin(): void
    {
        $this->assertSame('', Utils::humanJoin([]));
        $this->assertSame('a', Utils::humanJoin(['a']));
        $this->assertSame('a or b', Utils::humanJoin(['a', 'b']));
        $this->assertSame('a, b, or c', Utils::humanJoin(['a', 'b', 'c']));
        $this->assertSame('a; b; and c', Utils::humanJoin(['a', 'b', 'c'], '; ', 'and'));
    }

    public function testQuote(): void
    {
        $this->assertSame("'hello'", Utils::quote('hello'));
        $this->assertSame("'test string'", Utils::quote('test string'));
    }

    public function testRemovePrefix(): void
    {
        $this->assertSame('world', Utils::removePrefix('helloworld', 'hello'));
        $this->assertSame('helloworld', Utils::removePrefix('helloworld', 'foo'));
        $this->assertSame('', Utils::removePrefix('hello', 'hello'));
    }

    public function testRemoveSuffix(): void
    {
        $this->assertSame('hello', Utils::removeSuffix('helloworld', 'world'));
        $this->assertSame('helloworld', Utils::removeSuffix('helloworld', 'foo'));
        $this->assertSame('', Utils::removeSuffix('hello', 'hello'));
    }

    public function testCoerceInteger(): void
    {
        $this->assertSame(42, Utils::coerceInteger('42'));
        $this->assertSame(-5, Utils::coerceInteger('-5'));
        $this->assertSame(0, Utils::coerceInteger('0'));
    }

    public function testCoerceFloat(): void
    {
        $this->assertSame(3.14, Utils::coerceFloat('3.14'));
        $this->assertSame(-2.5, Utils::coerceFloat('-2.5'));
        $this->assertSame(0.0, Utils::coerceFloat('0'));
    }

    public function testCoerceBoolean(): void
    {
        $this->assertTrue(Utils::coerceBoolean('true'));
        $this->assertTrue(Utils::coerceBoolean('1'));
        $this->assertTrue(Utils::coerceBoolean('on'));
        $this->assertTrue(Utils::coerceBoolean('TRUE'));
        $this->assertFalse(Utils::coerceBoolean('false'));
        $this->assertFalse(Utils::coerceBoolean('0'));
        $this->assertFalse(Utils::coerceBoolean('off'));
    }

    public function testMaybeCoerce(): void
    {
        $this->assertNull(Utils::maybeCoerceInteger(null));
        $this->assertSame(42, Utils::maybeCoerceInteger('42'));

        $this->assertNull(Utils::maybeCoerceFloat(null));
        $this->assertSame(3.14, Utils::maybeCoerceFloat('3.14'));

        $this->assertNull(Utils::maybeCoerceBoolean(null));
        $this->assertTrue(Utils::maybeCoerceBoolean('true'));
    }

    public function testJsonSafe(): void
    {
        $data = [
            'string' => 'value',
            'number' => 42,
            'nested' => [
                'key' => 'value',
            ],
            'datetime' => new DateTimeImmutable('2023-01-01T12:00:00Z'),
        ];

        $safe = Utils::jsonSafe($data);

        $this->assertSame('value', $safe['string']);
        $this->assertSame(42, $safe['number']);
        $this->assertSame('value', $safe['nested']['key']);
        $this->assertIsString($safe['datetime']);
    }

    public function testGetRequiredHeader(): void
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Request-Id' => '12345',
        ];

        $this->assertSame('application/json', Utils::getRequiredHeader($headers, 'Content-Type'));
        $this->assertSame('application/json', Utils::getRequiredHeader($headers, 'content-type'));
        $this->assertSame('12345', Utils::getRequiredHeader($headers, 'x-request-id'));
    }

    public function testGetRequiredHeaderNotFound(): void
    {
        $this->expectException(\ValueError::class);
        Utils::getRequiredHeader([], 'missing-header');
    }

    public function testFileFromPath(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tmpFile, 'test content');

        try {
            $content = Utils::fileFromPath($tmpFile);
            $this->assertSame('test content', $content);
        } finally {
            unlink($tmpFile);
        }
    }

    public function testFileFromPathNotFound(): void
    {
        $this->expectException(\RuntimeException::class);
        Utils::fileFromPath('/nonexistent/path/file.txt');
    }
}
