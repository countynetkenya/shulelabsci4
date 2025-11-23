<?php

declare(strict_types=1);

namespace Tests\Ci4\Library;

use Modules\Foundation\Services\AuditService;
use Modules\Foundation\Services\QrService;
use Modules\Library\Services\DocumentCatalog;
use Modules\Library\Services\DriveAdapterInterface;
use PHPUnit\Framework\TestCase;

class DocumentCatalogTest extends TestCase
{
    public function testRegisterDocumentUploadsToDriveIssuesQrAndAudits(): void
    {
        $drive = $this->createMock(DriveAdapterInterface::class);
        $qr = $this->createMock(QrService::class);
        $audit = $this->createMock(AuditService::class);

        $catalog = new DocumentCatalog($drive, $qr, $audit);

        $drive
            ->expects($this->once())
            ->method('upload')
            ->with('policies/term1.pdf', 'pdf-bytes', $this->arrayHasKey('title'))
            ->willReturn('drive-file-1');

        $drive
            ->expects($this->once())
            ->method('share')
            ->with('drive-file-1', ['visibility' => 'tenant']);

        $qr
            ->expects($this->once())
            ->method('issueToken')
            ->with('library_document', $this->isType('string'), $this->arrayHasKey('tenant_id'))
            ->willReturn([
                'token' => 'qr-doc-token',
                'url'   => 'https://docs.example/verify/qr-doc-token',
                'png'   => 'binary',
            ]);

        $audit
            ->expects($this->once())
            ->method('recordEvent')
            ->with(
                $this->stringStartsWith('library.document.'),
                'document_registered',
                $this->arrayHasKey('tenant_id'),
                null,
                $this->arrayHasKey('driveFileId')
            );

        $result = $catalog->registerDocument([
            'title'        => 'Term 1 Policies',
            'category'     => 'Policies',
            'storage_path' => 'policies/term1.pdf',
            'contents'     => 'pdf-bytes',
        ], [
            'tenant_id' => 'tenant-99',
            'base_url'  => 'https://docs.example',
        ]);

        $this->assertSame('drive-file-1', $result['driveFileId']);
        $this->assertSame('https://docs.example/verify/qr-doc-token', $result['qrUrl']);
    }
}
