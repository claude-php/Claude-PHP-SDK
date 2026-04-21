<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta\Agents;

use ClaudePhp\Resources\Resource;
use ClaudePhp\Utils\Path;

class Agents extends Resource
{
    public function create(array $params = []): array
    {
        return $this->_post('/agents', $params, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }

    public function retrieve(string $agentId): array
    {
        $path = Path::pathTemplate('/agents/{agent_id}', ['agent_id' => $agentId]);

        return $this->_get($path, null, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }

    public function update(string $agentId, array $params = []): array
    {
        $path = Path::pathTemplate('/agents/{agent_id}', ['agent_id' => $agentId]);

        return $this->_post($path, $params, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }

    public function list(array $params = []): array
    {
        return $this->_get('/agents', null, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }

    public function archive(string $agentId): array
    {
        $path = Path::pathTemplate('/agents/{agent_id}/archive', ['agent_id' => $agentId]);

        return $this->_post($path, [], ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }

    public function versions(): Versions
    {
        return new Versions($this->client);
    }
}
