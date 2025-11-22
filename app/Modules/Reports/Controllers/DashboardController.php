<?php

declare(strict_types=1);

namespace Modules\Reports\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Modules\Reports\Services\DashboardApiService;

/**
 * Controller for Dashboard and Mobile API endpoints
 */
class DashboardController extends ResourceController
{
    protected $format = 'json';
    protected DashboardApiService $dashboardService;

    public function __construct()
    {
        $this->dashboardService = new DashboardApiService();
    }

    /**
     * Get dashboard data
     */
    public function index(): ResponseInterface
    {
        try {
            $tenantId = $this->request->getGet('tenant_id');
            
            if (!$tenantId) {
                return $this->failValidationErrors('Tenant ID is required');
            }

            $options = [
                'mobile'  => (bool) $this->request->getGet('mobile'),
                'widgets' => $this->request->getGet('widgets'),
            ];

            $data = $this->dashboardService->getDashboardData($tenantId, $options);

            return $this->respond([
                'status' => 'success',
                'data'   => $data,
            ]);
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Get widget data
     */
    public function widget($id = null): ResponseInterface
    {
        try {
            $tenantId = $this->request->getGet('tenant_id');
            
            if (!$tenantId) {
                return $this->failValidationErrors('Tenant ID is required');
            }

            if (!$id) {
                return $this->failValidationErrors('Report ID is required');
            }

            $filters = $this->request->getJSON(true) ?? [];
            $data = $this->dashboardService->getWidgetData((int) $id, $tenantId, $filters);

            return $this->respond([
                'status' => 'success',
                'data'   => $data,
            ]);
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Get summary statistics
     */
    public function stats(): ResponseInterface
    {
        try {
            $tenantId = $this->request->getGet('tenant_id');
            
            if (!$tenantId) {
                return $this->failValidationErrors('Tenant ID is required');
            }

            $stats = $this->dashboardService->getSummaryStats($tenantId);

            return $this->respond([
                'status' => 'success',
                'data'   => $stats,
            ]);
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }
}
