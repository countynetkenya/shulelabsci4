<?php

namespace Modules\Learning\Models;

use CodeIgniter\Model;

class EnrollmentModel extends Model
{
    protected $table = 'learning_enrollments';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = false;

    protected $protectFields = true;

    protected $allowedFields = [
        'school_id', 'student_id', 'course_id', 'status', 'enrolled_at', 'completed_at',
    ];

    protected $useTimestamps = false; // We manage enrolled_at manually or via default

    protected $createdField = 'enrolled_at';

    protected $updatedField = '';

    protected $deletedField = '';

    protected $validationRules = [
        'school_id'  => 'required|integer',
        'student_id' => 'required|integer',
        'course_id'  => 'required|integer',
        'status'     => 'required|in_list[active,completed,dropped]',
    ];

    protected $validationMessages = [];

    protected $skipValidation = false;

    protected $cleanValidationRules = true;
}
