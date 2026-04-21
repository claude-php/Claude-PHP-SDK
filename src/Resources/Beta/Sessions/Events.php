<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta\Sessions;

use ClaudePhp\Resources\Resource;
use ClaudePhp\Utils\Path;

class Events extends Resource
{
    public function list(string $sessionId, array $params = []): array
    {
        $path = Path::pathTemplate('/sessions/{session_id}/events', ['session_id' => $sessionId]);

        return $this->_get($path, null, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }

    public function send(string $sessionId, array $params = []): array
    {
        $path = Path::pathTemplate('/sessions/{session_id}/events', ['session_id' => $sessionId]);

        return $this->_post($path, $params, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }

    /**
     * Stream events from a session.
     *
     * @return \ClaudePhp\Responses\StreamResponse
     */
    public function stream(string $sessionId, array $params = [])
    {
        $path = Path::pathTemplate('/sessions/{session_id}/events/stream', ['session_id' => $sessionId]);

        return $this->_postStream($path, $params, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }
}
