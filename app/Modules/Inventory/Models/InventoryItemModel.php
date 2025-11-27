<?php

namespace Modules\Inventory\Models;

use CodeIgniter\Model;

class InventoryItemModel extends Model
{
    protected $table            = 'inventory_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'category_id', 'name', 'sku', 'description', 'type', 
        'unit_cost', 'reorder_level', 'location', 'is_billable'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'category_id' => 'required|integer',
        'name'        => 'required|max_length[150]',
        'sku'         => 'permit_empty|is_unique[inventory_items.sku,id,{id}]',
        'type'        => 'required|in_list[physical,service,bundle]',
        'quantity'    => 'integer|greater_than_equal_to[0]',
        'unit_cost'   => 'decimal',
    ];
}
