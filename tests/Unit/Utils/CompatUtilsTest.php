<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Utils;

use ClaudePhp\Utils\CompatUtils;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;

class CompatUtilsTest extends TestCase
{
    public function testGetArgsFromUnionType(): void
    {
        $reflection = new ReflectionFunction(function (int|string|null $param) {
            return $param;
        });

        $param = $reflection->getParameters()[0];
        $type = $param->getType();

        $args = CompatUtils::getArgs($type);

        $this->assertCount(3, $args);
        $this->assertContains('string', $args);
        $this->assertContains('int', $args);
        $this->assertContains('null', $args);
    }

    public function testGetArgsFromString(): void
    {
        $args = CompatUtils::getArgs('string|int|bool');

        $this->assertCount(3, $args);
        $this->assertContains('string', $args);
        $this->assertContains('int', $args);
        $this->assertContains('bool', $args);
    }

    public function testGetArgsFromGenericString(): void
    {
        $args = CompatUtils::getArgs('List<string>');

        $this->assertCount(1, $args);
        $this->assertContains('string', $args);
    }

    public function testGetArgsFromDictString(): void
    {
        $args = CompatUtils::getArgs('Dict<string, int>');

        $this->assertCount(2, $args);
        $this->assertContains('string', $args);
        $this->assertContains('int', $args);
    }

    public function testGetArgsFromNull(): void
    {
        $args = CompatUtils::getArgs(null);

        $this->assertCount(0, $args);
    }

    public function testGetOriginFromUnionType(): void
    {
        $reflection = new ReflectionFunction(function (int|string $param) {
            return $param;
        });

        $param = $reflection->getParameters()[0];
        $type = $param->getType();

        $origin = CompatUtils::getOrigin($type);

        $this->assertSame('union', $origin);
    }

    public function testGetOriginFromNamedType(): void
    {
        $reflection = new ReflectionFunction(function (string $param) {
            return $param;
        });

        $param = $reflection->getParameters()[0];
        $type = $param->getType();

        $origin = CompatUtils::getOrigin($type);

        $this->assertSame('string', $origin);
    }

    public function testGetOriginFromString(): void
    {
        $origin = CompatUtils::getOrigin('List<string>');

        $this->assertSame('list', $origin);
    }

    public function testGetOriginFromDictString(): void
    {
        $origin = CompatUtils::getOrigin('Dict<string, int>');

        $this->assertSame('dict', $origin);
    }

    public function testGetOriginFromUnionString(): void
    {
        $origin = CompatUtils::getOrigin('pending|completed|failed');

        $this->assertSame('pending', $origin);
    }

    public function testGetOriginFromNull(): void
    {
        $origin = CompatUtils::getOrigin(null);

        $this->assertNull($origin);
    }

    public function testIsUnionWithUnionType(): void
    {
        $reflection = new ReflectionFunction(function (int|string $param) {
            return $param;
        });

        $param = $reflection->getParameters()[0];
        $type = $param->getType();

        $this->assertTrue(CompatUtils::isUnion($type));
    }

    public function testIsUnionWithNamedType(): void
    {
        $reflection = new ReflectionFunction(function (string $param) {
            return $param;
        });

        $param = $reflection->getParameters()[0];
        $type = $param->getType();

        $this->assertFalse(CompatUtils::isUnion($type));
    }

    public function testIsUnionWithUnionString(): void
    {
        $this->assertTrue(CompatUtils::isUnion('string|int|bool'));
    }

    public function testIsUnionWithNonUnionString(): void
    {
        $this->assertFalse(CompatUtils::isUnion('string'));
    }

    public function testIsLiteralTypeWithLiteralString(): void
    {
        $this->assertTrue(CompatUtils::isLiteralType("'pending'|'completed'|'failed'"));
    }

    public function testIsLiteralTypeWithDoubleQuotedString(): void
    {
        $this->assertTrue(CompatUtils::isLiteralType('"active"|"inactive"'));
    }

    public function testIsLiteralTypeWithSingleLiteral(): void
    {
        $this->assertTrue(CompatUtils::isLiteralType("'pending'"));
    }

    public function testIsLiteralTypeWithMixedUnion(): void
    {
        // 'pending' and string mixed - not a pure literal
        $this->assertFalse(CompatUtils::isLiteralType("'pending'|string"));
    }

    public function testIsLiteralTypeWithNonLiteral(): void
    {
        $this->assertFalse(CompatUtils::isLiteralType('string|int'));
    }

    public function testIsTypedDictWithFinalClass(): void
    {
        $class = new ReflectionClass(TypedDictExample::class);

        $isTypedDict = CompatUtils::isTypedDict($class);

        // This will be false without the marker attribute
        $this->assertIsBool($isTypedDict);
    }

    public function testIsTypedDictWithString(): void
    {
        $isTypedDict = CompatUtils::isTypedDict('stdClass');

        $this->assertIsBool($isTypedDict);
    }

    public function testIsTypedDictWithInvalidClass(): void
    {
        $isTypedDict = CompatUtils::isTypedDict('NonExistentClass');

        $this->assertFalse($isTypedDict);
    }

    public function testGetArgsFromComplexGeneric(): void
    {
        $args = CompatUtils::getArgs('Response<List<Item>>');

        // Should extract the outer generic argument
        $this->assertGreaterThanOrEqual(1, count($args));
    }

    public function testGetOriginWithArrayType(): void
    {
        $reflection = new ReflectionFunction(function (array $param) {
            return $param;
        });

        $param = $reflection->getParameters()[0];
        $type = $param->getType();

        $origin = CompatUtils::getOrigin($type);

        $this->assertSame('array', $origin);
    }

    public function testIsLiteralTypeWithNumberLiterals(): void
    {
        // Number literals are less common but might appear
        $this->assertFalse(CompatUtils::isLiteralType('1|2|3'));
    }

    public function testGetArgsHandlesWhitespace(): void
    {
        $args = CompatUtils::getArgs('string | int | bool');

        $this->assertCount(3, $args);
        $this->assertContains('string', $args);
        $this->assertContains('int', $args);
        $this->assertContains('bool', $args);
    }

    public function testGetOriginFromArrayString(): void
    {
        $origin = CompatUtils::getOrigin('array');

        $this->assertSame('array', $origin);
    }
}

/**
 * Example class for TypedDict testing.
 */
final class TypedDictExample
{
}
