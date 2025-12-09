<?php

namespace App\Modules\Finance\Controllers\Web;

use App\Controllers\BaseController;
use App\Modules\Finance\Models\InvoiceModel;
use App\Modules\Finance\Services\FinanceService;

/**
 * FinanceController - Handles CRUD operations for finance transactions.
 *
 * All data is tenant-scoped by school_id from session.
 */
class FinanceController extends BaseController
{
    protected FinanceService $service;

    protected InvoiceModel $invoiceModel;

    public function __construct()
    {
        $this->service = new FinanceService();
        $this->invoiceModel = new InvoiceModel();
    }

    /**
     * Check if user has permission to access finance module.
     */
    protected function checkAccess(): bool
    {
        // Allow admins and finance staff
        $usertypeID = session()->get('usertypeID');
        $isAdmin = in_array($usertypeID, [0, 1, '0', '1']);

        if ($isAdmin) {
            return true;
        }

        // Check finance-specific permission if auth service is available
        try {
            $auth = service('auth');
            if ($auth && method_exists($auth, 'can')) {
                return $auth->can('finance.view');
            }
        } catch (\Throwable $e) {
            // Auth service not available
        }

        // Default: require admin for now
        return $isAdmin;
    }

    /**
     * Get current school ID from session.
     */
    protected function getSchoolId(): int
    {
        return (int) (session()->get('school_id') ?? session()->get('schoolID') ?? 1);
    }

    /**
     * List all transactions.
     */
    public function index()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();

        // Get filter parameters
        $filters = [
            'search'    => $this->request->getGet('search'),
            'method'    => $this->request->getGet('method'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to'   => $this->request->getGet('date_to'),
        ];

        $data = [
            'transactions'     => $this->service->getAll($schoolId, array_filter($filters)),
            'payment_methods'  => $this->service->getPaymentMethods($schoolId),
            'summary'          => $this->service->getSummary($schoolId, $filters['date_from'] ?? null, $filters['date_to'] ?? null),
            'filters'          => $filters,
        ];

        return view('App\Modules\Finance\Views\transactions\index', $data);
    }

    /**
     * Show create form.
     */
    public function create()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();

        // Get unpaid/partial invoices for the dropdown
        $invoices = $this->invoiceModel
            ->where('school_id', $schoolId)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->orderBy('due_date', 'ASC')
            ->findAll();

        $data = [
            'invoices' => $invoices,
        ];

        return view('App\Modules\Finance\Views\transactions\create', $data);
    }

    /**
     * Store a new transaction.
     */
    public function store()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();

        // Validation rules
        $rules = [
            'invoice_id'     => 'required|integer',
            'amount'         => 'required|decimal|greater_than[0]',
            'method'         => 'required|in_list[cash,bank_transfer,mobile_money,cheque]',
            'reference_code' => 'permit_empty|max_length[100]',
            'paid_at'        => 'permit_empty|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'school_id'      => $schoolId,
            'invoice_id'     => $this->request->getPost('invoice_id'),
            'amount'         => $this->request->getPost('amount'),
            'method'         => $this->request->getPost('method'),
            'reference_code' => $this->request->getPost('reference_code') ?: null,
            'paid_at'        => $this->request->getPost('paid_at') ?: date('Y-m-d H:i:s'),
            'recorded_by'    => session()->get('user_id') ?? 1,
        ];

        $result = $this->service->create($data);

        if ($result) {
            return redirect()->to('/finance/transactions')->with('message', 'Transaction recorded successfully!');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to record transaction. Please try again.');
    }

    /**
     * Show edit form.
     */
    public function edit(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();
        $transaction = $this->service->getById($id, $schoolId);

        if (!$transaction) {
            return redirect()->to('/finance/transactions')->with('error', 'Transaction not found.');
        }

        // Get invoices for the dropdown
        $invoices = $this->invoiceModel
            ->where('school_id', $schoolId)
            ->orderBy('due_date', 'ASC')
            ->findAll();

        $data = [
            'transaction' => $transaction,
            'invoices'    => $invoices,
        ];

        return view('App\Modules\Finance\Views\transactions\edit', $data);
    }

    /**
     * Update an existing transaction.
     */
    public function update(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();

        // Verify transaction exists
        $existingTransaction = $this->service->getById($id, $schoolId);
        if (!$existingTransaction) {
            return redirect()->to('/finance/transactions')->with('error', 'Transaction not found.');
        }

        // Validation rules
        $rules = [
            'invoice_id'     => 'required|integer',
            'amount'         => 'required|decimal|greater_than[0]',
            'method'         => 'required|in_list[cash,bank_transfer,mobile_money,cheque]',
            'reference_code' => 'permit_empty|max_length[100]',
            'paid_at'        => 'permit_empty|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'invoice_id'     => $this->request->getPost('invoice_id'),
            'amount'         => $this->request->getPost('amount'),
            'method'         => $this->request->getPost('method'),
            'reference_code' => $this->request->getPost('reference_code') ?: null,
            'paid_at'        => $this->request->getPost('paid_at') ?: $existingTransaction['paid_at'],
        ];

        $result = $this->service->update($id, $data, $schoolId);

        if ($result) {
            return redirect()->to('/finance/transactions')->with('message', 'Transaction updated successfully!');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update transaction. Please try again.');
    }

    /**
     * Delete a transaction.
     */
    public function delete(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();

        // Verify transaction exists
        $transaction = $this->service->getById($id, $schoolId);
        if (!$transaction) {
            return redirect()->to('/finance/transactions')->with('error', 'Transaction not found.');
        }

        $result = $this->service->delete($id, $schoolId);

        if ($result) {
            return redirect()->to('/finance/transactions')->with('message', 'Transaction deleted successfully!');
        }

        return redirect()->to('/finance/transactions')->with('error', 'Failed to delete transaction. Please try again.');
    }
}
