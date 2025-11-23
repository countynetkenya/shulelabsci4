<?php

namespace App\Models;

use App\Models\TenantModel;

/**
 * PaymentModel - Payment records.
 */
class PaymentModel extends TenantModel
{
    protected $table = 'payments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'school_id',
        'invoice_id',
        'amount',
        'payment_method',
        'reference_number',
        'payment_date',
        'status',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
