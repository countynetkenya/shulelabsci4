<?php

namespace Modules\Hostel\Models;

use CodeIgniter\Model;

class HostelModel extends Model
{
    protected $table = 'hostels';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = false;

    protected $protectFields = true;

    protected $allowedFields = [
        'school_id', 'name', 'type', 'warden_id', 'capacity', 'location', 'description',
    ];

    // Dates
    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';

    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'school_id' => 'required|integer',
        'name'      => 'required|max_length[100]',
        'type'      => 'required|in_list[boys,girls,staff,mixed]',
    ];
}
