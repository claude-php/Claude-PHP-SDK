<?php

declare(strict_types=1);

namespace ClaudePhp\Responses;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Iterator wrapper around Anthropic's server-sent event stream responses.
 *
 * Provides lazy parsing of SSE chunks into associative arrays that can be
 * consumed by the streaming helpers.
 *
 * @implements \IteratorAggregate<int, array<string, mixed>>
 */
class StreamResponse implements \IteratorAggregate
{
    private ?\Generator $generator = null;

    public function __construct(private readonly ResponseInterface $response)
    {
    }

    /**
     * @return \Generator<int, array<string, mixed>>
     */
    public function getIterator(): \Traversable
    {
        if ($this->generator === null) {
            $this->generator = $this->createGenerator();
        }

        return $this->generator;
    }

    /**
     * Access the underlying PSR response.
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Close the underlying stream to free network resources.
     */
    public function close(): void
    {
        $body = $this->response->getBody();
        if (method_exists($body, 'close')) {
            $body->close();
        }
    }

    /**
     * Create the generator that yields decoded SSE events.
     *
     * @return \Generator<int, array<string, mixed>>
     */
    private function createGenerator(): \Generator
    {
        $body = $this->response->getBody();
        $buffer = '';

        while (!$body->eof()) {
            $chunk = $body->read(8192);
            if ($chunk === '') {
                // Avoid tight loops if the transport is non-blocking
                usleep(1000);
                continue;
            }

            $buffer .= $chunk;

            while (($delimiter = $this->findEventDelimiter($buffer)) !== null) {
                [$position, $length] = $delimiter;
                $rawEvent = substr($buffer, 0, $position);
                $buffer = substr($buffer, $position + $length);

                $parsed = $this->parseEventChunk($rawEvent);
                if ($parsed !== null) {
                    yield $parsed;
                }
            }
        }

        $parsed = $this->parseEventChunk($buffer);
        if ($parsed !== null) {
            yield $parsed;
        }

        $this->close();
    }

    /**
     * Find the location of the next SSE event delimiter.
     *
     * @return array{0:int,1:int}|null
     */
    private function findEventDelimiter(string $buffer): ?array
    {
        foreach (["\r\n\r\n", "\n\n", "\r\r"] as $delimiter) {
            $pos = strpos($buffer, $delimiter);
            if ($pos !== false) {
                return [$pos, strlen($delimiter)];
            }
        }

        return null;
    }

    /**
     * Parse a raw SSE event chunk into an array payload.
     *
     * @return array<string, mixed>|null
     */
    private function parseEventChunk(string $chunk): ?array
    {
        $trimmed = trim($chunk);
        if ($trimmed === '' || $trimmed === 'data: [DONE]') {
            return null;
        }

        $lines = preg_split("/\r\n|\r|\n/", $trimmed) ?: [];
        $eventType = null;
        $dataLines = [];

        foreach ($lines as $line) {
            if ($line === '' || str_starts_with($line, ':')) {
                continue;
            }

            if (str_starts_with($line, 'event:')) {
                $eventType = trim(substr($line, 6));
                continue;
            }

            if (str_starts_with($line, 'data:')) {
                $dataLines[] = ltrim(substr($line, 5));
            }
        }

        if ($dataLines === []) {
            return null;
        }

        $payload = implode("\n", $dataLines);
        if ($payload === '[DONE]') {
            return null;
        }

        try {
            /** @var array<string, mixed> $decoded */
            $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException('Failed to decode SSE payload: ' . $e->getMessage(), 0, $e);
        }

        if ($eventType !== null && !isset($decoded['type'])) {
            $decoded['type'] = $eventType;
        }

        return $decoded;
    }
}
