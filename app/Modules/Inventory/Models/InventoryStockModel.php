<?php

namespace Modules\Inventory\Models;

use CodeIgniter\Model;

class InventoryStockModel extends Model
{
    protected $table = 'inventory_stock';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $allowedFields = ['item_id', 'location_id', 'quantity'];

    protected $useTimestamps = true;

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';
}
