<?php

namespace Modules\Finance\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class HealthController extends ResourceController
{
    protected $format = 'json';

    public function index(): ResponseInterface
    {
        return $this->respond([
            'status'    => 'ok',
            'module'    => 'finance',
            'timestamp' => time(),
        ]);
    }
}
