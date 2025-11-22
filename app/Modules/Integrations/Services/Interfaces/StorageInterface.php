<?php

namespace Modules\Integrations\Services\Interfaces;

/**
 * Interface for storage/file system integrations.
 */
interface StorageInterface extends IntegrationAdapterInterface
{
    /**
     * Upload a file to storage.
     *
     * @param array{file_path: string, destination: string, metadata?: array<string, mixed>} $payload
     * @param array<string, mixed> $context
     * @return array{file_id: string, url: string, size?: int}
     */
    public function upload(array $payload, array $context): array;

    /**
     * Download a file from storage.
     *
     * @param string $fileId
     * @param array<string, mixed> $context
     * @return array{content: string, metadata?: array<string, mixed>}
     */
    public function download(string $fileId, array $context): array;

    /**
     * Delete a file from storage.
     *
     * @param string $fileId
     * @param array<string, mixed> $context
     * @return array{status: string, message?: string}
     */
    public function delete(string $fileId, array $context): array;

    /**
     * List files in a directory.
     *
     * @param string $path
     * @param array<string, mixed> $context
     * @return array{files: array<array{id: string, name: string, size?: int}>}
     */
    public function list(string $path, array $context): array;
}
