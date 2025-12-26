<?php

namespace Modules\Transport\Models;

use CodeIgniter\Model;

class TransportVehicleModel extends Model
{
    protected $table = 'transport_vehicles';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = false;

    protected $protectFields = true;

    protected $allowedFields = [
        'school_id', 'registration_number', 'make', 'model', 'year',
        'capacity', 'fuel_type', 'insurance_expiry', 'fitness_expiry',
        'status', 'gps_device_id', 'driver_name',
    ];

    protected $useTimestamps = true;

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'school_id'           => 'required|integer',
        'registration_number' => 'required|string|max_length[20]',
        'capacity'            => 'required|integer',
        'fuel_type'           => 'in_list[petrol,diesel,electric,hybrid]',
        'status'              => 'in_list[active,maintenance,retired]',
    ];

    protected $validationMessages = [];

    protected $skipValidation = false;

    protected $cleanValidationRules = true;
}
