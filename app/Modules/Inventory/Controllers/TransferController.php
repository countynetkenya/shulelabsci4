<?php

namespace Modules\Inventory\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Modules\Foundation\Services\AuditService;
use Modules\Foundation\Services\MakerCheckerService;
use Modules\Foundation\Services\QrService;
use Modules\Inventory\Services\InMemoryTransferRepository;
use Modules\Inventory\Services\TransferRepositoryInterface;
use Modules\Inventory\Services\TransferService;
use RuntimeException;
use Throwable;

class TransferController extends ResourceController
{
    private TransferService $transferService;

    public function __construct(?TransferService $transferService = null)
    {
        $this->transferService = $transferService ?? $this->buildDefaultService();
    }

    public function create(): ResponseInterface
    {
        $payload = $this->request->getJSON(true) ?? [];
        $context = $this->buildContext();

        $transfer = $this->transferService->initiateTransfer($payload, $context);

        return $this->respondCreated($transfer->toArray());
    }

    public function complete(string $transferId): ResponseInterface
    {
        $payload  = $this->request->getJSON(true) ?? [];
        $decision = (string) ($payload['decision'] ?? '');
        $metadata = $payload['metadata'] ?? [];
        $context  = $this->buildContext();

        if (! is_array($metadata)) {
            $metadata = [];
        }

        $transfer = $this->transferService->completeTransfer($transferId, $decision, $context, $metadata);

        return $this->respond($transfer->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    private function buildContext(): array
    {
        return [
            'tenant_id'      => $this->request->getHeaderLine('X-Tenant-ID') ?: null,
            'actor_id'       => $this->request->getHeaderLine('X-Actor-ID') ?: null,
            'request_origin' => $this->request->getIPAddress(),
            'base_url'       => rtrim((string) site_url(), '/'),
        ];
    }

    private function buildDefaultService(): TransferService
    {
        $repository = $this->resolveRepository();

        return new TransferService(
            $repository,
            new QrService(),
            new AuditService(),
            new MakerCheckerService()
        );
    }

    private function resolveRepository(): TransferRepositoryInterface
    {
        // Allow integrators to inject a repository via service container bindings.
        try {
            $service = service('inventoryTransferRepository');
        } catch (Throwable) {
            $service = null;
        }
        if ($service instanceof TransferRepositoryInterface) {
            return $service;
        }

        if ($service !== null && ! $service instanceof TransferRepositoryInterface) {
            throw new RuntimeException('Configured transfer repository must implement TransferRepositoryInterface.');
        }

        return new InMemoryTransferRepository();
    }
}
