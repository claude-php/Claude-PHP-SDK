<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Utils;

use ClaudePhp\Types\NotGiven;
use ClaudePhp\Utils\PropertyInfo;
use ClaudePhp\Utils\Transform;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class TransformTest extends TestCase
{
    public function testTransformFieldAliasing(): void
    {
        $data = [
            'user_id' => 123,
            'first_name' => 'John',
        ];

        $typeHints = [
            'user_id' => new PropertyInfo(alias: 'userId'),
            'first_name' => new PropertyInfo(alias: 'firstName'),
        ];

        $result = Transform::transform($data, $typeHints);

        $this->assertArrayHasKey('userId', $result);
        $this->assertArrayHasKey('firstName', $result);
        $this->assertSame(123, $result['userId']);
        $this->assertSame('John', $result['firstName']);
    }

    public function testTransformWithoutAliasing(): void
    {
        $data = [
            'normal_key' => 'value',
            'another_key' => 42,
        ];

        $result = Transform::transform($data, []);

        $this->assertArrayHasKey('normal_key', $result);
        $this->assertArrayHasKey('another_key', $result);
        $this->assertSame('value', $result['normal_key']);
        $this->assertSame(42, $result['another_key']);
    }

    public function testTransformDateTimeFormatting(): void
    {
        $date = new DateTimeImmutable('2024-01-15T14:30:00+00:00');
        $data = ['created_at' => $date];

        $typeHints = [
            'created_at' => new PropertyInfo(format: 'iso8601'),
        ];

        $result = Transform::transform($data, $typeHints);

        $this->assertIsString($result['created_at']);
        $this->assertStringContainsString('2024-01-15', $result['created_at']);
    }

    public function testTransformBase64Formatting(): void
    {
        $data = ['file_content' => 'hello'];

        $typeHints = [
            'file_content' => new PropertyInfo(format: 'base64'),
        ];

        $result = Transform::transform($data, $typeHints);

        $this->assertSame(base64_encode('hello'), $result['file_content']);
    }

    public function testTransformNestedArrays(): void
    {
        $data = [
            'user' => [
                'name' => 'John',
                'age' => 30,
            ],
        ];

        $result = Transform::transform($data, []);

        $this->assertIsArray($result['user']);
        $this->assertSame('John', $result['user']['name']);
        $this->assertSame(30, $result['user']['age']);
    }

    public function testTransformLists(): void
    {
        $data = [
            'items' => [1, 2, 3, 4],
        ];

        $result = Transform::transform($data, []);

        $this->assertIsArray($result['items']);
        $this->assertSame([1, 2, 3, 4], $result['items']);
    }

    public function testTransformNullValues(): void
    {
        $data = [
            'nullable_field' => null,
            'regular_field' => 'value',
        ];

        $result = Transform::transform($data, []);

        $this->assertNull($result['nullable_field']);
        $this->assertSame('value', $result['regular_field']);
    }

    public function testFormatDataWithCustomDateTemplate(): void
    {
        $date = new DateTimeImmutable('2024-01-15 14:30:00');
        $result = Transform::formatData($date, 'custom', 'Y/m/d H:i');

        $this->assertSame('2024/01/15 14:30', $result);
    }

    public function testCleanRequestParams(): void
    {
        $params = [
            'key1' => 'value1',
            'key2' => NotGiven::getInstance(),
            'key3' => 'value3',
        ];

        $result = Transform::cleanRequestParams($params);

        $this->assertArrayHasKey('key1', $result);
        $this->assertArrayHasKey('key3', $result);
        $this->assertArrayNotHasKey('key2', $result);
    }

    public function testMergeParams(): void
    {
        $base = ['id' => 1, 'name' => 'test'];
        $additional = ['status' => 'active'];
        $typeHints = ['name' => new PropertyInfo(alias: 'displayName')];

        $result = Transform::mergeParams($base, $additional, $typeHints);

        $this->assertSame(1, $result['id']);
        $this->assertSame('test', $result['displayName']);
        $this->assertSame('active', $result['status']);
    }
}
