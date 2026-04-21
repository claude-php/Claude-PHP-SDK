<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Utils;

use ClaudePhp\Utils\QueryString;
use PHPUnit\Framework\TestCase;

class QueryStringTest extends TestCase
{
    public function testBracketsFormat(): void
    {
        $result = QueryString::build(['tags' => ['a', 'b']], QueryString::FORMAT_BRACKETS);
        $this->assertSame('tags[]=a&tags[]=b', $result);
    }

    public function testCommaFormat(): void
    {
        $result = QueryString::build(['tags' => ['a', 'b']], QueryString::FORMAT_COMMA);
        $this->assertSame('tags=a,b', $result);
    }

    public function testRepeatFormat(): void
    {
        $result = QueryString::build(['tags' => ['a', 'b']], QueryString::FORMAT_REPEAT);
        $this->assertSame('tags=a&tags=b', $result);
    }

    public function testIndicesFormat(): void
    {
        $result = QueryString::build(['tags' => ['a', 'b']], QueryString::FORMAT_INDICES);
        $this->assertSame('tags[0]=a&tags[1]=b', $result);
    }

    public function testScalarValues(): void
    {
        $result = QueryString::build(['name' => 'test', 'count' => 5]);
        $this->assertSame('name=test&count=5', $result);
    }

    public function testBooleanValues(): void
    {
        $result = QueryString::build(['active' => true, 'deleted' => false]);
        $this->assertSame('active=true&deleted=false', $result);
    }

    public function testNullValuesSkipped(): void
    {
        $result = QueryString::build(['a' => 'yes', 'b' => null]);
        $this->assertSame('a=yes', $result);
    }

    public function testMergePreservingHardcoded(): void
    {
        $merged = QueryString::mergePreservingHardcoded(
            ['beta' => 'true'],
            ['beta' => 'false', 'limit' => '10'],
        );
        $this->assertSame('true', $merged['beta']);
        $this->assertSame('10', $merged['limit']);
    }
}
