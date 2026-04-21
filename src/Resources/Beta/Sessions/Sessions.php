<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta\Sessions;

use ClaudePhp\Resources\Resource;
use ClaudePhp\Utils\Path;

class Sessions extends Resource
{
    public function create(array $params = []): array
    {
        return $this->_post('/sessions', $params, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }

    public function retrieve(string $sessionId): array
    {
        $path = Path::pathTemplate('/sessions/{session_id}', ['session_id' => $sessionId]);

        return $this->_get($path, null, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }

    public function update(string $sessionId, array $params = []): array
    {
        $path = Path::pathTemplate('/sessions/{session_id}', ['session_id' => $sessionId]);

        return $this->_post($path, $params, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }

    public function list(array $params = []): array
    {
        return $this->_get('/sessions', null, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }

    public function delete(string $sessionId): array
    {
        return $this->_delete(
            Path::pathTemplate('/sessions/{session_id}', ['session_id' => $sessionId]),
            ['anthropic-beta' => 'managed-agents-2026-04-01'],
        );
    }

    public function archive(string $sessionId): array
    {
        $path = Path::pathTemplate('/sessions/{session_id}/archive', ['session_id' => $sessionId]);

        return $this->_post($path, [], ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }

    public function events(): Events
    {
        return new Events($this->client);
    }

    public function resources(): Resources
    {
        return new Resources($this->client);
    }
}
