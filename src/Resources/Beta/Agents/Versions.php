<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta\Agents;

use ClaudePhp\Resources\Resource;
use ClaudePhp\Utils\Path;

class Versions extends Resource
{
    private const BETA_HEADER = ['anthropic-beta' => 'managed-agents-2026-04-01'];

    public function list(string $agentId, array $params = []): array
    {
        $path = Path::pathTemplate('/agents/{agent_id}/versions?beta=true', ['agent_id' => $agentId]);

        return $this->_get($path, $params, self::BETA_HEADER);
    }
}
