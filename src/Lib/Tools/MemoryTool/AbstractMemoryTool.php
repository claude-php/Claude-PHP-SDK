<?php

declare(strict_types=1);

namespace ClaudePhp\Lib\Tools\MemoryTool;

/**
 * Abstract base class for memory tool implementations.
 *
 * Dispatches memory commands to concrete view/create/strReplace/insert/delete/rename methods.
 */
abstract class AbstractMemoryTool
{
    /**
     * Execute a memory command.
     *
     * @param array<string, mixed> $command
     * @return array<string, mixed>
     */
    public function execute(array $command): array
    {
        $commandType = $command['command'] ?? '';

        return match ($commandType) {
            'view' => $this->view($command),
            'create' => $this->create($command),
            'str_replace' => $this->strReplace($command),
            'insert' => $this->insert($command),
            'delete' => $this->delete($command),
            'rename' => $this->rename($command),
            default => ['error' => "Unknown memory command: {$commandType}"],
        };
    }

    /**
     * Get the tool definition for API requests.
     *
     * @return array<string, mixed>
     */
    public function toDict(): array
    {
        return [
            'type' => 'memory_20250818',
            'name' => 'memory',
        ];
    }

    /** @return array<string, mixed> */
    abstract protected function view(array $command): array;

    /** @return array<string, mixed> */
    abstract protected function create(array $command): array;

    /** @return array<string, mixed> */
    abstract protected function strReplace(array $command): array;

    /** @return array<string, mixed> */
    abstract protected function insert(array $command): array;

    /** @return array<string, mixed> */
    abstract protected function delete(array $command): array;

    /** @return array<string, mixed> */
    abstract protected function rename(array $command): array;
}
