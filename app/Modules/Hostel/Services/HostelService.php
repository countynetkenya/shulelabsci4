<?php

namespace Modules\Hostel\Services;

use Modules\Hostel\Models\HostelModel;

class HostelService
{
    protected $hostelModel;

    public function __construct()
    {
        $this->hostelModel = new HostelModel();
    }

    /**
     * Get all hostels for a school.
     *
     * @param int $schoolId
     * @return array
     */
    public function getHostels(int $schoolId): array
    {
        return $this->hostelModel->where('school_id', $schoolId)->findAll();
    }

    /**
     * Create a new hostel.
     *
     * @param array $data
     * @return int|bool Insert ID or false
     */
    public function createHostel(array $data)
    {
        return $this->hostelModel->insert($data);
    }

    /**
     * Get a specific hostel by ID.
     *
     * @param int $id
     * @return array|null
     */
    public function getHostel(int $id)
    {
        return $this->hostelModel->find($id);
    }

    /**
     * Update a hostel.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateHostel(int $id, array $data): bool
    {
        return $this->hostelModel->update($id, $data);
    }

    /**
     * Delete a hostel.
     *
     * @param int $id
     * @return bool
     */
    public function deleteHostel(int $id): bool
    {
        return $this->hostelModel->delete($id);
    }
}
