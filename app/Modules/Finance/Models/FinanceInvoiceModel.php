<?php

namespace Modules\Finance\Models;

use CodeIgniter\Model;

class FinanceInvoiceModel extends Model
{
    protected $table            = 'finance_invoices';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['student_id', 'fee_structure_id', 'amount', 'balance', 'status', 'due_date'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules      = [
        'student_id' => 'required|integer',
        'fee_structure_id' => 'required|integer',
        'amount' => 'required|numeric',
        'balance' => 'required|numeric',
    ];
}
