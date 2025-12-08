<?php

namespace Modules\Transport\Services;

use Modules\Transport\Models\RouteModel;

class TransportService
{
    protected $routeModel;

    public function __construct()
    {
        $this->routeModel = new RouteModel();
    }

    /**
     * Get all transport routes for a school.
     *
     * @param int $schoolId
     * @return array
     */
    public function getRoutes(int $schoolId): array
    {
        return $this->routeModel->where('school_id', $schoolId)->findAll();
    }

    /**
     * Create a new route.
     *
     * @param array $data
     * @return int|bool Insert ID or false
     */
    public function createRoute(array $data)
    {
        return $this->routeModel->insert($data);
    }

    /**
     * Get a specific route by ID.
     *
     * @param int $id
     * @return array|null
     */
    public function getRoute(int $id)
    {
        return $this->routeModel->find($id);
    }

    /**
     * Update a route.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateRoute(int $id, array $data)
    {
        return $this->routeModel->update($id, $data);
    }

    /**
     * Delete a route.
     *
     * @param int $id
     * @return bool
     */
    public function deleteRoute(int $id)
    {
        return $this->routeModel->delete($id);
    }
}
