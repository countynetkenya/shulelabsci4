<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reportform extends Admin_Controller {
    /*
    | -----------------------------------------------------
    | PRODUCT NAME: 	INILABS SCHOOL MANAGEMENT SYSTEM
    | -----------------------------------------------------
    | AUTHOR:			INILABS TEAM
    | -----------------------------------------------------
    | EMAIL:			info@inilabs.net
    | -----------------------------------------------------
    | COPYRIGHT:		RESERVED BY INILABS IT
    | -----------------------------------------------------
    | WEBSITE:			http://inilabs.net
    | -----------------------------------------------------
    */

    function __construct() {
        parent::__construct();
        $this->load->model("classes_m");
        $this->load->model('section_m');
        $this->load->model("exam_m");
        $this->load->model("studentrelation_m");
        $this->load->model("marksetting_m");
        $this->load->model("menu_m");
        $this->load->model("examcompilation_m");
        $this->load->model("examranking_m");

        $language = $this->session->userdata('lang');
        $this->lang->load('reportform', $language);

        $reportFormMenu = $this->menu_m->get_menu(array('link' => 'reportform'), TRUE);
        if(!customCompute($reportFormMenu)) {
            $reportMenu = $this->menu_m->get_menu(array('menuName' => 'Report', 'link' => '#'), TRUE);
            if(customCompute($reportMenu)) {
                $array = array(
                    'menuName' => 'Report Form',
                    'parentID' => $reportMenu->menuID,
                    'link' => 'reportform',
                    'icon' => 'fa-file-text-o',
                    'status' => 1,
                    'priority' => 100
                );
                $this->menu_m->insert_menu($array);
            }
        }
    }

    public function index() {
        $this->data['headerassets'] = array(
            'css' => array(
                'assets/select2/css/select2.css',
                'assets/select2/css/select2-bootstrap.css',
                'assets/custom-scrollbar/jquery.mCustomScrollbar.css',
            ),
            'js' => array(
                'assets/select2/select2.js',
                'assets/custom-scrollbar/jquery.mCustomScrollbar.concat.min.js',
            )
        );

        $schoolID = $this->session->userdata('schoolID');
        $this->data['classes'] = $this->classes_m->general_get_order_by_classes(array('schoolID' => $schoolID));
        $this->data['exams'] = $this->exam_m->get_order_by_exam(array('schoolID' => $schoolID));
        $this->data['examcompilations']  = $this->examcompilation_m->get_order_by_examcompilation(array('schoolID' => $schoolID));
        $this->data["subview"] = "report/reportform/ReportformView";
        $this->load->view('_layout_main', $this->data);
    }

    public function getExamranking() {
        $classesID = $this->input->post('classesID');
        echo "<option value='0' selected>", $this->lang->line("reportform_please_select"),"</option>";
        echo "<option value='mandatory'>", $this->lang->line("terminalreport_total_mandatory_subjects"),"</option>";
        echo "<option value='optional'>", $this->lang->line("terminalreport_total_optional_subjects"),"</option>";
        echo "<option value='nonexaminable'>", $this->lang->line("terminalreport_total_nonexaminable_subjects"),"</option>";
        echo "<option value='mandatory_and_optional'>", $this->lang->line("terminalreport_total_mandatory_and_best_optional_subjects"),"</option>";
        if((int)$classesID) {
            $schoolID = $this->session->userdata('schoolID');
            $examrankings = pluck($this->examranking_m->get_order_by_examranking(array('classesID' => $classesID, 'schoolID' => $schoolID)), 'obj', 'examrankingID');
            if(customCompute($examrankings)) {
                foreach ($examrankings as $examranking) {
                    echo "<option value=".$examranking->examrankingID.">".$examranking->examranking."</option>";
                }
            }
        }
    }

    public function getSection() {
        $classesID = $this->input->post('classesID');
        if((int)$classesID) {
            $schoolID = $this->session->userdata('schoolID');
            $sections = $this->section_m->general_get_order_by_section(array('classesID' => $classesID, 'schoolID' => $schoolID));
            echo "<option value='0'>", $this->lang->line("reportform_please_select"),"</option>";
            if(customCompute($sections)) {
                foreach ($sections as $section) {
                    echo "<option value=\"$section->sectionID\">".$section->section."</option>";
                }
            }
        }
    }

    public function getStudent() {
        $classesID = $this->input->post('classesID');
        $sectionID = $this->input->post('sectionID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $schoolID = $this->session->userdata('schoolID');
        $studentQuery['srschoolID'] = $schoolID;
        $studentQuery['srschoolyearID'] = $schoolyearID;
        if((int)$classesID) {
            $studentQuery['srclassesID'] = $classesID;
        }
        if((int)$sectionID) {
            $studentQuery['srsectionID'] = $sectionID;
        }
        $students = $this->studentrelation_m->general_get_order_by_student($studentQuery);
        echo "<option value='0'>". $this->lang->line("reportform_please_select") . "</option>";
        if(customCompute($students)) {
            foreach ($students as $student) {
                echo "<option value='".$student->srstudentID."'>".$student->srname."</option>";
            }
        }
    }

    public function get_report() {
        $report_type = $this->input->post('report_type');
        $post_data = $this->input->post();
        unset($post_data['report_type']);

        $target_url = '';
        if ($report_type == 'terminal') {
            $target_url = base_url('terminalreport/getTerminalreport');
        } elseif ($report_type == 'tabulationsheet') {
            $target_url = base_url('tabulationsheetreport/getTabulatonsheetReport');
        } elseif ($report_type == 'progresscard') {
            $target_url = base_url('progresscardreport/getProgresscardreport');
        } elseif ($report_type == 'studentsession') {
            $target_url = base_url('studentsessionreport/getstudentsessionreport');
        }

        if ($target_url) {
            $cookie_string = '';
            if (isset($_COOKIE[config_item('sess_cookie_name')])) {
                $cookie_string = config_item('sess_cookie_name') . '=' . $_COOKIE[config_item('sess_cookie_name')];
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $target_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if ($cookie_string) {
                curl_setopt($ch, CURLOPT_COOKIE, $cookie_string);
            }
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($http_code == 200) {
                header('Content-Type: application/json');
                echo $response;
            } else {
                $error_message = 'Error generating report. Target controller returned HTTP status ' . $http_code;
                if ($error) {
                    $error_message .= '. cURL error: ' . $error;
                }
                echo json_encode(['status' => false, 'render' => '<div class="alert alert-danger">' . $error_message . '</div>']);
            }
        } else {
            echo json_encode(['status' => false, 'render' => '<div class="alert alert-danger">Invalid report type selected.</div>']);
        }
    }
}