<?php

declare(strict_types=1);

namespace Modules\Reports\Models;

use CodeIgniter\Model;

/**
 * Model for cached report results
 */
class ReportResultModel extends Model
{
    protected $table            = 'ci4_reports_results';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'report_id',
        'filter_hash',
        'result_data',
        'row_count',
        'generated_at',
        'expires_at',
    ];

    protected $validationRules = [
        'report_id'   => 'required|integer',
        'filter_hash' => 'required|max_length[64]',
        'result_data' => 'required',
        'row_count'   => 'required|integer',
    ];

    protected $beforeInsert = ['decodeResultData'];
    protected $beforeUpdate = ['decodeResultData'];
    protected $afterFind    = ['encodeResultData'];

    /**
     * Decode result data before insert/update
     */
    protected function decodeResultData(array $data): array
    {
        if (isset($data['data']['result_data']) && is_array($data['data']['result_data'])) {
            $data['data']['result_data'] = json_encode($data['data']['result_data']);
        }
        return $data;
    }

    /**
     * Encode result data after find
     */
    protected function encodeResultData(array $data): array
    {
        if (isset($data['data'])) {
            if (is_array($data['data'])) {
                foreach ($data['data'] as &$row) {
                    if (isset($row['result_data']) && is_string($row['result_data'])) {
                        $row['result_data'] = json_decode($row['result_data'], true);
                    }
                }
            } elseif (isset($data['data']['result_data']) && is_string($data['data']['result_data'])) {
                $data['data']['result_data'] = json_decode($data['data']['result_data'], true);
            }
        }
        return $data;
    }

    /**
     * Get cached result by report ID and filter hash
     */
    public function getCachedResult(int $reportId, string $filterHash): ?array
    {
        $result = $this->where('report_id', $reportId)
                       ->where('filter_hash', $filterHash)
                       ->where('expires_at >', date('Y-m-d H:i:s'))
                       ->first();

        return $result ?: null;
    }

    /**
     * Clean expired results
     */
    public function cleanExpired(): int
    {
        return $this->where('expires_at <', date('Y-m-d H:i:s'))->delete();
    }

    /**
     * Clean results for a specific report
     */
    public function cleanForReport(int $reportId): int
    {
        return $this->where('report_id', $reportId)->delete();
    }
}
