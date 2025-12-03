<?php

declare(strict_types=1);

namespace Modules\Hr\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Modules\Hr\Services\PayrollApprovalService;

class PayrollApprovalApiController extends ResourceController
{
    private PayrollApprovalService $approvalService;

    public function __construct(?PayrollApprovalService $approvalService = null)
    {
        $this->approvalService = $approvalService ?? service('payrollApproval');
    }

    public function pending(): ResponseInterface
    {
        $schoolId = $this->request->getGet('school_id');
        $schoolId = $schoolId !== null && $schoolId !== '' ? (int) $schoolId : null;

        $approvals = $this->approvalService->listPending($schoolId);

        return $this->respond($approvals);
    }

    public function approve(int $id): ResponseInterface
    {
        $userId = (int) session()->get('user_id');
        $this->approvalService->approve($id, $userId);

        return $this->respond(['message' => 'Approved successfully']);
    }

    public function reject(int $id): ResponseInterface
    {
        $userId = (int) session()->get('user_id');
        $reason = $this->request->getJSON()->reason ?? 'No reason provided';
        
        $this->approvalService->reject($id, $userId, $reason);

        return $this->respond(['message' => 'Rejected successfully']);
    }
}
