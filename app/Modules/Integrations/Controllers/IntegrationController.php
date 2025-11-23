<?php

namespace Modules\Integrations\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Modules\Integrations\Services\IntegrationService;

/**
 * Integration management controller.
 */
class IntegrationController extends ResourceController
{
    protected $format = 'json';

    protected IntegrationService $integrationService;

    public function __construct()
    {
        $this->integrationService = service('integrations');
    }

    /**
     * List all registered integrations.
     */
    public function index(): ResponseInterface
    {
        $adapters = $this->integrationService->getRegisteredAdapters();

        return $this->respond([
            'status'  => 'success',
            'data'    => ['adapters' => $adapters],
            'message' => 'Registered integrations retrieved successfully',
        ]);
    }

    /**
     * Overall health check.
     */
    public function health(): ResponseInterface
    {
        return $this->respond([
            'status'  => 'ok',
            'message' => 'Integrations module is operational',
        ]);
    }

    /**
     * Check health of a specific adapter.
     */
    public function checkAdapter(string $adapterName): ResponseInterface
    {
        try {
            $health = $this->integrationService->checkHealth($adapterName);

            return $this->respond([
                'status' => 'success',
                'data'   => $health,
            ]);
        } catch (\Throwable $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Get status of a specific integration.
     */
    public function status(string $adapterName): ResponseInterface
    {
        try {
            $hasAdapter = $this->integrationService->hasAdapter($adapterName);

            return $this->respond([
                'status' => 'success',
                'data'   => [
                    'adapter'      => $adapterName,
                    'is_registered' => $hasAdapter,
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->failServerError($e->getMessage());
        }
    }
}
