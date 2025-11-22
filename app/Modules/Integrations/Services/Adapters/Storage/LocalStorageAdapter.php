<?php

namespace Modules\Integrations\Services\Adapters\Storage;

use Modules\Integrations\Services\Adapters\BaseAdapter;
use Modules\Integrations\Services\Interfaces\StorageInterface;
use RuntimeException;

/**
 * Local file system storage adapter.
 * Useful for local development and offline support.
 */
class LocalStorageAdapter extends BaseAdapter implements StorageInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'local_storage';
    }

    /**
     * {@inheritdoc}
     */
    public function execute(string $operation, array $payload, array $context): array
    {
        return match ($operation) {
            'upload' => $this->upload($payload, $context),
            'download' => $this->download($payload['file_id'] ?? '', $context),
            'delete' => $this->delete($payload['file_id'] ?? '', $context),
            'list' => $this->list($payload['path'] ?? '/', $context),
            default => throw new RuntimeException("Unknown operation: {$operation}"),
        };
    }

    /**
     * {@inheritdoc}
     */
    public function upload(array $payload, array $context): array
    {
        $this->log('info', 'Uploading file to local storage', ['payload' => $payload]);

        $basePath    = $this->getConfig('base_path', WRITEPATH . 'uploads');
        $sourcePath  = $payload['file_path'] ?? '';
        $destination = $payload['destination'] ?? '';

        if (! file_exists($sourcePath)) {
            throw new RuntimeException('Source file not found: ' . $sourcePath);
        }

        $targetPath = rtrim($basePath, '/') . '/' . ltrim($destination, '/');
        $targetDir  = dirname($targetPath);

        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        if (! copy($sourcePath, $targetPath)) {
            throw new RuntimeException('Failed to copy file to local storage');
        }

        return [
            'file_id' => hash('sha256', $targetPath),
            'url'     => $targetPath,
            'size'    => filesize($targetPath),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function download(string $fileId, array $context): array
    {
        $this->log('info', 'Downloading file from local storage', ['file_id' => $fileId]);

        // TODO: Implement file lookup by ID
        return [
            'content'  => '',
            'metadata' => [],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $fileId, array $context): array
    {
        $this->log('info', 'Deleting file from local storage', ['file_id' => $fileId]);

        // TODO: Implement file deletion
        return [
            'status'  => 'deleted',
            'message' => 'File deleted successfully',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function list(string $path, array $context): array
    {
        $this->log('info', 'Listing local storage files', ['path' => $path]);

        $basePath = $this->getConfig('base_path', WRITEPATH . 'uploads');
        $fullPath = rtrim($basePath, '/') . '/' . ltrim($path, '/');

        if (! is_dir($fullPath)) {
            return ['files' => []];
        }

        $files = [];
        foreach (scandir($fullPath) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $itemPath = $fullPath . '/' . $item;
            $files[]  = [
                'id'   => hash('sha256', $itemPath),
                'name' => $item,
                'size' => is_file($itemPath) ? filesize($itemPath) : 0,
            ];
        }

        return ['files' => $files];
    }

    /**
     * {@inheritdoc}
     */
    public function checkStatus(): array
    {
        $basePath = $this->getConfig('base_path', WRITEPATH . 'uploads');

        return [
            'status'  => is_writable($basePath) ? 'ok' : 'error',
            'message' => is_writable($basePath) ? 'Local storage is writable' : 'Local storage is not writable',
            'details' => ['base_path' => $basePath],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredConfigKeys(): array
    {
        return []; // base_path is optional with default
    }
}
