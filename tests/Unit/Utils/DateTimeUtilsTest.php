<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Utils;

use ClaudePhp\Utils\DateTimeUtils;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class DateTimeUtilsTest extends TestCase
{
    public function testParseDate(): void
    {
        $result = DateTimeUtils::parseDate('2023-01-15');
        $this->assertInstanceOf(DateTimeImmutable::class, $result);
        $this->assertSame('2023-01-15', $result->format('Y-m-d'));
    }

    public function testParseDateInvalid(): void
    {
        $this->expectException(\Exception::class);
        DateTimeUtils::parseDate('not-a-date');
    }

    public function testParseDateTime(): void
    {
        $result = DateTimeUtils::parseDateTime('2023-01-15T14:30:00Z');
        $this->assertInstanceOf(DateTimeImmutable::class, $result);
        $this->assertSame('2023-01-15', $result->format('Y-m-d'));
    }

    public function testParseDateTimeWithMilliseconds(): void
    {
        $result = DateTimeUtils::parseDateTime('2023-01-15T14:30:00.123Z');
        $this->assertInstanceOf(DateTimeImmutable::class, $result);
    }

    public function testParseDateTimeAtomFormat(): void
    {
        $result = DateTimeUtils::parseDateTime('2023-01-15T14:30:00+00:00');
        $this->assertInstanceOf(DateTimeImmutable::class, $result);
    }

    public function testParseDateTimeInvalid(): void
    {
        $this->expectException(\Exception::class);
        DateTimeUtils::parseDateTime('not-a-datetime');
    }

    public function testFormatDateTime(): void
    {
        $dt = DateTimeImmutable::createFromFormat('Y-m-d', '2023-01-15');
        $result = DateTimeUtils::formatDateTime($dt);
        $this->assertStringContainsString('2023-01-15', $result);
    }

    public function testFormatDate(): void
    {
        $dt = DateTimeImmutable::createFromFormat('Y-m-d', '2023-01-15');
        $result = DateTimeUtils::formatDate($dt);
        $this->assertSame('2023-01-15', $result);
    }
}
