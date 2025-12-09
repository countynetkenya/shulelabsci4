<?php

namespace Modules\LMS\Models;

use App\Models\TenantModel;

class CourseModel extends TenantModel
{
    protected $table = 'learning_courses';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = true;

    protected $protectFields = true;

    protected $allowedFields = ['school_id', 'teacher_id', 'title', 'description', 'status', 'deleted_at'];

    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';

    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'school_id'  => 'required|integer',
        'teacher_id' => 'required|integer',
        'title'      => 'required|max_length[255]',
        'status'     => 'in_list[draft,published,archived]',
    ];

    protected $validationMessages = [];

    protected $skipValidation = false;

    protected $cleanValidationRules = true;
}
