<?php

declare(strict_types=1);

namespace Modules\Reports\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Modules\Reports\Services\ReportMetadataService;
use Modules\Reports\Services\ReportBuilderService;
use Modules\Reports\Models\ReportTemplateModel;

/**
 * Controller for Report Builder UI endpoints
 */
class BuilderController extends ResourceController
{
    protected $format = 'json';
    protected ReportMetadataService $metadata;
    protected ReportBuilderService $builder;
    protected ReportTemplateModel $templateModel;

    public function __construct()
    {
        $this->metadata = new ReportMetadataService();
        $this->builder = new ReportBuilderService();
        $this->templateModel = new ReportTemplateModel();
    }

    /**
     * Get metadata for report builder
     */
    public function metadata(): ResponseInterface
    {
        try {
            $metadata = $this->metadata->getBuilderMetadata();

            return $this->respond([
                'status' => 'success',
                'data'   => $metadata,
            ]);
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Get data sources
     */
    public function dataSources(): ResponseInterface
    {
        try {
            $sources = $this->metadata->getDataSources();

            return $this->respond([
                'status' => 'success',
                'data'   => $sources,
            ]);
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Get fields for a module
     */
    public function fields($module = null): ResponseInterface
    {
        try {
            if (!$module) {
                return $this->failValidationErrors('Module parameter is required');
            }

            $fields = $this->metadata->getFieldsForModule($module);

            return $this->respond([
                'status' => 'success',
                'data'   => $fields,
            ]);
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Get templates
     */
    public function templates(): ResponseInterface
    {
        try {
            $module = $this->request->getGet('module');
            $category = $this->request->getGet('category');

            if ($module) {
                $templates = $this->templateModel->getByModule($module);
            } elseif ($category) {
                $templates = $this->templateModel->getByCategory($category);
            } else {
                $templates = $this->templateModel->where('is_active', 1)->findAll();
            }

            return $this->respond([
                'status' => 'success',
                'data'   => $templates,
            ]);
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Validate report configuration
     */
    public function validate(): ResponseInterface
    {
        try {
            $config = $this->request->getJSON(true);

            if (empty($config)) {
                return $this->failValidationErrors('Report configuration is required');
            }

            $result = $this->builder->validateAndNormalize($config);

            if (!$result['valid']) {
                return $this->respond([
                    'status' => 'error',
                    'errors' => $result['errors'],
                ], 400);
            }

            return $this->respond([
                'status' => 'success',
                'data'   => $result['normalized'],
            ]);
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Preview report without saving
     */
    public function preview(): ResponseInterface
    {
        try {
            $config = $this->request->getJSON(true);

            if (empty($config)) {
                return $this->failValidationErrors('Report configuration is required');
            }

            $result = $this->builder->validateAndNormalize($config);

            if (!$result['valid']) {
                return $this->respond([
                    'status' => 'error',
                    'errors' => $result['errors'],
                ], 400);
            }

            $definition = $this->builder->buildDefinition($result['normalized']);
            $executor = new \Modules\Reports\Services\ReportExecutorService();
            
            // Execute without caching for preview
            $data = $executor->execute($definition, null, false);

            return $this->respond([
                'status' => 'success',
                'data'   => $data,
            ]);
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }
}
