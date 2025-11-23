<?php

namespace App\Models;

/**
 * AssignmentModel - Assignment management.
 */
class AssignmentModel extends TenantModel
{
    protected $table = 'assignments';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'school_id',
        'course_id',
        'title',
        'description',
        'due_date',
        'max_points',
        'status',
    ];

    protected bool $allowEmptyInserts = false;

    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = true;

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';
}
