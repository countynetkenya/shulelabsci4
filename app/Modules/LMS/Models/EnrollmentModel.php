<?php

namespace Modules\LMS\Models;

use CodeIgniter\Model;

class EnrollmentModel extends Model
{
    protected $table            = 'learning_enrollments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['school_id', 'student_id', 'course_id', 'status', 'completed_at'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules      = [
        'school_id'  => 'required|integer',
        'student_id' => 'required|integer',
        'course_id'  => 'required|integer',
        'status'     => 'in_list[active,completed,dropped]',
    ];
}
