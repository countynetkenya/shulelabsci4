<?php

declare(strict_types=1);

namespace Modules\Reports\Models;

use CodeIgniter\Model;

/**
 * Model for report filters
 */
class ReportFilterModel extends Model
{
    protected $table            = 'ci4_reports_filters';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'report_id',
        'name',
        'filter_json',
        'is_default',
    ];

    protected $validationRules = [
        'report_id'   => 'required|integer',
        'name'        => 'required|max_length[255]',
        'filter_json' => 'required',
        'is_default'  => 'in_list[0,1]',
    ];

    protected $validationMessages = [
        'report_id' => [
            'required' => 'Report ID is required',
        ],
        'name' => [
            'required' => 'Filter name is required',
        ],
    ];

    protected $beforeInsert = ['decodeFilterJson'];
    protected $beforeUpdate = ['decodeFilterJson'];
    protected $afterFind    = ['encodeFilterJson'];

    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Decode filter JSON before insert/update
     */
    protected function decodeFilterJson(array $data): array
    {
        if (isset($data['data']['filter_json']) && is_array($data['data']['filter_json'])) {
            $data['data']['filter_json'] = json_encode($data['data']['filter_json']);
        }
        return $data;
    }

    /**
     * Encode filter JSON after find
     */
    protected function encodeFilterJson(array $data): array
    {
        if (isset($data['data'])) {
            if (is_array($data['data'])) {
                foreach ($data['data'] as &$row) {
                    if (isset($row['filter_json']) && is_string($row['filter_json'])) {
                        $row['filter_json'] = json_decode($row['filter_json'], true);
                    }
                }
            } elseif (isset($data['data']['filter_json']) && is_string($data['data']['filter_json'])) {
                $data['data']['filter_json'] = json_decode($data['data']['filter_json'], true);
            }
        }
        return $data;
    }

    /**
     * Get filters by report ID
     */
    public function getByReportId(int $reportId): array
    {
        return $this->where('report_id', $reportId)
                    ->orderBy('is_default', 'DESC')
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }

    /**
     * Get default filter for a report
     */
    public function getDefaultFilter(int $reportId): ?array
    {
        return $this->where('report_id', $reportId)
                    ->where('is_default', 1)
                    ->first();
    }
}
