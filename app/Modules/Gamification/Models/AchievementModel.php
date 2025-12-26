<?php

namespace App\Modules\Gamification\Models;

use CodeIgniter\Model;

/**
 * AchievementModel - Manages achievement definitions.
 */
class AchievementModel extends Model
{
    protected $table = 'achievements';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = false;

    protected $protectFields = true;

    protected $allowedFields = [
        'school_id',
        'name',
        'code',
        'description',
        'category',
        'criteria_type',
        'criteria_value',
        'points_reward',
        'badge_id',
        'is_active',
    ];

    // Dates
    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';

    protected $createdField = 'created_at';

    protected $updatedField = '';

    protected $deletedField = '';

    // Validation
    protected $validationRules = [
        'school_id' => 'permit_empty|integer',
        'name' => 'required|max_length[100]',
        'code' => 'required|max_length[50]',
        'category' => 'required|max_length[50]',
        'criteria_type' => 'required|max_length[50]',
        'criteria_value' => 'required|integer',
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
