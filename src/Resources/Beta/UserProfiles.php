<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta;

use ClaudePhp\Resources\Resource;
use ClaudePhp\Utils\Path;

/**
 * Beta User Profiles resource.
 *
 * Mirrors Python `src/anthropic/resources/beta/user_profiles.py`.
 *
 * All endpoints are routed through the `?beta=true` query string and
 * the `anthropic-beta: user-profiles-2026-03-24` header, matching the
 * Python SDK and the documented Anthropic API.
 */
class UserProfiles extends Resource
{
    private const BETA_HEADER = ['anthropic-beta' => 'user-profiles-2026-03-24'];

    public function create(array $params = []): array
    {
        return $this->_post('/user_profiles?beta=true', $params, self::BETA_HEADER);
    }

    public function retrieve(string $userProfileId): array
    {
        return $this->_get(
            Path::pathTemplate('/user_profiles/{user_profile_id}?beta=true', ['user_profile_id' => $userProfileId]),
            null,
            self::BETA_HEADER,
        );
    }

    public function update(string $userProfileId, array $params = []): array
    {
        return $this->_post(
            Path::pathTemplate('/user_profiles/{user_profile_id}?beta=true', ['user_profile_id' => $userProfileId]),
            $params,
            self::BETA_HEADER,
        );
    }

    public function list(array $params = []): array
    {
        return $this->_get('/user_profiles?beta=true', $params, self::BETA_HEADER);
    }

    public function createEnrollmentUrl(string $userProfileId, array $params = []): array
    {
        $path = Path::pathTemplate(
            '/user_profiles/{user_profile_id}/enrollment_url?beta=true',
            ['user_profile_id' => $userProfileId],
        );

        return $this->_post($path, $params, self::BETA_HEADER);
    }
}
