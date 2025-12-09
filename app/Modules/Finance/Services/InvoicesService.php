<?php

namespace Modules\Finance\Services;

use App\Models\UserModel;
use Modules\Finance\Models\InvoiceModel;

class InvoicesService
{
    protected $invoiceModel;

    protected $userModel;

    protected $db;

    public function __construct()
    {
        $this->invoiceModel = new InvoiceModel();
        $this->userModel = new UserModel();
        $this->db = \Config\Database::connect();
    }

    public function getAllInvoices($schoolId)
    {
        return $this->invoiceModel
                    ->select('finance_invoices.*, users.full_name as student_name')
                    ->join('users', 'users.id = finance_invoices.student_id')
                    ->where('finance_invoices.school_id', $schoolId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    public function getInvoiceById($id)
    {
        return $this->invoiceModel
                    ->select('finance_invoices.*, users.full_name as student_name, users.email as student_email')
                    ->join('users', 'users.id = finance_invoices.student_id')
                    ->find($id);
    }

    public function createInvoice(array $data)
    {
        // Generate Reference Number if not provided
        if (empty($data['reference_number'])) {
            $data['reference_number'] = 'INV-' . strtoupper(uniqid());
        }

        // Set initial balance equal to amount
        $data['balance'] = $data['amount'];
        $data['status'] = 'unpaid';

        return $this->invoiceModel->insert($data);
    }

    public function updateInvoice($id, array $data)
    {
        return $this->invoiceModel->update($id, $data);
    }

    public function deleteInvoice($id)
    {
        return $this->invoiceModel->delete($id);
    }

    public function getStudentsForSchool($schoolId)
    {
        // This logic was previously in the controller
        return $this->db->table('users')
                   ->join('school_users', 'school_users.user_id = users.id')
                   ->where('school_users.school_id', $schoolId)
                   // ->where('school_users.role_id', 4) // Optional: Filter by student role
                   ->select('users.id, users.full_name, users.username')
                   ->get()
                   ->getResultArray();
    }
}
