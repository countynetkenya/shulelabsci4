<?php

namespace Modules\Inventory\Models;

use CodeIgniter\Model;

class InventoryLocationModel extends Model
{
    protected $table = 'inventory_locations';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = true;

    protected $allowedFields = ['name', 'description', 'is_default'];

    protected $useTimestamps = true;

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';

    protected $deletedField = 'deleted_at';
}
