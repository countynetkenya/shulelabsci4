<?php

namespace Modules\Finance\Services;

use Modules\Finance\Models\PaymentModel;
use Modules\Finance\Models\InvoiceModel;

class PaymentsService
{
    protected $paymentModel;
    protected $invoiceModel;
    protected $db;

    public function __construct()
    {
        $this->paymentModel = new PaymentModel();
        $this->invoiceModel = new InvoiceModel();
        $this->db = \Config\Database::connect();
    }

    public function getAllPayments($schoolId)
    {
        return $this->paymentModel
                    ->select('finance_payments.*, users.full_name as student_name, finance_invoices.reference_number as invoice_ref')
                    ->join('finance_invoices', 'finance_invoices.id = finance_payments.invoice_id')
                    ->join('users', 'users.id = finance_invoices.student_id')
                    ->where('finance_invoices.school_id', $schoolId)
                    ->orderBy('finance_payments.created_at', 'DESC')
                    ->findAll();
    }

    public function createPayment(array $data)
    {
        $this->db->transStart();

        // Fetch Invoice to get School ID and validate
        $invoice = null;
        if (!empty($data['invoice_id'])) {
            $invoice = $this->invoiceModel->find($data['invoice_id']);
        }

        if (!$invoice) {
            return false;
        }

        // Set School ID from Invoice
        $data['school_id'] = $invoice['school_id'];

        // 1. Record Payment
        if (empty($data['reference_code'])) {
            $data['reference_code'] = $data['reference_number'] ?? 'PAY-' . strtoupper(uniqid());
        }
        $data['paid_at'] = date('Y-m-d H:i:s');
        
        if (empty($data['method'])) {
            $data['method'] = $data['payment_method'] ?? 'cash';
        }
        
        $this->paymentModel->insert($data);

        // 2. Update Invoice Balance
        $newBalance = $invoice['balance'] - $data['amount'];
        $status = $invoice['status'];
        
        if ($newBalance <= 0) {
            $newBalance = 0;
            $status = 'paid';
        } elseif ($newBalance < $invoice['amount']) {
            $status = 'partial';
        }

        $this->invoiceModel->update($data['invoice_id'], [
            'balance' => $newBalance,
            'status' => $status
        ]);

        $this->db->transComplete();

        return $this->db->transStatus();
    }
}
