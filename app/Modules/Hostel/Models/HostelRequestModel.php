<?php

namespace Modules\Hostel\Models;

use CodeIgniter\Model;

class HostelRequestModel extends Model
{
    protected $table = 'hostel_requests';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'object';

    protected $useSoftDeletes = false;

    protected $protectFields = true;

    protected $allowedFields = [
        'student_id', 'request_type', 'details', 'status', 'admin_response',
    ];

    // Dates
    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'student_id'   => 'required|integer',
        'request_type' => 'required|in_list[new_allocation,change_room,maintenance,vacate]',
        'status'       => 'required|in_list[pending,approved,rejected,completed]',
    ];
}
