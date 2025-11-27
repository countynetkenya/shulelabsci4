<?php

namespace Modules\Hostel\Controllers;

use CodeIgniter\RESTful\ResourceController;
use Modules\Hostel\Models\HostelAllocationModel;

class HostelAllocationController extends ResourceController
{
    protected $modelName = HostelAllocationModel::class;

    protected $format = 'json';

    public function index()
    {
        $studentId = $this->request->getGet('student_id');
        if ($studentId) {
            return $this->respond($this->model->where('student_id', $studentId)->findAll());
        }
        return $this->respond($this->model->findAll());
    }

    public function show($id = null)
    {
        $data = $this->model->find($id);
        if (!$data) {
            return $this->failNotFound('Allocation not found');
        }
        return $this->respond($data);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        // TODO: Check room capacity before allocating
        if (!$this->model->insert($data)) {
            return $this->fail($this->model->errors());
        }
        return $this->respondCreated($data, 'Allocation created');
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        if (!$this->model->find($id)) {
            return $this->failNotFound('Allocation not found');
        }
        if (!$this->model->update($id, $data)) {
            return $this->fail($this->model->errors());
        }
        return $this->respond($data, 200, 'Allocation updated');
    }

    public function delete($id = null)
    {
        if (!$this->model->find($id)) {
            return $this->failNotFound('Allocation not found');
        }
        if (!$this->model->delete($id)) {
            return $this->fail($this->model->errors());
        }
        return $this->respondDeleted(['id' => $id], 'Allocation deleted');
    }
}
