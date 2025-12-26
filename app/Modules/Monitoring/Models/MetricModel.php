<?php

namespace App\Modules\Monitoring\Models;

use CodeIgniter\Model;

/**
 * MetricModel - Stores application metrics and performance data.
 */
class MetricModel extends Model
{
    protected $table = 'metrics';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = false;

    protected $protectFields = true;

    protected $allowedFields = [
        'school_id', 'metric_name', 'metric_type', 'value', 'labels', 'recorded_at',
    ];

    protected $useTimestamps = false;

    protected $createdField = 'recorded_at';

    protected $updatedField = '';

    protected $casts = [
        'id' => 'int',
        'school_id' => '?int',
        'value' => 'float',
        'labels' => 'json-array',
    ];

    protected $validationRules = [
        'metric_name' => 'required|max_length[100]',
        'metric_type' => 'required|in_list[counter,gauge,histogram,summary]',
        'value' => 'required|decimal',
    ];

    protected $validationMessages = [
        'metric_name' => [
            'required' => 'Metric name is required',
        ],
        'metric_type' => [
            'in_list' => 'Invalid metric type',
        ],
    ];

    /**
     * Get metrics by school and filters.
     */
    public function getBySchool(int $schoolId, array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $builder = $this->where('school_id', $schoolId);

        if (!empty($filters['metric_name'])) {
            $builder = $builder->where('metric_name', $filters['metric_name']);
        }
        if (!empty($filters['metric_type'])) {
            $builder = $builder->where('metric_type', $filters['metric_type']);
        }
        if (!empty($filters['date_from'])) {
            $builder = $builder->where('recorded_at >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $builder = $builder->where('recorded_at <=', $filters['date_to']);
        }

        return $builder->orderBy('recorded_at', 'DESC')
            ->limit($limit, $offset)
            ->findAll();
    }

    /**
     * Get distinct metric names for a school.
     */
    public function getMetricNames(int $schoolId): array
    {
        $results = $this->select('DISTINCT metric_name as name', false)
            ->where('school_id', $schoolId)
            ->orderBy('metric_name', 'ASC')
            ->findAll();

        return array_column($results, 'name');
    }
}
