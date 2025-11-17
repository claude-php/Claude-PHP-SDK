<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta;

use ClaudePhp\Resources\Resource;

/**
 * Files resource for beta API.
 *
 * Provides file management capabilities including upload, list, retrieve, and delete.
 */
class Files extends Resource
{
    /**
     * Upload a file.
     *
     * @param array<string, mixed> $params File upload parameters:
     *   - file: string|resource (required) - File content or path
     *   - mime_type: string (optional) - MIME type
     *
     * @return array File metadata
     */
    public function upload(array $params = []): array
    {
        if (!isset($params['file'])) {
            throw new \InvalidArgumentException('file parameter is required');
        }

        // Handle file upload - would need multipart encoding
        return $this->_post('/files', $params);
    }

    /**
     * List uploaded files.
     *
     * @param array<string, mixed> $params Query parameters
     * @return array List of files
     */
    public function list(array $params = []): array
    {
        return $this->_get('/files', $params);
    }

    /**
     * Retrieve file metadata.
     *
     * @param string $fileId The file ID
     * @return array File metadata
     */
    public function retrieveMetadata(string $fileId): array
    {
        return $this->_get("/files/{$fileId}");
    }

    /**
     * Download file content.
     *
     * @param string $fileId The file ID
     * @return mixed File content
     */
    public function download(string $fileId): string
    {
        return $this->_get("/files/{$fileId}/content");
    }

    /**
     * Delete a file.
     *
     * @param string $fileId The file ID
     * @return void
     */
    public function delete(string $fileId): void
    {
        if (empty($fileId)) {
            throw new \InvalidArgumentException('file_id is required');
        }

        $this->_delete("/v1/files/{$fileId}");
    }
}
