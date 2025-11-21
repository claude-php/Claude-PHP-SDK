<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Bedrock;

use ClaudePhp\Lib\Streaming\AsyncMessageStreamManager;
use ClaudePhp\Responses\Message;

/**
 * Async Anthropic client for AWS Bedrock.
 *
 * Async variant of AnthropicBedrock for use with async/await patterns.
 */
class AsyncAnthropicBedrock
{
    /**
     * @var null|string AWS region
     */
    private ?string $region;

    /**
     * @var array<string, mixed> AWS configuration
     */
    private array $awsConfig;

    /**
     * Create a new AsyncAnthropicBedrock client.
     *
     * @param array<string, mixed> $config AWS SDK configuration
     * @param null|string $region AWS region
     */
    public function __construct(array $config = [], ?string $region = null)
    {
        $this->awsConfig = $config;
        $this->region = $region;
    }

    /**
     * Create a message via Bedrock asynchronously.
     *
     * @param array<string, mixed> $params Message parameters
     *
     * @return mixed Promise resolving to Message
     */
    public function createMessage(array $params): mixed
    {
        return \Amp\async(function () use ($params) {
            // In practice, this would use async AWS SDK or similar
            $bedrockClient = new AnthropicBedrock($this->awsConfig, $this->region);

            return $bedrockClient->createMessage($params);
        });
    }

    /**
     * Create a message with streaming via Bedrock asynchronously.
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
            $bedrockParams = $this->transformParams($params);
            $bedrockParams['stream'] = true;

            // In practice, this would use async Bedrock client
            $manager = new AsyncMessageStreamManager();

            // Simulate event processing
            $events = [];
            foreach ($events as $event) {
                $manager->addEvent($event);
                if (null !== $onChunk) {
                    ($onChunk)($event);
                }
            }

            return $manager->getMessage();
        });
    }

    /**
     * Transform SDK params to Bedrock format.
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    private function transformParams(array $params): array
    {
        return [
            'anthropic_version' => '2023-06-01',
            'max_tokens' => $params['max_tokens'] ?? 1024,
            'messages' => $params['messages'] ?? [],
            'system' => $params['system'] ?? null,
            'tools' => $params['tools'] ?? null,
            'temperature' => $params['temperature'] ?? null,
            'top_p' => $params['top_p'] ?? null,
        ];
    }
}
