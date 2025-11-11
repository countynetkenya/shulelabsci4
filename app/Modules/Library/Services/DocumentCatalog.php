<?php

namespace Modules\Library\Services;

use InvalidArgumentException;
use Modules\Foundation\Services\AuditService;
use Modules\Foundation\Services\QrService;
use Ramsey\Uuid\Uuid;

/**
 * Handles registration and QR issuance for library and resource documents.
 */
class DocumentCatalog
{
    public function __construct(
        private readonly DriveAdapterInterface $driveAdapter,
        private readonly QrService $qrService,
        private readonly AuditService $auditService
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function registerDocument(array $payload, array $context): array
    {
        $title    = trim((string) ($payload['title'] ?? ''));
        $category = trim((string) ($payload['category'] ?? ''));
        $contents = $payload['contents'] ?? null;
        $path     = (string) ($payload['storage_path'] ?? $title);

        if ($title === '' || $category === '') {
            throw new InvalidArgumentException('Title and category are required.');
        }

        if (! is_string($contents) || $contents === '') {
            throw new InvalidArgumentException('Document contents are required for Drive sync.');
        }

        $documentId = Uuid::uuid4()->toString();

        $metadata = [
            'title'     => $title,
            'category'  => $category,
            'tenant_id' => $context['tenant_id'] ?? null,
        ];

        $driveFileId = $this->driveAdapter->upload($path, $contents, $metadata);
        $this->driveAdapter->share($driveFileId, [
            'visibility' => $payload['visibility'] ?? 'tenant',
        ]);

        $qr = $this->qrService->issueToken(
            resourceType: 'library_document',
            resourceId: $documentId,
            context: [
                'tenant_id' => $context['tenant_id'] ?? null,
                'base_url'  => $context['base_url'] ?? null,
            ]
        );

        $record = [
            'documentId'  => $documentId,
            'title'       => $title,
            'category'    => $category,
            'driveFileId' => $driveFileId,
            'qrUrl'       => $qr['url'],
        ];

        $this->auditService->recordEvent(
            eventKey: sprintf('library.document.%s', $documentId),
            eventType: 'document_registered',
            context: $context,
            before: null,
            after: $record,
            metadata: [
                'drive_file' => $driveFileId,
            ]
        );

        return $record;
    }
}
