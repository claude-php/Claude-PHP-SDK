<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Responses\Decoders;

use ClaudePhp\Responses\Decoders\JSONLDecoder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class JSONLDecoderTest extends TestCase
{
    /**
     * @var ResponseInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private ResponseInterface $mockResponse;

    protected function setUp(): void
    {
        $this->mockResponse = $this->createMock(ResponseInterface::class);
    }

    public function testDecodeSingleLine(): void
    {
        $jsonLine = '{"id": "msg_123", "type": "message"}';
        $iterator = new \ArrayIterator([$jsonLine]);

        $decoder = new JSONLDecoder($iterator, 'array', $this->mockResponse);

        $items = iterator_to_array($decoder);

        $this->assertCount(1, $items);
        $this->assertSame('msg_123', $items[0]['id']);
        $this->assertSame('message', $items[0]['type']);
    }

    public function testDecodeMultipleLines(): void
    {
        $jsonLines = [
            '{"id": "msg_1", "status": "queued"}' . "\n",
            '{"id": "msg_2", "status": "processing"}' . "\n",
            '{"id": "msg_3", "status": "succeeded"}',
        ];
        $iterator = new \ArrayIterator($jsonLines);

        $decoder = new JSONLDecoder($iterator, 'array', $this->mockResponse);

        $items = iterator_to_array($decoder);

        $this->assertCount(3, $items);
        $this->assertSame('msg_1', $items[0]['id']);
        $this->assertSame('msg_2', $items[1]['id']);
        $this->assertSame('msg_3', $items[2]['id']);
    }

    public function testDecodeWithDifferentLineEndings(): void
    {
        $jsonLines = [
            '{"id": "msg_1"}' . "\r\n",
            '{"id": "msg_2"}' . "\r",
            '{"id": "msg_3"}' . "\n",
        ];
        $iterator = new \ArrayIterator($jsonLines);

        $decoder = new JSONLDecoder($iterator, 'array', $this->mockResponse);

        $items = iterator_to_array($decoder);

        $this->assertCount(3, $items);
        $this->assertSame('msg_1', $items[0]['id']);
        $this->assertSame('msg_2', $items[1]['id']);
        $this->assertSame('msg_3', $items[2]['id']);
    }

    public function testDecodeWithChunkedData(): void
    {
        // Simulate streaming data arriving in chunks
        $chunks = [
            '{"id": "msg_1"}',
            "\n",
            '{"id": "msg_2"}',
            "\n",
        ];
        $iterator = new \ArrayIterator($chunks);

        $decoder = new JSONLDecoder($iterator, 'array', $this->mockResponse);

        $items = iterator_to_array($decoder);

        $this->assertCount(2, $items);
        $this->assertSame('msg_1', $items[0]['id']);
        $this->assertSame('msg_2', $items[1]['id']);
    }

    public function testDecodeEmptyLines(): void
    {
        $jsonLines = [
            '{"id": "msg_1"}' . "\n",
            "\n",  // Empty line
            '{"id": "msg_2"}' . "\n",
        ];
        $iterator = new \ArrayIterator($jsonLines);

        $decoder = new JSONLDecoder($iterator, 'array', $this->mockResponse);

        $items = iterator_to_array($decoder);

        // Empty lines should be skipped
        $this->assertCount(2, $items);
        $this->assertSame('msg_1', $items[0]['id']);
        $this->assertSame('msg_2', $items[1]['id']);
    }

    public function testDecodeBatchResultFormat(): void
    {
        // Test with typical batch result format
        $jsonLines = [
            '{"custom_id": "request_1", "result": {"type": "succeeded", "message": {"id": "msg_1", "content": [{"type": "text", "text": "Hello"}]}}}' . "\n",
            '{"custom_id": "request_2", "result": {"type": "succeeded", "message": {"id": "msg_2", "content": [{"type": "text", "text": "World"}]}}}' . "\n",
        ];
        $iterator = new \ArrayIterator($jsonLines);

        $decoder = new JSONLDecoder($iterator, 'array', $this->mockResponse);

        $items = iterator_to_array($decoder);

        $this->assertCount(2, $items);
        $this->assertSame('request_1', $items[0]['custom_id']);
        $this->assertSame('request_2', $items[1]['custom_id']);
    }

    public function testIteratorInterface(): void
    {
        $jsonLines = [
            '{"id": "msg_1"}' . "\n",
            '{"id": "msg_2"}' . "\n",
            '{"id": "msg_3"}' . "\n",
        ];
        $iterator = new \ArrayIterator($jsonLines);

        $decoder = new JSONLDecoder($iterator, 'array', $this->mockResponse);

        // Test iterator interface methods
        $decoder->rewind();
        $this->assertTrue($decoder->valid());
        $this->assertSame(0, $decoder->key());

        $first = $decoder->current();
        $this->assertSame('msg_1', $first['id']);

        $decoder->next();
        $this->assertTrue($decoder->valid());
        $this->assertSame(1, $decoder->key());
    }

    public function testInvalidJsonThrowsException(): void
    {
        $this->expectException(\JsonException::class);

        $jsonLines = [
            '{invalid json}' . "\n",
        ];
        $iterator = new \ArrayIterator($jsonLines);

        $decoder = new JSONLDecoder($iterator, 'array', $this->mockResponse);

        iterator_to_array($decoder);
    }
}
