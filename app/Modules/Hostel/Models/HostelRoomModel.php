<?php

namespace App\Modules\Hostel\Models;

use CodeIgniter\Model;

class HostelRoomModel extends Model
{
    protected $table = 'hostel_rooms';
    protected $primaryKey = 'id';
    protected $allowedFields = ['school_id', 'room_number', 'capacity', 'type', 'status'];
    protected $useTimestamps = true;
    protected $returnType = 'array';
}
