<?php

declare(strict_types=1);

namespace Modules\Hr\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use InvalidArgumentException;
use Modules\Hr\Services\PayrollApprovalService;
use RuntimeException;

class PayrollApprovalController extends BaseController
{
    private PayrollApprovalService $approvalService;

    public function __construct(?PayrollApprovalService $approvalService = null)
    {
        $this->approvalService = $approvalService ?? service('payrollApproval');
    }

    public function index(): string
    {
        $tenantId = $this->request->getGet('tenant_id');
        $tenantId = $tenantId !== null && $tenantId !== '' ? (string) $tenantId : null;

        $summary = $this->approvalService->summarise($tenantId);
        $approvals = $this->approvalService->listPending($tenantId);

        return view('Modules\\Hr\\Views\\payroll_approvals', [
            'summary'   => $summary,
            'approvals' => $approvals,
            'tenantId'  => $tenantId,
            'baseUrl'   => rtrim((string) site_url(), '/'),
        ]);
    }

    public function pending(): ResponseInterface
    {
        $tenantId = $this->request->getGet('tenant_id');
        $tenantId = $tenantId !== null && $tenantId !== '' ? (string) $tenantId : null;

        $summary = $this->approvalService->summarise($tenantId);
        $approvals = $this->approvalService->listPending($tenantId);

        return $this->response->setJSON([
            'summary'   => $summary,
            'approvals' => $approvals,
        ]);
    }

    public function approve(int $requestId): ResponseInterface
    {
        try {
            $approval = $this->approvalService->approve($requestId, $this->buildContext());
        } catch (RuntimeException $exception) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['error' => $exception->getMessage()]);
        }

        return $this->response->setJSON(['approval' => $approval]);
    }

    public function reject(int $requestId): ResponseInterface
    {
        $payload = $this->request->getJSON(true) ?? [];
        $reason = (string) ($payload['reason'] ?? '');

        try {
            $approval = $this->approvalService->reject($requestId, $reason, $this->buildContext());
        } catch (InvalidArgumentException $exception) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON(['error' => $exception->getMessage()]);
        } catch (RuntimeException $exception) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['error' => $exception->getMessage()]);
        }

        return $this->response->setJSON(['approval' => $approval]);
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
        ];
    }
}
