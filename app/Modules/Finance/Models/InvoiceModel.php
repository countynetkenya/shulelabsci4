<?php

namespace Modules\Finance\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table = 'finance_invoices';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = true;

    protected $protectFields = true;

    protected $allowedFields = [
        'school_id',
        'student_id',
        'fee_structure_id',
        'reference_number',
        'amount',
        'balance',
        'status',
        'due_date',
        'deleted_at',
    ];

    // Dates
    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';

    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'school_id'        => 'required|integer',
        'student_id'       => 'required|integer',
        'fee_structure_id' => 'permit_empty|integer',
        'reference_number' => 'required|max_length[50]',
        'amount'           => 'required|decimal',
        'balance'          => 'required|decimal',
        'status'           => 'required|in_list[unpaid,partial,paid,overdue]',
        'due_date'         => 'required|valid_date',
    ];

    protected $validationMessages = [];

    protected $skipValidation = false;

    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;

    protected $beforeInsert = [];

    protected $afterInsert = [];

    protected $beforeUpdate = [];

    protected $afterUpdate = [];

    protected $beforeFind = [];

    protected $afterFind = [];

    protected $beforeDelete = [];

    protected $afterDelete = [];
}
