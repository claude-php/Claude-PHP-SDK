<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta\Agents;

use ClaudePhp\Resources\Resource;
use ClaudePhp\Utils\Path;

class Agents extends Resource
{
    private const BETA_HEADER = ['anthropic-beta' => 'managed-agents-2026-04-01'];

    public function create(array $params = []): array
    {
        return $this->_post('/agents?beta=true', $params, self::BETA_HEADER);
    }

    public function retrieve(string $agentId): array
    {
        $path = Path::pathTemplate('/agents/{agent_id}?beta=true', ['agent_id' => $agentId]);

        return $this->_get($path, null, self::BETA_HEADER);
    }

    public function update(string $agentId, array $params = []): array
    {
        $path = Path::pathTemplate('/agents/{agent_id}?beta=true', ['agent_id' => $agentId]);

        return $this->_post($path, $params, self::BETA_HEADER);
    }

    public function list(array $params = []): array
    {
        return $this->_get('/agents?beta=true', $params, self::BETA_HEADER);
    }

    public function archive(string $agentId): array
    {
        $path = Path::pathTemplate('/agents/{agent_id}/archive?beta=true', ['agent_id' => $agentId]);

        return $this->_post($path, [], self::BETA_HEADER);
    }

    public function versions(): Versions
    {
        return new Versions($this->client);
    }
}
