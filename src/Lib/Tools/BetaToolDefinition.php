<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Tools;

use Closure;

/**
 * Represents a runnable beta tool definition (callable + schema metadata).
 */
class BetaToolDefinition
{
    private readonly Closure $handler;

    /**
     * @param callable(array<string, mixed>):mixed $handler
     * @param array<string, mixed> $inputSchema
     */
    public function __construct(
        public readonly string $name,
        callable $handler,
        public readonly string $description = '',
        private readonly array $inputSchema = [],
    ) {
        $this->handler = $handler instanceof Closure ? $handler : Closure::fromCallable($handler);
    }

    /**
     * Create a beta tool definition from a callable configuration array.
     *
     * @param array{
     *     name: string,
     *     handler?: callable,
     *     description?: string,
     *     input_schema?: array<string, mixed>
     * } $definition
     */
    public static function fromCallable(callable $handler, array $definition): self
    {
        if (!isset($definition['name'])) {
            throw new \InvalidArgumentException('Tool definitions require a name.');
        }

        return new self(
            name: $definition['name'],
            handler: $handler,
            description: $definition['description'] ?? '',
            inputSchema: $definition['input_schema'] ?? [],
        );
    }

    /**
     * Invoke the tool handler with decoded JSON input.
     *
     * @param array<string, mixed> $input
     */
    public function invoke(array $input): mixed
    {
        return ($this->handler)($input);
    }

    /**
     * Convert to the API representation required by Claude.
     *
     * @return array<string, mixed>
     */
    public function toApiDefinition(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'input_schema' => $this->inputSchema,
        ];
    }
}
