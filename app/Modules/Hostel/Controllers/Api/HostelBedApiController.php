<?php

namespace Modules\Hostel\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\Hostel\Models\HostelBedModel;

class HostelBedApiController extends ResourceController
{
    protected $modelName = HostelBedModel::class;

    protected $format = 'json';

    public function index()
    {
        $roomId = $this->request->getGet('room_id');
        if ($roomId) {
            return $this->respond($this->model->where('room_id', $roomId)->findAll());
        }
        return $this->respond($this->model->findAll());
    }

    public function show($id = null)
    {
        $data = $this->model->find($id);
        if (!$data) {
            return $this->failNotFound('Bed not found');
        }
        return $this->respond($data);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        if (!$this->model->insert($data)) {
            return $this->fail($this->model->errors());
        }
        return $this->respondCreated($data, 'Bed created');
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        if (!$this->model->find($id)) {
            return $this->failNotFound('Bed not found');
        }
        if (!$this->model->update($id, $data)) {
            return $this->fail($this->model->errors());
        }
        return $this->respond($data, 200, 'Bed updated');
    }

    public function delete($id = null)
    {
        if (!$this->model->find($id)) {
            return $this->failNotFound('Bed not found');
        }
        if (!$this->model->delete($id)) {
            return $this->fail($this->model->errors());
        }
        return $this->respondDeleted(['id' => $id], 'Bed deleted');
    }
}
