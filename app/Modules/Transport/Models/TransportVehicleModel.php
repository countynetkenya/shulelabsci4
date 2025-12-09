<?php

namespace App\Modules\Transport\Models;

use CodeIgniter\Model;

class TransportVehicleModel extends Model
{
    protected $table = 'transport_vehicles';
    protected $primaryKey = 'id';
    protected $allowedFields = ['school_id', 'registration_number', 'capacity', 'driver_name', 'status'];
    protected $useTimestamps = true;
}
