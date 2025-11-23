<?php

namespace Modules\Finance\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Modules\Finance\Services\InMemoryInvoiceRepository;
use Modules\Finance\Services\InvoiceRepositoryInterface;
use Modules\Finance\Services\InvoiceService;
use Modules\Foundation\Services\AuditService;
use Modules\Foundation\Services\LedgerService;
use RuntimeException;
use Throwable;

class InvoiceController extends ResourceController
{
    protected $format = 'json';

    private InvoiceService $invoiceService;

    public function __construct(?InvoiceService $invoiceService = null)
    {
        $this->invoiceService = $invoiceService ?? $this->buildDefaultService();
    }

    public function create(): ResponseInterface
    {
        $payload = $this->request->getJSON(true) ?? [];
        $context = $this->buildContext();

        $invoice = $this->invoiceService->issueInvoice($payload, $context);

        return $this->respondCreated($invoice->toArray());
    }

    public function settle(string $invoiceNumber): ResponseInterface
    {
        $payload = $this->request->getJSON(true) ?? [];
        $context = $this->buildContext();

        $invoice = $this->invoiceService->settleInvoice($invoiceNumber, $payload, $context);

        return $this->respond($invoice->toArray());
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
            'currency'       => $this->request->getHeaderLine('X-Currency') ?: null,
        ];
    }

    private function buildDefaultService(): InvoiceService
    {
        $repository = $this->resolveRepository();

        return new InvoiceService(
            $repository,
            new LedgerService(),
            new AuditService()
        );
    }

    private function resolveRepository(): InvoiceRepositoryInterface
    {
        try {
            $service = service('financeInvoiceRepository');
        } catch (Throwable) {
            $service = null;
        }

        if ($service instanceof InvoiceRepositoryInterface) {
            return $service;
        }

        if ($service !== null && !$service instanceof InvoiceRepositoryInterface) {
            throw new RuntimeException('Configured finance invoice repository must implement InvoiceRepositoryInterface.');
        }

        return new InMemoryInvoiceRepository();
    }
}
