<?php

namespace Modules\Inventory\Controllers;

use CodeIgniter\RESTful\ResourceController;
use Modules\Inventory\Models\InventoryItemModel;

class InventoryItemController extends ResourceController
{
    protected $modelName = InventoryItemModel::class;

    protected $format = 'json';

    public function index()
    {
        return $this->respond($this->model->findAll());
    }

    public function show($id = null)
    {
        $data = $this->model->find($id);
        if (!$data) {
            return $this->failNotFound('Item not found');
        }
        return $this->respond($data);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        if (!$this->model->insert($data)) {
            return $this->fail($this->model->errors());
        }
        return $this->respondCreated($data, 'Item created');
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        if (!$this->model->find($id)) {
            return $this->failNotFound('Item not found');
        }
        if (!$this->model->update($id, $data)) {
            return $this->fail($this->model->errors());
        }
        return $this->respond($data, 200, 'Item updated');
    }

    public function delete($id = null)
    {
        if (!$this->model->find($id)) {
            return $this->failNotFound('Item not found');
        }
        if (!$this->model->delete($id)) {
            return $this->fail($this->model->errors());
        }
        return $this->respondDeleted(['id' => $id], 'Item deleted');
    }
}
