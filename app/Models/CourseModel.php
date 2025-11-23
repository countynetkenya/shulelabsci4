<?php

namespace App\Models;

use App\Models\TenantModel;

/**
 * CourseModel - Course management.
 */
class CourseModel extends TenantModel
{
    protected $table = 'courses';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'school_id',
        'class_id',
        'course_name',
        'course_code',
        'teacher_id',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
