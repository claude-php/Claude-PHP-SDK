<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta;

use ClaudePhp\Resources\Beta\Skills\Skills;
use ClaudePhp\Resources\Resource;

/**
 * Beta resource wrapper.
 *
 * Provides access to beta APIs and experimental features.
 */
class Beta extends Resource
{
    /**
     * Get the Files sub-resource.
     */
    public function files(): Files
    {
        return new Files($this->client);
    }

    /**
     * Get the Messages sub-resource.
     */
    public function messages(): Messages
    {
        return new Messages($this->client);
    }

    /**
     * Get the Models sub-resource.
     */
    public function models(): Models
    {
        return new Models($this->client);
    }

    /**
     * Get the Skills sub-resource.
     */
    public function skills(): Skills
    {
        return new Skills($this->client);
    }
}
