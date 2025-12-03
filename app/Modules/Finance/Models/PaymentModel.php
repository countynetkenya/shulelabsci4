<?php

namespace Modules\Finance\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table            = 'finance_payments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'school_id',
        'invoice_id',
        'amount',
        'method',
        'reference_code',
        'paid_at',
        'recorded_by',
        'deleted_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'school_id'      => 'required|integer',
        'invoice_id'     => 'required|integer',
        'amount'         => 'required|decimal|greater_than[0]',
        'method'         => 'required|in_list[cash,bank_transfer,mobile_money,cheque]',
        'reference_code' => 'permit_empty|max_length[100]',
        'paid_at'        => 'required|valid_date',
        'recorded_by'    => 'required|integer',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
