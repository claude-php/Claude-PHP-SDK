<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta\Sessions;

use ClaudePhp\Resources\Resource;
use ClaudePhp\Utils\Path;

class Sessions extends Resource
{
    private const BETA_HEADER = ['anthropic-beta' => 'managed-agents-2026-04-01'];

    public function create(array $params = []): array
    {
        return $this->_post('/sessions?beta=true', $params, self::BETA_HEADER);
    }

    public function retrieve(string $sessionId): array
    {
        $path = Path::pathTemplate('/sessions/{session_id}?beta=true', ['session_id' => $sessionId]);

        return $this->_get($path, null, self::BETA_HEADER);
    }

    public function update(string $sessionId, array $params = []): array
    {
        $path = Path::pathTemplate('/sessions/{session_id}?beta=true', ['session_id' => $sessionId]);

        return $this->_post($path, $params, self::BETA_HEADER);
    }

    public function list(array $params = []): array
    {
        return $this->_get('/sessions?beta=true', $params, self::BETA_HEADER);
    }

    public function delete(string $sessionId): array
    {
        return $this->_delete(
            Path::pathTemplate('/sessions/{session_id}?beta=true', ['session_id' => $sessionId]),
            self::BETA_HEADER,
        );
    }

    public function archive(string $sessionId): array
    {
        $path = Path::pathTemplate('/sessions/{session_id}/archive?beta=true', ['session_id' => $sessionId]);

        return $this->_post($path, [], self::BETA_HEADER);
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
