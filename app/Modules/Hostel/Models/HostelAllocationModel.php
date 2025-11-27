<?php

namespace Modules\Hostel\Models;

use CodeIgniter\Model;

class HostelAllocationModel extends Model
{
    protected $table            = 'hostel_allocations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'student_id', 'room_id', 'bed_id', 'academic_year', 'term', 'start_date', 'end_date', 'status', 'notes', 'created_by'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'student_id' => 'required|integer',
        'room_id'    => 'required|integer',
        'bed_id'     => 'required|integer',
        'start_date' => 'required|valid_date',
        'status'     => 'required|in_list[active,vacated,evicted]',
    ];
}
