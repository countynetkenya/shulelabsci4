<?php

namespace Modules\Inventory\Models;

use CodeIgniter\Model;

class InventoryTransferModel extends Model
{
    protected $table = 'inventory_transfers';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $allowedFields = ['item_id', 'from_location_id', 'to_location_id', 'quantity', 'status', 'initiated_by', 'completed_by', 'thread_id', 'completed_at'];

    protected $useTimestamps = true;

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';
}
