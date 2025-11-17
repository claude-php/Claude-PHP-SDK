<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Messages;

use ClaudePhp\Resources\Resource;
use ClaudePhp\Responses\Decoders\JSONLDecoder;
use ClaudePhp\Utils\Transform;
use Psr\Http\Message\StreamInterface;

/**
 * Batches sub-resource for Messages API.
 *
 * Provides methods for batch processing of messages. Batches allow processing
 * many requests asynchronously at a lower cost (50% savings).
 */
class Batches extends Resource
{
    /**
     * Create a batch.
     *
     * Submits a batch of message requests for processing.
     *
     * @param array<string, mixed> $params Batch creation parameters:
     *   - requests: array (required) - Array of batch request objects
     *   - processing_type: string (optional) - How to process the batch
     *
     * @return array<string, mixed> Batch object with id, processing_status, etc.
     */
    public function create(array $params = []): array
    {
        if (!isset($params['requests'])) {
            throw new \InvalidArgumentException('Missing required parameter: requests');
        }

        return $this->_post('/messages/batches', $params);
    }

    /**
     * Retrieve a batch by ID.
     *
     * Gets the status and metadata of a batch.
     *
     * @param string $batchId The batch ID to retrieve
     * @return array<string, mixed> Batch object with current status
     */
    public function retrieve(string $batchId): array
    {
        if (empty($batchId)) {
            throw new \InvalidArgumentException('batch_id is required');
        }

        return $this->_get("/messages/batches/{$batchId}");
    }

    /**
     * List batches.
     *
     * Returns a paginated list of batches.
     *
     * @param array<string, mixed> $params Query parameters:
     *   - limit: int (optional) - Number of batches to return
     *   - before_id: string (optional) - Pagination cursor
     *
     * @return array Response with batches array and pagination
     */
    public function list(array $params = []): array
    {
        $query = Transform::transform($params, [
            'limit' => ['type' => 'int'],
            'before_id' => ['type' => 'string'],
        ]);

        return $this->_get('/messages/batches', $query);
    }

    /**
     * Cancel a batch.
     *
     * Cancels a batch that hasn't finished processing.
     *
     * @param string $batchId The batch ID to cancel
     * @return array<string, mixed> Updated batch object
     */
    public function cancel(string $batchId): array
    {
        if (empty($batchId)) {
            throw new \InvalidArgumentException('batch_id is required');
        }

        return $this->_post("/messages/batches/{$batchId}/cancel", []);
    }

    /**
     * Get results from a batch.
     *
     * Retrieves the results of a completed batch as a JSONL stream decoder.
     *
     * @param string $batchId The batch ID to get results for
     * @return JSONLDecoder Decoder yielding batch result objects
     */
    public function results(string $batchId): JSONLDecoder
    {
        if (empty($batchId)) {
            throw new \InvalidArgumentException('batch_id is required');
        }

        $batch = $this->retrieve($batchId);
        $resultsUrl = $this->extractResultsUrl($batch);

        return $this->fetchResultsStream($resultsUrl);
    }

    /**
     * Delete a batch.
     *
     * Deletes a batch. Batch must be in terminal state.
     *
     * @param string $batchId The batch ID to delete
     * @return void
     */
    public function delete(string $batchId): void
    {
        if (empty($batchId)) {
            throw new \InvalidArgumentException('batch_id is required');
        }

        $this->_delete("/v1/messages/batches/{$batchId}");
    }

    /**
     * Extract the results_url from the API response regardless of format.
     *
     * @param array<string, mixed>|object $batch
     */
    private function extractResultsUrl(array|object $batch): string
    {
        $resultsUrl = null;

        if (is_array($batch)) {
            $resultsUrl = $batch['results_url'] ?? null;
        } elseif (is_object($batch) && isset($batch->results_url)) {
            $resultsUrl = $batch->results_url;
        }

        if ($resultsUrl === null || $resultsUrl === '') {
            $status = null;
            if (is_array($batch)) {
                $status = $batch['processing_status'] ?? null;
            } elseif (is_object($batch) && isset($batch->processing_status)) {
                $status = $batch->processing_status;
            }

            $statusPart = $status ? " Current status: {$status}." : '';
            throw new \RuntimeException('Batch does not have a results_url yet.' . $statusPart);
        }

        return $resultsUrl;
    }

    /**
     * Fetch the batch results JSONL stream and wrap in a decoder.
     */
    private function fetchResultsStream(string $url): JSONLDecoder
    {
        $transport = $this->client->getHttpTransport();
        $response = $transport->getRaw($url, [], array_merge(
            ['Accept' => 'application/binary'],
            $this->getCustomHeaders()
        ));

        $iterator = $this->createStreamIterator($response->getBody());

        return new JSONLDecoder($iterator, 'array', $response);
    }

    /**
     * Convert a PSR stream into a generator of byte chunks.
     *
     * @return \Generator<int, string>
     */
    private function createStreamIterator(StreamInterface $body): \Generator
    {
        while (!$body->eof()) {
            $chunk = $body->read(16384);

            if ($chunk === '' || $chunk === false) {
                if ($body->eof()) {
                    break;
                }

                usleep(1000);
                continue;
            }

            yield $chunk;
        }
    }
}
