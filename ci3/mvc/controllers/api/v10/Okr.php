<?php
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') OR exit('No direct script access allowed');

class Okr extends Api_Controller
{
    /** @var array<string> */
    protected $ownerTypes = ['admin', 'teacher', 'student', 'department', 'custom'];

    /** @var array<string> */
    protected $dataSources = ['manual', 'exams', 'attendance', 'gamification', 'finance'];

    public function __construct()
    {
        parent::__construct();
        require_feature_flag('OKR_V1');

        $this->load->model('okr_objective_m');
        $this->load->model('okr_key_result_m');
        $this->load->model('okr_log_m');
        $this->load->library('Okr_progress_service');
        $this->lang->load('okr', $this->session->userdata('lang'));
    }

    public function index_get()
    {
        $schoolID = (int) $this->session->userdata('schoolID');
        $ownerType = strtolower($this->input->get('ownerType'));
        $status = $this->input->get('status');

        $filters = ['schoolID' => $schoolID];
        if ($ownerType && in_array($ownerType, $this->ownerTypes, true)) {
            $filters['ownerType'] = $ownerType;
        }

        $ownerID = $this->input->get('ownerID');
        if ($ownerID !== null && $ownerID !== '') {
            $filters['ownerID'] = (int) $ownerID;
        }

        $objectives = $this->okr_objective_m->get_order_by_okr_objective($filters);

        $payload = [];
        foreach ($objectives as $objective) {
            if ($status && $objective->status !== $status) {
                continue;
            }
            $keyResults = $this->okr_key_result_m->get_order_by_okr_key_result([
                'okrObjectiveID' => $objective->okrObjectiveID,
            ]);
            $payload[] = $this->okr_progress_service->formatObjective($objective, $keyResults);
        }

        $this->response([
            'status'  => true,
            'message' => 'OK',
            'data'    => $payload,
        ], REST_Controller::HTTP_OK);
    }

    public function index_post()
    {
        $schoolID = (int) $this->session->userdata('schoolID');
        $payload = $this->payload();

        if (!$payload) {
            return $this->respondError($this->lang->line('okr_error_invalid_payload'));
        }

        $normalized = $this->normalizeObjectivePayload($payload, $schoolID);
        if ($normalized['errors']) {
            return $this->respondError($normalized['errors'], REST_Controller::HTTP_BAD_REQUEST);
        }

        $now = date('Y-m-d H:i:s');
        $objectiveData = $normalized['objective'];
        $objectiveData['created_at'] = $now;
        $objectiveData['updated_at'] = $now;

        $this->db->trans_begin();
        try {
            $objectiveID = $this->okr_objective_m->insert_okr_objective($objectiveData);
            $keyResultData = $this->buildKeyResultInsertPayloads($normalized['key_results'], $objectiveID, $schoolID, $now);
            if (!empty($keyResultData)) {
                $this->okr_key_result_m->insert_batch_okr_key_result($keyResultData);
            }
        } catch (\Throwable $exception) {
            $this->db->trans_rollback();
            log_message('error', 'Failed to create OKR: ' . $exception->getMessage());
            return $this->respondError('Unable to create objective at this time.', REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->db->trans_commit();

        $this->okr_progress_service->recomputeObjective($objectiveID);
        $objective = $this->okr_objective_m->get_single_okr_objective(['okrObjectiveID' => $objectiveID]);
        $keyResults = $this->okr_key_result_m->get_order_by_okr_key_result(['okrObjectiveID' => $objectiveID]);

        $this->response([
            'status'  => true,
            'message' => $this->lang->line('okr_success_create'),
            'data'    => $this->okr_progress_service->formatObjective($objective, $keyResults),
        ], REST_Controller::HTTP_CREATED);
    }

    public function index_put($id)
    {
        $schoolID = (int) $this->session->userdata('schoolID');
        $objectiveID = (int) $id;
        $objective = $this->okr_objective_m->get_single_okr_objective([
            'okrObjectiveID' => $objectiveID,
            'schoolID'       => $schoolID,
        ]);

        if (!$objective) {
            return $this->respondError($this->lang->line('okr_error_not_found'), REST_Controller::HTTP_NOT_FOUND);
        }

        $payload = $this->payload();
        if (!$payload) {
            return $this->respondError($this->lang->line('okr_error_invalid_payload'));
        }

        $normalized = $this->normalizeObjectivePayload($payload, $schoolID, $objective);
        if ($normalized['errors']) {
            return $this->respondError($normalized['errors'], REST_Controller::HTTP_BAD_REQUEST);
        }

        $now = date('Y-m-d H:i:s');
        $objectiveData = $normalized['objective'];
        $objectiveData['updated_at'] = $now;

        $this->db->trans_begin();
        try {
            if (!empty($objectiveData)) {
                $this->okr_objective_m->update_okr_objective($objectiveData, $objectiveID);
            }

            if (isset($normalized['key_results'])) {
                $this->persistKeyResults($objectiveID, $schoolID, $normalized['key_results'], $now);
            }
        } catch (\Throwable $exception) {
            $this->db->trans_rollback();
            log_message('error', 'Failed to update OKR: ' . $exception->getMessage());
            return $this->respondError('Unable to update objective at this time.', REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->db->trans_commit();

        $this->okr_progress_service->recomputeObjective($objectiveID);
        $objective = $this->okr_objective_m->get_single_okr_objective(['okrObjectiveID' => $objectiveID]);
        $keyResults = $this->okr_key_result_m->get_order_by_okr_key_result(['okrObjectiveID' => $objectiveID]);

        $this->response([
            'status'  => true,
            'message' => $this->lang->line('okr_success_update'),
            'data'    => $this->okr_progress_service->formatObjective($objective, $keyResults),
        ], REST_Controller::HTTP_OK);
    }

    public function progress_post()
    {
        $schoolID = (int) $this->session->userdata('schoolID');
        $payload = $this->payload();

        if (!$payload) {
            return $this->respondError($this->lang->line('okr_error_invalid_payload'));
        }

        $response = [];
        if (!empty($payload['objective_id'])) {
            $objectiveID = (int) $payload['objective_id'];
            $objective = $this->okr_objective_m->get_single_okr_objective([
                'okrObjectiveID' => $objectiveID,
                'schoolID'       => $schoolID,
            ]);
            if (!$objective) {
                return $this->respondError($this->lang->line('okr_error_not_found'), REST_Controller::HTTP_NOT_FOUND);
            }
            $response = $this->okr_progress_service->recomputeObjective($objective);
        } elseif (!empty($payload['key_result_id'])) {
            $keyResultID = (int) $payload['key_result_id'];
            $keyResult = $this->okr_key_result_m->get_single_okr_key_result([
                'okrKeyResultID' => $keyResultID,
                'schoolID'       => $schoolID,
            ]);
            if (!$keyResult) {
                return $this->respondError($this->lang->line('okr_error_not_found'), REST_Controller::HTTP_NOT_FOUND);
            }
            $response = $this->okr_progress_service->recomputeKeyResult($keyResult);
            $this->okr_progress_service->recomputeObjective($keyResult->okrObjectiveID);
        } else {
            return $this->respondError('objective_id or key_result_id is required.', REST_Controller::HTTP_BAD_REQUEST);
        }

        $this->response([
            'status'  => true,
            'message' => 'Progress recomputed.',
            'data'    => $response,
        ], REST_Controller::HTTP_OK);
    }

    public function summary_get()
    {
        $schoolID = (int) $this->session->userdata('schoolID');
        $filters = [];
        $ownerType = strtolower($this->input->get('ownerType'));
        if ($ownerType && in_array($ownerType, $this->ownerTypes, true)) {
            $filters['ownerType'] = $ownerType;
        }
        if ($this->input->get('ownerID') !== null) {
            $filters['ownerID'] = (int) $this->input->get('ownerID');
        }
        if ($this->input->get('status')) {
            $filters['status'] = $this->input->get('status');
        }

        $summary = $this->okr_progress_service->getSummary($schoolID, $filters);
        $this->response([
            'status'  => true,
            'message' => 'OK',
            'data'    => $summary,
        ], REST_Controller::HTTP_OK);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function payload()
    {
        $raw = $this->input->raw_input_stream;
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        $post = $this->input->post();
        return !empty($post) ? $post : null;
    }

    /**
     * @param array       $payload
     * @param int         $schoolID
     * @param object|null $existing
     * @return array{objective: array<string, mixed>, key_results: array<int, array<string, mixed>>, errors: array}
     */
    protected function normalizeObjectivePayload(array $payload, $schoolID, $existing = null)
    {
        $errors = [];
        $objectiveData = [];

        $title = isset($payload['title']) ? trim($payload['title']) : null;
        if ($title !== null || !$existing) {
            if (!$title) {
                $errors[] = 'Objective title is required.';
            } else {
                $objectiveData['title'] = $title;
            }
        }

        if (array_key_exists('description', $payload)) {
            $objectiveData['description'] = trim((string) $payload['description']);
        }

        if (array_key_exists('owner_type', $payload)) {
            $ownerType = strtolower(trim((string) $payload['owner_type']));
            if (!in_array($ownerType, $this->ownerTypes, true)) {
                $errors[] = 'Invalid owner type provided.';
            } else {
                $objectiveData['ownerType'] = $ownerType;
            }
        } elseif (!$existing) {
            $errors[] = 'Owner type is required.';
        }

        if (array_key_exists('owner_id', $payload)) {
            $objectiveData['ownerID'] = (int) $payload['owner_id'];
        }

        if (array_key_exists('status', $payload)) {
            $objectiveData['status'] = trim((string) $payload['status']);
        }

        if (array_key_exists('start_date', $payload)) {
            $objectiveData['start_date'] = $this->validateDate($payload['start_date']);
        }

        if (array_key_exists('end_date', $payload)) {
            $objectiveData['end_date'] = $this->validateDate($payload['end_date']);
        }

        $objectiveData['schoolID'] = $schoolID;

        $keyResults = [];
        if (isset($payload['key_results'])) {
            if (!is_array($payload['key_results'])) {
                $errors[] = 'key_results must be an array.';
            } else {
                foreach ($payload['key_results'] as $index => $item) {
                    $normalizedKeyResult = $this->normalizeKeyResultPayload($item, $index);
                    if ($normalizedKeyResult['errors']) {
                        $errors = array_merge($errors, $normalizedKeyResult['errors']);
                    } else {
                        $keyResults[] = $normalizedKeyResult['data'];
                    }
                }
            }
        }

        return [
            'objective'   => $objectiveData,
            'key_results' => $keyResults,
            'errors'      => $errors,
        ];
    }

    /**
     * @param array $payload
     * @param int   $index
     * @return array{data: array<string, mixed>, errors: array}
     */
    protected function normalizeKeyResultPayload(array $payload, $index)
    {
        $errors = [];
        $data = [];

        $title = isset($payload['title']) ? trim($payload['title']) : '';
        if ($title === '') {
            $errors[] = sprintf('Key result #%d is missing a title.', $index + 1);
        }
        $data['title'] = $title;

        $data['description'] = isset($payload['description']) ? trim((string) $payload['description']) : null;
        $data['unit'] = isset($payload['unit']) ? trim((string) $payload['unit']) : null;
        $data['target_value'] = isset($payload['target_value']) ? (float) $payload['target_value'] : 0.0;
        $data['current_value'] = isset($payload['current_value']) ? (float) $payload['current_value'] : 0.0;
        $data['weight'] = isset($payload['weight']) ? (float) $payload['weight'] : 1.0;

        $source = isset($payload['data_source']) ? strtolower($payload['data_source']) : 'manual';
        if (!in_array($source, $this->dataSources, true)) {
            $errors[] = sprintf('Key result #%d has an invalid data source.', $index + 1);
        }
        $data['data_source'] = $source;

        $data['data_config'] = isset($payload['data_config']) ? $payload['data_config'] : [];
        $data['status'] = isset($payload['status']) ? (string) $payload['status'] : 'active';

        if (!empty($payload['okrKeyResultID'])) {
            $data['okrKeyResultID'] = (int) $payload['okrKeyResultID'];
        }

        return [
            'data'   => $data,
            'errors' => $errors,
        ];
    }

    protected function buildKeyResultInsertPayloads(array $keyResults, $objectiveID, $schoolID, $timestamp)
    {
        $rows = [];
        foreach ($keyResults as $keyResult) {
            $rows[] = [
                'okrObjectiveID' => $objectiveID,
                'schoolID'       => $schoolID,
                'title'          => $keyResult['title'],
                'description'    => $keyResult['description'],
                'unit'           => $keyResult['unit'],
                'target_value'   => $keyResult['target_value'],
                'current_value'  => $keyResult['current_value'],
                'weight'         => $keyResult['weight'],
                'data_source'    => $keyResult['data_source'],
                'data_config'    => !empty($keyResult['data_config']) ? json_encode($keyResult['data_config']) : null,
                'status'         => $keyResult['status'],
                'created_at'     => $timestamp,
                'updated_at'     => $timestamp,
            ];
        }
        return $rows;
    }

    protected function persistKeyResults($objectiveID, $schoolID, array $keyResults, $timestamp)
    {
        foreach ($keyResults as $keyResult) {
            $data = [
                'title'         => $keyResult['title'],
                'description'   => $keyResult['description'],
                'unit'          => $keyResult['unit'],
                'target_value'  => $keyResult['target_value'],
                'current_value' => $keyResult['current_value'],
                'weight'        => $keyResult['weight'],
                'data_source'   => $keyResult['data_source'],
                'data_config'   => !empty($keyResult['data_config']) ? json_encode($keyResult['data_config']) : null,
                'status'        => $keyResult['status'],
                'updated_at'    => $timestamp,
            ];

            if (!empty($keyResult['okrKeyResultID'])) {
                $existing = $this->okr_key_result_m->get_single_okr_key_result([
                    'okrKeyResultID' => (int) $keyResult['okrKeyResultID'],
                    'okrObjectiveID' => $objectiveID,
                    'schoolID'       => $schoolID,
                ]);
                if ($existing) {
                    $this->okr_key_result_m->update_okr_key_result($data, $existing->okrKeyResultID);
                    continue;
                }
            }

            $data['okrObjectiveID'] = $objectiveID;
            $data['schoolID'] = $schoolID;
            $data['created_at'] = $timestamp;
            $this->okr_key_result_m->insert_okr_key_result($data);
        }
    }

    protected function validateDate($value)
    {
        if (!$value) {
            return null;
        }
        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }
        return date('Y-m-d', $timestamp);
    }

    protected function respondError($message, $status = REST_Controller::HTTP_BAD_REQUEST)
    {
        $payload = is_array($message) ? $message : ['error' => $message];
        $this->response([
            'status'  => false,
            'message' => $payload,
        ], $status);
        return null;
    }
}
