<?php

declare(strict_types=1);

namespace ClaudePhp\Responses\Decoders;

use Iterator;
use Psr\Http\Message\ResponseInterface;

/**
 * A decoder for JSON Lines (JSONL) format.
 *
 * This class provides an iterator interface over a stream of byte chunks that parses
 * each JSON Line into the specified type. Used primarily for retrieving results from
 * the Batch Processing API endpoint: GET /v1/messages/batches/{id}/results
 *
 * The decoder handles multiple line ending styles (\r\n, \n, \r) and properly buffers
 * incomplete lines across chunk boundaries.
 *
 * @template T
 * @implements Iterator<int, T>
 */
class JSONLDecoder implements Iterator
{
    /**
     * @var ResponseInterface The HTTP response object
     */
    private ResponseInterface $response;

    /**
     * @var Iterator<int, string> Iterator over byte chunks (strings in PHP)
     */
    private Iterator $rawIterator;

    /**
     * @var class-string<T>|'array' The class type to deserialize each line into, or 'array' for associative arrays
     */
    private string $lineType;

    /**
     * @var \Generator<int, mixed>|null The internal generator for line parsing
     */
    private ?\Generator $generator = null;

    /**
     * @var int Current iteration key (0-based index)
     */
    private int $currentKey = 0;

    /**
     * @var mixed Current iteration value
     */
    private mixed $currentValue = null;

    /**
     * @var bool Whether the current position is valid
     */
    private bool $valid = false;

    /**
     * Create a new JSONL decoder.
     *
     * @template T
     * @param Iterator<int, string> $rawIterator Iterator over byte chunks from the response stream
     * @param class-string<T>|'array' $lineType The class type to deserialize each line into.
     *                                            Use 'array' for arrays, or a class name for objects.
     * @param ResponseInterface $response The HTTP response object
     *
     * @example
     * ```php
     * $decoder = new JSONLDecoder(
     *     rawIterator: $chunkIterator,
     *     lineType: 'array',  // or BatchRequestResult::class
     *     response: $response
     * );
     *
     * foreach ($decoder as $index => $line) {
     *     echo "Line $index: " . json_encode($line) . "\n";
     * }
     * ```
     */
    public function __construct(
        Iterator $rawIterator,
        string $lineType,
        ResponseInterface $response
    ) {
        $this->rawIterator = $rawIterator;
        $this->lineType = $lineType;
        $this->response = $response;
    }

    /**
     * Close the response body stream.
     *
     * This is called automatically when iteration completes or can be called manually
     * to release resources early.
     *
     * @return void
     */
    public function close(): void
    {
        // In PSR-7, streams implement StreamInterface which may be closeable
        $body = $this->response->getBody();
        if (method_exists($body, 'close')) {
            $body->close();
        }
    }

    /**
     * Internal generator that parses JSONL from raw byte chunks.
     *
     * This generator handles:
     * - Buffering incomplete lines across chunk boundaries
     * - Multiple line ending styles (\r\n, \n, \r)
     * - Yielding deserialized objects
     *
     * @return \Generator<int, mixed> Generator yielding parsed line objects
     * @throws \JsonException If a line contains invalid JSON
     */
    private function decode(): \Generator
    {
        $buffer = '';

        foreach ($this->rawIterator as $chunk) {
            $buffer .= $chunk;

            // Process all complete lines in the buffer
            while (true) {
                $endingLength = 0;
                $endPosition = $this->findLineEnd($buffer, $endingLength);

                if ($endPosition === -1) {
                    // No complete line found, wait for more data
                    break;
                }

                // Extract the line (without line ending)
                $line = substr($buffer, 0, $endPosition);
                $buffer = substr($buffer, $endPosition + $endingLength);

                // Deserialize non-empty lines
                $trimmedLine = trim($line);
                if ($trimmedLine !== '') {
                    yield $this->deserializeLine($trimmedLine);
                }
            }
        }

        // Process any remaining data in the buffer after all chunks are consumed
        $trimmedBuffer = trim($buffer);
        if ($trimmedBuffer !== '') {
            yield $this->deserializeLine($trimmedBuffer);
        }
    }

    /**
     * Find the position of the next line ending in the buffer.
     *
     * Detects line endings in order of preference: \r\n, \n, \r
     *
     * @param string $buffer The buffer to search
     * @param int &$endingLength Output parameter: length of the detected line ending (1 or 2)
     * @return int Position of line ending, or -1 if not found
     */
    private function findLineEnd(string $buffer, int &$endingLength): int
    {
        $crlfPos = strpos($buffer, "\r\n");
        $lfPos = strpos($buffer, "\n");
        $crPos = strpos($buffer, "\r");

        // Check \r\n first (Windows/HTTP standard)
        if ($crlfPos !== false) {
            $endingLength = 2;
            return $crlfPos;
        }

        // Check \n (Unix)
        if ($lfPos !== false) {
            $endingLength = 1;
            return $lfPos;
        }

        // Check \r (legacy Mac, but could appear if \r\n was partially read)
        if ($crPos !== false) {
            $endingLength = 1;
            return $crPos;
        }

        return -1;
    }

    /**
     * Deserialize a single JSONL line into the target type.
     *
     * @param string $line The JSON string to deserialize
     * @return mixed The deserialized value (array or object instance)
     * @throws \JsonException If the JSON is invalid
     */
    private function deserializeLine(string $line): mixed
    {
        $data = json_decode($line, true, 512, JSON_THROW_ON_ERROR);

        // If the target type is 'array', return the associative array as-is
        if ($this->lineType === 'array') {
            return $data;
        }

        // If a class name is specified and it exists, instantiate it
        // Assumes the class has a constructor that accepts array data
        if (class_exists($this->lineType)) {
            return new ($this->lineType)($data);
        }

        // Fallback: return the array if the class doesn't exist
        // (allows graceful degradation in some scenarios)
        return $data;
    }

    /**
     * Get the current value in the iteration.
     *
     * @return mixed
     */
    public function current(): mixed
    {
        return $this->currentValue;
    }

    /**
     * Get the current key in the iteration.
     *
     * @return int
     */
    public function key(): mixed
    {
        return $this->currentKey;
    }

    /**
     * Move to the next element.
     *
     * @return void
     */
    public function next(): void
    {
        if ($this->generator === null) {
            return;
        }

        $this->generator->next();
        $this->valid = $this->generator->valid();

        if ($this->valid) {
            $this->currentValue = $this->generator->current();
            $this->currentKey++;
        }
    }

    /**
     * Rewind the iterator to the beginning.
     *
     * @return void
     */
    public function rewind(): void
    {
        $this->generator = $this->decode();
        $this->currentKey = 0;
        $this->currentValue = null;
        $this->valid = false;

        // Advance to the first element
        if ($this->generator->valid()) {
            $this->currentValue = $this->generator->current();
            $this->valid = true;
        }
    }

    /**
     * Check if the current position is valid.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return $this->valid;
    }
}
