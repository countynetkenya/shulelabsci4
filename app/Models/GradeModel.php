<?php

namespace App\Models;

use App\Models\TenantModel;

/**
 * GradeModel - Grade management.
 */
class GradeModel extends TenantModel
{
    protected $table = 'grades';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'school_id',
        'assignment_id',
        'student_id',
        'points_earned',
        'max_points',
        'feedback',
        'graded_at',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
