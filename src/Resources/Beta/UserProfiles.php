<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta;

use ClaudePhp\Resources\Resource;
use ClaudePhp\Utils\Path;

class UserProfiles extends Resource
{
    public function create(array $params = []): array
    {
        return $this->_post('/user_profiles', $params, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }

    public function retrieve(string $userProfileId): array
    {
        return $this->_get(
            Path::pathTemplate('/user_profiles/{user_profile_id}', ['user_profile_id' => $userProfileId]),
            null,
            ['anthropic-beta' => 'managed-agents-2026-04-01'],
        );
    }

    public function update(string $userProfileId, array $params = []): array
    {
        return $this->_post(
            Path::pathTemplate('/user_profiles/{user_profile_id}', ['user_profile_id' => $userProfileId]),
            $params,
            ['anthropic-beta' => 'managed-agents-2026-04-01'],
        );
    }

    public function list(array $params = []): array
    {
        return $this->_get('/user_profiles', null, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }

    public function createEnrollmentUrl(string $userProfileId, array $params = []): array
    {
        $path = Path::pathTemplate(
            '/user_profiles/{user_profile_id}/enrollment_url',
            ['user_profile_id' => $userProfileId],
        );

        return $this->_post($path, $params, ['anthropic-beta' => 'managed-agents-2026-04-01']);
    }
}
