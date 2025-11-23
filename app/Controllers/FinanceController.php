<?php

namespace App\Controllers;

use App\Services\FinanceService;
use App\Services\TenantService;

/**
 * FinanceController - Financial management.
 */
class FinanceController extends BaseController
{
    protected FinanceService $financeService;
    protected TenantService $tenantService;

    public function __construct()
    {
        $this->financeService = new FinanceService();
        $this->tenantService = service('tenant');
    }

    /**
     * Display invoices list.
     */
    public function invoices()
    {
        $schoolId = $this->tenantService->getCurrentSchoolId();
        
        if (!$schoolId) {
            return redirect()->to('/school/select');
        }

        $status = $this->request->getGet('status');
        $invoices = $this->financeService->getSchoolInvoices($schoolId, $status);

        return view('finance/invoices', [
            'invoices' => $invoices,
            'school_id' => $schoolId,
        ]);
    }

    /**
     * Create invoice (API).
     */
    public function createInvoice()
    {
        $schoolId = $this->tenantService->getCurrentSchoolId();
        $studentId = $this->request->getPost('student_id');
        $items = $this->request->getPost('items');
        $dueDate = $this->request->getPost('due_date');

        if (!$schoolId || !$studentId || !$items || !$dueDate) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request',
            ])->setStatusCode(400);
        }

        $result = $this->financeService->createInvoice((int)$studentId, $schoolId, $items, $dueDate);

        return $this->response->setJSON($result);
    }

    /**
     * Record payment (API).
     */
    public function recordPayment()
    {
        $invoiceId = $this->request->getPost('invoice_id');
        $amount = $this->request->getPost('amount');
        $method = $this->request->getPost('payment_method');
        $reference = $this->request->getPost('reference_number');

        if (!$invoiceId || !$amount || !$method) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request',
            ])->setStatusCode(400);
        }

        $result = $this->financeService->recordPayment((int)$invoiceId, (float)$amount, $method, $reference);

        return $this->response->setJSON($result);
    }

    /**
     * Get invoice details (API).
     */
    public function invoiceDetails(int $invoiceId)
    {
        $invoice = $this->financeService->getInvoiceDetails($invoiceId);

        if (!$invoice) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invoice not found',
            ])->setStatusCode(404);
        }

        return $this->response->setJSON([
            'success' => true,
            'invoice' => $invoice,
        ]);
    }

    /**
     * Get payment statistics (API).
     */
    public function paymentStats()
    {
        $schoolId = $this->tenantService->getCurrentSchoolId();

        if (!$schoolId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No school context',
            ])->setStatusCode(400);
        }

        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        $stats = $this->financeService->getPaymentStats($schoolId, $startDate, $endDate);

        return $this->response->setJSON([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * Get outstanding invoices (API).
     */
    public function outstanding()
    {
        $schoolId = $this->tenantService->getCurrentSchoolId();

        if (!$schoolId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No school context',
            ])->setStatusCode(400);
        }

        $invoices = $this->financeService->getOutstandingInvoices($schoolId);

        return $this->response->setJSON([
            'success' => true,
            'invoices' => $invoices,
        ]);
    }

    /**
     * Get fee structure (API).
     */
    public function feeStructure()
    {
        $schoolId = $this->tenantService->getCurrentSchoolId();

        if (!$schoolId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No school context',
            ])->setStatusCode(400);
        }

        $gradeLevel = $this->request->getGet('grade_level');
        $structure = $this->financeService->getFeeStructure($schoolId, $gradeLevel);

        return $this->response->setJSON([
            'success' => true,
            'fee_structure' => $structure,
        ]);
    }

    /**
     * Set fee structure (API).
     */
    public function setFeeStructure()
    {
        $schoolId = $this->tenantService->getCurrentSchoolId();
        $gradeLevel = $this->request->getPost('grade_level');
        $feeItems = $this->request->getPost('fee_items');

        if (!$schoolId || !$gradeLevel || !$feeItems) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request',
            ])->setStatusCode(400);
        }

        $result = $this->financeService->setFeeStructure($schoolId, $gradeLevel, $feeItems);

        return $this->response->setJSON($result);
    }
}
