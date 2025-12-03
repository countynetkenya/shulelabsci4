<?php

declare(strict_types=1);

namespace Modules\Hr\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use InvalidArgumentException;
use Modules\Hr\Services\PayrollApprovalService;
use RuntimeException;

class PayrollApprovalWebController extends BaseController
{
    private PayrollApprovalService $approvalService;

    public function __construct(?PayrollApprovalService $approvalService = null)
    {
        $this->approvalService = $approvalService ?? service('payrollApproval');
    }

    public function index(): string
    {
        $schoolId = $this->request->getGet('school_id');
        $schoolId = $schoolId !== null && $schoolId !== '' ? (int) $schoolId : null;

        $summary = $this->approvalService->summarise($schoolId);
        $approvals = $this->approvalService->listPending($schoolId);

        return view('Modules\\Hr\\Views\\payroll_approvals', [
            'summary'   => $summary,
            'approvals' => $approvals,
            'schoolId'  => $schoolId,
            'baseUrl'   => rtrim((string) site_url(), '/'),
        ]);
    }
}

