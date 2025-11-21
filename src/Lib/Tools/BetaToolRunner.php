<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Tools;

use ClaudePhp\ClaudePhp;
use ClaudePhp\Responses\Message;

/**
 * Iterates beta messages while automatically executing tool calls.
 *
 * Mirrors the Python `client.beta.messages.tool_runner()` helper.
 */
class BetaToolRunner implements \IteratorAggregate
{
    /**
     * @var array<string, BetaToolDefinition>
     */
    private array $toolMap = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    private array $apiToolDefinitions = [];

    public function __construct(
        private readonly ClaudePhp $client,
        private readonly array $baseParams,
        array $tools = [],
    ) {
        $this->toolMap = $this->normalizeTools($tools);
        $this->apiToolDefinitions = array_map(
            static fn (BetaToolDefinition $tool): array => $tool->toApiDefinition(),
            $this->toolMap,
        );
    }

    /**
     * @return \Traversable<int, array<string, mixed>|Message>
     */
    public function getIterator(): \Traversable
    {
        $messages = $this->baseParams['messages'] ?? [];
        $iterations = 0;

        while (true) {
            $payload = $this->baseParams;
            $payload['messages'] = $messages;

            if ([] !== $this->apiToolDefinitions) {
                $payload['tools'] = $this->apiToolDefinitions;
            }

            $response = $this->client->beta()->messages()->create($payload);

            yield $response;

            $toolUses = $this->extractToolUses($response);
            if ([] === $toolUses) {
                break;
            }

            $messages[] = [
                'role' => 'assistant',
                'content' => $this->getResponseContent($response),
            ];
            $messages[] = [
                'role' => 'user',
                'content' => $this->buildToolResults($toolUses),
            ];

            ++$iterations;
            if ($iterations >= ($this->baseParams['max_iterations'] ?? 16)) {
                throw new \RuntimeException('Beta tool runner exceeded maximum iterations');
            }
        }
    }

    /**
     * @param array<int, array<string, mixed>|BetaToolDefinition> $tools
     *
     * @return array<string, BetaToolDefinition>
     */
    private function normalizeTools(array $tools): array
    {
        $normalized = [];
        foreach ($tools as $tool) {
            if ($tool instanceof BetaToolDefinition) {
                $normalized[$tool->name] = $tool;

                continue;
            }

            if (\is_array($tool) && isset($tool['handler']) && \is_callable($tool['handler'])) {
                $definition = BetaToolDefinition::fromCallable($tool['handler'], $tool);
                $normalized[$definition->name] = $definition;

                continue;
            }

            throw new \InvalidArgumentException('Invalid beta tool definition. Provide BetaToolDefinition or array with handler.');
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed>|Message $response
     *
     * @return array<int, array<string, mixed>>
     */
    private function extractToolUses(array|Message $response): array
    {
        $content = $this->getResponseContent($response);
        $toolUses = [];

        foreach ($content as $block) {
            if (($block['type'] ?? null) === 'tool_use') {
                $toolUses[] = $block;
            }
        }

        return $toolUses;
    }

    /**
     * @param array<string, mixed>|Message $response
     *
     * @return array<int, array<string, mixed>>
     */
    private function getResponseContent(array|Message $response): array
    {
        if ($response instanceof Message) {
            return $response->content ?? [];
        }

        return $response['content'] ?? [];
    }

    /**
     * @param array<int, array<string, mixed>> $toolUses
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildToolResults(array $toolUses): array
    {
        $results = [];

        foreach ($toolUses as $toolUse) {
            $name = $toolUse['name'] ?? '';
            $tool = $this->toolMap[$name] ?? null;

            if (null === $tool) {
                $results[] = $this->formatToolError($toolUse, 'No handler registered for tool ' . $name);

                continue;
            }

            try {
                $input = \is_array($toolUse['input'] ?? null) ? $toolUse['input'] : [];
                $result = $tool->invoke($input);
                $results[] = $this->formatToolResult($toolUse, $result);
            } catch (\Throwable $e) {
                $results[] = $this->formatToolError($toolUse, $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * @param array<string, mixed> $toolUse
     *
     * @return array<string, mixed>
     */
    private function formatToolResult(array $toolUse, mixed $result): array
    {
        $content = $this->normalizeToolResultContent($result);

        return [
            'type' => 'tool_result',
            'tool_use_id' => $toolUse['id'] ?? '',
            'content' => $content,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeToolResultContent(mixed $result): array
    {
        if (\is_string($result)) {
            return [['type' => 'text', 'text' => $result]];
        }

        if (\is_array($result) && isset($result[0]['type'])) {
            // @var array<int, array<string, mixed>> $result
            return $result;
        }

        return [[
            'type' => 'text',
            'text' => \is_scalar($result) ? (string) $result : \json_encode($result),
        ]];
    }

    /**
     * @param array<string, mixed> $toolUse
     *
     * @return array<string, mixed>
     */
    private function formatToolError(array $toolUse, string $message): array
    {
        return [
            'type' => 'tool_result',
            'tool_use_id' => $toolUse['id'] ?? '',
            'is_error' => true,
            'content' => [['type' => 'text', 'text' => 'Error: ' . $message]],
        ];
    }
}
