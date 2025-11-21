<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Vertex;

use ClaudePhp\Responses\Message;

/**
 * Async Anthropic client for Google Vertex AI.
 *
 * Async variant of AnthropicVertex for use with async/await patterns.
 */
class AsyncAnthropicVertex
{
    /**
     * @var string Google Cloud project ID
     */
    private string $projectId;

    /**
     * @var null|string Google Cloud region
     */
    private ?string $region;

    /**
     * @var null|string API access token
     */
    private ?string $accessToken;

    /**
     * Create a new AsyncAnthropicVertex client.
     *
     * @param string $projectId Google Cloud project ID
     * @param null|string $region Google Cloud region (default: us-central1)
     * @param null|string $accessToken Optional access token
     */
    public function __construct(
        string $projectId,
        ?string $region = null,
        ?string $accessToken = null,
    ) {
        $this->projectId = $projectId;
        $this->region = $region ?? 'us-central1';
        $this->accessToken = $accessToken ?? $this->loadAccessToken();
    }

    /**
     * Create a message via Vertex AI asynchronously.
     *
     * @param array<string, mixed> $params Message parameters
     *
     * @return mixed Promise resolving to Message
     */
    public function createMessage(array $params): mixed
    {
        return \Amp\async(function () use ($params) {
            $vertexClient = new AnthropicVertex(
                $this->projectId,
                $this->region,
                $this->accessToken,
            );

            return $vertexClient->createMessage($params);
        });
    }

    /**
     * Create a message with streaming via Vertex AI asynchronously.
     *
     * @param array<string, mixed> $params Message parameters
     * @param null|callable $onChunk Optional async callback for each chunk
     *
     * @return mixed Promise resolving to Message
     */
    public function createMessageStream(
        array $params,
        ?callable $onChunk = null,
    ): mixed {
        return \Amp\async(function () use ($params, $onChunk) {
            $vertexClient = new AnthropicVertex(
                $this->projectId,
                $this->region,
                $this->accessToken,
            );

            return $vertexClient->createMessageStream(
                $params,
                $onChunk,
            );
        });
    }

    /**
     * Load access token from Google Cloud auth.
     */
    private function loadAccessToken(): ?string
    {
        // In production, use google/auth library
        return null;
    }
}
