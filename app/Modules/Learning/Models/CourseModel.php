<?php

namespace Modules\Learning\Models;

use CodeIgniter\Model;

class CourseModel extends Model
{
    protected $table = 'learning_courses';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = true;

    protected $protectFields = true;

    protected $allowedFields = [
        'school_id',
        'teacher_id',
        'title',
        'description',
        'status',
        'deleted_at',
    ];

    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';

    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'school_id' => 'required|integer',
        'teacher_id' => 'required|integer',
        'title' => 'required|min_length[3]|max_length[255]',
        'status' => 'required|in_list[draft,published,archived]',
    ];
}
