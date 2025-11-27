<?php

namespace Modules\Finance\Models;

use CodeIgniter\Model;

class FinanceFeeStructureModel extends Model
{
    protected $table            = 'finance_fee_structures';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'amount', 'academic_period_id', 'class_id'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules      = [
        'name' => 'required|min_length[3]|max_length[255]',
        'amount' => 'required|numeric',
    ];
}
