<?php

namespace Modules\Transport\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class TransportApiController extends ResourceController
{
    protected $format = 'json';

    public function routes()
    {
        return $this->respond([
            ['id' => 1, 'name' => 'Route A', 'driver' => 'John Doe'],
            ['id' => 2, 'name' => 'Route B', 'driver' => 'Jane Smith'],
        ]);
    }

    public function vehicles()
    {
        return $this->respond([
            ['id' => 1, 'plate' => 'KAA 123A', 'capacity' => 30],
            ['id' => 2, 'plate' => 'KBB 456B', 'capacity' => 14],
        ]);
    }

    public function createRoute()
    {
        return $this->respondCreated(['id' => 123, 'status' => 'created']);
    }
}
