<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Quickbooksexportservice
{
    /** @var CI_Controller */
    protected $CI;

    protected $scope = 'quickbooks_export';

    protected $engineLabels = [
        'dry-run'        => 'Dry-run preview',
        'schedule-term'  => 'Scheduled export by term',
        'schedule-month' => 'Scheduled export by month',
        'schedule-day'   => 'Scheduled export by day',
        'reconciliation' => 'Reconciliation window',
    ];

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('idempotency_key_m');
        $this->CI->load->model('quickbookslog_m');
    }

    public function startExport($idempotencyKey, array $payload, $schoolID)
    {
        $payload = $this->preparePayload($payload);
        $engineResolution = $this->resolveEngine($payload);
        if (isset($engineResolution['error'])) {
            $this->logError('EXPORT_ENGINE_INVALID', $engineResolution['error']['message'], $schoolID);

            return [
                'state' => 'error',
                'replayed' => false,
                'error' => $engineResolution['error'],
            ];
        }

        $normalizedPayload = $engineResolution['normalized_payload'];
        $requestPayload = json_encode($this->normaliseArray($normalizedPayload));
        $requestHash = hash('sha256', $requestPayload);

        $idempotencyKey = trim($idempotencyKey);
        if ($idempotencyKey === '') {
            $idempotencyKey = $this->generateKey($normalizedPayload, $schoolID);
        }

        $existing = $this->CI->idempotency_key_m->find_by_key_scope($idempotencyKey, $this->scope);
        if ($existing) {
            if (!empty($existing->request_hash) && $existing->request_hash !== $requestHash) {
                $error = [
                    'code' => 'IDEMPOTENCY_HASH_MISMATCH',
                    'message' => 'Payload hash mismatch for provided idempotency key.',
                ];

                $this->logError('EXPORT_ENGINE_CONFLICT', $error['message'], $schoolID, [
                    'idempotencyKeyID' => $existing->idempotencyKeyID,
                    'idempotency_key' => $idempotencyKey,
                ]);

                return [
                    'idempotencyKeyID' => $existing->idempotencyKeyID,
                    'state' => 'conflict',
                    'replayed' => true,
                    'error' => $error,
                ];
            }

            $response = $existing->response_body ? json_decode($existing->response_body, true) : [];

            return [
                'idempotencyKeyID' => $existing->idempotencyKeyID,
                'state' => $existing->status,
                'replayed' => true,
                'response' => $response,
            ];
        }

        $recordID = $this->CI->idempotency_key_m->create_record([
            'idempotency_key' => $idempotencyKey,
            'scope' => $this->scope,
            'request_hash' => $requestHash,
            'payload' => $requestPayload,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $summary = $this->buildSummary($engineResolution, $schoolID, $idempotencyKey, $requestHash);

        $this->CI->quickbookslog_m->insert_quickbookslog([
            'ip' => $this->CI->input->ip_address(),
            'request' => 'EXPORT_ENGINE',
            'status' => 'QUEUED',
            'message' => json_encode($summary),
            'schoolID' => $schoolID,
        ]);

        $responseBody = json_encode($summary);
        $this->CI->idempotency_key_m->update_record($recordID, [
            'status' => 'complete',
            'response_code' => 200,
            'response_body' => $responseBody,
            'response_hash' => hash('sha256', $responseBody),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return [
            'idempotencyKeyID' => $recordID,
            'state' => 'complete',
            'replayed' => false,
            'response' => $summary,
        ];
    }

    protected function generateKey(array $payload, $schoolID)
    {
        $seed = $schoolID . ':' . json_encode($payload) . ':' . microtime(true);
        return hash('sha256', $seed);
    }

    protected function preparePayload(array $payload)
    {
        $sanitised = [];
        foreach ($payload as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
            }

            if (is_array($value)) {
                $value = $this->preparePayload($value);
                if ($value === []) {
                    continue;
                }
            }

            if ($value === '' || $value === null) {
                continue;
            }

            $sanitised[$key] = $value;
        }

        return $sanitised;
    }

    protected function resolveEngine(array $payload)
    {
        $engine = isset($payload['engine']) ? strtolower($payload['engine']) : 'dry-run';
        if ($engine === 'schedule' && isset($payload['schedule_scope'])) {
            $engine = 'schedule-' . strtolower($payload['schedule_scope']);
        }

        if (!array_key_exists($engine, $this->engineLabels)) {
            return [
                'error' => [
                    'code' => 'INVALID_ENGINE',
                    'message' => 'Unsupported export engine requested.',
                ],
            ];
        }

        $filters = $payload;
        unset($filters['engine'], $filters['schedule_scope']);

        $scheduleScope = null;
        if (strpos($engine, 'schedule-') === 0) {
            $scheduleScope = substr($engine, strlen('schedule-'));
        }

        $validationError = $this->validateEngineFilters($engine, $filters);
        if ($validationError) {
            return ['error' => $validationError];
        }

        $normalized = [
            'engine' => $engine,
        ];

        if ($scheduleScope) {
            $normalized['schedule_scope'] = $scheduleScope;
        }

        if (!empty($filters)) {
            $normalized['filters'] = $filters;
        }

        if (isset($payload['notes'])) {
            $normalized['notes'] = $payload['notes'];
        }

        return [
            'engine' => $engine,
            'schedule_scope' => $scheduleScope,
            'filters' => $filters,
            'normalized_payload' => $normalized,
        ];
    }

    protected function validateEngineFilters($engine, array &$filters)
    {
        if (isset($filters['notes']) && is_string($filters['notes'])) {
            $filters['notes'] = trim($filters['notes']);
            if ($filters['notes'] === '') {
                unset($filters['notes']);
            }
        }

        switch ($engine) {
            case 'schedule-term':
                if (empty($filters['term'])) {
                    return [
                        'code' => 'INVALID_FILTERS',
                        'message' => 'Term must be provided for term-based schedules.',
                    ];
                }
                break;
            case 'schedule-month':
                if (empty($filters['month']) || !$this->isValidMonth($filters['month'])) {
                    return [
                        'code' => 'INVALID_FILTERS',
                        'message' => 'Month must be provided in YYYY-MM format for month schedules.',
                    ];
                }
                break;
            case 'schedule-day':
                if (empty($filters['day']) || !$this->isValidDate($filters['day'])) {
                    return [
                        'code' => 'INVALID_FILTERS',
                        'message' => 'Day must be provided in YYYY-MM-DD format for day schedules.',
                    ];
                }
                break;
            case 'reconciliation':
                $start = isset($filters['start_date']) ? $filters['start_date'] : null;
                $end = isset($filters['end_date']) ? $filters['end_date'] : null;
                if (!$start && !$end) {
                    return [
                        'code' => 'INVALID_FILTERS',
                        'message' => 'A reconciliation window requires a start and/or end date.',
                    ];
                }
                if ($start && !$this->isValidDate($start)) {
                    return [
                        'code' => 'INVALID_FILTERS',
                        'message' => 'Start date must be in YYYY-MM-DD format.',
                    ];
                }
                if ($end && !$this->isValidDate($end)) {
                    return [
                        'code' => 'INVALID_FILTERS',
                        'message' => 'End date must be in YYYY-MM-DD format.',
                    ];
                }
                break;
        }

        return null;
    }

    protected function buildSummary(array $resolution, $schoolID, $idempotencyKey, $requestHash)
    {
        $engine = $resolution['engine'];
        $filters = isset($resolution['filters']) ? $resolution['filters'] : [];
        $backfill = $this->buildBackfillDescriptor($engine, $filters);

        return [
            'queued_at' => date(DATE_ISO8601),
            'school_id' => $schoolID,
            'engine' => $engine,
            'engine_label' => $this->engineLabels[$engine],
            'idempotency_key' => $idempotencyKey,
            'request_hash' => $requestHash,
            'filters' => $filters,
            'backfill' => $backfill,
            'acceptance' => [
                'reruns' => 'safe',
                'reason' => 'Responses are cached using strict payload hashing.',
            ],
            'message' => $this->buildEngineMessage($engine, $backfill),
        ];
    }

    protected function buildBackfillDescriptor($engine, array $filters)
    {
        switch ($engine) {
            case 'dry-run':
                return [
                    'mode' => 'preview',
                    'description' => 'No records are posted to QuickBooks during a dry run.',
                ];
            case 'schedule-term':
                return [
                    'mode' => 'schedule',
                    'scope' => 'term',
                    'value' => isset($filters['term']) ? $filters['term'] : null,
                ];
            case 'schedule-month':
                return [
                    'mode' => 'schedule',
                    'scope' => 'month',
                    'value' => isset($filters['month']) ? $filters['month'] : null,
                ];
            case 'schedule-day':
                return [
                    'mode' => 'schedule',
                    'scope' => 'day',
                    'value' => isset($filters['day']) ? $filters['day'] : null,
                ];
            case 'reconciliation':
                return [
                    'mode' => 'reconciliation',
                    'window' => [
                        'start' => isset($filters['start_date']) ? $filters['start_date'] : null,
                        'end' => isset($filters['end_date']) ? $filters['end_date'] : null,
                    ],
                ];
        }

        return [];
    }

    protected function buildEngineMessage($engine, array $backfill)
    {
        switch ($engine) {
            case 'dry-run':
                return 'Dry-run export simulated; no mutations were sent to QuickBooks.';
            case 'schedule-term':
                return 'Scheduled QuickBooks export queued for the selected term.';
            case 'schedule-month':
                return 'Scheduled QuickBooks export queued for the selected month.';
            case 'schedule-day':
                return 'Scheduled QuickBooks export queued for the selected day.';
            case 'reconciliation':
                return 'Reconciliation export queued for the requested window.';
        }

        return 'QuickBooks export queued.';
    }

    protected function logError($request, $message, $schoolID, array $context = [])
    {
        $payload = [
            'message' => $message,
        ];

        if (!empty($context)) {
            $payload['context'] = $context;
        }

        $this->CI->quickbookslog_m->insert_quickbookslog([
            'ip' => $this->CI->input->ip_address(),
            'request' => $request,
            'status' => 'ERROR',
            'message' => json_encode($payload),
            'schoolID' => $schoolID,
        ]);
    }

    protected function normaliseArray($value)
    {
        if (is_array($value)) {
            if ($this->isAssoc($value)) {
                ksort($value);
            }

            foreach ($value as $key => $item) {
                $value[$key] = $this->normaliseArray($item);
            }
        }

        return $value;
    }

    protected function isAssoc(array $array)
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }

    protected function isValidMonth($value)
    {
        return (bool) preg_match('/^\d{4}-\d{2}$/', $value);
    }

    protected function isValidDate($value)
    {
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $value);
    }
}
