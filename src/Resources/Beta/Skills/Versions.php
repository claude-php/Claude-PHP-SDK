<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta\Skills;

use ClaudePhp\Resources\Resource;

/**
 * Versions sub-resource for skills.
 *
 * Manages skill versions.
 */
class Versions extends Resource
{
    /**
     * Create a skill version.
     *
     * @param string $skillId Skill ID
     * @param array<string, mixed> $params Version parameters
     * @return array Version response
     */
    public function create(string $skillId, array $params = []): array
    {
        if (!isset($params['name'])) {
            throw new \InvalidArgumentException('name is required');
        }

        return $this->_post("/skills/{$skillId}/versions", $params);
    }

    /**
     * List skill versions.
     *
     * @param string $skillId Skill ID
     * @param array<string, mixed> $params Query parameters
     * @return array List of versions
     */
    public function list(string $skillId, array $params = []): array
    {
        return $this->_get("/skills/{$skillId}/versions", $params);
    }

    /**
     * Retrieve a skill version.
     *
     * @param string $skillId Skill ID
     * @param string $versionId Version ID
     * @return array Version details
     */
    public function retrieve(string $skillId, string $versionId): array
    {
        if (empty($skillId) || empty($versionId)) {
            throw new \InvalidArgumentException('skill_id and version_id are required');
        }

        return $this->_get("/skills/{$skillId}/versions/{$versionId}");
    }

    /**
     * Delete a skill version.
     *
     * @param string $skillId Skill ID
     * @param string $versionId Version ID
     * @return void
     */
    public function delete(string $skillId, string $versionId): void
    {
        if (empty($skillId) || empty($versionId)) {
            throw new \InvalidArgumentException('skill_id and version_id are required');
        }

        $this->_delete("/skills/{$skillId}/versions/{$versionId}");
    }
}
