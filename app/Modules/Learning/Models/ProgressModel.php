<?php

namespace Modules\Learning\Models;

use CodeIgniter\Model;

class ProgressModel extends Model
{
    protected $table            = 'learning_progress';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'enrollment_id', 'lesson_id', 'completed_at'
    ];

    protected $useTimestamps = false;
    protected $createdField  = 'completed_at';
    protected $updatedField  = '';
    protected $deletedField  = '';

    protected $validationRules      = [
        'enrollment_id' => 'required|integer',
        'lesson_id'     => 'required|integer',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
