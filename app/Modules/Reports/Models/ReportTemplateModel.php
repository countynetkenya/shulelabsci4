<?php

declare(strict_types=1);

namespace Modules\Reports\Models;

use CodeIgniter\Model;

/**
 * Model for report templates
 */
class ReportTemplateModel extends Model
{
    protected $table            = 'ci4_reports_templates';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'name',
        'description',
        'category',
        'module',
        'config_json',
        'is_system',
        'is_active',
    ];

    protected $validationRules = [
        'name'        => 'required|max_length[255]',
        'category'    => 'required|max_length[100]',
        'module'      => 'required|max_length[50]',
        'config_json' => 'required',
        'is_system'   => 'in_list[0,1]',
        'is_active'   => 'in_list[0,1]',
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Template name is required',
        ],
        'category' => [
            'required' => 'Template category is required',
        ],
        'module' => [
            'required' => 'Template module is required',
        ],
    ];

    protected $beforeInsert = ['decodeConfigJson'];
    protected $beforeUpdate = ['decodeConfigJson'];
    protected $afterFind    = ['encodeConfigJson'];

    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

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
     * Get templates by module
     */
    public function getByModule(string $module): array
    {
        return $this->where('module', $module)
                    ->where('is_active', 1)
                    ->orderBy('category', 'ASC')
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }

    /**
     * Get templates by category
     */
    public function getByCategory(string $category): array
    {
        return $this->where('category', $category)
                    ->where('is_active', 1)
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }

    /**
     * Get system templates
     */
    public function getSystemTemplates(): array
    {
        return $this->where('is_system', 1)
                    ->where('is_active', 1)
                    ->orderBy('module', 'ASC')
                    ->orderBy('category', 'ASC')
                    ->findAll();
    }
}
