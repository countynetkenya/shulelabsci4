<?php

namespace Modules\Mobile\Services;

use Modules\Mobile\Models\MobileDeviceModel;

/**
 * MobileService - Business logic for mobile device management
 */
class MobileService
{
    protected MobileDeviceModel $model;

    public function __construct()
    {
        $this->model = new MobileDeviceModel();
    }

    /**
     * Get all devices for a school (via users)
     */
    public function getAll(int $schoolId): array
    {
        $db = \Config\Database::connect();
        
        return $db->table('mobile_devices md')
            ->select('md.*, u.username, u.email')
            ->join('users u', 'u.id = md.user_id', 'left')
            ->where('u.school_id', $schoolId)
            ->orderBy('md.last_active_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get a single device by ID
     */
    public function getById(int $id, int $schoolId): ?array
    {
        $db = \Config\Database::connect();
        
        return $db->table('mobile_devices md')
            ->select('md.*, u.username, u.email')
            ->join('users u', 'u.id = md.user_id', 'left')
            ->where('md.id', $id)
            ->where('u.school_id', $schoolId)
            ->get()
            ->getRowArray();
    }

    /**
     * Create a new device record
     */
    public function create(array $data): int|false
    {
        return $this->model->insert($data);
    }

    /**
     * Update an existing device
     */
    public function update(int $id, array $data): bool
    {
        return $this->model->update($id, $data);
    }

    /**
     * Delete a device
     */
    public function delete(int $id): bool
    {
        return $this->model->delete($id);
    }

    /**
     * Get device statistics for a school
     */
    public function getStatistics(int $schoolId): array
    {
        $db = \Config\Database::connect();
        
        $totalDevices = $db->table('mobile_devices md')
            ->join('users u', 'u.id = md.user_id', 'left')
            ->where('u.school_id', $schoolId)
            ->countAllResults();
        
        $activeDevices = $db->table('mobile_devices md')
            ->join('users u', 'u.id = md.user_id', 'left')
            ->where('u.school_id', $schoolId)
            ->where('md.is_active', 1)
            ->countAllResults();
        
        $iosDevices = $db->table('mobile_devices md')
            ->join('users u', 'u.id = md.user_id', 'left')
            ->where('u.school_id', $schoolId)
            ->where('md.device_type', 'ios')
            ->countAllResults();
        
        $androidDevices = $db->table('mobile_devices md')
            ->join('users u', 'u.id = md.user_id', 'left')
            ->where('u.school_id', $schoolId)
            ->where('md.device_type', 'android')
            ->countAllResults();

        return [
            'total_devices' => $totalDevices,
            'active_devices' => $activeDevices,
            'ios_devices' => $iosDevices,
            'android_devices' => $androidDevices,
        ];
    }
}
