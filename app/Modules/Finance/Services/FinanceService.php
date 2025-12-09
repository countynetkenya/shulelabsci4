<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\TransactionModel;
use App\Modules\Finance\Models\InvoiceModel;
use Modules\Foundation\Services\AuditService;

/**
 * FinanceService - Business logic for finance transaction management
 * 
 * All queries are tenant-scoped by school_id.
 * Integrates with AuditService for logging critical actions.
 */
class FinanceService
{
    protected TransactionModel $model;
    protected InvoiceModel $invoiceModel;
    protected ?AuditService $auditService = null;

    public function __construct(?AuditService $auditService = null)
    {
        $this->model = new TransactionModel();
        $this->invoiceModel = new InvoiceModel();
        
        // Try to inject AuditService
        try {
            $this->auditService = $auditService ?? new AuditService();
        } catch (\Throwable $e) {
            // AuditService not available, continue without it
            log_message('debug', 'AuditService not available: ' . $e->getMessage());
        }
    }

    /**
     * Get all transactions for a school
     * 
     * @param int $schoolId
     * @param array $filters Optional filters
     * @return array
     */
    public function getAll(int $schoolId, array $filters = []): array
    {
        return $this->model->getTransactionsBySchool($schoolId, $filters);
    }

    /**
     * Get a single transaction by ID (scoped to school)
     * 
     * @param int $id
     * @param int $schoolId
     * @return array|null
     */
    public function getById(int $id, int $schoolId): ?array
    {
        $transaction = $this->model
            ->where('school_id', $schoolId)
            ->where('id', $id)
            ->first();
        
        return $transaction ?: null;
    }

    /**
     * Create a new transaction
     * 
     * @param array $data
     * @return int|false Transaction ID or false on failure
     */
    public function create(array $data): int|false
    {
        // Ensure paid_at is set
        if (!isset($data['paid_at'])) {
            $data['paid_at'] = date('Y-m-d H:i:s');
        }

        // Ensure recorded_by is set
        if (!isset($data['recorded_by'])) {
            $data['recorded_by'] = session()->get('user_id') ?? 1;
        }

        $result = $this->model->insert($data);

        if ($result && $this->auditService) {
            try {
                $this->auditService->recordEvent(
                    'finance.transaction.created',
                    'create',
                    [
                        'school_id' => $data['school_id'] ?? null,
                        'actor_id'  => session()->get('user_id'),
                    ],
                    null,
                    $data,
                    $this->getRequestMetadata()
                );
            } catch (\Throwable $e) {
                log_message('warning', 'Audit log failed: ' . $e->getMessage());
            }
        }

        // Update invoice balance if invoice_id is provided
        if ($result && !empty($data['invoice_id'])) {
            $this->updateInvoiceBalance($data['invoice_id'], $data['school_id']);
        }

        return $result;
    }

    /**
     * Update an existing transaction
     * 
     * @param int $id
     * @param array $data
     * @param int $schoolId
     * @return bool
     */
    public function update(int $id, array $data, int $schoolId): bool
    {
        // Get before state for audit
        $before = $this->getById($id, $schoolId);
        
        if (!$before) {
            return false;
        }

        $result = $this->model
            ->where('school_id', $schoolId)
            ->update($id, $data);

        if ($result && $this->auditService) {
            try {
                $this->auditService->recordEvent(
                    'finance.transaction.updated',
                    'update',
                    [
                        'school_id' => $schoolId,
                        'actor_id'  => session()->get('user_id'),
                    ],
                    $before,
                    array_merge($before, $data),
                    $this->getRequestMetadata()
                );
            } catch (\Throwable $e) {
                log_message('warning', 'Audit log failed: ' . $e->getMessage());
            }
        }

        // Update invoice balance if invoice changed or amount changed
        if ($result && (!empty($data['invoice_id']) || !empty($before['invoice_id']))) {
            $invoiceId = $data['invoice_id'] ?? $before['invoice_id'];
            $this->updateInvoiceBalance($invoiceId, $schoolId);
        }

        return $result;
    }

    /**
     * Delete a transaction
     * 
     * @param int $id
     * @param int $schoolId
     * @return bool
     */
    public function delete(int $id, int $schoolId): bool
    {
        // Get before state for audit
        $before = $this->getById($id, $schoolId);
        
        if (!$before) {
            return false;
        }

        $result = $this->model
            ->where('school_id', $schoolId)
            ->delete($id);

        if ($result && $this->auditService) {
            try {
                $this->auditService->recordEvent(
                    'finance.transaction.deleted',
                    'delete',
                    [
                        'school_id' => $schoolId,
                        'actor_id'  => session()->get('user_id'),
                    ],
                    $before,
                    null,
                    $this->getRequestMetadata()
                );
            } catch (\Throwable $e) {
                log_message('warning', 'Audit log failed: ' . $e->getMessage());
            }
        }

        // Update invoice balance after deletion
        if ($result && !empty($before['invoice_id'])) {
            $this->updateInvoiceBalance($before['invoice_id'], $schoolId);
        }

        return $result;
    }

    /**
     * Get payment methods available in the school
     * 
     * @param int $schoolId
     * @return array
     */
    public function getPaymentMethods(int $schoolId): array
    {
        return $this->model->getPaymentMethods($schoolId);
    }

    /**
     * Get transaction summary
     * 
     * @param int $schoolId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getSummary(int $schoolId, ?string $startDate = null, ?string $endDate = null): array
    {
        return $this->model->getTransactionSummary($schoolId, $startDate, $endDate);
    }

    /**
     * Get transactions for a specific invoice
     * 
     * @param int $invoiceId
     * @param int $schoolId
     * @return array
     */
    public function getByInvoice(int $invoiceId, int $schoolId): array
    {
        return $this->model->getTransactionsByInvoice($invoiceId, $schoolId);
    }

    /**
     * Update invoice balance after transaction changes
     * 
     * @param int $invoiceId
     * @param int $schoolId
     * @return void
     */
    protected function updateInvoiceBalance(int $invoiceId, int $schoolId): void
    {
        try {
            $invoice = $this->invoiceModel
                ->where('id', $invoiceId)
                ->where('school_id', $schoolId)
                ->first();

            if (!$invoice) {
                return;
            }

            // Calculate total paid
            $totalPaid = $this->model->getTotalPaidForInvoice($invoiceId, $schoolId);

            // Calculate new balance
            $balance = max(0, $invoice['amount'] - $totalPaid);

            // Determine status
            $status = 'unpaid';
            if ($balance == 0) {
                $status = 'paid';
            } elseif ($totalPaid > 0) {
                $status = 'partial';
            } elseif (!empty($invoice['due_date']) && strtotime($invoice['due_date']) < time()) {
                $status = 'overdue';
            }

            // Update invoice
            $this->invoiceModel->update($invoiceId, [
                'balance' => $balance,
                'status'  => $status,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to update invoice balance: ' . $e->getMessage());
        }
    }

    /**
     * Get request metadata for audit logging
     * 
     * @return array
     */
    protected function getRequestMetadata(): array
    {
        $request = service('request');
        
        return [
            'ip'          => $request->getIPAddress(),
            'user_agent'  => $request->getUserAgent()->getAgentString(),
            'request_uri' => current_url(),
        ];
    }
}
