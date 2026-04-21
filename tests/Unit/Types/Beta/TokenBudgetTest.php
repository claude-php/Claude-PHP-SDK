<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Types\Beta;

use ClaudePhp\Types\Beta\BetaInputTokensClearAtLeastParam;
use ClaudePhp\Types\Beta\BetaInputTokensTriggerParam;
use ClaudePhp\Types\Beta\BetaTokenTaskBudgetParam;
use ClaudePhp\Types\Beta\BetaToolUsesKeepParam;
use ClaudePhp\Types\Beta\BetaToolUsesTriggerParam;
use PHPUnit\Framework\TestCase;

class TokenBudgetTest extends TestCase
{
    public function testTaskBudget(): void
    {
        $b = new BetaTokenTaskBudgetParam(total: 1000, remaining: 500);
        $arr = $b->toArray();
        $this->assertSame('tokens', $arr['type']);
        $this->assertSame(1000, $arr['total']);
        $this->assertSame(500, $arr['remaining']);
    }

    public function testInputTokensTrigger(): void
    {
        $t = new BetaInputTokensTriggerParam(value: 50000);
        $this->assertSame('input_tokens', $t->type);
        $this->assertSame(50000, $t->value);
    }

    public function testInputTokensClearAtLeast(): void
    {
        $c = new BetaInputTokensClearAtLeastParam(value: 20000);
        $this->assertSame('input_tokens', $c->type);
        $this->assertSame(['type' => 'input_tokens', 'value' => 20000], $c->toArray());
    }

    public function testToolUsesTrigger(): void
    {
        $t = new BetaToolUsesTriggerParam(value: 10);
        $this->assertSame('tool_uses', $t->type);
        $this->assertSame(10, $t->value);
    }

    public function testToolUsesKeep(): void
    {
        $k = new BetaToolUsesKeepParam(value: 5);
        $this->assertSame('tool_uses', $k->type);
        $this->assertSame(5, $k->value);
    }
}
