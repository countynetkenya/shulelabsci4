<?php

namespace Modules\Hostel\Controllers;

use CodeIgniter\RESTful\ResourceController;
use Modules\Hostel\Models\HostelRoomModel;

class HostelRoomController extends ResourceController
{
    protected $modelName = HostelRoomModel::class;
    protected $format    = 'json';

    public function index()
    {
        $hostelId = $this->request->getGet('hostel_id');
        if ($hostelId) {
            return $this->respond($this->model->where('hostel_id', $hostelId)->findAll());
        }
        return $this->respond($this->model->findAll());
    }

    public function show($id = null)
    {
        $data = $this->model->find($id);
        if (!$data) {
            return $this->failNotFound('Room not found');
        }
        return $this->respond($data);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        if (!$this->model->insert($data)) {
            return $this->fail($this->model->errors());
        }
        return $this->respondCreated($data, 'Room created');
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        if (!$this->model->find($id)) {
            return $this->failNotFound('Room not found');
        }
        if (!$this->model->update($id, $data)) {
            return $this->fail($this->model->errors());
        }
        return $this->respond($data, 200, 'Room updated');
    }

    public function delete($id = null)
    {
        if (!$this->model->find($id)) {
            return $this->failNotFound('Room not found');
        }
        if (!$this->model->delete($id)) {
            return $this->fail($this->model->errors());
        }
        return $this->respondDeleted(['id' => $id], 'Room deleted');
    }
}
