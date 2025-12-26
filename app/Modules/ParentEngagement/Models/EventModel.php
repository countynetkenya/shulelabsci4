<?php

namespace Modules\ParentEngagement\Models;

use CodeIgniter\Model;

class EventModel extends Model
{
    protected $table = 'events';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = false;

    protected $protectFields = true;

    protected $allowedFields = [
        'school_id', 'title', 'description', 'event_type', 'venue',
        'start_datetime', 'end_datetime', 'max_attendees', 'registration_required',
        'registration_deadline', 'fee', 'status', 'created_by',
    ];

    protected $useTimestamps = true;

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'school_id'      => 'required|integer',
        'title'          => 'required|string|max_length[255]',
        'event_type'     => 'required|in_list[academic,sports,cultural,meeting,fundraising,other]',
        'start_datetime' => 'required|valid_date',
        'status'         => 'in_list[draft,published,cancelled,completed]',
    ];

    protected $validationMessages = [];

    protected $skipValidation = false;

    protected $cleanValidationRules = true;

    protected $casts = [
        'registration_required' => 'boolean',
        'fee' => 'decimal',
    ];
}
