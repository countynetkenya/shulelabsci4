<?php

namespace Modules\Integrations\Services\Adapters\Storage;

use Modules\Integrations\Services\Adapters\BaseAdapter;
use Modules\Integrations\Services\Interfaces\StorageInterface;
use RuntimeException;

/**
 * Google Drive storage adapter.
 */
class GoogleDriveAdapter extends BaseAdapter implements StorageInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'google_drive';
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
        $this->log('info', 'Uploading file to Google Drive', ['payload' => $payload]);

        // TODO: Implement actual Google Drive API upload
        return [
            'file_id' => 'GDRIVE' . time(),
            'url'     => 'https://drive.google.com/file/d/example',
            'size'    => filesize($payload['file_path'] ?? ''),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function download(string $fileId, array $context): array
    {
        $this->log('info', 'Downloading file from Google Drive', ['file_id' => $fileId]);

        // TODO: Implement actual download
        return [
            'content'  => '',
            'metadata' => ['name' => 'example.pdf', 'size' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $fileId, array $context): array
    {
        $this->log('info', 'Deleting file from Google Drive', ['file_id' => $fileId]);

        // TODO: Implement actual delete
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
        $this->log('info', 'Listing Google Drive files', ['path' => $path]);

        // TODO: Implement actual list
        return [
            'files' => [],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function checkStatus(): array
    {
        return [
            'status'  => 'ok',
            'message' => 'Google Drive adapter is operational',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredConfigKeys(): array
    {
        return ['client_id', 'client_secret', 'refresh_token'];
    }
}
