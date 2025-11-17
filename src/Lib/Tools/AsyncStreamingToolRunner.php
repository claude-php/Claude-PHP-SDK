<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Tools;

use ClaudePhp\ClaudePhp;
use ClaudePhp\Lib\Streaming\AsyncMessageStreamManager;
use ClaudePhp\Responses\Message;

/**
 * AsyncStreamingToolRunner manages async tool execution with streaming responses.
 *
 * Async variant that handles tool calls in an agentic loop with streaming message support.
 */
class AsyncStreamingToolRunner
{
    /**
     * @var ClaudePhp The async API client
     */
    private ClaudePhp $client;

    /**
     * @var array<string, callable> Registered tools/functions
     */
    private array $tools = [];

    /**
     * @var int Maximum iterations for the agentic loop
     */
    private int $maxIterations;

    /**
     * Create a new AsyncStreamingToolRunner.
     *
     * @param ClaudePhp $client The async API client
     * @param array<string, callable> $tools Mapping of tool names to async callables
     * @param int $maxIterations Maximum iterations
     */
    public function __construct(
        ClaudePhp $client,
        array $tools = [],
        int $maxIterations = 10
    ) {
        $this->client = $client;
        $this->tools = $tools;
        $this->maxIterations = $maxIterations;
    }

    /**
     * Register a tool/function.
     *
     * @param string $name Tool name
     * @param callable $callable The async tool implementation
     */
    public function registerTool(string $name, callable $callable): void
    {
        $this->tools[$name] = $callable;
    }

    /**
     * Run the agentic loop with streaming responses asynchronously.
     *
     * @param array<string, mixed> $params Message creation parameters
     * @param callable|null $onStream Optional async callback for stream events
     * @return mixed Promise resolving to the final message
     */
    public function run(array $params, ?callable $onStream = null): mixed
    {
        return \Amp\async(function () use ($params, $onStream) {
            $messages = $params['messages'] ?? [];
            $iterationCount = 0;

            while ($iterationCount < $this->maxIterations) {
                $iterationCount++;

                // Create streaming message
                $stream = $this->client->messages()->stream([
                    ...$params,
                    'messages' => $messages,
                ]);

                // Aggregate stream events
                $manager = new AsyncMessageStreamManager();
                foreach ($stream as $event) {
                    $manager->addEvent($event);
                    if ($onStream !== null) {
                        ($onStream)($event);
                    }
                }

                $response = $manager->getMessage();

                // Check if there are tool uses
                $hasToolUse = false;
                $toolResults = [];

                foreach ($response->content ?? [] as $block) {
                    if ($block['type'] === 'tool_use') {
                        $hasToolUse = true;
                        $toolName = $block['name'] ?? '';
                        $toolInput = $block['input'] ?? [];
                        $toolId = $block['id'] ?? '';

                        if (isset($this->tools[$toolName])) {
                            try {
                                $result = ($this->tools[$toolName])($toolInput);
                                $toolResults[] = [
                                    'type' => 'tool_result',
                                    'tool_use_id' => $toolId,
                                    'content' => (string)$result,
                                ];
                            } catch (\Throwable $e) {
                                $toolResults[] = [
                                    'type' => 'tool_result',
                                    'tool_use_id' => $toolId,
                                    'content' => 'Error: ' . $e->getMessage(),
                                    'is_error' => true,
                                ];
                            }
                        }
                    }
                }

                // If no tool use, we're done
                if (!$hasToolUse) {
                    return $response;
                }

                // Add assistant response to messages
                $messages[] = [
                    'role' => 'assistant',
                    'content' => $response->content,
                ];

                // Add tool results
                $messages[] = [
                    'role' => 'user',
                    'content' => $toolResults,
                ];
            }

            throw new \RuntimeException(
                "Async streaming tool runner reached maximum iterations ({$this->maxIterations})"
            );
        });
    }
}
