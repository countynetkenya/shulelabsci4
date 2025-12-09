<?php

namespace Modules\ParentEngagement\Models;

use CodeIgniter\Model;

class FundraisingCampaignModel extends Model
{
    protected $table            = 'fundraising_campaigns';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'school_id', 'name', 'description', 'target_amount', 'raised_amount',
        'start_date', 'end_date', 'status', 'donor_count', 'created_by'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'school_id'     => 'required|integer',
        'name'          => 'required|string|max_length[200]',
        'target_amount' => 'required|decimal',
        'start_date'    => 'required|valid_date',
        'end_date'      => 'required|valid_date',
        'status'        => 'in_list[draft,active,completed,cancelled]',
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;
    protected $cleanValidationRules = true;

    protected $casts = [
        'target_amount' => 'decimal',
        'raised_amount' => 'decimal',
    ];
}
