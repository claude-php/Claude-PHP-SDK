<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta\Sessions;

use ClaudePhp\Resources\Resource;
use ClaudePhp\Utils\Path;

class Events extends Resource
{
    private const BETA_HEADER = ['anthropic-beta' => 'managed-agents-2026-04-01'];

    public function list(string $sessionId, array $params = []): array
    {
        $path = Path::pathTemplate('/sessions/{session_id}/events?beta=true', ['session_id' => $sessionId]);

        return $this->_get($path, $params, self::BETA_HEADER);
    }

    public function send(string $sessionId, array $params = []): array
    {
        $path = Path::pathTemplate('/sessions/{session_id}/events?beta=true', ['session_id' => $sessionId]);

        return $this->_post($path, $params, self::BETA_HEADER);
    }

    /**
     * Stream events from a session.
     *
     * @return \ClaudePhp\Responses\StreamResponse
     */
    public function stream(string $sessionId, array $params = [])
    {
        $path = Path::pathTemplate('/sessions/{session_id}/events/stream?beta=true', ['session_id' => $sessionId]);

        return $this->_postStream($path, $params, self::BETA_HEADER);
    }
}
