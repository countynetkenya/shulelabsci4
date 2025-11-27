<?php

namespace Modules\Hostel\Controllers;

use CodeIgniter\RESTful\ResourceController;
use Modules\Hostel\Models\HostelModel;

class HostelController extends ResourceController
{
    protected $modelName = HostelModel::class;

    protected $format = 'json';

    /**
     * Return an array of resource objects, themselves in array format.
     *
     * @return mixed
     */
    public function index()
    {
        return $this->respond($this->model->findAll());
    }

    /**
     * Return the properties of a resource object.
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function show($id = null)
    {
        $data = $this->model->find($id);
        if (!$data) {
            return $this->failNotFound('Hostel not found');
        }
        return $this->respond($data);
    }

    /**
     * Create a new resource object, from "posted" parameters.
     *
     * @return mixed
     */
    public function create()
    {
        $data = $this->request->getJSON(true); // Get JSON data as array

        if (!$this->model->insert($data)) {
            return $this->fail($this->model->errors());
        }

        return $this->respondCreated($data, 'Hostel created');
    }

    /**
     * Add or update a model resource, from "posted" properties.
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        if (!$this->model->find($id)) {
            return $this->failNotFound('Hostel not found');
        }

        if (!$this->model->update($id, $data)) {
            return $this->fail($this->model->errors());
        }

        return $this->respond($data, 200, 'Hostel updated');
    }

    /**
     * Delete the designated resource object from the model.
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function delete($id = null)
    {
        if (!$this->model->find($id)) {
            return $this->failNotFound('Hostel not found');
        }

        if (!$this->model->delete($id)) {
            return $this->fail($this->model->errors());
        }

        return $this->respondDeleted(['id' => $id], 'Hostel deleted');
    }
}
