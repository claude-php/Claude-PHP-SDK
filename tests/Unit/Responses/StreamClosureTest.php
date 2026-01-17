<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Responses;

use ClaudePhp\Responses\StreamResponse;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\TestCase;

/**
 * Tests for stream closure guarantees
 *
 * Based on Python SDK v0.76.0 feature: ensure streams are always closed (388bd0c)
 */
class StreamClosureTest extends TestCase
{
    public function testStreamClosedAfterIteration(): void
    {
        $body = $this->createStreamBody("data: " . json_encode(['type' => 'test', 'data' => 'value']) . "\n\n");
        $response = new Response(200, ['Content-Type' => 'text/event-stream'], $body);
        $streamResponse = new StreamResponse($response);

        // Iterate through stream
        foreach ($streamResponse as $event) {
            $this->assertEquals('test', $event['type']);
        }

        // Stream should be closed after iteration completes
        $this->assertFalse($body->isReadable());
    }

    public function testExplicitCloseMethod(): void
    {
        $body = $this->createStreamBody("data: " . json_encode(['type' => 'test']) . "\n\n");
        $response = new Response(200, ['Content-Type' => 'text/event-stream'], $body);
        $streamResponse = new StreamResponse($response);

        // Explicitly close the stream
        $streamResponse->close();

        // Stream should be closed
        $this->assertFalse($body->isReadable());
    }

    public function testCloseIsIdempotent(): void
    {
        $body = $this->createStreamBody("data: " . json_encode(['type' => 'test']) . "\n\n");
        $response = new Response(200, ['Content-Type' => 'text/event-stream'], $body);
        $streamResponse = new StreamResponse($response);

        // Call close multiple times
        $streamResponse->close();
        $streamResponse->close();
        $streamResponse->close();

        // Should not throw any errors and stream should be closed
        $this->assertFalse($body->isReadable());
    }

    public function testDestructorClosesStream(): void
    {
        $body = $this->createStreamBody("data: " . json_encode(['type' => 'test']) . "\n\n");
        $response = new Response(200, ['Content-Type' => 'text/event-stream'], $body);

        // Create stream response in limited scope
        $streamResponse = new StreamResponse($response);

        // Don't iterate or close explicitly
        // Unset to trigger destructor
        unset($streamResponse);

        // Stream should be closed by destructor
        $this->assertFalse($body->isReadable());
    }

    public function testStreamClosedEvenOnEarlyExit(): void
    {
        $body = $this->createStreamBody(
            "data: " . json_encode(['type' => 'event1']) . "\n\n" .
            "data: " . json_encode(['type' => 'event2']) . "\n\n" .
            "data: " . json_encode(['type' => 'event3']) . "\n\n"
        );
        $response = new Response(200, ['Content-Type' => 'text/event-stream'], $body);
        $streamResponse = new StreamResponse($response);

        // Iterate but break early
        $count = 0;
        foreach ($streamResponse as $event) {
            $count++;
            if ($count === 1) {
                break; // Early exit
            }
        }

        // Explicitly close after early exit
        $streamResponse->close();

        // Stream should be closed
        $this->assertFalse($body->isReadable());
    }

    public function testMultipleStreamsClosedIndependently(): void
    {
        $body1 = $this->createStreamBody("data: " . json_encode(['type' => 'stream1']) . "\n\n");
        $body2 = $this->createStreamBody("data: " . json_encode(['type' => 'stream2']) . "\n\n");

        $response1 = new Response(200, ['Content-Type' => 'text/event-stream'], $body1);
        $response2 = new Response(200, ['Content-Type' => 'text/event-stream'], $body2);

        $stream1 = new StreamResponse($response1);
        $stream2 = new StreamResponse($response2);

        // Close first stream
        $stream1->close();

        // First should be closed, second should still be readable
        $this->assertFalse($body1->isReadable());
        $this->assertTrue($body2->isReadable());

        // Close second stream
        $stream2->close();

        // Both should be closed
        $this->assertFalse($body1->isReadable());
        $this->assertFalse($body2->isReadable());
    }

    public function testEmptyStreamClosesCleanly(): void
    {
        $body = $this->createStreamBody("");
        $response = new Response(200, ['Content-Type' => 'text/event-stream'], $body);
        $streamResponse = new StreamResponse($response);

        // Iterate through empty stream
        $count = 0;
        foreach ($streamResponse as $event) {
            $count++;
        }

        $this->assertEquals(0, $count);

        // Stream should be closed
        $this->assertFalse($body->isReadable());
    }

    public function testStreamWithDoneMarkerClosesCorrectly(): void
    {
        $body = $this->createStreamBody(
            "data: " . json_encode(['type' => 'test', 'data' => 'hello']) . "\n\n" .
            "data: [DONE]\n\n"
        );
        $response = new Response(200, ['Content-Type' => 'text/event-stream'], $body);
        $streamResponse = new StreamResponse($response);

        $events = [];
        foreach ($streamResponse as $event) {
            $events[] = $event;
        }

        // Should have received one event (DONE marker should be filtered out)
        $this->assertCount(1, $events);

        // Stream should be closed
        $this->assertFalse($body->isReadable());
    }

    /**
     * Create a mock stream body for testing
     */
    private function createStreamBody(string $content): Stream
    {
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, $content);
        rewind($resource);

        return new Stream($resource);
    }
}
