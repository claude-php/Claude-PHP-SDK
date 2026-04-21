<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta\Skills;

use ClaudePhp\Resources\Resource;
use ClaudePhp\Utils\Path;

/**
 * Versions sub-resource for skills.
 *
 * Manages skill versions.
 */
class Versions extends Resource
{
    private const BETA_HEADER = ['anthropic-beta' => 'skills-2025-10-02'];

    /**
     * Create a skill version.
     *
     * @param string $skillId Skill ID
     * @param array<string, mixed> $params Version parameters
     *
     * @return array Version response
     */
    public function create(string $skillId, array $params = []): array
    {
        if (!isset($params['name'])) {
            throw new \InvalidArgumentException('name is required');
        }

        return $this->_post(
            Path::pathTemplate('/skills/{skill_id}/versions?beta=true', ['skill_id' => $skillId]),
            $params,
            self::BETA_HEADER,
        );
    }

    /**
     * List skill versions.
     *
     * @param string $skillId Skill ID
     * @param array<string, mixed> $params Query parameters
     *
     * @return array List of versions
     */
    public function list(string $skillId, array $params = []): array
    {
        return $this->_get(
            Path::pathTemplate('/skills/{skill_id}/versions?beta=true', ['skill_id' => $skillId]),
            $params,
            self::BETA_HEADER,
        );
    }

    /**
     * Retrieve a skill version.
     *
     * @param string $skillId Skill ID
     * @param string $versionId Version ID
     *
     * @return array Version details
     */
    public function retrieve(string $skillId, string $versionId): array
    {
        if (empty($skillId) || empty($versionId)) {
            throw new \InvalidArgumentException('skill_id and version_id are required');
        }

        return $this->_get(
            Path::pathTemplate(
                '/skills/{skill_id}/versions/{version_id}?beta=true',
                ['skill_id' => $skillId, 'version_id' => $versionId],
            ),
            null,
            self::BETA_HEADER,
        );
    }

    /**
     * Delete a skill version.
     *
     * @param string $skillId Skill ID
     * @param string $versionId Version ID
     */
    public function delete(string $skillId, string $versionId): void
    {
        if (empty($skillId) || empty($versionId)) {
            throw new \InvalidArgumentException('skill_id and version_id are required');
        }

        $this->_delete(
            Path::pathTemplate(
                '/skills/{skill_id}/versions/{version_id}?beta=true',
                ['skill_id' => $skillId, 'version_id' => $versionId],
            ),
            self::BETA_HEADER,
        );
    }
}
