<?php

declare(strict_types=1);

namespace ClaudePhp\Resources\Beta;

use ClaudePhp\Resources\Resource;
use ClaudePhp\Utils\Path;

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
     * File bytes are sent via the multipart 'file' field only — never duplicated
     * in the JSON body. Other params (e.g. purpose) are sent as form fields.
     *
     * @param array<string, mixed> $params File upload parameters:
     *                                     - file: string|resource (required) - File content or path
     *                                     - mime_type: string (optional) - MIME type
     *                                     - purpose: string (optional) - File purpose
     *
     * @return array File metadata
     */
    public function upload(array $params = []): array
    {
        if (!isset($params['file'])) {
            throw new \InvalidArgumentException('file parameter is required');
        }

        $fileData = $params['file'];
        unset($params['file']);

        $body = $params;
        $body['file'] = $fileData;

        return $this->_post('/files?beta=true', $body);
    }

    /**
     * List uploaded files.
     *
     * @param array<string, mixed> $params Query parameters
     *
     * @return array List of files
     */
    public function list(array $params = []): array
    {
        return $this->_get('/files?beta=true', $params);
    }

    /**
     * Retrieve file metadata.
     *
     * @param string $fileId The file ID
     *
     * @return array File metadata
     */
    public function retrieveMetadata(string $fileId): array
    {
        $path = Path::pathTemplate('/files/{file_id}?beta=true', ['file_id' => $fileId]);

        return $this->_get($path);
    }

    /**
     * Download file content.
     *
     * @param string $fileId The file ID
     *
     * @return mixed File content
     */
    public function download(string $fileId): string
    {
        $path = Path::pathTemplate('/files/{file_id}/content?beta=true', ['file_id' => $fileId]);

        return $this->_get($path);
    }

    /**
     * Delete a file.
     *
     * @param string $fileId The file ID
     */
    public function delete(string $fileId): void
    {
        if (empty($fileId)) {
            throw new \InvalidArgumentException('file_id is required');
        }

        $path = Path::pathTemplate('/files/{file_id}?beta=true', ['file_id' => $fileId]);
        $this->_delete($path);
    }
}
