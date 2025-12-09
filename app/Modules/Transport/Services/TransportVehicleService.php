<?php

namespace App\Modules\Transport\Services;

use App\Modules\Transport\Models\TransportVehicleModel;

class TransportVehicleService
{
    protected $model;

    public function __construct()
    {
        $this->model = new TransportVehicleModel();
    }

    public function getAll(int $schoolId)
    {
        return $this->model->where('school_id', $schoolId)->findAll();
    }

    public function getById(int $id, int $schoolId)
    {
        return $this->model->where('school_id', $schoolId)->find($id);
    }

    public function create(array $data)
    {
        return $this->model->insert($data);
    }

    public function update(int $id, array $data, int $schoolId)
    {
        return $this->model->where('school_id', $schoolId)->update($id, $data);
    }

    public function delete(int $id, int $schoolId)
    {
        return $this->model->where('school_id', $schoolId)->delete($id);
    }
}
