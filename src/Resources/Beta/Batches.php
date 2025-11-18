<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta;

use ClaudePhp\Resources\Resource;
use ClaudePhp\Responses\Decoders\JSONLDecoder;
use ClaudePhp\Utils\Transform;
use Psr\Http\Message\StreamInterface;

/**
 * Batches sub-resource for beta Messages API.
 *
 * Beta variant of batch processing with experimental features.
 */
class Batches extends Resource
{
    /**
     * Create a batch.
     *
     * @param array<string, mixed> $params Batch parameters
     * @return array Batch response
     */
    public function create(array $params = []): array
    {
        if (!isset($params['requests'])) {
            throw new \InvalidArgumentException('requests parameter is required');
        }

        return $this->_post('/messages/batches', $params);
    }

    /**
     * Retrieve a batch.
     *
     * @param string $batchId Batch ID
     * @return array Batch response
     */
    public function retrieve(string $batchId): array
    {
        if (empty($batchId)) {
            throw new \InvalidArgumentException('batch_id is required');
        }

        return $this->_get("/messages/batches/{$batchId}");
    }

    /**
     * Cancel a batch.
     *
     * @param string $batchId Batch ID
     * @return array Updated batch response
     */
    public function cancel(string $batchId): array
    {
        if (empty($batchId)) {
            throw new \InvalidArgumentException('batch_id is required');
        }

        return $this->_post("/messages/batches/{$batchId}/cancel", []);
    }

    /**
     * Get batch results.
     *
     * @param string $batchId Batch ID
     * @return JSONLDecoder Results iterator
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
     * Extract results_url from a beta batch payload.
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
     * Fetch and decode the JSONL results payload.
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
     * Convert a PSR stream into an iterator of byte chunks.
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
