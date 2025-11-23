<?php

namespace App\Services;

use App\Models\InvoiceModel;
use App\Models\PaymentModel;
use App\Models\FeeStructureModel;

/**
 * FinanceService - Financial management for multi-school system.
 */
class FinanceService
{
    protected InvoiceModel $invoiceModel;
    protected PaymentModel $paymentModel;
    protected FeeStructureModel $feeStructureModel;

    public function __construct()
    {
        $this->invoiceModel = model(InvoiceModel::class);
        $this->paymentModel = model(PaymentModel::class);
        $this->feeStructureModel = model(FeeStructureModel::class);
    }

    /**
     * Get all invoices for a school.
     */
    public function getSchoolInvoices(int $schoolId, ?string $status = null): array
    {
        $builder = $this->invoiceModel->forSchool($schoolId);

        if ($status) {
            $builder->where('status', $status);
        }

        return $builder->findAll();
    }

    /**
     * Create invoice for student.
     */
    public function createInvoice(int $studentId, int $schoolId, array $items, string $dueDate): array
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['amount'];
        }

        $invoiceData = [
            'student_id' => $studentId,
            'school_id' => $schoolId,
            'total_amount' => $total,
            'paid_amount' => 0,
            'balance' => $total,
            'due_date' => $dueDate,
            'status' => 'pending',
            'items' => json_encode($items),
        ];

        $invoiceId = $this->invoiceModel->insert($invoiceData);

        if (!$invoiceId) {
            return ['success' => false, 'message' => 'Failed to create invoice'];
        }

        return ['success' => true, 'invoice_id' => $invoiceId];
    }

    /**
     * Record payment for invoice.
     */
    public function recordPayment(int $invoiceId, float $amount, string $method, ?string $referenceNumber = null): array
    {
        $invoice = $this->invoiceModel->find($invoiceId);

        if (!$invoice) {
            return ['success' => false, 'message' => 'Invoice not found'];
        }

        // Record payment
        $paymentData = [
            'invoice_id' => $invoiceId,
            'school_id' => $invoice['school_id'],
            'amount' => $amount,
            'payment_method' => $method,
            'reference_number' => $referenceNumber,
            'payment_date' => date('Y-m-d H:i:s'),
            'status' => 'completed',
        ];

        $paymentId = $this->paymentModel->insert($paymentData);

        if (!$paymentId) {
            return ['success' => false, 'message' => 'Failed to record payment'];
        }

        // Update invoice
        $newPaidAmount = $invoice['paid_amount'] + $amount;
        $newBalance = $invoice['total_amount'] - $newPaidAmount;
        $newStatus = $newBalance <= 0 ? 'paid' : 'partial';

        $this->invoiceModel->update($invoiceId, [
            'paid_amount' => $newPaidAmount,
            'balance' => $newBalance,
            'status' => $newStatus,
        ]);

        return ['success' => true, 'payment_id' => $paymentId, 'new_balance' => $newBalance];
    }

    /**
     * Get invoice details with payments.
     */
    public function getInvoiceDetails(int $invoiceId): ?array
    {
        $invoice = $this->invoiceModel->find($invoiceId);

        if (!$invoice) {
            return null;
        }

        // Get payments for this invoice
        $payments = $this->paymentModel
            ->where('invoice_id', $invoiceId)
            ->orderBy('payment_date', 'ASC')
            ->findAll();

        $invoice['payments'] = $payments;
        $invoice['items'] = json_decode($invoice['items'], true);

        return $invoice;
    }

    /**
     * Get payment statistics for school.
     */
    public function getPaymentStats(int $schoolId, ?string $startDate = null, ?string $endDate = null): array
    {
        $builder = $this->paymentModel->forSchool($schoolId);

        if ($startDate) {
            $builder->where('payment_date >=', $startDate);
        }

        if ($endDate) {
            $builder->where('payment_date <=', $endDate);
        }

        $payments = $builder->findAll();

        $stats = [
            'total_collected' => 0,
            'payment_count' => count($payments),
            'by_method' => [],
        ];

        foreach ($payments as $payment) {
            $stats['total_collected'] += $payment['amount'];
            
            $method = $payment['payment_method'];
            if (!isset($stats['by_method'][$method])) {
                $stats['by_method'][$method] = ['count' => 0, 'amount' => 0];
            }
            $stats['by_method'][$method]['count']++;
            $stats['by_method'][$method]['amount'] += $payment['amount'];
        }

        return $stats;
    }

    /**
     * Get outstanding invoices.
     */
    public function getOutstandingInvoices(int $schoolId): array
    {
        return $this->invoiceModel
            ->forSchool($schoolId)
            ->where('balance >', 0)
            ->orderBy('due_date', 'ASC')
            ->findAll();
    }

    /**
     * Get fee structure for school.
     */
    public function getFeeStructure(int $schoolId, ?string $gradeLevel = null): array
    {
        $builder = $this->feeStructureModel->forSchool($schoolId);

        if ($gradeLevel) {
            $builder->where('grade_level', $gradeLevel);
        }

        return $builder->findAll();
    }

    /**
     * Set fee structure for grade.
     */
    public function setFeeStructure(int $schoolId, string $gradeLevel, array $feeItems): array
    {
        // Check if fee structure exists
        $existing = $this->feeStructureModel
            ->forSchool($schoolId)
            ->where('grade_level', $gradeLevel)
            ->first();

        $data = [
            'school_id' => $schoolId,
            'grade_level' => $gradeLevel,
            'fee_items' => json_encode($feeItems),
        ];

        if ($existing) {
            $result = $this->feeStructureModel->update($existing['id'], $data);
        } else {
            $result = $this->feeStructureModel->insert($data);
        }

        if (!$result) {
            return ['success' => false, 'message' => 'Failed to set fee structure'];
        }

        return ['success' => true];
    }
}
