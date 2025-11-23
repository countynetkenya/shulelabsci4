<?php

namespace App\Models;

/**
 * FeeStructureModel - Fee structure per grade level.
 */
class FeeStructureModel extends TenantModel
{
    protected $table = 'fee_structures';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'school_id',
        'grade_level',
        'fee_items',
    ];

    protected bool $allowEmptyInserts = false;

    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = true;

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';
}
