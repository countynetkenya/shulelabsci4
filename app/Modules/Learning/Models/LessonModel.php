<?php

namespace Modules\Learning\Models;

use CodeIgniter\Model;

class LessonModel extends Model
{
    protected $table = 'learning_lessons';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = true;

    protected $protectFields = true;

    protected $allowedFields = [
        'course_id',
        'title',
        'content',
        'video_url',
        'sequence_order',
        'deleted_at',
    ];

    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';

    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'course_id' => 'required|integer',
        'title' => 'required|min_length[3]|max_length[255]',
        'sequence_order' => 'integer',
    ];
}
