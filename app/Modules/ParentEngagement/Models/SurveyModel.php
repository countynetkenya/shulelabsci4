<?php

namespace Modules\ParentEngagement\Models;

use CodeIgniter\Model;

class SurveyModel extends Model
{
    protected $table = 'surveys';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = false;

    protected $protectFields = true;

    protected $allowedFields = [
        'school_id', 'title', 'description', 'survey_type', 'target_audience',
        'target_ids', 'questions', 'is_anonymous', 'start_date', 'end_date',
        'status', 'response_count', 'created_by',
    ];

    protected $useTimestamps = true;

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'school_id'    => 'required|integer',
        'title'        => 'required|string|max_length[255]',
        'survey_type'  => 'required|in_list[feedback,poll,evaluation,custom]',
        'target_audience' => 'required|in_list[all_parents,class_parents,specific]',
        'questions'    => 'required',
        'status'       => 'in_list[draft,active,closed,archived]',
    ];

    protected $validationMessages = [];

    protected $skipValidation = false;

    protected $cleanValidationRules = true;

    protected $casts = [
        'target_ids' => 'json',
        'questions'  => 'json',
        'is_anonymous' => 'boolean',
    ];
}
