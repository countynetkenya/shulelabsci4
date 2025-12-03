<?php

namespace Modules\Finance\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class FinanceWebController extends BaseController
{
    public function index()
    {
        return view('Modules\Finance\Views\finance\index', [
            'title' => 'Finance Dashboard',
        ]);
    }

    public function createFeeStructure()
    {
        $rules = [
            'name' => 'required|min_length[3]',
            'amount' => 'required|decimal',
            'term' => 'required',
            'academic_year' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        
        // Get School ID from Session (TenantService should handle this, but for now we assume it's in session or we get it from service)
        // Since we are using TenantTestTrait, we know 'current_school_id' is in session.
        $schoolId = session()->get('current_school_id');

        $db = \Config\Database::connect();
        $db->table('finance_fee_structures')->insert([
            'school_id' => $schoolId,
            'name' => $data['name'],
            'amount' => $data['amount'],
            'term' => $data['term'],
            'academic_year' => $data['academic_year'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/finance')->with('message', 'Fee Structure created successfully');
    }

    public function createInvoice()
    {
        $rules = [
            'student_id' => 'required|integer',
            'amount' => 'required|decimal',
            'due_date' => 'required|valid_date',
            'fee_structure_id' => 'permit_empty|integer',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        $schoolId = session()->get('current_school_id');

        // Generate Reference Number
        $reference = 'INV-' . date('Ymd') . '-' . rand(1000, 9999);

        $invoiceModel = new \Modules\Finance\Models\InvoiceModel();
        
        $insertData = [
            'school_id' => $schoolId,
            'student_id' => $data['student_id'],
            'fee_structure_id' => $data['fee_structure_id'] ?? null,
            'reference_number' => $reference,
            'amount' => $data['amount'],
            'balance' => $data['amount'], // Initial balance is total amount
            'status' => 'unpaid',
            'due_date' => $data['due_date'],
        ];

        if (!$invoiceModel->insert($insertData)) {
             return redirect()->back()->withInput()->with('errors', $invoiceModel->errors());
        }

        return redirect()->to('/finance')->with('message', 'Invoice created successfully');
    }
}
