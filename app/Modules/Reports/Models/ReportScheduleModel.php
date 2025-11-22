<?php

declare(strict_types=1);

namespace Modules\Reports\Models;

use CodeIgniter\Model;

/**
 * Model for report schedules
 */
class ReportScheduleModel extends Model
{
    protected $table            = 'ci4_reports_schedules';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'report_id',
        'name',
        'frequency',
        'format',
        'recipients',
        'schedule_data',
        'last_run_at',
        'next_run_at',
        'is_active',
    ];

    protected $validationRules = [
        'report_id'  => 'required|integer',
        'name'       => 'required|max_length[255]',
        'frequency'  => 'required|in_list[daily,weekly,monthly,quarterly,yearly]',
        'format'     => 'required|in_list[pdf,excel,csv]',
        'recipients' => 'required',
        'is_active'  => 'in_list[0,1]',
    ];

    protected $validationMessages = [
        'report_id' => [
            'required' => 'Report ID is required',
        ],
        'name' => [
            'required' => 'Schedule name is required',
        ],
        'frequency' => [
            'required' => 'Frequency is required',
            'in_list'  => 'Invalid frequency',
        ],
        'format' => [
            'required' => 'Export format is required',
            'in_list'  => 'Invalid export format',
        ],
    ];

    protected $beforeInsert = ['decodeJsonFields'];
    protected $beforeUpdate = ['decodeJsonFields'];
    protected $afterFind    = ['encodeJsonFields'];

    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Decode JSON fields before insert/update
     */
    protected function decodeJsonFields(array $data): array
    {
        if (isset($data['data']['recipients']) && is_array($data['data']['recipients'])) {
            $data['data']['recipients'] = json_encode($data['data']['recipients']);
        }
        if (isset($data['data']['schedule_data']) && is_array($data['data']['schedule_data'])) {
            $data['data']['schedule_data'] = json_encode($data['data']['schedule_data']);
        }
        return $data;
    }

    /**
     * Encode JSON fields after find
     */
    protected function encodeJsonFields(array $data): array
    {
        if (isset($data['data'])) {
            if (is_array($data['data'])) {
                foreach ($data['data'] as &$row) {
                    if (isset($row['recipients']) && is_string($row['recipients'])) {
                        $row['recipients'] = json_decode($row['recipients'], true);
                    }
                    if (isset($row['schedule_data']) && is_string($row['schedule_data'])) {
                        $row['schedule_data'] = json_decode($row['schedule_data'], true);
                    }
                }
            } else {
                if (isset($data['data']['recipients']) && is_string($data['data']['recipients'])) {
                    $data['data']['recipients'] = json_decode($data['data']['recipients'], true);
                }
                if (isset($data['data']['schedule_data']) && is_string($data['data']['schedule_data'])) {
                    $data['data']['schedule_data'] = json_decode($data['data']['schedule_data'], true);
                }
            }
        }
        return $data;
    }

    /**
     * Get active schedules due for execution
     */
    public function getDueSchedules(): array
    {
        return $this->where('is_active', 1)
                    ->where('next_run_at <=', date('Y-m-d H:i:s'))
                    ->findAll();
    }

    /**
     * Get schedules by report ID
     */
    public function getByReportId(int $reportId): array
    {
        return $this->where('report_id', $reportId)
                    ->orderBy('is_active', 'DESC')
                    ->orderBy('frequency', 'ASC')
                    ->findAll();
    }

    /**
     * Update last run time and calculate next run time
     */
    public function updateRunTime(int $scheduleId, string $lastRunAt, string $nextRunAt): bool
    {
        return $this->update($scheduleId, [
            'last_run_at' => $lastRunAt,
            'next_run_at' => $nextRunAt,
        ]);
    }
}
