<?php

namespace Modules\Inventory\Models;

use CodeIgniter\Model;

class InventoryTransactionModel extends Model
{
    protected $table = 'inventory_transactions';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'object';

    protected $useSoftDeletes = false;

    protected $protectFields = true;

    protected $allowedFields = [
        'item_id', 'user_id', 'recipient_id', 'supplier_id',
        'type', 'quantity', 'unit_price', 'notes',
    ];

    // Dates
    protected $useTimestamps = true; // Only created_at

    protected $dateFormat = 'datetime';

    protected $createdField = 'created_at';

    protected $updatedField = ''; // No updated_at

    // Validation
    protected $validationRules = [
        'item_id'  => 'required|integer',
        'user_id'  => 'required|integer',
        'type'     => 'required|in_list[receive,issue,adjustment,return]',
        'quantity' => 'required|integer|not_in_list[0]',
    ];
}
