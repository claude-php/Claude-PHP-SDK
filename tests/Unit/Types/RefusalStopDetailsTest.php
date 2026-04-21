<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Types;

use ClaudePhp\Types\RefusalStopDetails;
use PHPUnit\Framework\TestCase;

class RefusalStopDetailsTest extends TestCase
{
    public function testFromArray(): void
    {
        $details = RefusalStopDetails::fromArray([
            'type' => 'refusal',
            'category' => 'cyber',
            'explanation' => 'Content policy',
        ]);
        $this->assertSame('refusal', $details->type);
        $this->assertSame('cyber', $details->category);
        $this->assertSame('Content policy', $details->explanation);
    }

    public function testToArray(): void
    {
        $details = new RefusalStopDetails(category: 'bio');
        $arr = $details->toArray();
        $this->assertSame('refusal', $arr['type']);
        $this->assertSame('bio', $arr['category']);
        $this->assertArrayNotHasKey('explanation', $arr);
    }

    public function testDefaultValues(): void
    {
        $details = new RefusalStopDetails();
        $this->assertSame('refusal', $details->type);
        $this->assertNull($details->category);
        $this->assertNull($details->explanation);
    }
}
