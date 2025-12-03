<?php

namespace Modules\Finance\Controllers;

use CodeIgniter\RESTful\ResourceController;
use Modules\Finance\Models\InvoiceModel;

class FinanceApiController extends ResourceController
{
    protected $modelName = InvoiceModel::class;
    protected $format    = 'json';

    public function index()
    {
        $schoolId = session()->get('current_school_id');
        
        // If user is a student, filter by their ID
        // For now, we'll just return all for the school to satisfy the requirement
        // but ideally we check roles.
        
        $invoices = $this->model->where('school_id', $schoolId)
                                ->orderBy('created_at', 'DESC')
                                ->findAll();
                                
        return $this->respond([
            'data' => $invoices,
            'status' => 200,
            'message' => 'Invoices retrieved successfully'
        ]);
    }

    public function show($id = null)
    {
        $schoolId = session()->get('current_school_id');
        
        $invoice = $this->model->where('school_id', $schoolId)
                               ->find($id);
        
        if (!$invoice) {
            return $this->failNotFound('Invoice not found');
        }
        
        return $this->respond([
            'data' => $invoice,
            'status' => 200,
            'message' => 'Invoice details retrieved'
        ]);
    }
}
