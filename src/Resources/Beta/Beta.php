<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta;

use ClaudePhp\Resources\Beta\Agents\Agents;
use ClaudePhp\Resources\Beta\Sessions\Sessions;
use ClaudePhp\Resources\Beta\Skills\Skills;
use ClaudePhp\Resources\Beta\Vaults\Vaults;
use ClaudePhp\Resources\Resource;

/**
 * Beta resource wrapper.
 *
 * Provides access to beta APIs and experimental features including
 * Managed Agents, Sessions, Vaults, Environments, and User Profiles.
 */
class Beta extends Resource
{
    public function files(): Files
    {
        return new Files($this->client);
    }

    public function messages(): Messages
    {
        return new Messages($this->client);
    }

    public function models(): Models
    {
        return new Models($this->client);
    }

    public function skills(): Skills
    {
        return new Skills($this->client);
    }

    public function agents(): Agents
    {
        return new Agents($this->client);
    }

    public function sessions(): Sessions
    {
        return new Sessions($this->client);
    }

    public function vaults(): Vaults
    {
        return new Vaults($this->client);
    }

    public function environments(): Environments
    {
        return new Environments($this->client);
    }

    public function userProfiles(): UserProfiles
    {
        return new UserProfiles($this->client);
    }
}
