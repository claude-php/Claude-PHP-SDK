<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Responses\Helpers;

use ClaudePhp\Responses\Helpers\StreamEventHelper;
use PHPUnit\Framework\TestCase;

final class StreamEventHelperTest extends TestCase
{
    public function testDetectsTextDelta(): void
    {
        $event = [
            'type' => 'content_block_delta',
            'delta' => [
                'type' => 'text_delta',
                'text' => 'Hello',
            ],
        ];

        $this->assertTrue(StreamEventHelper::isTextDelta($event));
        $this->assertSame('Hello', StreamEventHelper::textDelta($event));
    }

    public function testDetectsInputJsonDelta(): void
    {
        $event = [
            'type' => 'content_block_delta',
            'delta' => [
                'type' => 'input_json_delta',
                'partial_json' => '{"location":"SF"}',
            ],
        ];

        $this->assertTrue(StreamEventHelper::isInputJsonDelta($event));
        $this->assertSame('{"location":"SF"}', StreamEventHelper::inputJsonDelta($event));
    }

    public function testDetectsMessageStop(): void
    {
        $event = ['type' => 'message_stop'];

        $this->assertTrue(StreamEventHelper::isMessageStop($event));
    }
}
