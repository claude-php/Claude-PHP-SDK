<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta\Skills;

use ClaudePhp\Resources\Resource;
use ClaudePhp\Utils\Path;

/**
 * Skills resource for beta API.
 *
 * Provides access to skills management.
 */
class Skills extends Resource
{
    private const BETA_HEADER = ['anthropic-beta' => 'skills-2025-10-02'];

    /**
     * Get versions sub-resource.
     */
    public function versions(): Versions
    {
        return new Versions($this->client);
    }

    /**
     * Create a skill.
     *
     * @param array<string, mixed> $params Skill parameters
     *
     * @return array Skill response
     */
    public function create(array $params = []): array
    {
        if (!isset($params['name'], $params['description'])) {
            throw new \InvalidArgumentException('name and description are required');
        }

        return $this->_post('/skills', $params, self::BETA_HEADER);
    }

    /**
     * List skills.
     *
     * @param array<string, mixed> $params Query parameters
     *
     * @return array List of skills
     */
    public function list(array $params = []): array
    {
        return $this->_get('/skills', $params, self::BETA_HEADER);
    }

    /**
     * Retrieve a skill.
     *
     * @param string $skillId Skill ID
     *
     * @return array Skill details
     */
    public function retrieve(string $skillId): array
    {
        return $this->_get(
            Path::pathTemplate('/skills/{skill_id}', ['skill_id' => $skillId]),
            null,
            self::BETA_HEADER,
        );
    }

    /**
     * Delete a skill.
     *
     * @param string $skillId Skill ID
     */
    public function delete(string $skillId): void
    {
        if (empty($skillId)) {
            throw new \InvalidArgumentException('skill_id is required');
        }

        $this->_delete(
            Path::pathTemplate('/skills/{skill_id}', ['skill_id' => $skillId]),
            self::BETA_HEADER,
        );
    }
}

class_alias(Skills::class, 'ClaudePhp\Resources\Beta\Skills');
