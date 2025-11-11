<?php

namespace Modules\Library\Services;

/**
 * Abstraction for Google Drive (or similar) document storage interactions.
 */
interface DriveAdapterInterface
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function upload(string $path, string $contents, array $metadata = []): string;

    /**
     * @param array<string, mixed> $options
     */
    public function share(string $fileId, array $options = []): void;
}
