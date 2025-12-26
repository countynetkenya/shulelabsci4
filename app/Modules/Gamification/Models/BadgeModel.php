<?php

namespace App\Modules\Gamification\Models;

use CodeIgniter\Model;

/**
 * BadgeModel - Manages badge definitions
 */
class BadgeModel extends Model
{
    protected $table = 'badges';
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
        'icon',
        'color',
        'category',
        'tier',
        'points_reward',
        'criteria',
        'is_secret',
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
        'category' => 'required|in_list[academic,attendance,behavior,sports,leadership,special]',
        'tier' => 'permit_empty|in_list[bronze,silver,gold,platinum,diamond]',
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
