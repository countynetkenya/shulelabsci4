<?php

namespace App\Models;

use App\Models\TenantModel;

/**
 * InvoiceModel - Student invoices.
 */
class InvoiceModel extends TenantModel
{
    protected $table = 'invoices';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'school_id',
        'student_id',
        'total_amount',
        'paid_amount',
        'balance',
        'due_date',
        'status',
        'items',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
