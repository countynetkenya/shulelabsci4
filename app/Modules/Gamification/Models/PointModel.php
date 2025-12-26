<?php

namespace App\Modules\Gamification\Models;

use CodeIgniter\Model;

/**
 * PointModel - Manages point transactions.
 */
class PointModel extends Model
{
    protected $table = 'points';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = false;

    protected $protectFields = true;

    protected $allowedFields = [
        'school_id',
        'user_id',
        'points',
        'type',
        'source',
        'source_id',
        'description',
        'metadata',
    ];

    // Dates
    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';

    protected $createdField = 'created_at';

    protected $updatedField = '';

    protected $deletedField = '';

    // Validation
    protected $validationRules = [
        'school_id' => 'required|integer',
        'user_id' => 'required|integer',
        'points' => 'required|integer',
        'type' => 'required|in_list[earned,spent,bonus,penalty,expired]',
        'source' => 'required|max_length[50]',
    ];

    protected $validationMessages = [];

    protected $skipValidation = false;

    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;

    protected $beforeInsert = [];

    protected $afterInsert = [];

    protected $beforeUpdate = [];

    protected $afterUpdate = [];

    protected $beforeFind = [];

    protected $afterFind = [];

    protected $beforeDelete = [];

    protected $afterDelete = [];
}
