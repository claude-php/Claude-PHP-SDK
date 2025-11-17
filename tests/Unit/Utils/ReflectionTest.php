<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Utils;

use ClaudePhp\Utils\Reflection;
use PHPUnit\Framework\TestCase;

class ReflectionTest extends TestCase
{
    public function testFunctionHasArgument(): void
    {
        $func = static function (int $id, string $name): void {};

        $this->assertTrue(Reflection::functionHasArgument($func, 'id'));
        $this->assertTrue(Reflection::functionHasArgument($func, 'name'));
        $this->assertFalse(Reflection::functionHasArgument($func, 'missing'));
    }

    public function testFunctionHasArgumentWithMethod(): void
    {
        $class = new class {
            public function method(string $param): void {}
        };

        $this->assertTrue(Reflection::functionHasArgument([$class, 'method'], 'param'));
        $this->assertFalse(Reflection::functionHasArgument([$class, 'method'], 'missing'));
    }

    public function testGetParameterNames(): void
    {
        $func = static function (string $first, int $second, bool $third): void {};

        $names = Reflection::getParameterNames($func);

        $this->assertSame(['first', 'second', 'third'], $names);
    }

    public function testGetParameterNamesEmpty(): void
    {
        $func = static function (): void {};

        $names = Reflection::getParameterNames($func);

        $this->assertSame([], $names);
    }

    public function testSignaturesInSync(): void
    {
        $func1 = static function (int $id, string $name): void {};
        $func2 = static function (int $id, string $name): string {
            return '';
        };
        $func3 = static function (int $id): void {};

        $this->assertTrue(Reflection::signaturesInSync($func1, $func2));
        $this->assertFalse(Reflection::signaturesInSync($func1, $func3));
    }

    public function testSignaturesInSyncWithExclude(): void
    {
        $func1 = static function (int $id, string $name): void {};
        $func2 = static function (int $id): void {};

        // When excluding 'name', they should be in sync
        $this->assertTrue(Reflection::signaturesInSync($func1, $func2, ['name']));
    }

    public function testGetParameterTypes(): void
    {
        $func = static function (int $id, ?string $name): void {};

        $types = Reflection::getParameterTypes($func);

        $this->assertArrayHasKey('id', $types);
        $this->assertArrayHasKey('name', $types);
        $this->assertSame('int', $types['id']);
        $this->assertSame('string', $types['name']);
    }

    public function testGetParameterTypesNoTypes(): void
    {
        $func = static function ($untyped): void {};

        $types = Reflection::getParameterTypes($func);

        $this->assertArrayHasKey('untyped', $types);
        $this->assertNull($types['untyped']);
    }
}
