<?php

namespace Modules\Foundation\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class HealthApiController extends ResourceController
{
    public function index()
    {
        return $this->respond(['status' => 'ok', 'timestamp' => time()]);
    }
}
