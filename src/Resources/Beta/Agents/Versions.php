<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta\Agents;

use ClaudePhp\Resources\Resource;
use ClaudePhp\Utils\Path;

class Versions extends Resource
{
    public function list(string $agentId, array $params = []): array
    {
        $path = Path::pathTemplate('/agents/{agent_id}/versions', ['agent_id' => $agentId]);

        return $this->_get($path, null, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }
}
