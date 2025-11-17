<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Lib\Parse;

use ClaudePhp\Lib\Parse\SchemaTransformer;
use PHPUnit\Framework\TestCase;

final class SchemaTransformerTest extends TestCase
{
    public function testParseSimpleType(): void
    {
        $schema = SchemaTransformer::parseType('string');
        $this->assertSame('string', $schema['type']);

        $schema = SchemaTransformer::parseType('int');
        $this->assertSame('integer', $schema['type']);

        $schema = SchemaTransformer::parseType('float');
        $this->assertSame('number', $schema['type']);
    }

    public function testParseArrayType(): void
    {
        $schema = SchemaTransformer::parseType('array[string]');

        $this->assertSame('array', $schema['type']);
        $this->assertSame('string', $schema['items']['type']);
    }

    public function testBuildObjectSchema(): void
    {
        $schema = SchemaTransformer::buildObjectSchema(
            [
                'name' => 'string',
                'age' => 'int',
                'tags' => 'array[string]',
            ],
            ['name'],
            'Person'
        );

        $this->assertSame('object', $schema['type']);
        $this->assertSame('Person', $schema['title']);
        $this->assertSame('string', $schema['properties']['name']['type']);
        $this->assertSame('integer', $schema['properties']['age']['type']);
        $this->assertTrue(\in_array('name', $schema['required'], true));
    }
}
