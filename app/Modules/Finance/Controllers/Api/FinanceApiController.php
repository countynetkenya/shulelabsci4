<?php

namespace Modules\Finance\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\Finance\Models\FinanceInvoiceModel;

class FinanceApiController extends ResourceController
{
    protected $modelName = FinanceInvoiceModel::class;
    protected $format    = 'json';

    public function invoices($studentId = null)
    {
        if (!$studentId) {
            return $this->failNotFound('Student ID required');
        }
        
        $invoices = $this->model->where('student_id', $studentId)->findAll();
        return $this->respond($invoices);
    }
}
