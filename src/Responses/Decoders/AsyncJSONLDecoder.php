<?php

declare(strict_types=1);

namespace ClaudePhp\Responses\Decoders;

use Psr\Http\Message\ResponseInterface;

/**
 * An asynchronous decoder for JSON Lines (JSONL) format.
 *
 * This class provides an async iterator over a byte-iterator that parses each JSON Line
 * into a given type. Used primarily for streaming responses from the Batch API with async support.
 *
 * For synchronous contexts, use JSONLDecoder instead.
 * For async contexts with Amphp, this can be used with async iterables.
 *
 * @template T
 */
class AsyncJSONLDecoder
{
    /**
     * @var ResponseInterface
     */
    private ResponseInterface $response;

    /**
     * @var iterable<int, string>
     */
    private iterable $rawIterator;

    /**
     * @var class-string<T>
     */
    private string $lineType;

    /**
     * Create a new async JSONL decoder.
     *
     * @template T
     * @param iterable<int, string> $rawIterator Async or sync iterable over byte chunks
     * @param class-string<T> $lineType The class type to deserialize each line into
     * @param ResponseInterface $response The HTTP response object
     */
    public function __construct(
        iterable $rawIterator,
        string $lineType,
        ResponseInterface $response
    ) {
        $this->rawIterator = $rawIterator;
        $this->lineType = $lineType;
        $this->response = $response;
    }

    /**
     * Close the response body stream asynchronously.
     *
     * This is called automatically if you consume the entire stream.
     */
    public function close(): void
    {
        // In PSR-7, we don't explicitly close the stream, but we can get the body
        // and close it if it implements Closeable
        $body = $this->response->getBody();
        if (method_exists($body, 'close')) {
            $body->close();
        }
    }

    /**
     * Get the generator for decoding JSONL asynchronously.
     *
     * This returns a PHP Generator that can be used with async/await patterns.
     *
     * Example usage with Amphp:
     * ```php
     * $decoder = new AsyncJSONLDecoder($asyncIterator, Message::class, $response);
     * foreach ($decoder->decode() as $item) {
     *     // In async context, use \Amp\await() if needed
     *     echo $item['id'];
     * }
     * ```
     *
     * @return \Generator<int, T, void, void>
     */
    public function decode(): \Generator
    {
        $buffer = '';

        foreach ($this->rawIterator as $chunk) {
            $buffer .= $chunk;

            // Process complete lines
            while (true) {
                // Check for different line ending styles, prioritizing \r\n
                $crlfPos = strpos($buffer, "\r\n");
                $lfPos = strpos($buffer, "\n");
                $crPos = strpos($buffer, "\r");

                // Determine which line ending comes first
                $endPosition = -1;
                $endingLength = 0;

                if ($crlfPos !== false) {
                    $endPosition = $crlfPos;
                    $endingLength = 2;
                } elseif ($lfPos !== false) {
                    $endPosition = $lfPos;
                    $endingLength = 1;
                } elseif ($crPos !== false) {
                    $endPosition = $crPos;
                    $endingLength = 1;
                }

                if ($endPosition === -1) {
                    // No complete line yet, wait for more data
                    break;
                }

                // Extract the line (without the line ending)
                $line = substr($buffer, 0, $endPosition);
                $buffer = substr($buffer, $endPosition + $endingLength);

                // Parse non-empty lines
                $trimmedLine = trim($line);
                if (!empty($trimmedLine)) {
                    yield $this->parseLine($trimmedLine);
                }
            }
        }

        // Flush any remaining buffer
        $trimmedBuffer = trim($buffer);
        if (!empty($trimmedBuffer)) {
            yield $this->parseLine($trimmedBuffer);
        }

        // Close the stream when finished
        $this->close();
    }

    /**
     * Parse a single JSON line.
     *
     * @param string $line
     * @return mixed
     * @throws \JsonException
     */
    private function parseLine(string $line): mixed
    {
        $data = json_decode($line, true, 512, JSON_THROW_ON_ERROR);

        // If a specific type is required, instantiate it from the data
        // This is a simple approach; for more complex types, use a proper deserializer
        if ($this->lineType !== 'array' && class_exists($this->lineType)) {
            return new ($this->lineType)($data);
        }

        return $data;
    }
}
