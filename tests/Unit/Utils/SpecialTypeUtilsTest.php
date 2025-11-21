<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Utils;

use ClaudePhp\Types\NotGiven;
use ClaudePhp\Types\Omit;
use ClaudePhp\Utils\SpecialTypeUtils;
use PHPUnit\Framework\TestCase;

class SpecialTypeUtilsTest extends TestCase
{
    public function testIsNotGiven(): void
    {
        $notGiven = NotGiven::getInstance();
        $this->assertTrue(SpecialTypeUtils::isNotGiven($notGiven));
        $this->assertFalse(SpecialTypeUtils::isNotGiven(Omit::getInstance()));
        $this->assertFalse(SpecialTypeUtils::isNotGiven('some string'));
    }

    public function testIsOmit(): void
    {
        $omit = Omit::getInstance();
        $this->assertTrue(SpecialTypeUtils::isOmit($omit));
        $this->assertFalse(SpecialTypeUtils::isOmit(NotGiven::getInstance()));
        $this->assertFalse(SpecialTypeUtils::isOmit('some string'));
    }

    public function testIsGiven(): void
    {
        $this->assertFalse(SpecialTypeUtils::isGiven(NotGiven::getInstance()));
        $this->assertFalse(SpecialTypeUtils::isGiven(Omit::getInstance()));
        $this->assertTrue(SpecialTypeUtils::isGiven('some string'));
        $this->assertTrue(SpecialTypeUtils::isGiven(123));
        $this->assertTrue(SpecialTypeUtils::isGiven(null));
        $this->assertTrue(SpecialTypeUtils::isGiven([]));
    }

    public function testStripNotGiven(): void
    {
        $data = [
            'key1' => 'value1',
            'key2' => NotGiven::getInstance(),
            'key3' => 'value3',
        ];

        $result = SpecialTypeUtils::stripNotGiven($data);
        $this->assertArrayHasKey('key1', $result);
        $this->assertArrayHasKey('key3', $result);
        $this->assertArrayNotHasKey('key2', $result);
        $this->assertSame('value1', $result['key1']);
    }

    public function testStripNotGivenNestedArray(): void
    {
        $data = [
            'parent' => [
                'child1' => 'value',
                'child2' => NotGiven::getInstance(),
            ],
            'other' => NotGiven::getInstance(),
        ];

        $result = SpecialTypeUtils::stripNotGiven($data);
        $this->assertArrayHasKey('parent', $result);
        $this->assertArrayNotHasKey('other', $result);
    }

    public function testStripSpecialMarkers(): void
    {
        $data = [
            'key1' => 'value1',
            'key2' => NotGiven::getInstance(),
            'key3' => Omit::getInstance(),
            'key4' => 'value4',
        ];

        $result = SpecialTypeUtils::stripSpecialMarkers($data);
        $this->assertArrayHasKey('key1', $result);
        $this->assertArrayNotHasKey('key2', $result);
        $this->assertArrayNotHasKey('key3', $result);
        $this->assertArrayHasKey('key4', $result);
    }

    public function testStripSpecialMarkersWithNullValues(): void
    {
        $data = [
            'key1' => 'value1',
            'key2' => null,
            'key3' => NotGiven::getInstance(),
        ];

        $result = SpecialTypeUtils::stripSpecialMarkers($data);
        $this->assertArrayHasKey('key1', $result);
        $this->assertArrayHasKey('key2', $result);
        $this->assertArrayNotHasKey('key3', $result);
        $this->assertNull($result['key2']);
    }

    public function testStripNotGivenWithEmptyArray(): void
    {
        $data = [];
        $result = SpecialTypeUtils::stripNotGiven($data);
        $this->assertSame([], $result);
    }

    public function testStripSpecialMarkersWithOnlyMarkers(): void
    {
        $data = [
            'key1' => NotGiven::getInstance(),
            'key2' => Omit::getInstance(),
        ];

        $result = SpecialTypeUtils::stripSpecialMarkers($data);
        $this->assertSame([], $result);
    }
}
