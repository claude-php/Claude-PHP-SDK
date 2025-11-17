<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta;

use ClaudePhp\Resources\Resource;
use ClaudePhp\Resources\Beta\Skills\Skills;

/**
 * Beta resource wrapper.
 *
 * Provides access to beta APIs and experimental features.
 */
class Beta extends Resource
{
    /**
     * Get the Files sub-resource.
     *
     * @return Files
     */
    public function files(): Files
    {
        return new Files($this->client);
    }

    /**
     * Get the Messages sub-resource.
     *
     * @return Messages
     */
    public function messages(): Messages
    {
        return new Messages($this->client);
    }

    /**
     * Get the Models sub-resource.
     *
     * @return Models
     */
    public function models(): Models
    {
        return new Models($this->client);
    }

    /**
     * Get the Skills sub-resource.
     *
     * @return Skills
     */
    public function skills(): Skills
    {
        return new Skills($this->client);
    }
}
