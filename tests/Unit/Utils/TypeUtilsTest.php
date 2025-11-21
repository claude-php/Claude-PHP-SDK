<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Utils;

use ClaudePhp\Utils\TypeUtils;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;

class TypeUtilsTest extends TestCase
{
    public function testIsUnionType(): void
    {
        // This test requires creating a function with union types
        $fn = new ReflectionFunction(function (int|string $value): void {
        });
        $param = $fn->getParameters()[0];
        $type = $param->getType();

        $this->assertTrue(TypeUtils::isUnionType($type));
    }

    public function testIsUnionTypeWithNonUnion(): void
    {
        $fn = new ReflectionFunction(function (int $value): void {
        });
        $param = $fn->getParameters()[0];
        $type = $param->getType();

        $this->assertFalse(TypeUtils::isUnionType($type));
    }

    public function testIsNamedType(): void
    {
        $fn = new ReflectionFunction(function (string $value): void {
        });
        $param = $fn->getParameters()[0];
        $type = $param->getType();

        $this->assertTrue(TypeUtils::isNamedType($type));
    }

    public function testGetTypeName(): void
    {
        $fn = new ReflectionFunction(function (string $value): void {
        });
        $param = $fn->getParameters()[0];
        $type = $param->getType();

        $this->assertSame('string', TypeUtils::getTypeName($type));
    }

    public function testGetTypeNameWithIntType(): void
    {
        $fn = new ReflectionFunction(function (int $value): void {
        });
        $param = $fn->getParameters()[0];
        $type = $param->getType();

        $this->assertSame('int', TypeUtils::getTypeName($type));
    }

    public function testIsNullableType(): void
    {
        $fn = new ReflectionFunction(function (?string $value): void {
        });
        $param = $fn->getParameters()[0];
        $type = $param->getType();

        $this->assertTrue(TypeUtils::isNullableType($type));
    }

    public function testIsNullableTypeWithNonNullable(): void
    {
        $fn = new ReflectionFunction(function (string $value): void {
        });
        $param = $fn->getParameters()[0];
        $type = $param->getType();

        $this->assertFalse(TypeUtils::isNullableType($type));
    }

    public function testGetUnionTypeNames(): void
    {
        $fn = new ReflectionFunction(function (int|string $value): void {
        });
        $param = $fn->getParameters()[0];
        $type = $param->getType();

        $names = TypeUtils::getUnionTypeNames($type);
        $this->assertCount(2, $names);
        $this->assertContains('int', $names);
        $this->assertContains('string', $names);
    }

    public function testMatchesType(): void
    {
        $this->assertTrue(TypeUtils::matchesType('hello', 'string'));
        $this->assertFalse(TypeUtils::matchesType('hello', 'int'));
        $this->assertTrue(TypeUtils::matchesType(123, 'int'));
        $this->assertFalse(TypeUtils::matchesType(123, 'string'));
    }
}
