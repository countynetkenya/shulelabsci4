<?php

namespace Modules\Integrations\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

/**
 * Integration logs controller.
 */
class LogController extends ResourceController
{
    protected $format = 'json';

    /**
     * List integration logs with filtering.
     */
    public function index(): ResponseInterface
    {
        // TODO: Implement log listing with pagination and filters
        // Query integration_logs table

        return $this->respond([
            'status' => 'success',
            'data'   => [
                'logs' => [],
                'total' => 0,
            ],
        ]);
    }

    /**
     * Show a specific log entry.
     */
    public function show(string $id): ResponseInterface
    {
        // TODO: Implement single log retrieval

        return $this->respond([
            'status' => 'success',
            'data'   => [
                'log' => null,
            ],
        ]);
    }
}
