<?php

namespace Modules\Finance\Models;

use CodeIgniter\Model;

class FinancePaymentModel extends Model
{
    protected $table = 'finance_payments';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = false;

    protected $protectFields = true;

    protected $allowedFields = ['student_id', 'invoice_id', 'amount', 'method', 'reference_number', 'recorded_by', 'transaction_date'];

    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';

    protected $createdField = 'created_at';

    protected $updatedField = '';

    protected $validationRules = [
        'student_id' => 'required|integer',
        'amount' => 'required|numeric',
        'method' => 'required',
    ];
}
