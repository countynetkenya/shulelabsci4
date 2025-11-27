<?php

namespace Modules\Hostel\Models;

use CodeIgniter\Model;

class HostelRoomModel extends Model
{
    protected $table            = 'hostel_rooms';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'hostel_id', 'room_number', 'floor_number', 'capacity', 'cost_per_term', 'is_active'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'hostel_id'     => 'required|integer',
        'room_number'   => 'required|max_length[20]',
        'capacity'      => 'required|integer|greater_than[0]',
        'cost_per_term' => 'decimal',
    ];
    // Callbacks
    protected $allowCallbacks = true;
    protected $afterInsert    = ['createBeds'];

    protected function createBeds(array $data)
    {
        if ($data['result']) {
            $roomId = $data['id'];
            $capacity = $data['data']['capacity'] ?? 0;

            if ($capacity > 0) {
                $bedModel = new HostelBedModel();
                for ($i = 1; $i <= $capacity; $i++) {
                    $bedModel->insert([
                        'room_id'    => $roomId,
                        'bed_number' => (string)$i,
                        'status'     => 'available'
                    ]);
                }
            }
        }
        return $data;
    }
}
