<?php

declare(strict_types=1);

namespace ClaudePhp\Tests\Unit\Responses;

use ClaudePhp\Responses\StreamResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class StreamResponseTest extends TestCase
{
    public function testParsesServerSentEvents(): void
    {
        $sse = implode("\n", [
            'event: message_start',
            'data: {"type":"message_start","message":{"id":"msg_1","role":"assistant","content":[]}}',
            '',
            'event: content_block_delta',
            'data: {"type":"content_block_delta","delta":{"type":"text_delta","text":"Hello"}}',
            '',
            'data: [DONE]',
            '',
        ]);

        $stream = new StreamStub($sse);
        $response = new ResponseStub($stream);
        $streamResponse = new StreamResponse($response);

        $events = iterator_to_array($streamResponse);

        $this->assertCount(2, $events);
        $this->assertSame('message_start', $events[0]['type']);
        $this->assertSame('content_block_delta', $events[1]['type']);
        $this->assertSame('Hello', $events[1]['delta']['text']);
    }

    public function testCloseClosesUnderlyingStream(): void
    {
        $stream = new StreamStub('');
        $response = new ResponseStub($stream);

        $streamResponse = new StreamResponse($response);
        $streamResponse->close();

        $this->assertTrue($stream->closed);
    }
}

/**
 * Simple in-memory stream implementation for testing.
 */
final class StreamStub implements StreamInterface
{
    public bool $closed = false;

    private int $position = 0;
    private int $length = 0;

    public function __construct(private string $contents)
    {
        $this->length = strlen($contents);
    }

    public function __toString(): string
    {
        return $this->contents;
    }

    public function close(): void
    {
        $this->closed = true;
    }

    public function detach()
    {
        $this->close();

        return null;
    }

    public function getSize(): ?int
    {
        return $this->length;
    }

    public function tell(): int
    {
        return $this->position;
    }

    public function eof(): bool
    {
        return $this->position >= $this->length;
    }

    public function isSeekable(): bool
    {
        return true;
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        switch ($whence) {
            case SEEK_CUR:
                $this->position += (int) $offset;

                break;

            case SEEK_END:
                $this->position = $this->length + (int) $offset;

                break;

            case SEEK_SET:
            default:
                $this->position = (int) $offset;

                break;
        }

        $this->position = max(0, min($this->position, $this->length));
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function isWritable(): bool
    {
        return false;
    }

    public function write($string): int
    {
        throw new \RuntimeException('StreamStub is not writable.');
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function read($length): string
    {
        if ($this->eof()) {
            return '';
        }

        $length = max(1, (int) $length);
        $chunk = substr($this->contents, $this->position, $length);
        $this->position += strlen($chunk);

        return $chunk;
    }

    public function getContents(): string
    {
        $remaining = substr($this->contents, $this->position);
        $this->position = $this->length;

        return $remaining;
    }

    public function getMetadata($key = null)
    {
        return null;
    }
}

/**
 * Minimal ResponseInterface implementation backed by a stream.
 */
final class ResponseStub implements ResponseInterface
{
    private string $protocol = '1.1';
    private string $reasonPhrase = 'OK';

    /**
     * @param array<string, array<int, string>> $headers
     */
    public function __construct(
        private StreamInterface $body,
        private int $status = 200,
        private array $headers = [],
    ) {
        $this->headers = $this->normalizeHeaders($this->headers);
    }

    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    public function withProtocolVersion($version): ResponseInterface
    {
        $clone = clone $this;
        $clone->protocol = (string) $version;

        return $clone;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader($name): bool
    {
        $key = strtolower($name);

        return isset($this->headers[$key]);
    }

    public function getHeader($name): array
    {
        $key = strtolower($name);

        return $this->headers[$key] ?? [];
    }

    public function getHeaderLine($name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    public function withHeader($name, $value): ResponseInterface
    {
        $clone = clone $this;
        $clone->headers[strtolower($name)] = (array) $value;

        return $clone;
    }

    public function withAddedHeader($name, $value): ResponseInterface
    {
        $clone = clone $this;
        $key = strtolower($name);
        $clone->headers[$key] = array_merge($clone->headers[$key] ?? [], (array) $value);

        return $clone;
    }

    public function withoutHeader($name): ResponseInterface
    {
        $clone = clone $this;
        unset($clone->headers[strtolower($name)]);

        return $clone;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): ResponseInterface
    {
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }

    public function withStatus($code, $reasonPhrase = ''): ResponseInterface
    {
        $clone = clone $this;
        $clone->status = (int) $code;
        $clone->reasonPhrase = (string) $reasonPhrase;

        return $clone;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    /**
     * @param array<string, array<int, string>> $headers
     *
     * @return array<string, array<int, string>>
     */
    private function normalizeHeaders(array $headers): array
    {
        $normalized = [];
        foreach ($headers as $name => $values) {
            $normalized[strtolower($name)] = (array) $values;
        }

        return $normalized;
    }
}
