<?php

namespace Modules\Inventory\Models;

use CodeIgniter\Model;

class InventorySupplierModel extends Model
{
    protected $table = 'inventory_suppliers';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'object';

    protected $useSoftDeletes = false;

    protected $protectFields = true;

    protected $allowedFields = ['name', 'contact_person', 'phone', 'email', 'address'];

    // Dates
    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'name'  => 'required|max_length[100]',
        'email' => 'permit_empty|valid_email',
    ];
}
