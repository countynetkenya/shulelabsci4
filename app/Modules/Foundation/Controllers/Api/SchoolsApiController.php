<?php

namespace Modules\Foundation\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class SchoolsApiController extends ResourceController
{
    public function index()
    {
        return $this->respond(['data' => [['id' => 1, 'name' => 'School A'], ['id' => 2, 'name' => 'School B']]]);
    }
}
