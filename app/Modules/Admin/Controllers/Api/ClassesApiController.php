<?php

namespace Modules\Admin\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class ClassesApiController extends ResourceController
{
    public function index()
    {
        return $this->respond(['data' => [['id' => 1, 'name' => 'Class 1A']]]);
    }
}
