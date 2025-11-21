<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Okr extends Admin_Controller
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

    public function index()
    {
        $schoolID = (int) $this->session->userdata('schoolID');
        $userTypeID = (int) $this->session->userdata('usertypeID');
        $userType = strtolower($this->session->userdata('usertype'));

        $filters = ['schoolID' => $schoolID];
        $activeRole = 'admin';

        $ownerFilter = $this->resolveOwnerFilter($userTypeID, $userType);
        if ($ownerFilter) {
            $filters['ownerType'] = $ownerFilter['ownerType'];
            $filters['ownerID']   = $ownerFilter['ownerID'];
            $activeRole = $ownerFilter['role'];
        }

        if ($this->input->get('ownerType')) {
            $requestedOwnerType = strtolower($this->input->get('ownerType'));
            if (in_array($requestedOwnerType, $this->ownerTypes, true)) {
                $filters['ownerType'] = $requestedOwnerType;
                if ($this->input->get('ownerID') !== null) {
                    $filters['ownerID'] = (int) $this->input->get('ownerID');
                }
                $activeRole = $requestedOwnerType;
            }
        }

        $objectives = $this->okr_objective_m->get_order_by_okr_objective($filters);
        $keyResults = pluck_multi_array(
            $this->okr_key_result_m->get_order_by_okr_key_result(['schoolID' => $schoolID]),
            'obj',
            'okrObjectiveID'
        );

        $summaryFilters = $filters;
        unset($summaryFilters['schoolID']);
        $this->data['summary'] = $this->okr_progress_service->getSummary($schoolID, $summaryFilters);

        $logs = $this->okr_log_m->get_order_by_okr_log(['schoolID' => $schoolID]);
        $this->data['logs'] = array_slice($logs, 0, 10);

        $this->data['objectives'] = $objectives;
        $this->data['keyResults'] = $keyResults;
        $this->data['activeRole'] = $activeRole;
        $this->data['ownerTypes'] = $this->ownerTypes;
        $this->data['dataSources'] = $this->dataSources;
        $this->data['canManage'] = $this->canManage();
        $this->data['subview'] = 'okr/index';
        $this->load->view('_layout_main', $this->data);
    }

    public function create()
    {
        $this->ensureManageAccess();
        $schoolID = (int) $this->session->userdata('schoolID');
        $this->load->library('form_validation');

        $this->form_validation->set_rules('title', $this->lang->line('okr_title'), 'trim|required|max_length[191]');
        $this->form_validation->set_rules('ownerType', $this->lang->line('okr_owner_type'), 'trim|required');
        $this->form_validation->set_rules('ownerID', $this->lang->line('okr_owner_id'), 'trim|integer');
        $this->form_validation->set_rules('start_date', $this->lang->line('okr_start_date'), 'trim');
        $this->form_validation->set_rules('end_date', $this->lang->line('okr_end_date'), 'trim');
        $this->form_validation->set_rules('kr_title', $this->lang->line('okr_key_result_title'), 'trim|required|max_length[191]');
        $this->form_validation->set_rules('kr_target_value', $this->lang->line('okr_key_result_target'), 'trim|numeric');
        $this->form_validation->set_rules('kr_weight', $this->lang->line('okr_key_result_weight'), 'trim|numeric');
        $this->form_validation->set_rules('kr_current_value', $this->lang->line('okr_key_result_current'), 'trim|numeric');

        $this->data['ownerTypes'] = $this->ownerTypes;
        $this->data['dataSources'] = $this->dataSources;

        if ($this->form_validation->run() === false) {
            $this->data['objective'] = (object) [
                'title'       => set_value('title'),
                'description' => set_value('description'),
                'ownerType'   => set_value('ownerType', 'admin'),
                'ownerID'     => set_value('ownerID'),
                'start_date'  => set_value('start_date'),
                'end_date'    => set_value('end_date'),
                'status'      => set_value('status', 'active'),
            ];
            $this->data['keyResult'] = (object) [
                'title'         => set_value('kr_title'),
                'description'   => set_value('kr_description'),
                'unit'          => set_value('kr_unit'),
                'target_value'  => set_value('kr_target_value', '100'),
                'weight'        => set_value('kr_weight', '1'),
                'current_value' => set_value('kr_current_value', '0'),
                'data_source'   => set_value('kr_data_source', 'manual'),
                'data_config'   => set_value('kr_data_config'),
            ];
            $this->data['subview'] = 'okr/form';
            $this->load->view('_layout_main', $this->data);
            return;
        }

        $ownerType = strtolower($this->input->post('ownerType'));
        if (!in_array($ownerType, $this->ownerTypes, true)) {
            $this->session->set_flashdata('error', 'Invalid owner type selected.');
            redirect(base_url('okr/create'));
            return;
        }

        $dataSource = strtolower($this->input->post('kr_data_source'));
        if (!in_array($dataSource, $this->dataSources, true)) {
            $this->session->set_flashdata('error', 'Invalid key result data source selected.');
            redirect(base_url('okr/create'));
            return;
        }

        $configInput = $this->input->post('kr_data_config');
        $config = [];
        if ($configInput) {
            $decoded = json_decode($configInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->session->set_flashdata('error', 'Key result configuration must be valid JSON.');
                redirect(base_url('okr/create'));
                return;
            }
            $config = $decoded;
        }

        $now = date('Y-m-d H:i:s');
        $objectiveData = [
            'schoolID'        => $schoolID,
            'title'           => $this->input->post('title'),
            'description'     => $this->input->post('description'),
            'ownerType'       => $ownerType,
            'ownerID'         => (int) $this->input->post('ownerID'),
            'status'          => $this->input->post('status') ?: 'active',
            'start_date'      => $this->sanitizeDate($this->input->post('start_date')),
            'end_date'        => $this->sanitizeDate($this->input->post('end_date')),
            'progress_cached' => 0.0,
            'created_at'      => $now,
            'updated_at'      => $now,
        ];

        $keyResultData = [
            'title'         => $this->input->post('kr_title'),
            'description'   => $this->input->post('kr_description'),
            'unit'          => $this->input->post('kr_unit'),
            'target_value'  => (float) $this->input->post('kr_target_value'),
            'current_value' => (float) $this->input->post('kr_current_value'),
            'weight'        => (float) $this->input->post('kr_weight') ?: 1.0,
            'data_source'   => $dataSource,
            'data_config'   => !empty($config) ? json_encode($config) : null,
            'status'        => $this->input->post('kr_status') ?: 'active',
            'created_at'    => $now,
            'updated_at'    => $now,
        ];

        $this->db->trans_begin();
        try {
            $objectiveID = $this->okr_objective_m->insert_okr_objective($objectiveData);
            $keyResultData['okrObjectiveID'] = $objectiveID;
            $keyResultData['schoolID'] = $schoolID;
            $this->okr_key_result_m->insert_okr_key_result($keyResultData);
        } catch (\Throwable $exception) {
            $this->db->trans_rollback();
            log_message('error', 'Failed to create OKR via UI: ' . $exception->getMessage());
            $this->session->set_flashdata('error', 'Unable to create objective at this time.');
            redirect(base_url('okr/create'));
            return;
        }

        $this->db->trans_commit();

        $this->okr_progress_service->recomputeObjective($objectiveID);
        $this->session->set_flashdata('success', $this->lang->line('okr_success_create'));
        redirect(base_url('okr/index'));
    }

    public function recompute($objectiveID)
    {
        $this->ensureManageAccess();
        $schoolID = (int) $this->session->userdata('schoolID');
        $objective = $this->okr_objective_m->get_single_okr_objective([
            'okrObjectiveID' => (int) $objectiveID,
            'schoolID'       => $schoolID,
        ]);

        if (!$objective) {
            show_404();
        }

        $this->okr_progress_service->recomputeObjective($objective);
        $this->session->set_flashdata('success', $this->lang->line('okr_progress_recalculate'));
        redirect(base_url('okr/index'));
    }

    public function recompute_key_result($keyResultID)
    {
        $this->ensureManageAccess();
        $schoolID = (int) $this->session->userdata('schoolID');
        $keyResult = $this->okr_key_result_m->get_single_okr_key_result([
            'okrKeyResultID' => (int) $keyResultID,
            'schoolID'       => $schoolID,
        ]);

        if (!$keyResult) {
            show_404();
        }

        $this->okr_progress_service->recomputeKeyResult($keyResult);
        $this->okr_progress_service->recomputeObjective($keyResult->okrObjectiveID);
        $this->session->set_flashdata('success', $this->lang->line('okr_key_result_recalculate'));
        redirect(base_url('okr/index'));
    }

    protected function resolveOwnerFilter($userTypeID, $userType)
    {
        switch ($userTypeID) {
            case 2:
                return [
                    'ownerType' => 'teacher',
                    'ownerID'   => (int) $this->session->userdata('loginuserID'),
                    'role'      => 'teacher',
                ];
            case 3:
                return [
                    'ownerType' => 'student',
                    'ownerID'   => (int) $this->session->userdata('loginuserID'),
                    'role'      => 'student',
                ];
            default:
                if ($userType === 'department') {
                    return [
                        'ownerType' => 'department',
                        'ownerID'   => (int) $this->session->userdata('loginuserID'),
                        'role'      => 'department',
                    ];
                }
        }

        if ($userTypeID === 1) {
            return null;
        }

        return null;
    }

    protected function canManage()
    {
        $userTypeID = (int) $this->session->userdata('usertypeID');
        $userType = strtolower($this->session->userdata('usertype'));
        return $userTypeID === 1 || $userType === 'department';
    }

    protected function ensureManageAccess()
    {
        if (!$this->canManage()) {
            show_error($this->lang->line('okr_error_permission'), 403);
        }
    }

    protected function sanitizeDate($value)
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
}
