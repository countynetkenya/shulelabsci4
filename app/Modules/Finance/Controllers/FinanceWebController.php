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
        // Implementation for invoice creation
    }
}
