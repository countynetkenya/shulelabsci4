<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Okr_progress_service
{
    /** @var CI_Controller */
    protected $CI;

    /** @var array<string, array> */
    protected $tableFieldsCache = [];

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('okr_objective_m');
        $this->CI->load->model('okr_key_result_m');
        $this->CI->load->model('okr_log_m');
    }

    /**
     * Recomputes all objectives for a given school.
     *
     * @param int $schoolID
     * @return array<int, array<string, mixed>>
     */
    public function recomputeAllForSchool($schoolID)
    {
        $results = [];
        $schoolID = (int) $schoolID;

        if (!$this->CI->db->table_exists('okr_objectives')) {
            return $results;
        }

        $objectives = $this->CI->okr_objective_m->get_order_by_okr_objective([
            'schoolID' => $schoolID,
        ]);

        foreach ($objectives as $objective) {
            $results[] = $this->recomputeObjective($objective);
        }

        return $results;
    }

    /**
     * Recompute a single objective's aggregate progress.
     *
     * @param object|int $objective
     * @return array<string, mixed>|null
     */
    public function recomputeObjective($objective)
    {
        if (!is_object($objective)) {
            $objectiveID = (int) $objective;
            if ($objectiveID <= 0) {
                return null;
            }
            $objective = $this->CI->okr_objective_m->get_single_okr_objective([
                'okrObjectiveID' => $objectiveID,
            ]);
            if (!$objective) {
                return null;
            }
        }

        $keyResults = $this->CI->okr_key_result_m->get_order_by_okr_key_result([
            'okrObjectiveID' => $objective->okrObjectiveID,
            'status'        => 'active',
        ]);

        $weightedTotal = 0.0;
        $totalWeight   = 0.0;
        $keyResultSummaries = [];

        foreach ($keyResults as $keyResult) {
            $summary = $this->recomputeKeyResult($keyResult);
            if ($summary) {
                $weight = isset($keyResult->weight) ? (float) $keyResult->weight : 1.0;
                if ($weight <= 0) {
                    $weight = 1.0;
                }

                $weightedTotal += $summary['progress'] * $weight;
                $totalWeight   += $weight;
                $keyResultSummaries[] = $summary;
            }
        }

        $progress = $totalWeight > 0 ? round($weightedTotal / $totalWeight, 2) : 0.0;
        $now = date('Y-m-d H:i:s');

        $changes = [];
        if (round((float) $objective->progress_cached, 2) !== $progress) {
            $changes['progress_cached'] = $progress;
        }
        $changes['updated_at'] = $now;

        if (!empty($changes)) {
            $this->CI->okr_objective_m->update_okr_objective($changes, $objective->okrObjectiveID);

            $this->recordLog(
                $objective->schoolID,
                $objective->okrObjectiveID,
                null,
                'auto_update',
                'Objective progress recalculated.',
                [
                    'objective_id'      => (int) $objective->okrObjectiveID,
                    'previous_progress' => (float) $objective->progress_cached,
                    'current_progress'  => $progress,
                    'key_results'       => array_map(function ($item) {
                        return [
                            'key_result_id' => $item['key_result']->okrKeyResultID,
                            'progress'      => $item['progress'],
                            'value'         => $item['value'],
                            'data_source'   => $item['key_result']->data_source,
                        ];
                    }, $keyResultSummaries),
                ],
                $progress
            );
        }

        return [
            'objective'   => $objective,
            'progress'    => $progress,
            'key_results' => $keyResultSummaries,
        ];
    }

    /**
     * Recompute a key result's progress.
     *
     * @param object|int $keyResult
     * @return array<string, mixed>|null
     */
    public function recomputeKeyResult($keyResult)
    {
        if (!is_object($keyResult)) {
            $keyResultID = (int) $keyResult;
            if ($keyResultID <= 0) {
                return null;
            }
            $keyResult = $this->CI->okr_key_result_m->get_single_okr_key_result([
                'okrKeyResultID' => $keyResultID,
            ]);
            if (!$keyResult) {
                return null;
            }
        }

        $source = isset($keyResult->data_source) ? strtolower($keyResult->data_source) : 'manual';
        $config = $this->decodeConfig($keyResult->data_config);
        $calculation = $this->calculateFromSource($source, $keyResult, $config);

        $value    = round($calculation['value'], 2);
        $progress = round($calculation['progress'], 2);
        $now      = date('Y-m-d H:i:s');

        $changes = [
            'last_computed_at' => $now,
        ];
        $changed = false;

        if (round((float) $keyResult->current_value, 2) !== $value) {
            $changes['current_value'] = $value;
            $changed = true;
        }

        if (round((float) $keyResult->progress_cached, 2) !== $progress) {
            $changes['progress_cached'] = $progress;
            $changed = true;
        }

        if ($changed) {
            $changes['updated_at'] = $now;
            $this->CI->okr_key_result_m->update_okr_key_result($changes, $keyResult->okrKeyResultID);

            $this->recordLog(
                $keyResult->schoolID,
                $keyResult->okrObjectiveID,
                $keyResult->okrKeyResultID,
                'auto_update',
                'Key result progress recalculated.',
                [
                    'key_result_id'     => (int) $keyResult->okrKeyResultID,
                    'previous_value'    => (float) $keyResult->current_value,
                    'current_value'     => $value,
                    'previous_progress' => (float) $keyResult->progress_cached,
                    'current_progress'  => $progress,
                    'data_source'       => $source,
                    'config'            => $config,
                    'meta'              => $calculation['meta'],
                ],
                $progress
            );
        }

        return [
            'key_result' => $keyResult,
            'value'      => $value,
            'progress'   => $progress,
            'meta'       => $calculation['meta'],
            'changed'    => $changed,
        ];
    }

    /**
     * Builds a dashboard style summary for a school.
     *
     * @param int   $schoolID
     * @param array $filters
     * @return array<string, mixed>
     */
    public function getSummary($schoolID, array $filters = [])
    {
        $summary = [
            'total_objectives'   => 0,
            'active_objectives'  => 0,
            'completed_objectives' => 0,
            'average_progress'   => 0.0,
            'owner_type_breakdown' => [],
            'total_key_results'  => 0,
            'stale_key_results'  => 0,
            'last_activity_at'   => null,
        ];

        if (!$this->CI->db->table_exists('okr_objectives')) {
            return $summary;
        }

        $schoolID = (int) $schoolID;

        $objectiveQuery = $this->CI->db->from('okr_objectives')
            ->where('schoolID', $schoolID);

        if (!empty($filters['ownerType'])) {
            $objectiveQuery->where('ownerType', $filters['ownerType']);
        }
        if (!empty($filters['ownerID'])) {
            $objectiveQuery->where('ownerID', (int) $filters['ownerID']);
        }
        if (!empty($filters['status'])) {
            $statuses = (array) $filters['status'];
            $objectiveQuery->where_in('status', $statuses);
        }

        $objectives = $objectiveQuery->get()->result();

        if (!empty($objectives)) {
            $summary['total_objectives'] = count($objectives);
            $progressAccumulator = 0.0;

            foreach ($objectives as $objective) {
                if ($objective->status === 'active') {
                    $summary['active_objectives']++;
                }
                if ((float) $objective->progress_cached >= 100.0) {
                    $summary['completed_objectives']++;
                }

                $progressAccumulator += (float) $objective->progress_cached;

                $ownerType = strtolower($objective->ownerType);
                if (!isset($summary['owner_type_breakdown'][$ownerType])) {
                    $summary['owner_type_breakdown'][$ownerType] = 0;
                }
                $summary['owner_type_breakdown'][$ownerType]++;

                $updatedAt = $objective->updated_at ?: $objective->created_at;
                if ($updatedAt && ($summary['last_activity_at'] === null || $updatedAt > $summary['last_activity_at'])) {
                    $summary['last_activity_at'] = $updatedAt;
                }
            }

            $summary['average_progress'] = round($progressAccumulator / max(1, $summary['total_objectives']), 2);
        }

        if ($this->CI->db->table_exists('okr_key_results')) {
            $this->CI->db->from('okr_key_results')->where('schoolID', $schoolID);
            $summary['total_key_results'] = (int) $this->CI->db->count_all_results();

            $staleThresholdHours = isset($filters['stale_threshold_hours']) ? (int) $filters['stale_threshold_hours'] : 24;
            $thresholdDate = date('Y-m-d H:i:s', strtotime(sprintf('-%d hours', max(1, $staleThresholdHours))));

            $this->CI->db->from('okr_key_results')
                ->where('schoolID', $schoolID)
                ->group_start()
                    ->where('last_computed_at <', $thresholdDate)
                    ->or_where('last_computed_at IS NULL', null, false)
                ->group_end();
            $summary['stale_key_results'] = (int) $this->CI->db->count_all_results();
        }

        if ($summary['last_activity_at'] === null && $this->CI->db->table_exists('okr_logs')) {
            $log = $this->CI->db->select('created_at')
                ->from('okr_logs')
                ->where('schoolID', $schoolID)
                ->order_by('created_at', 'DESC')
                ->limit(1)
                ->get()
                ->row();
            if ($log) {
                $summary['last_activity_at'] = $log->created_at;
            }
        }

        return $summary;
    }

    /**
     * Transforms an objective and its key results into an API friendly structure.
     *
     * @param object $objective
     * @param array<int, object> $keyResults
     * @return array<string, mixed>
     */
    public function formatObjective($objective, $keyResults)
    {
        $formattedResults = [];
        foreach ($keyResults as $keyResult) {
            $formattedResults[] = [
                'okrKeyResultID' => (int) $keyResult->okrKeyResultID,
                'okrObjectiveID' => (int) $keyResult->okrObjectiveID,
                'title'          => $keyResult->title,
                'description'    => $keyResult->description,
                'unit'           => $keyResult->unit,
                'target_value'   => (float) $keyResult->target_value,
                'current_value'  => (float) $keyResult->current_value,
                'weight'         => (float) $keyResult->weight,
                'data_source'    => $keyResult->data_source,
                'data_config'    => $this->decodeConfig($keyResult->data_config),
                'progress'       => (float) $keyResult->progress_cached,
                'last_computed_at' => $keyResult->last_computed_at,
                'status'         => $keyResult->status,
                'created_at'     => $keyResult->created_at,
                'updated_at'     => $keyResult->updated_at,
            ];
        }

        return [
            'okrObjectiveID' => (int) $objective->okrObjectiveID,
            'title'          => $objective->title,
            'description'    => $objective->description,
            'ownerType'      => $objective->ownerType,
            'ownerID'        => (int) $objective->ownerID,
            'status'         => $objective->status,
            'start_date'     => $objective->start_date,
            'end_date'       => $objective->end_date,
            'progress'       => (float) $objective->progress_cached,
            'created_at'     => $objective->created_at,
            'updated_at'     => $objective->updated_at,
            'key_results'    => $formattedResults,
        ];
    }

    /**
     * @param string $source
     * @param object $keyResult
     * @param array  $config
     * @return array{value: float, progress: float, meta: array<string, mixed>}
     */
    protected function calculateFromSource($source, $keyResult, array $config)
    {
        switch ($source) {
            case 'exams':
                return $this->calculateExamProgress($keyResult, $config);
            case 'attendance':
                return $this->calculateAttendanceProgress($keyResult, $config);
            case 'gamification':
                return $this->calculateGamificationProgress($keyResult, $config);
            case 'finance':
                return $this->calculateFinanceProgress($keyResult, $config);
            case 'manual':
            default:
                return $this->calculateManualProgress($keyResult, $config);
        }
    }

    /**
     * Manual progress uses an optional value from the configuration or keeps the stored value.
     */
    protected function calculateManualProgress($keyResult, array $config)
    {
        $value = isset($config['current_value']) ? (float) $config['current_value'] : (float) $keyResult->current_value;
        $progress = $this->progressAgainstTarget($value, (float) $keyResult->target_value);

        if (isset($config['progress'])) {
            $progress = (float) $config['progress'];
        }

        return [
            'value'    => $value,
            'progress' => $progress,
            'meta'     => ['mode' => 'manual'],
        ];
    }

    protected function calculateExamProgress($keyResult, array $config)
    {
        $this->CI->load->model('mark_m');

        $builder = $this->CI->db->select('AVG(mark.mark) as value, COUNT(mark.markID) as sample_size')
            ->from('mark')
            ->where('mark.schoolID', (int) $keyResult->schoolID);

        if (!empty($config['exam_ids'])) {
            $builder->where_in('mark.examID', array_map('intval', (array) $config['exam_ids']));
        }
        if (!empty($config['classes_ids'])) {
            $builder->where_in('mark.classesID', array_map('intval', (array) $config['classes_ids']));
        }
        if (!empty($config['student_ids'])) {
            $builder->where_in('mark.studentID', array_map('intval', (array) $config['student_ids']));
        }
        if (!empty($config['subject_ids'])) {
            $builder->where_in('mark.subjectID', array_map('intval', (array) $config['subject_ids']));
        }
        if (!empty($config['schoolyear_ids'])) {
            $builder->where_in('mark.schoolyearID', array_map('intval', (array) $config['schoolyear_ids']));
        }

        $row = $builder->get()->row();
        $value = $row && $row->value !== null ? (float) $row->value : 0.0;
        $progress = $this->progressAgainstTarget($value, (float) $keyResult->target_value);

        return [
            'value'    => $value,
            'progress' => $progress,
            'meta'     => [
                'sample_size' => $row ? (int) $row->sample_size : 0,
                'filters'     => $config,
            ],
        ];
    }

    protected function calculateAttendanceProgress($keyResult, array $config)
    {
        if (!$this->CI->db->table_exists('attendance')) {
            return [
                'value'    => 0.0,
                'progress' => 0.0,
                'meta'     => ['reason' => 'attendance_table_missing'],
            ];
        }

        $builder = $this->CI->db->from('attendance')
            ->where('schoolID', (int) $keyResult->schoolID);

        if (!empty($config['classes_ids'])) {
            $builder->where_in('classesID', array_map('intval', (array) $config['classes_ids']));
        }
        if (!empty($config['section_ids'])) {
            $builder->where_in('sectionID', array_map('intval', (array) $config['section_ids']));
        }
        if (!empty($config['student_ids'])) {
            $builder->where_in('studentID', array_map('intval', (array) $config['student_ids']));
        }
        if (!empty($config['schoolyear_ids'])) {
            $builder->where_in('schoolyearID', array_map('intval', (array) $config['schoolyear_ids']));
        }
        if (!empty($config['monthyear'])) {
            $builder->where('monthyear', $config['monthyear']);
        }

        $records = $builder->get()->result();

        $fields = $this->listFields('attendance');
        $dayFields = array_filter($fields, function ($field) {
            return (bool) preg_match('/^a\d+$/i', $field);
        });

        $total = 0;
        $present = 0;

        foreach ($records as $record) {
            foreach ($dayFields as $field) {
                $value = isset($record->$field) ? trim((string) $record->$field) : '';
                if ($value === '') {
                    continue;
                }
                $total++;
                $normalized = strtoupper($value);
                if (in_array($normalized, ['P', 'L', 'H'], true)) {
                    $present++;
                } elseif (is_numeric($value) && (float) $value > 0) {
                    $present++;
                }
            }
        }

        $attendancePercentage = $total > 0 ? round(($present / $total) * 100, 2) : 0.0;
        $target = (float) ($keyResult->target_value ?: 100);
        $progress = $this->progressAgainstTarget($attendancePercentage, $target);

        return [
            'value'    => $attendancePercentage,
            'progress' => $progress,
            'meta'     => [
                'total_days'    => $total,
                'present_days'  => $present,
                'filters'       => $config,
                'value_unit'    => 'percentage',
            ],
        ];
    }

    protected function calculateGamificationProgress($keyResult, array $config)
    {
        $table = isset($config['table']) ? $config['table'] : 'gamification_points';
        if (!$this->CI->db->table_exists($table)) {
            return [
                'value'    => 0.0,
                'progress' => 0.0,
                'meta'     => ['reason' => 'gamification_table_missing', 'table' => $table],
            ];
        }

        $column = isset($config['column']) ? $config['column'] : 'points';
        $aggregation = isset($config['aggregation']) ? strtolower($config['aggregation']) : 'sum';
        $filters = isset($config['filters']) && is_array($config['filters']) ? $config['filters'] : [];

        $builder = $this->CI->db->from($table);
        if ($this->tableHasColumn($table, 'schoolID')) {
            $builder->where('schoolID', (int) $keyResult->schoolID);
        }

        foreach ($filters as $field => $value) {
            if (!$this->tableHasColumn($table, $field)) {
                continue;
            }
            if (is_array($value)) {
                $builder->where_in($field, $value);
            } else {
                $builder->where($field, $value);
            }
        }

        switch ($aggregation) {
            case 'avg':
            case 'average':
                $builder->select_avg($column, 'value');
                break;
            case 'max':
                $builder->select_max($column, 'value');
                break;
            case 'min':
                $builder->select_min($column, 'value');
                break;
            case 'count':
                $builder->select('COUNT(*) as value', false);
                break;
            case 'sum':
            default:
                $builder->select_sum($column, 'value');
                break;
        }

        $row = $builder->get()->row();
        $value = $row && isset($row->value) ? (float) $row->value : 0.0;
        $progress = $this->progressAgainstTarget($value, (float) $keyResult->target_value);

        return [
            'value'    => $value,
            'progress' => $progress,
            'meta'     => [
                'aggregation' => $aggregation,
                'filters'     => $filters,
                'table'       => $table,
            ],
        ];
    }

    protected function calculateFinanceProgress($keyResult, array $config)
    {
        if (!$this->CI->db->table_exists('payment')) {
            return [
                'value'    => 0.0,
                'progress' => 0.0,
                'meta'     => ['reason' => 'payment_table_missing'],
            ];
        }

        $builder = $this->CI->db->select_sum('paymentamount', 'value')
            ->from('payment')
            ->where('schoolID', (int) $keyResult->schoolID);

        if (!empty($config['student_ids'])) {
            $builder->where_in('studentID', array_map('intval', (array) $config['student_ids']));
        }
        if (!empty($config['invoice_ids']) && $this->tableHasColumn('payment', 'invoiceID')) {
            $builder->where_in('invoiceID', array_map('intval', (array) $config['invoice_ids']));
        }
        if (!empty($config['from_date']) && $this->tableHasColumn('payment', 'paymentdate')) {
            $builder->where('paymentdate >=', $config['from_date']);
        }
        if (!empty($config['to_date']) && $this->tableHasColumn('payment', 'paymentdate')) {
            $builder->where('paymentdate <=', $config['to_date']);
        }
        if (!empty($config['payment_types']) && $this->tableHasColumn('payment', 'paymentmethodID')) {
            $builder->where_in('paymentmethodID', (array) $config['payment_types']);
        }

        $row = $builder->get()->row();
        $value = $row && $row->value !== null ? (float) $row->value : 0.0;
        $progress = $this->progressAgainstTarget($value, (float) $keyResult->target_value);

        return [
            'value'    => $value,
            'progress' => $progress,
            'meta'     => [
                'filters' => $config,
            ],
        ];
    }

    /**
     * @param mixed $raw
     * @return array
     */
    protected function decodeConfig($raw)
    {
        if (is_array($raw)) {
            return $raw;
        }
        if (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }
        return [];
    }

    /**
     * Calculates the progress percentage against a target value.
     */
    protected function progressAgainstTarget($value, $target)
    {
        $value = (float) $value;
        $target = (float) $target;

        if ($target <= 0) {
            return $value > 0 ? 100.0 : 0.0;
        }

        $ratio = ($value / $target) * 100;
        return round(max(0.0, min(200.0, $ratio)), 2);
    }

    /**
     * @param string $table
     * @return array<int, string>
     */
    protected function listFields($table)
    {
        if (!isset($this->tableFieldsCache[$table])) {
            $this->tableFieldsCache[$table] = $this->CI->db->list_fields($table);
        }
        return $this->tableFieldsCache[$table];
    }

    protected function tableHasColumn($table, $column)
    {
        $fields = $this->listFields($table);
        return in_array($column, $fields, true);
    }

    protected function recordLog($schoolID, $objectiveID, $keyResultID, $entryType, $message, array $payload = [], $progress = null)
    {
        if (!$this->CI->db->table_exists('okr_logs')) {
            return;
        }

        list($userID, $usertypeID) = $this->resolveSessionUser();

        $log = [
            'schoolID'             => (int) $schoolID,
            'okrObjectiveID'       => $objectiveID ? (int) $objectiveID : null,
            'okrKeyResultID'       => $keyResultID ? (int) $keyResultID : null,
            'entry_type'           => $entryType,
            'message'              => $message,
            'progress'             => $progress !== null ? round($progress, 2) : null,
            'payload'              => !empty($payload) ? json_encode($payload) : null,
            'created_by_userID'    => $userID,
            'created_by_usertypeID'=> $usertypeID,
            'created_at'           => date('Y-m-d H:i:s'),
        ];

        $this->CI->okr_log_m->insert_okr_log($log);
    }

    /**
     * @return array{0:?int,1:?int}
     */
    protected function resolveSessionUser()
    {
        if (property_exists($this->CI, 'session') && $this->CI->session) {
            $userID = $this->CI->session->userdata('loginuserID');
            $usertypeID = $this->CI->session->userdata('usertypeID');
            return [
                $userID !== null ? (int) $userID : null,
                $usertypeID !== null ? (int) $usertypeID : null,
            ];
        }

        return [null, null];
    }
}
