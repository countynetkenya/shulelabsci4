<?php

namespace Modules\ParentEngagement\Models;

use CodeIgniter\Model;

class VolunteerModel extends Model
{
    protected $table = 'volunteers';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = false;

    protected $protectFields = true;

    protected $allowedFields = [
        'school_id', 'user_id', 'skills', 'availability', 'status', 'total_hours',
    ];

    protected $useTimestamps = true;

    protected $createdField = 'created_at';

    protected $updatedField = '';

    protected $validationRules = [
        'school_id' => 'required|integer',
        'user_id'   => 'required|integer',
        'status'    => 'in_list[active,inactive]',
    ];

    protected $validationMessages = [];

    protected $skipValidation = false;

    protected $cleanValidationRules = true;

    protected $casts = [
        'skills' => 'json',
        'availability' => 'json',
        'total_hours' => 'decimal',
    ];
}
