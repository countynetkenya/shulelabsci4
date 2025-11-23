<?php

namespace Modules\Library\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Modules\Foundation\Services\AuditService;
use Modules\Foundation\Services\QrService;
use Modules\Library\Services\DocumentCatalog;
use Modules\Library\Services\DriveAdapterInterface;
use RuntimeException;
use Throwable;

class DocumentController extends ResourceController
{
    private DocumentCatalog $catalog;

    public function __construct(?DocumentCatalog $catalog = null)
    {
        $this->catalog = $catalog ?? $this->buildDefaultCatalog();
    }

    public function create(): ResponseInterface
    {
        $payload = $this->request->getJSON(true) ?? [];
        $context = $this->buildContext();

        $record = $this->catalog->registerDocument($payload, $context);

        return $this->respondCreated($record);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildContext(): array
    {
        return [
            'tenant_id' => $this->request->getHeaderLine('X-Tenant-ID') ?: null,
            'actor_id'  => $this->request->getHeaderLine('X-Actor-ID') ?: null,
            'base_url'  => rtrim((string) site_url(), '/'),
        ];
    }

    private function buildDefaultCatalog(): DocumentCatalog
    {
        try {
            $drive = service('libraryDriveAdapter');
        } catch (Throwable) {
            $drive = null;
        }
        if (!$drive instanceof DriveAdapterInterface) {
            throw new RuntimeException('No Drive adapter has been configured for the Library module.');
        }

        return new DocumentCatalog($drive, new QrService(), new AuditService());
    }
}
