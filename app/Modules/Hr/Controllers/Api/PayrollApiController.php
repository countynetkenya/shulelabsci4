<?php

declare(strict_types=1);

namespace Modules\Hr\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Modules\Foundation\Services\AuditService;
use Modules\Foundation\Services\MakerCheckerService;
use Modules\Hr\Services\KenyaPayrollTemplate;
use Modules\Hr\Services\PayrollService;

class PayrollApiController extends ResourceController
{
    protected $format = 'json';

    private PayrollService $payrollService;

    public function __construct(?PayrollService $payrollService = null)
    {
        $this->payrollService = $payrollService ?? $this->buildDefaultService();
    }

    public function create(): ResponseInterface
    {
        $payload = $this->request->getJSON(true) ?? [];
        $context = $this->buildContext();

        $payslip = $this->payrollService->generatePayslip($payload, $context);

        return $this->respondCreated($payslip->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    private function buildContext(): array
    {
        $schoolId = $this->request->getHeaderLine('X-School-ID');
        if (!$schoolId) {
            // Fallback for legacy clients
            $schoolId = $this->request->getHeaderLine('X-Tenant-ID');
        }

        return [
            'school_id'      => $schoolId ? (int) $schoolId : null,
            'actor_id'       => $this->request->getHeaderLine('X-Actor-ID') ?: null,
            'request_origin' => $this->request->getIPAddress(),
        ];
    }

    private function buildDefaultService(): PayrollService
    {
        return new PayrollService(
            [new KenyaPayrollTemplate()],
            new MakerCheckerService(),
            new AuditService()
        );
    }
}
