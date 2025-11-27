<?php

namespace Modules\Hostel\Models;

use CodeIgniter\Model;

class HostelBedModel extends Model
{
    protected $table            = 'hostel_beds';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'room_id', 'bed_number', 'status'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'room_id'    => 'required|integer',
        'bed_number' => 'required|max_length[20]',
        'status'     => 'required|in_list[available,occupied,maintenance]',
    ];
}
