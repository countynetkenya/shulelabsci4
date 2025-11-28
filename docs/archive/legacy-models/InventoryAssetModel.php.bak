<?php

namespace App\Models;

/**
 * InventoryAssetModel - Inventory assets.
 */
class InventoryAssetModel extends TenantModel
{
    protected $table = 'inventory_assets';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'school_id',
        'asset_name',
        'asset_code',
        'category',
        'quantity',
        'unit_price',
        'total_value',
    ];

    protected bool $allowEmptyInserts = false;

    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = true;

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';
}
