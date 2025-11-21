<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Extras;

/**
 * Batch processing utilities for efficiently processing multiple requests.
 *
 * Provides helpers for creating and managing batch requests to take advantage
 * of the 50% cost savings with Claude's batch processing API.
 */
class BatchUtils
{
    /**
     * Create a batch request from multiple message creation requests.
     *
     * @param array<array<string, mixed>> $requests Array of message creation parameters
     *
     * @return array<string, mixed> Batch request payload
     */
    public static function createBatchRequests(array $requests): array
    {
        $batchRequests = [];

        foreach ($requests as $index => $request) {
            $batchRequests[] = [
                'custom_id' => $request['custom_id'] ?? "request-{$index}",
                'params' => [
                    'model' => $request['model'] ?? 'claude-sonnet-4-5-20250929',
                    'max_tokens' => $request['max_tokens'] ?? 1024,
                    'messages' => $request['messages'] ?? [],
                    'system' => $request['system'] ?? null,
                    'tools' => $request['tools'] ?? null,
                    'temperature' => $request['temperature'] ?? null,
                    'top_p' => $request['top_p'] ?? null,
                ],
            ];
        }

        return $batchRequests;
    }

    /**
     * Submit a batch job.
     *
     * @param mixed $client The API client
     * @param array<array<string, mixed>> $requests Batch requests
     *
     * @return string Batch ID
     */
    public static function submitBatch(
        mixed $client,
        array $requests,
    ): string {
        $batchRequests = self::createBatchRequests($requests);

        $response = $client->batches()->create([
            'requests' => $batchRequests,
        ]);

        return $response['id'] ?? throw new \RuntimeException('No batch ID returned');
    }

    /**
     * Poll for batch completion.
     *
     * @param mixed $client The API client
     * @param string $batchId Batch ID
     * @param int $maxAttempts Maximum polling attempts
     * @param int $delaySeconds Delay between polls
     *
     * @return array<string, mixed> Completed batch response
     */
    public static function waitForBatchCompletion(
        mixed $client,
        string $batchId,
        int $maxAttempts = 60,
        int $delaySeconds = 10,
    ): array {
        for ($attempt = 0; $attempt < $maxAttempts; ++$attempt) {
            $batch = $client->batches()->retrieve($batchId);

            if (isset($batch['processing_status'])) {
                if ('succeeded' === $batch['processing_status']) {
                    return $batch;
                }
                if ('failed' === $batch['processing_status']) {
                    throw new \RuntimeException(
                        "Batch {$batchId} failed: " . ($batch['error_message'] ?? 'Unknown error'),
                    );
                }
                if ('expired' === $batch['processing_status']) {
                    throw new \RuntimeException("Batch {$batchId} expired");
                }
            }

            // Not done yet, wait and retry
            if ($attempt < $maxAttempts - 1) {
                \sleep($delaySeconds);
            }
        }

        throw new \RuntimeException(
            "Batch {$batchId} did not complete within " . ($maxAttempts * $delaySeconds) . ' seconds',
        );
    }

    /**
     * Get results from a completed batch.
     *
     * @param mixed $client The API client
     * @param string $batchId Batch ID
     *
     * @return array<array<string, mixed>> Batch results indexed by custom_id
     */
    public static function getBatchResults(
        mixed $client,
        string $batchId,
    ): array {
        $results = [];
        $batchResults = $client->batches()->results($batchId);

        foreach ($batchResults as $result) {
            $customId = $result['custom_id'] ?? '';
            $results[$customId] = $result;
        }

        return $results;
    }

    /**
     * Run a batch job from start to finish.
     *
     * @param mixed $client The API client
     * @param array<array<string, mixed>> $requests Batch requests
     *
     * @return array<array<string, mixed>> Results indexed by custom_id
     */
    public static function runBatch(
        mixed $client,
        array $requests,
    ): array {
        // Submit batch
        $batchId = self::submitBatch($client, $requests);

        // Wait for completion
        self::waitForBatchCompletion($client, $batchId);

        // Get results
        return self::getBatchResults($client, $batchId);
    }
}
