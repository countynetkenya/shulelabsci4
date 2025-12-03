<?php

namespace Modules\POS\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class PosApiController extends ResourceController
{
    protected $format = 'json';

    public function registers()
    {
        return $this->respond([
            ['id' => 1, 'name' => 'Canteen Till 1', 'status' => 'open'],
            ['id' => 2, 'name' => 'Uniform Shop', 'status' => 'closed'],
        ]);
    }

    public function createTransaction()
    {
        return $this->respondCreated(['id' => 123, 'status' => 'completed']);
    }
}
