<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Types\Beta;

use ClaudePhp\Types\Beta\BetaAdvisorTool20260301Param;
use PHPUnit\Framework\TestCase;

class BetaAdvisorToolTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $tool = new BetaAdvisorTool20260301Param();
        $this->assertSame('advisor_20260301', $tool->type);
        $this->assertSame('advisor', $tool->name);
        $this->assertNull($tool->model);
    }

    public function testToArray(): void
    {
        $tool = new BetaAdvisorTool20260301Param(
            model: 'claude-sonnet-4-6',
            max_uses: 3,
            strict: true,
        );

        $arr = $tool->toArray();
        $this->assertSame('advisor_20260301', $arr['type']);
        $this->assertSame('claude-sonnet-4-6', $arr['model']);
        $this->assertSame(3, $arr['max_uses']);
        $this->assertTrue($arr['strict']);
        $this->assertArrayNotHasKey('caching', $arr);
    }
}
