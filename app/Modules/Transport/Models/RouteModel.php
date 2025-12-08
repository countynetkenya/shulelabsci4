<?php

namespace Modules\Transport\Models;

use CodeIgniter\Model;

class RouteModel extends Model
{
    protected $table            = 'transport_routes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'school_id', 'name', 'code', 'description', 'vehicle_id', 'driver_id', 
        'assistant_id', 'distance_km', 'estimated_duration_min', 'monthly_fee', 'is_active'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = '';

    protected $validationRules      = [
        'school_id' => 'required|integer',
        'name'      => 'required|string|max_length[100]',
        'code'      => 'required|string|max_length[20]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
