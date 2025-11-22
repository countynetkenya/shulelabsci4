<?php

declare(strict_types=1);

namespace Modules\Reports\Models;

use App\Models\TenantAwareModel;

/**
 * Model for managing reports
 */
class ReportModel extends TenantAwareModel
{
    protected $table            = 'ci4_reports_reports';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'name',
        'description',
        'type',
        'config_json',
        'owner_id',
        'tenant_id',
        'template_ref',
        'is_public',
        'is_active',
    ];

    protected $validationRules = [
        'name'        => 'required|max_length[255]',
        'type'        => 'required|max_length[50]|in_list[table,chart,summary,custom]',
        'config_json' => 'required',
        'owner_id'    => 'required|max_length[64]',
        'tenant_id'   => 'required|max_length[64]',
        'is_public'   => 'in_list[0,1]',
        'is_active'   => 'in_list[0,1]',
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Report name is required',
        ],
        'type' => [
            'required' => 'Report type is required',
            'in_list'  => 'Invalid report type',
        ],
        'config_json' => [
            'required' => 'Report configuration is required',
        ],
    ];

    protected $beforeInsert = ['decodeConfigJson'];
    protected $beforeUpdate = ['decodeConfigJson'];
    protected $afterFind    = ['encodeConfigJson'];

    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Override tenant column for reports module
     */
    protected string $tenantColumn = 'tenant_id';

    /**
     * Decode config JSON before insert/update
     */
    protected function decodeConfigJson(array $data): array
    {
        if (isset($data['data']['config_json']) && is_array($data['data']['config_json'])) {
            $data['data']['config_json'] = json_encode($data['data']['config_json']);
        }
        return $data;
    }

    /**
     * Encode config JSON after find
     */
    protected function encodeConfigJson(array $data): array
    {
        if (isset($data['data'])) {
            if (is_array($data['data'])) {
                foreach ($data['data'] as &$row) {
                    if (isset($row['config_json']) && is_string($row['config_json'])) {
                        $row['config_json'] = json_decode($row['config_json'], true);
                    }
                }
            } elseif (isset($data['data']['config_json']) && is_string($data['data']['config_json'])) {
                $data['data']['config_json'] = json_decode($data['data']['config_json'], true);
            }
        }
        return $data;
    }

    /**
     * Get reports by owner
     */
    public function getByOwner(string $ownerId, int $limit = 0, int $offset = 0): array
    {
        $this->applyTenantScope();
        return $this->where('owner_id', $ownerId)
                    ->where('is_active', 1)
                    ->orderBy('created_at', 'DESC')
                    ->findAll($limit, $offset);
    }

    /**
     * Get public reports
     */
    public function getPublicReports(int $limit = 0, int $offset = 0): array
    {
        $this->applyTenantScope();
        return $this->where('is_public', 1)
                    ->where('is_active', 1)
                    ->orderBy('created_at', 'DESC')
                    ->findAll($limit, $offset);
    }

    /**
     * Get reports by type
     */
    public function getByType(string $type, int $limit = 0, int $offset = 0): array
    {
        $this->applyTenantScope();
        return $this->where('type', $type)
                    ->where('is_active', 1)
                    ->orderBy('created_at', 'DESC')
                    ->findAll($limit, $offset);
    }
}
