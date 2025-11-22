<?php

declare(strict_types=1);

namespace Modules\Reports\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Modules\Reports\Models\ReportModel;
use Modules\Reports\Services\ReportExecutorService;
use Modules\Reports\Domain\Report;
use Modules\Reports\Domain\ReportDefinition;

/**
 * Controller for Report CRUD operations
 */
class ReportController extends ResourceController
{
    protected $format = 'json';
    protected $modelName = ReportModel::class;
    protected ReportModel $reportModel;
    protected ReportExecutorService $executor;

    public function __construct()
    {
        $this->reportModel = new ReportModel();
        $this->executor = new ReportExecutorService();
    }

    /**
     * Get list of reports
     */
    public function index(): ResponseInterface
    {
        try {
            $tenantId = $this->request->getGet('tenant_id');
            
            if (!$tenantId) {
                return $this->failValidationErrors('Tenant ID is required');
            }

            $this->reportModel->setTenantId($tenantId);
            
            $page = (int) ($this->request->getGet('page') ?? 1);
            $perPage = (int) ($this->request->getGet('per_page') ?? 20);
            $type = $this->request->getGet('type');
            $ownerId = $this->request->getGet('owner_id');

            if ($type) {
                $reports = $this->reportModel->getByType($type, $perPage, ($page - 1) * $perPage);
            } elseif ($ownerId) {
                $reports = $this->reportModel->getByOwner($ownerId, $perPage, ($page - 1) * $perPage);
            } else {
                $reports = $this->reportModel->paginate($perPage, 'default', $page);
            }

            return $this->respond([
                'status'  => 'success',
                'data'    => $reports,
                'pagination' => [
                    'page'     => $page,
                    'per_page' => $perPage,
                ],
            ]);
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Get a single report
     */
    public function show($id = null): ResponseInterface
    {
        try {
            $tenantId = $this->request->getGet('tenant_id');
            
            if (!$tenantId) {
                return $this->failValidationErrors('Tenant ID is required');
            }

            $this->reportModel->setTenantId($tenantId);
            $report = $this->reportModel->find($id);

            if (!$report) {
                return $this->failNotFound('Report not found');
            }

            return $this->respond([
                'status' => 'success',
                'data'   => $report,
            ]);
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Create a new report
     */
    public function create(): ResponseInterface
    {
        try {
            $data = $this->request->getJSON(true);

            if (!isset($data['tenant_id'])) {
                return $this->failValidationErrors('Tenant ID is required');
            }

            $this->reportModel->setTenantId($data['tenant_id']);

            if (!$this->reportModel->insert($data)) {
                return $this->failValidationErrors($this->reportModel->errors());
            }

            $reportId = $this->reportModel->getInsertID();
            $report = $this->reportModel->find($reportId);

            return $this->respondCreated([
                'status'  => 'success',
                'message' => 'Report created successfully',
                'data'    => $report,
            ]);
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Update a report
     */
    public function update($id = null): ResponseInterface
    {
        try {
            $data = $this->request->getJSON(true);
            
            if (!isset($data['tenant_id'])) {
                return $this->failValidationErrors('Tenant ID is required');
            }

            $this->reportModel->setTenantId($data['tenant_id']);
            
            $existing = $this->reportModel->find($id);
            if (!$existing) {
                return $this->failNotFound('Report not found');
            }

            if (!$this->reportModel->update($id, $data)) {
                return $this->failValidationErrors($this->reportModel->errors());
            }

            // Invalidate cache
            $this->executor->invalidateCache((int) $id);

            $report = $this->reportModel->find($id);

            return $this->respond([
                'status'  => 'success',
                'message' => 'Report updated successfully',
                'data'    => $report,
            ]);
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Delete a report
     */
    public function delete($id = null): ResponseInterface
    {
        try {
            $tenantId = $this->request->getGet('tenant_id');
            
            if (!$tenantId) {
                return $this->failValidationErrors('Tenant ID is required');
            }

            $this->reportModel->setTenantId($tenantId);
            
            $existing = $this->reportModel->find($id);
            if (!$existing) {
                return $this->failNotFound('Report not found');
            }

            if (!$this->reportModel->delete($id)) {
                return $this->failServerError('Failed to delete report');
            }

            // Invalidate cache
            $this->executor->invalidateCache((int) $id);

            return $this->respondDeleted([
                'status'  => 'success',
                'message' => 'Report deleted successfully',
            ]);
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Execute a report and get results
     */
    public function execute($id = null): ResponseInterface
    {
        try {
            $tenantId = $this->request->getGet('tenant_id');
            
            if (!$tenantId) {
                return $this->failValidationErrors('Tenant ID is required');
            }

            $this->reportModel->setTenantId($tenantId);
            $report = $this->reportModel->find($id);

            if (!$report) {
                return $this->failNotFound('Report not found');
            }

            $config = $report['config_json'] ?? [];
            $definition = ReportDefinition::fromArray($config);
            
            $useCache = (bool) ($this->request->getGet('use_cache') ?? true);
            $result = $this->executor->execute($definition, (int) $id, $useCache);

            return $this->respond([
                'status' => 'success',
                'data'   => $result,
            ]);
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }
}
