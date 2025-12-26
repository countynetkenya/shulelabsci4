<?php

namespace Modules\ParentEngagement\Models;

use CodeIgniter\Model;

class ConferenceModel extends Model
{
    protected $table = 'conferences';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = false;

    protected $protectFields = true;

    protected $allowedFields = [
        'school_id', 'name', 'description', 'conference_date', 'start_time',
        'end_time', 'slot_duration_minutes', 'venue', 'is_virtual',
        'meeting_link', 'status', 'created_by',
    ];

    protected $useTimestamps = true;

    protected $createdField = 'created_at';

    protected $updatedField = '';

    protected $validationRules = [
        'school_id'       => 'required|integer',
        'name'            => 'required|string|max_length[200]',
        'conference_date' => 'required|valid_date',
        'start_time'      => 'required',
        'end_time'        => 'required',
        'status'          => 'in_list[draft,open,closed,completed]',
    ];

    protected $validationMessages = [];

    protected $skipValidation = false;

    protected $cleanValidationRules = true;

    protected $casts = [
        'is_virtual' => 'boolean',
    ];
}
