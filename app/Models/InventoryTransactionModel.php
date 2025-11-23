<?php

namespace App\Models;

use App\Models\TenantModel;

/**
 * InventoryTransactionModel - Inventory transaction history.
 */
class InventoryTransactionModel extends TenantModel
{
    protected $table = 'inventory_transactions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'school_id',
        'asset_id',
        'transaction_type',
        'quantity',
        'notes',
        'transaction_date',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
