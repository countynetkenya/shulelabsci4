<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Examtranscriptreport extends Admin_Controller {

    /** @var int maximum number of datasets (exams + compilations) that can be selected */
    private $datasetLimit = 6;
    private $datasetValidationContext = array();

    public function __construct() {
        parent::__construct();
        $this->load->model('exam_m');
        $this->load->model('examcompilation_m');
        $this->load->model('examcompilation_exams_m');
        $this->load->model('examranking_m');
        $this->load->model('classes_m');
        $this->load->model('section_m');
        $this->load->model('subject_m');
        $this->load->model('schoolyear_m');
        $this->load->model('studentrelation_m');
        $this->load->model('studentgroup_m');
        $this->load->model('markpercentage_m');
        $this->load->model('marksetting_m');
        $this->load->model('mark_m');
        $this->load->model('grade_m');
        $this->load->model('remark_m');
        $this->load->model('setting_m');
        $this->load->model('sattendance_m');
        $this->load->model('subjectattendance_m');

        $language = $this->session->userdata('lang');
        $this->lang->load('examtranscriptreport', $language);
    }

    public function index() {
        $this->data['headerassets'] = array(
            'css' => array(
                'assets/select2/css/select2.css',
                'assets/select2/css/select2-bootstrap.css',
                'assets/custom-scrollbar/jquery.mCustomScrollbar.css'
            ),
            'js' => array(
                'assets/select2/select2.js',
                'assets/custom-scrollbar/jquery.mCustomScrollbar.concat.min.js'
            )
        );

        $schoolID = $this->session->userdata('schoolID');
        $this->data['schoolyears']           = $this->schoolyear_m->get_order_by_schoolyear(array('schoolID' => $schoolID));
        $this->data['currentSchoolyearID']   = (int) $this->session->userdata('defaultschoolyearID');
        $this->data['classes']               = $this->classes_m->general_get_order_by_classes(array('schoolID' => $schoolID));
        $this->data['exams']                 = $this->exam_m->get_order_by_exam(array('schoolID' => $schoolID));
        $this->data['examcompilations']      = $this->examcompilation_m->get_order_by_examcompilation(array('schoolID' => $schoolID));
        $this->data['examrankings']          = $this->examranking_m->get_order_by_examranking(array('schoolID' => $schoolID));
        $this->data['datasetLimit']          = $this->datasetLimit;
        $this->data['settingmarktypeID']     = $this->data['siteinfos']->marktypeID;

        $this->data['subview'] = 'report/examtranscript/ExamtranscriptReportView';
        $this->load->view('_layout_main', $this->data);
    }

    protected function rules() {
        $rules = array(
            array(
                'field' => 'examID[]',
                'label' => $this->lang->line('examtranscriptreport_exam'),
                'rules' => 'trim|xss_clean'
            ),
            array(
                'field' => 'classesID',
                'label' => $this->lang->line('examtranscriptreport_class'),
                'rules' => 'trim|required|xss_clean|numeric|callback_unique_data'
            ),
            array(
                'field' => 'sectionID',
                'label' => $this->lang->line('examtranscriptreport_section'),
                'rules' => 'trim|xss_clean|numeric'
            ),
            array(
                'field' => 'studentID',
                'label' => $this->lang->line('examtranscriptreport_student'),
                'rules' => 'trim|xss_clean|numeric'
            ),
            array(
                'field' => 'schoolyearID',
                'label' => $this->lang->line('examtranscriptreport_academic_year'),
                'rules' => 'trim|required|xss_clean|numeric|callback_unique_data'
            ),
            array(
                'field' => 'examcompilationIDs[]',
                'label' => $this->lang->line('examtranscriptreport_examcompilation'),
                'rules' => 'trim|xss_clean'
            ),
            array(
                'field' => 'examrankingID',
                'label' => $this->lang->line('examtranscriptreport_examranking'),
                'rules' => 'trim|xss_clean|numeric'
            ),
            array(
                'field' => 'include_guardian',
                'label' => $this->lang->line('examtranscriptreport_include_guardian'),
                'rules' => 'trim|xss_clean|in_list[0,1]'
            ),
            array(
                'field' => 'include_address',
                'label' => $this->lang->line('examtranscriptreport_include_address'),
                'rules' => 'trim|xss_clean|in_list[0,1]'
            ),
            array(
                'field' => 'include_student_address',
                'label' => $this->lang->line('examtranscriptreport_include_student_address'),
                'rules' => 'trim|xss_clean|in_list[0,1]'
            ),
            array(
                'field' => 'show_subject_position',
                'label' => $this->lang->line('examtranscriptreport_show_subject_position'),
                'rules' => 'trim|xss_clean|in_list[0,1]'
            ),
            array(
                'field' => 'show_class_position',
                'label' => $this->lang->line('examtranscriptreport_show_class_position'),
                'rules' => 'trim|xss_clean|in_list[0,1]'
            ),
            array(
                'field' => 'datasetMode',
                'label' => $this->lang->line('examtranscriptreport_dataset_mode'),
                'rules' => 'trim|required|xss_clean|in_list[exam,exam_compilation]|callback_validate_datasets'
            ),
            array(
                'field' => 'enableExamComparison',
                'label' => $this->lang->line('examtranscriptreport_compare_with_exams'),
                'rules' => 'trim|xss_clean|in_list[0,1]'
            ),
            array(
                'field' => 'enableExamCompilationComparison',
                'label' => $this->lang->line('examtranscriptreport_compare_with_compilations'),
                'rules' => 'trim|xss_clean|in_list[0,1]'
            )
        );

        return $rules;
    }

    protected function send_pdf_to_mail_rules() {
        $rules = array(
            array(
                'field' => 'examID[]',
                'label' => $this->lang->line('examtranscriptreport_exam'),
                'rules' => 'trim|xss_clean'
            ),
            array(
                'field' => 'classesID',
                'label' => $this->lang->line('examtranscriptreport_class'),
                'rules' => 'trim|required|xss_clean|numeric|callback_unique_data'
            ),
            array(
                'field' => 'sectionID',
                'label' => $this->lang->line('examtranscriptreport_section'),
                'rules' => 'trim|xss_clean|numeric'
            ),
            array(
                'field' => 'studentID',
                'label' => $this->lang->line('examtranscriptreport_student'),
                'rules' => 'trim|xss_clean|numeric'
            ),
            array(
                'field' => 'schoolyearID',
                'label' => $this->lang->line('examtranscriptreport_academic_year'),
                'rules' => 'trim|required|xss_clean|numeric|callback_unique_data'
            ),
            array(
                'field' => 'examcompilationIDs[]',
                'label' => $this->lang->line('examtranscriptreport_examcompilation'),
                'rules' => 'trim|xss_clean'
            ),
            array(
                'field' => 'examrankingID',
                'label' => $this->lang->line('examtranscriptreport_examranking'),
                'rules' => 'trim|xss_clean|numeric'
            ),
            array(
                'field' => 'to',
                'label' => $this->lang->line('examtranscriptreport_to'),
                'rules' => 'trim|required|xss_clean|valid_email'
            ),
            array(
                'field' => 'subject',
                'label' => $this->lang->line('examtranscriptreport_subject'),
                'rules' => 'trim|required|xss_clean'
            ),
            array(
                'field' => 'message',
                'label' => $this->lang->line('examtranscriptreport_message'),
                'rules' => 'trim|xss_clean'
            ),
            array(
                'field' => 'include_guardian',
                'label' => $this->lang->line('examtranscriptreport_include_guardian'),
                'rules' => 'trim|xss_clean|in_list[0,1]'
            ),
            array(
                'field' => 'include_address',
                'label' => $this->lang->line('examtranscriptreport_include_address'),
                'rules' => 'trim|xss_clean|in_list[0,1]'
            ),
            array(
                'field' => 'include_student_address',
                'label' => $this->lang->line('examtranscriptreport_include_student_address'),
                'rules' => 'trim|xss_clean|in_list[0,1]'
            ),
            array(
                'field' => 'show_subject_position',
                'label' => $this->lang->line('examtranscriptreport_show_subject_position'),
                'rules' => 'trim|xss_clean|in_list[0,1]'
            ),
            array(
                'field' => 'show_class_position',
                'label' => $this->lang->line('examtranscriptreport_show_class_position'),
                'rules' => 'trim|xss_clean|in_list[0,1]'
            ),
            array(
                'field' => 'datasetMode',
                'label' => $this->lang->line('examtranscriptreport_dataset_mode'),
                'rules' => 'trim|required|xss_clean|in_list[exam,exam_compilation]|callback_validate_datasets'
            ),
            array(
                'field' => 'enableExamComparison',
                'label' => $this->lang->line('examtranscriptreport_compare_with_exams'),
                'rules' => 'trim|xss_clean|in_list[0,1]'
            ),
            array(
                'field' => 'enableExamCompilationComparison',
                'label' => $this->lang->line('examtranscriptreport_compare_with_compilations'),
                'rules' => 'trim|xss_clean|in_list[0,1]'
            )
        );

        return $rules;
    }

    public function getExamtranscript() {
        $retArray = array('status' => FALSE, 'render' => '');
        if(permissionChecker('examtranscriptreport')) {
            if($_POST) {
                $examIDs           = $this->input->post('examID');
                if(!is_array($examIDs)) {
                    $examIDs = $this->input->post('examID[]');
                }
                $examIDs           = array_map('intval', (array) $examIDs);
                $examIDs           = array_values(array_unique(array_filter($examIDs)));
                $examCompilationIDs = $this->input->post('examcompilationIDs');
                if(!is_array($examCompilationIDs)) {
                    $examCompilationIDs = $this->input->post('examcompilationIDs[]');
                }
                $examCompilationIDs = array_values(array_unique(array_filter(array_map('intval', (array) $examCompilationIDs))));
                $examrankingID     = (int) $this->input->post('examrankingID');
                $classesID         = (int) $this->input->post('classesID');
                $sectionID         = (int) $this->input->post('sectionID');
                $studentID         = (int) $this->input->post('studentID');
                $schoolyearID      = (int) $this->input->post('schoolyearID');
                $includeGuardian   = (int) $this->input->post('include_guardian') === 1 ? TRUE : FALSE;
                $includeAddress    = (int) $this->input->post('include_address') === 1 ? TRUE : FALSE;
                $includeStudentAddress = (int) $this->input->post('include_student_address') === 1 ? TRUE : FALSE;
                $datasetMode       = $this->input->post('datasetMode');
                $datasetMode       = ($datasetMode === 'exam_compilation') ? 'exam_compilation' : 'exam';
                $enableExamComparison = (int) $this->input->post('enableExamComparison') === 1;
                $enableExamCompilationComparison = (int) $this->input->post('enableExamCompilationComparison') === 1;
                $showSubjectPosition = (int) $this->input->post('show_subject_position') === 1;
                $showClassPosition   = (int) $this->input->post('show_class_position') === 1;

                $rules = $this->rules();
                $this->form_validation->set_rules($rules);
                if($this->form_validation->run() == FALSE) {
                    $retArray             = $this->form_validation->error_array();
                    if(isset($this->datasetValidationContext['fields']) && isset($this->datasetValidationContext['message'])) {
                        foreach((array) $this->datasetValidationContext['fields'] as $fieldName) {
                            $retArray[$fieldName] = $this->datasetValidationContext['message'];
                        }
                    }
                    $retArray['status']   = FALSE;
                    $retArray['datasetMode'] = $datasetMode;
                    $retArray['enableExamComparison'] = $enableExamComparison ? 1 : 0;
                    $retArray['enableExamCompilationComparison'] = $enableExamCompilationComparison ? 1 : 0;
                    echo json_encode($retArray);
                    exit;
                }

                if($datasetMode === 'exam') {
                    if(!$enableExamCompilationComparison) {
                        $examCompilationIDs = array();
                    }
                } else {
                    if(!$enableExamComparison) {
                        $examIDs = array();
                    }
                }

                $prepare = $this->prepareReportData($examIDs, $examCompilationIDs, $examrankingID, $classesID, $sectionID, $studentID, $schoolyearID, 'report/examtranscript/ExamtranscriptReport', $includeGuardian, $includeAddress, $includeStudentAddress, $datasetMode, $enableExamComparison, $enableExamCompilationComparison, $showSubjectPosition, $showClassPosition);

                if($prepare['status']) {
                    $retArray['render'] = $this->load->view($prepare['view'], $this->data, true);
                    $retArray['status'] = TRUE;
                    echo json_encode($retArray);
                    exit;
                } else {
                    $retArray['message'] = $prepare['message'];
                    echo json_encode($retArray);
                    exit;
                }
            } else {
                $retArray['message'] = $this->lang->line('examtranscriptreport_permissionmethod');
                echo json_encode($retArray);
                exit;
            }
        }
        $retArray['message'] = $this->lang->line('examtranscriptreport_permission');
        echo json_encode($retArray);
        exit;
    }

    private function prepareReportData($examIDs, $examCompilationIDs, $examrankingID, $classesID, $sectionID, $studentID, $schoolyearID, $view = 'report/examtranscript/ExamtranscriptReport', $includeGuardian = TRUE, $includeAddress = TRUE, $includeStudentAddress = FALSE, $datasetMode = 'exam', $enableExamComparison = FALSE, $enableExamCompilationComparison = FALSE, $showSubjectPosition = TRUE, $showClassPosition = TRUE) {
        $response = array('status' => FALSE, 'message' => '', 'view' => $view);

        $schoolID = $this->session->userdata('schoolID');

        $includeGuardian       = (bool) $includeGuardian;
        $includeAddress        = $includeGuardian ? (bool) $includeAddress : FALSE;
        $includeStudentAddress = (bool) $includeStudentAddress;
        $datasetMode           = ($datasetMode === 'exam_compilation') ? 'exam_compilation' : 'exam';
        $enableExamComparison  = (bool) $enableExamComparison;
        $enableExamCompilationComparison = (bool) $enableExamCompilationComparison;
        $showSubjectPosition   = (bool) $showSubjectPosition;
        $showClassPosition     = (bool) $showClassPosition;

        $examIDs            = array_values(array_unique(array_filter(array_map('intval', (array) $examIDs))));
        $examCompilationIDs = array_values(array_unique(array_filter(array_map('intval', (array) $examCompilationIDs))));

        if($datasetMode === 'exam') {
            if(!$enableExamCompilationComparison) {
                $examCompilationIDs = array();
            }
        } else {
            if(!$enableExamComparison) {
                $examIDs = array();
            }
        }

        $datasetCount = customCompute($examIDs) + customCompute($examCompilationIDs);
        if($datasetCount === 0) {
            $response['message'] = $this->lang->line('examtranscriptreport_dataset_required');
            return $response;
        }

        if($datasetCount > $this->datasetLimit) {
            $response['message'] = sprintf($this->lang->line('examtranscriptreport_dataset_limit'), $this->datasetLimit);
            return $response;
        }

        $queryArray = array(
            'schoolyearID' => $schoolyearID,
            'schoolID'     => $schoolID,
        );

        $studentBaseQuery = array(
            'srschoolyearID' => $schoolyearID,
            'srschoolID'     => $schoolID,
        );

        $studentMetricQuery  = $studentBaseQuery;
        $studentDisplayQuery = $studentBaseQuery;

        $subjectQuery = array(
            'schoolID' => $schoolID,
            'type'     => 1,
        );

        if($classesID > 0) {
            $queryArray['classesID']          = $classesID;
            $studentMetricQuery['srclassesID']  = $classesID;
            $studentDisplayQuery['srclassesID'] = $classesID;
            $subjectQuery['classesID']        = $classesID;
        }

        if($sectionID > 0) {
            $queryArray['sectionID']          = $sectionID;
            $studentMetricQuery['srsectionID']  = $sectionID;
            $studentDisplayQuery['srsectionID'] = $sectionID;
        }

        if($studentID > 0) {
            $studentDisplayQuery['srstudentID'] = $studentID;
        }

        $availableCompilations    = $this->examcompilation_m->get_order_by_examcompilation(array('schoolID' => $schoolID));
        $selectedExamCompilations = array();
        if(customCompute($examCompilationIDs) && customCompute($availableCompilations)) {
            foreach($availableCompilations as $compilation) {
                if(in_array((int) $compilation->examcompilationID, $examCompilationIDs, TRUE)) {
                    $selectedExamCompilations[$compilation->examcompilationID] = $compilation;
                }
            }
        }

        $this->data['classesID']               = $classesID;
        $this->data['sectionID']               = $sectionID;
        $this->data['studentIDD']              = $studentID;
        $this->data['schoolyearID']            = $schoolyearID;
        $this->data['examCompilationIDs']      = $examCompilationIDs;
        $this->data['examrankingID']           = $examrankingID;
        $this->data['examIDs']                 = $examIDs;
        $this->data['datasetMode']             = $datasetMode;
        $this->data['enableExamComparison']    = $enableExamComparison;
        $this->data['enableExamCompilationComparison'] = $enableExamCompilationComparison;
        $this->data['selectedExamCompilations']= $selectedExamCompilations;
        $this->data['selectedExamRanking']     = ($examrankingID > 0) ? $this->examranking_m->get_examranking($examrankingID) : NULL;

        $this->data['classes'] = pluck($this->classes_m->general_get_order_by_classes(array('schoolID' => $schoolID)), 'classes', 'classesID');
        $this->data['sections'] = pluck($this->section_m->general_get_order_by_section(array('schoolID' => $schoolID)), 'section', 'sectionID');
        $this->data['groups']   = pluck($this->studentgroup_m->get_order_by_studentgroup(array('schoolID' => $schoolID)), 'group', 'studentgroupID');
        $this->data['grades']   = $this->grade_m->get_order_by_grade(array('schoolID' => $schoolID));
        $grades                 = $this->data['grades'];

        $mandatorySubjects = $this->subject_m->general_get_order_by_subject($subjectQuery);

        $optionalSubjectQuery = array(
            'schoolID' => $schoolID,
            'type'     => 0,
        );
        if($classesID > 0) {
            $optionalSubjectQuery['classesID'] = $classesID;
        }
        $optionalSubjects = $this->subject_m->general_get_order_by_subject($optionalSubjectQuery);

        $students         = $this->studentrelation_m->general_get_order_by_student($studentMetricQuery);
        $displayStudents  = $this->studentrelation_m->general_get_order_by_student($studentDisplayQuery);

        $mandatorySubjectIDs = array();
        $allSubjectsByID     = array();
        if(customCompute($mandatorySubjects)) {
            foreach($mandatorySubjects as $subject) {
                $subjectID = (int) $subject->subjectID;
                $mandatorySubjectIDs[] = $subjectID;
                $allSubjectsByID[$subjectID] = $subject;
            }
        }

        $optionalSubjectsByID = array();
        if(customCompute($optionalSubjects)) {
            foreach($optionalSubjects as $optionalSubject) {
                $subjectID = (int) $optionalSubject->subjectID;
                $optionalSubjectsByID[$subjectID] = $optionalSubject;
                $allSubjectsByID[$subjectID]     = $optionalSubject;
            }
        }

        $studentSubjectsMap = array();
        if(customCompute($students)) {
            foreach($students as $student) {
                $studentSubjectIDs = $mandatorySubjectIDs;
                $optionalSelection = array();
                if(isset($student->sroptionalsubjectID) && trim((string) $student->sroptionalsubjectID) !== '') {
                    $optionalSelection = array_filter(array_map('intval', explode(',', $student->sroptionalsubjectID)));
                }
                if(customCompute($optionalSelection)) {
                    foreach($optionalSelection as $optionalID) {
                        if(isset($optionalSubjectsByID[$optionalID]) && !in_array($optionalID, $studentSubjectIDs, TRUE)) {
                            $studentSubjectIDs[] = $optionalID;
                        }
                    }
                }
                $studentSubjectsMap[$student->srstudentID] = $studentSubjectIDs;
            }
        }

        $studentSubjectsForView = array();
        if(customCompute($studentSubjectsMap)) {
            foreach($studentSubjectsMap as $studentUniqueID => $subjectIDs) {
                foreach($subjectIDs as $subjectID) {
                    if(isset($allSubjectsByID[$subjectID])) {
                        $studentSubjectsForView[$studentUniqueID][$subjectID] = $allSubjectsByID[$subjectID];
                    }
                }
            }
        }

        $percentageArr = pluck($this->markpercentage_m->get_order_by_markpercentage(array('schoolID' => $schoolID)), 'obj', 'markpercentageID');
        $this->data['percentageArr'] = $percentageArr;

        $compilationExamMap = array();
        $allExamIDs         = $examIDs;
        if(customCompute($selectedExamCompilations)) {
            foreach($selectedExamCompilations as $compilationID => $compilation) {
                $compilationExamMap[$compilationID] = array();
                $compilationExams = $this->examcompilation_exams_m->get_order_by_examcompilation_exams(array('examcompilationID' => $compilationID));
                if(customCompute($compilationExams)) {
                    foreach($compilationExams as $compExam) {
                        $examID = (int) $compExam->examID;
                        $compilationExamMap[$compilationID][$examID] = (float) $compExam->weight;
                        $allExamIDs[] = $examID;
                    }
                }
            }
        }
        $allExamIDs = array_values(array_unique(array_filter($allExamIDs)));

        if(!customCompute($allExamIDs)) {
            $response['message'] = $this->lang->line('examtranscriptreport_dataset_required');
            return $response;
        }

        $queryArray['examID'] = $allExamIDs;

        $marksRecords = $this->mark_m->get_order_by_all_student_mark_with_markrelation2($queryArray);
        $marks        = $this->getMark($marksRecords);

        $this->data['marks'] = $marks;

        $markpercentagesmainArr = $this->marksetting_m->get_marksetting_markpercentages();
        $markpercentagesArr     = array();
        if(isset($markpercentagesmainArr[$classesID])) {
            foreach($allExamIDs as $examID) {
                if(isset($markpercentagesmainArr[$classesID][$examID])) {
                    $markpercentagesArr[$examID] = $markpercentagesmainArr[$classesID][$examID];
                }
            }
        }

        $settingmarktypeID = $this->data['siteinfos']->marktypeID;
        $this->data['settingmarktypeID'] = $settingmarktypeID;
        $this->data['markpercentagesArr'] = $markpercentagesArr;

        $exams = $this->exam_m->get_exam_wherein(array('schoolID' => $schoolID, 'examID' => $allExamIDs));

        $this->data['exams']             = $exams;
        $this->data['students']          = $students;
        $this->data['studentLists']      = $displayStudents;
        $this->data['mandatorysubjects'] = $mandatorySubjects;
        $this->data['optionalsubjects']  = $optionalSubjects;
        $this->data['subjects']          = array_values($allSubjectsByID);
        $this->data['studentSubjects']   = $studentSubjectsForView;

        $studentPosition = $this->buildStudentPosition($exams, $students, $studentSubjectsMap, $marks, $markpercentagesArr, $settingmarktypeID, $examrankingID);

        $classSubjectTotals = array();
        $examAverageTotals  = array();

        if(customCompute($exams) && customCompute($students)) {
            foreach($exams as $exam) {
                $examID = $exam->examID;
                foreach($students as $student) {
                    $studentIDTemp = $student->srstudentID;
                    if(isset($studentPosition[$examID][$studentIDTemp]['subjectMark'])) {
                        foreach($studentPosition[$examID][$studentIDTemp]['subjectMark'] as $subjectID => $score) {
                            if(!isset($classSubjectTotals[$examID][$subjectID]['sum'])) {
                                $classSubjectTotals[$examID][$subjectID]['sum'] = 0;
                            }
                            if(!isset($classSubjectTotals[$examID][$subjectID]['count'])) {
                                $classSubjectTotals[$examID][$subjectID]['count'] = 0;
                            }
                            $classSubjectTotals[$examID][$subjectID]['sum']   += $score;
                            $classSubjectTotals[$examID][$subjectID]['count'] += 1;
                        }
                    }

                    if(isset($studentPosition[$examID][$studentIDTemp]['classPositionMark'])) {
                        if(!isset($examAverageTotals[$examID]['sum'])) {
                            $examAverageTotals[$examID]['sum'] = 0;
                        }
                        if(!isset($examAverageTotals[$examID]['count'])) {
                            $examAverageTotals[$examID]['count'] = 0;
                        }
                        $examAverageTotals[$examID]['sum']   += $studentPosition[$examID][$studentIDTemp]['classPositionMark'];
                        $examAverageTotals[$examID]['count'] += 1;
                    }
                }
            }
        }

        $classSubjectAverages = array();
        foreach($classSubjectTotals as $examID => $subjectsTotal) {
            foreach($subjectsTotal as $subjectID => $stat) {
                $count = isset($stat['count']) ? $stat['count'] : 0;
                $classSubjectAverages[$examID][$subjectID] = ($count > 0) ? round(($stat['sum'] / $count), 2) : NULL;
            }
        }

        $examAverageScores = array();
        foreach($examAverageTotals as $examID => $stat) {
            $count = isset($stat['count']) ? $stat['count'] : 0;
            $examAverageScores[$examID] = ($count > 0) ? round(($stat['sum'] / $count), 2) : NULL;
        }

        $subjectPositions = array();
        if(isset($studentPosition['studentSubjectPositionMark']) && customCompute($studentPosition['studentSubjectPositionMark'])) {
            foreach($studentPosition['studentSubjectPositionMark'] as $subjectID => $examWiseMarks) {
                foreach($examWiseMarks as $examID => $sortedMarks) {
                    $subjectPositions[$examID][$subjectID] = $this->calculateRankingPositions($sortedMarks);
                }
            }
        }

        $overallPositions = array();
        if(isset($studentPosition['studentClassPositionArray']) && customCompute($studentPosition['studentClassPositionArray'])) {
            foreach($studentPosition['studentClassPositionArray'] as $examID => $sortedMarks) {
                $overallPositions[$examID] = $this->calculateRankingPositions($sortedMarks);
            }
        }

        $examsByID = array();
        if(customCompute($exams)) {
            foreach($exams as $exam) {
                $examsByID[$exam->examID] = $exam;
            }
        }

        $datasets                    = array();
        $datasetStudentPosition      = array();
        $datasetClassSubjectAverages = array();
        $datasetExamAverageScores    = array();
        $datasetSubjectPositions     = array();
        $datasetOverallPositions     = array();
        $datasetExamSummaries        = array();
        $datasetMarks                = array();
        $datasetSubjectGrades        = array();
        $datasetSummaryGrades        = array();

        if(customCompute($students)) {
            foreach($students as $student) {
                $datasetExamSummaries[$student->srstudentID] = array();
                $datasetSummaryGrades[$student->srstudentID] = array();
            }
        }

        if(customCompute($examIDs)) {
            foreach($examIDs as $examID) {
                if(!isset($examsByID[$examID])) {
                    continue;
                }

                $datasetKey = 'exam_'.$examID;
                $datasets[] = (object) array(
                    'key'          => $datasetKey,
                    'type'         => 'exam',
                    'name'         => $examsByID[$examID]->exam,
                    'sourceExamID' => $examID,
                    'components'   => array(
                        array(
                            'examID' => $examID,
                            'name'   => $examsByID[$examID]->exam,
                            'weight' => 100,
                        ),
                    ),
                );

                $datasetStudentPosition[$datasetKey]      = isset($studentPosition[$examID]) ? $studentPosition[$examID] : array();
                $datasetClassSubjectAverages[$datasetKey] = isset($classSubjectAverages[$examID]) ? $classSubjectAverages[$examID] : array();
                $datasetExamAverageScores[$datasetKey]    = isset($examAverageScores[$examID]) ? $examAverageScores[$examID] : NULL;
                $datasetSubjectPositions[$datasetKey]     = isset($subjectPositions[$examID]) ? $subjectPositions[$examID] : array();
                $datasetOverallPositions[$datasetKey]     = isset($overallPositions[$examID]) ? $overallPositions[$examID] : array();
                $datasetMarks[$datasetKey]                = isset($marks[$examID]) ? $marks[$examID] : array();
                $datasetSubjectGrades[$datasetKey]        = array();

                if(customCompute($students)) {
                    foreach($students as $student) {
                        $studentTempID = $student->srstudentID;
                        $totalMark     = isset($studentPosition[$examID][$studentTempID]['totalSubjectMark']) ? $studentPosition[$examID][$studentTempID]['totalSubjectMark'] : NULL;
                        $averageMark   = isset($studentPosition[$examID][$studentTempID]['classPositionMark']) ? $studentPosition[$examID][$studentTempID]['classPositionMark'] : NULL;
                        $classAverage  = isset($examAverageScores[$examID]) ? $examAverageScores[$examID] : NULL;
                        $position      = isset($overallPositions[$examID][$studentTempID]) ? $overallPositions[$examID][$studentTempID] : NULL;
                        $difference    = ($averageMark !== NULL && $classAverage !== NULL) ? round($averageMark - $classAverage, 2) : NULL;
                        $summaryGrade  = ($averageMark !== NULL) ? $this->resolveGradeLetter($grades, $averageMark) : NULL;

                        if(isset($studentSubjectsMap[$studentTempID]) && customCompute($studentSubjectsMap[$studentTempID])) {
                            foreach($studentSubjectsMap[$studentTempID] as $subjectID) {
                                $subjectMark = isset($studentPosition[$examID][$studentTempID]['subjectMark'][$subjectID]) ? $studentPosition[$examID][$studentTempID]['subjectMark'][$subjectID] : NULL;
                                $datasetSubjectGrades[$datasetKey][$studentTempID][$subjectID] = ($subjectMark !== NULL) ? $this->resolveGradeLetter($grades, $subjectMark) : NULL;
                            }
                        }

                        $datasetExamSummaries[$studentTempID][$datasetKey] = array(
                            'datasetKey'   => $datasetKey,
                            'examName'     => $examsByID[$examID]->exam,
                            'totalMark'    => $totalMark,
                            'averageMark'  => $averageMark,
                            'classAverage' => $classAverage,
                            'position'     => $position,
                            'difference'   => $difference,
                            'grade'        => $summaryGrade,
                        );
                        $datasetSummaryGrades[$studentTempID][$datasetKey] = $summaryGrade;
                    }
                }
            }
        }

        if(customCompute($examCompilationIDs)) {
            foreach($examCompilationIDs as $compilationID) {
                if(!isset($selectedExamCompilations[$compilationID])) {
                    continue;
                }

                $datasetKey = 'comp_'.$compilationID;
                $components = array();
                if(isset($compilationExamMap[$compilationID]) && customCompute($compilationExamMap[$compilationID])) {
                    foreach($compilationExamMap[$compilationID] as $componentExamID => $weight) {
                        $components[] = array(
                            'examID' => $componentExamID,
                            'name'   => isset($examsByID[$componentExamID]) ? $examsByID[$componentExamID]->exam : '',
                            'weight' => $weight,
                        );
                    }
                }

                $datasets[] = (object) array(
                    'key'                     => $datasetKey,
                    'type'                    => 'compilation',
                    'name'                    => $selectedExamCompilations[$compilationID]->name,
                    'sourceExamCompilationID' => $compilationID,
                    'components'              => $components,
                );

                $datasetStudentPosition[$datasetKey]      = array();
                $datasetClassSubjectAverages[$datasetKey] = array();
                $datasetExamAverageScores[$datasetKey]    = NULL;
                $datasetSubjectPositions[$datasetKey]     = array();
                $datasetOverallPositions[$datasetKey]     = array();
                $datasetMarks[$datasetKey]                = array();
                $datasetSubjectGrades[$datasetKey]        = array();

                $subjectTotals         = array();
                $subjectPositionSource = array();
                $classPositionSource   = array();

                if(customCompute($students)) {
                    foreach($students as $student) {
                        $studentTempID = $student->srstudentID;
                        $subjectMarks  = array();
                        $datasetMarks[$datasetKey][$studentTempID] = array();

                        $studentSubjectIDs = isset($studentSubjectsMap[$studentTempID]) ? $studentSubjectsMap[$studentTempID] : array();
                        if(customCompute($studentSubjectIDs)) {
                            foreach($studentSubjectIDs as $subjectID) {
                                $aggregatedMark = 0;
                                $hasContribution = FALSE;
                                $comments        = array();

                                if(isset($compilationExamMap[$compilationID]) && customCompute($compilationExamMap[$compilationID])) {
                                    foreach($compilationExamMap[$compilationID] as $componentExamID => $weight) {
                                        if(isset($studentPosition[$componentExamID][$studentTempID]['subjectMark'][$subjectID])) {
                                            $aggregatedMark += (float) $studentPosition[$componentExamID][$studentTempID]['subjectMark'][$subjectID] * ($weight / 100);
                                            $hasContribution = TRUE;
                                        }

                                        if(isset($marks[$componentExamID][$studentTempID][$subjectID]['teacher_comment']) && $marks[$componentExamID][$studentTempID][$subjectID]['teacher_comment'] !== '') {
                                            $componentName = isset($examsByID[$componentExamID]) ? $examsByID[$componentExamID]->exam : '';
                                            $comments[] = ($componentName !== '' ? $componentName.': ' : '').$marks[$componentExamID][$studentTempID][$subjectID]['teacher_comment'];
                                        }
                                    }
                                }

                                if($hasContribution) {
                                    $aggregatedMark = round($aggregatedMark, 2);
                                    $subjectMarks[$subjectID] = $aggregatedMark;
                                    $datasetSubjectGrades[$datasetKey][$studentTempID][$subjectID] = $this->resolveGradeLetter($grades, $aggregatedMark);

                                    if(!isset($subjectTotals[$subjectID]['sum'])) {
                                        $subjectTotals[$subjectID]['sum'] = 0;
                                    }
                                    if(!isset($subjectTotals[$subjectID]['count'])) {
                                        $subjectTotals[$subjectID]['count'] = 0;
                                    }
                                    $subjectTotals[$subjectID]['sum']   += $aggregatedMark;
                                    $subjectTotals[$subjectID]['count'] += 1;

                                    if(!isset($subjectPositionSource[$subjectID])) {
                                        $subjectPositionSource[$subjectID] = array();
                                    }
                                    $subjectPositionSource[$subjectID][$studentTempID] = $aggregatedMark;

                                    $datasetMarks[$datasetKey][$studentTempID][$subjectID]['teacher_comment'] = customCompute($comments) ? implode(' | ', array_unique($comments)) : '';
                                }
                            }
                        }

                        $totalSubjectMark = customCompute($subjectMarks) ? array_sum($subjectMarks) : NULL;
                        $subjectCount     = customCompute($subjectMarks);
                        $classPositionMark = ($subjectCount > 0 && $totalSubjectMark !== NULL) ? round(($totalSubjectMark / $subjectCount), 2) : NULL;
                        $summaryGrade      = ($classPositionMark !== NULL) ? $this->resolveGradeLetter($grades, $classPositionMark) : NULL;

                        $datasetStudentPosition[$datasetKey][$studentTempID] = array(
                            'subjectMark'             => $subjectMarks,
                            'markpercentageMark'      => array(),
                            'markpercentagetotalmark' => array(),
                            'totalSubjectMark'        => ($totalSubjectMark !== NULL) ? $totalSubjectMark : 0,
                            'classPositionMark'       => $classPositionMark,
                        );

                        if($classPositionMark !== NULL) {
                            $classPositionSource[$studentTempID] = $classPositionMark;
                        }

                        $datasetExamSummaries[$studentTempID][$datasetKey] = array(
                            'datasetKey'   => $datasetKey,
                            'examName'     => $selectedExamCompilations[$compilationID]->name,
                            'totalMark'    => ($totalSubjectMark !== NULL) ? $totalSubjectMark : NULL,
                            'averageMark'  => $classPositionMark,
                            'classAverage' => NULL,
                            'position'     => NULL,
                            'difference'   => NULL,
                            'grade'        => $summaryGrade,
                        );
                        $datasetSummaryGrades[$studentTempID][$datasetKey] = $summaryGrade;
                    }
                }

                $classAverage = NULL;
                if(customCompute($classPositionSource)) {
                    $sortedClassPosition = $this->sortArray($classPositionSource);
                    $datasetOverallPositions[$datasetKey] = $this->calculateRankingPositions($sortedClassPosition);
                    $classAverage = round((array_sum($sortedClassPosition) / customCompute($sortedClassPosition)), 2);
                }

                $datasetExamAverageScores[$datasetKey] = $classAverage;

                foreach($subjectTotals as $subjectID => $stat) {
                    $count = isset($stat['count']) ? $stat['count'] : 0;
                    $datasetClassSubjectAverages[$datasetKey][$subjectID] = ($count > 0) ? round(($stat['sum'] / $count), 2) : NULL;
                }

                foreach($subjectPositionSource as $subjectID => $values) {
                    arsort($values);
                    $datasetSubjectPositions[$datasetKey][$subjectID] = $this->calculateRankingPositions($values);
                }

                if(customCompute($datasetExamSummaries)) {
                    foreach($datasetExamSummaries as $studentTempID => &$summaries) {
                        if(isset($summaries[$datasetKey])) {
                            $summaries[$datasetKey]['classAverage'] = $classAverage;
                            $summaries[$datasetKey]['position']     = isset($datasetOverallPositions[$datasetKey][$studentTempID]) ? $datasetOverallPositions[$datasetKey][$studentTempID] : NULL;
                            $averageMarkTemp = $summaries[$datasetKey]['averageMark'];
                            $summaries[$datasetKey]['difference']   = ($averageMarkTemp !== NULL && $classAverage !== NULL) ? round($averageMarkTemp - $classAverage, 2) : NULL;
                        }
                    }
                    unset($summaries);
                }
            }
        }

        $chartSeries = array();
        if(customCompute($students)) {
            foreach($students as $student) {
                $studentTempID = $student->srstudentID;
                $chartSeries[$studentTempID] = array(
                    'labels'        => array(),
                    'studentScores' => array(),
                    'classAverages' => array(),
                    'datasetKeys'   => array(),
                );

                foreach($datasets as $dataset) {
                    $datasetKey = $dataset->key;
                    $chartSeries[$studentTempID]['labels'][]      = $dataset->name;
                    $chartSeries[$studentTempID]['datasetKeys'][] = $datasetKey;

                    $studentAverage = isset($datasetStudentPosition[$datasetKey][$studentTempID]['classPositionMark']) ? $datasetStudentPosition[$datasetKey][$studentTempID]['classPositionMark'] : NULL;
                    $classAverage   = isset($datasetExamAverageScores[$datasetKey]) ? $datasetExamAverageScores[$datasetKey] : NULL;

                    $chartSeries[$studentTempID]['studentScores'][] = ($studentAverage !== NULL) ? round($studentAverage, 2) : NULL;
                    $chartSeries[$studentTempID]['classAverages'][] = ($classAverage !== NULL) ? round($classAverage, 2) : NULL;
                }
            }
        }

        $this->data['datasets']              = $datasets;
        $this->data['studentPosition']       = $datasetStudentPosition;
        $this->data['classSubjectAverages']  = $datasetClassSubjectAverages;
        $this->data['examAverageScores']     = $datasetExamAverageScores;
        $this->data['subjectPositions']      = $datasetSubjectPositions;
        $this->data['overallPositions']      = $datasetOverallPositions;
        $this->data['examSummaries']         = $datasetExamSummaries;
        $this->data['chartSeries']           = $chartSeries;
        $this->data['datasetMarks']          = $datasetMarks;
        $this->data['datasetSubjectGrades']  = $datasetSubjectGrades;
        $this->data['datasetSummaryGrades']  = $datasetSummaryGrades;
        $this->data['showSubjectPosition']   = $showSubjectPosition;
        $this->data['showClassPosition']     = $showClassPosition;

        $guardianContacts = array();
        if($includeGuardian) {
            $guardianRecords = $this->studentrelation_m->general_get_order_by_student_with_parent($studentDisplayQuery);
            if(customCompute($guardianRecords)) {
                foreach($guardianRecords as $relation) {
                    $studentUniqueID = $relation->srstudentID;
                    $guardianContacts[$studentUniqueID] = array();

                    $primary = array(
                        'name'       => trim((string) $relation->parent_name),
                        'relation'   => $this->lang->line('examtranscriptreport_guardian_primary'),
                        'phone'      => trim((string) $relation->parent_phone),
                        'email'      => trim((string) $relation->parent_email),
                        'address'    => $includeAddress ? trim((string) $relation->parent_address) : ''
                    );

                    if($primary['name'] !== '' || $primary['phone'] !== '' || $primary['email'] !== '' || ($includeAddress && $primary['address'] !== '')) {
                        $guardianContacts[$studentUniqueID][] = $primary;
                    }

                    $fatherContact = array(
                        'name'     => trim((string) $relation->father_name),
                        'relation' => $this->lang->line('examtranscriptreport_guardian_father'),
                        'phone'    => property_exists($relation, 'father_phone') ? trim((string) $relation->father_phone) : '',
                        'email'    => property_exists($relation, 'father_email') ? trim((string) $relation->father_email) : '',
                        'address'  => ($includeAddress && property_exists($relation, 'father_address')) ? trim((string) $relation->father_address) : ''
                    );

                    if($fatherContact['name'] !== '' || $fatherContact['phone'] !== '' || $fatherContact['email'] !== '' || ($includeAddress && $fatherContact['address'] !== '')) {
                        $guardianContacts[$studentUniqueID][] = $fatherContact;
                    }

                    $motherContact = array(
                        'name'     => trim((string) $relation->mother_name),
                        'relation' => $this->lang->line('examtranscriptreport_guardian_mother'),
                        'phone'    => property_exists($relation, 'mother_phone') ? trim((string) $relation->mother_phone) : '',
                        'email'    => property_exists($relation, 'mother_email') ? trim((string) $relation->mother_email) : '',
                        'address'  => ($includeAddress && property_exists($relation, 'mother_address')) ? trim((string) $relation->mother_address) : ''
                    );

                    if($motherContact['name'] !== '' || $motherContact['phone'] !== '' || $motherContact['email'] !== '' || ($includeAddress && $motherContact['address'] !== '')) {
                        $guardianContacts[$studentUniqueID][] = $motherContact;
                    }

                    if(!customCompute($guardianContacts[$studentUniqueID])) {
                        unset($guardianContacts[$studentUniqueID]);
                    }
                }
            }
        }

        $this->data['guardianContacts']        = $guardianContacts;
        $this->data['include_guardian']        = $includeGuardian;
        $this->data['include_address']         = $includeAddress;
        $this->data['include_student_address'] = $includeStudentAddress;
        $this->data['compilationExamMap']      = $compilationExamMap;
        $this->data['datasetLimit']            = $this->datasetLimit;

        if($examrankingID > 0) {
            $rankingExamID = NULL;
            if(customCompute($examIDs)) {
                $rankingExamID = (int) reset($examIDs);
            } elseif(customCompute($allExamIDs)) {
                $rankingExamID = (int) reset($allExamIDs);
            }

            if($rankingExamID) {
                $remarks = $this->remark_m->get_order_by_remark(array('examID' => $rankingExamID, 'schoolID' => $schoolID));
                $this->data['remarks'] = $this->getRemark($remarks);
            }

            $this->data['attendance'] = $this->get_student_attendance($queryArray, $this->subject_m->get_order_by_subject_with_subjectteacher(array('classesID' => $classesID, 'schoolID' => $schoolID)), $students);
        }

        $response['status'] = TRUE;
        return $response;
    }

    private function buildStudentPosition($exams, $students, $studentSubjectsMap, $marks, $markpercentagesArr, $settingmarktypeID, $examrankingID) {
        $studentPosition             = array();
        $studentChecker              = array();
        $studentClassPositionArray   = array();
        $studentSubjectPositionArray = array();

        if(!customCompute($exams) || !customCompute($students)) {
            return $studentPosition;
        }

        foreach($exams as $exam) {
            $examID = $exam->examID;
            foreach($students as $student) {
                $studentPosition[$examID][$student->srstudentID]['totalSubjectMark'] = 0;
                $studentPosition[$examID][$student->srstudentID]['subjectMark']      = array();
                $studentPosition[$examID][$student->srstudentID]['markpercentageMark'] = array();
                $studentPosition[$examID][$student->srstudentID]['markpercentagetotalmark'] = array();
                $studentSubjects = isset($studentSubjectsMap[$student->srstudentID]) ? $studentSubjectsMap[$student->srstudentID] : array();
                if(customCompute($studentSubjects)) {
                    foreach($studentSubjects as $subjectID) {
                        $uniquepercentageArr = isset($markpercentagesArr[$examID][$subjectID]) ? $markpercentagesArr[$examID][$subjectID] : array();
                        $markpercentages = array();
                        if(customCompute($uniquepercentageArr)) {
                            $markpercentages = $uniquepercentageArr[(($settingmarktypeID==4) || ($settingmarktypeID==6)) ? 'unique' : 'own'];
                        }

                        if(customCompute($markpercentages)) {
                            foreach($markpercentages as $markpercentageID) {
                                $f = (isset($uniquepercentageArr['own']) && in_array($markpercentageID, $uniquepercentageArr['own']));
                                $markValue = 0;
                                if(isset($marks[$examID][$student->srstudentID][$subjectID][$markpercentageID]) && $f) {
                                    $markValue = $marks[$examID][$student->srstudentID][$subjectID][$markpercentageID];
                                }

                                if(isset($studentPosition[$examID][$student->srstudentID]['subjectMark'][$subjectID])) {
                                    $studentPosition[$examID][$student->srstudentID]['subjectMark'][$subjectID] += $markValue;
                                } else {
                                    $studentPosition[$examID][$student->srstudentID]['subjectMark'][$subjectID] = $markValue;
                                }

                                if($markValue > 0) {
                                    $studentPosition[$examID][$student->srstudentID]['markpercentageMark'][$subjectID][$markpercentageID] = $markValue;
                                    if(isset($studentPosition[$examID][$student->srstudentID]['markpercentagetotalmark'][$markpercentageID])) {
                                        $studentPosition[$examID][$student->srstudentID]['markpercentagetotalmark'][$markpercentageID] += $markValue;
                                    } else {
                                        $studentPosition[$examID][$student->srstudentID]['markpercentagetotalmark'][$markpercentageID] = $markValue;
                                    }
                                }
                            }
                        }

                        if(isset($studentPosition[$examID][$student->srstudentID]['subjectMark'][$subjectID])) {
                            $studentPosition[$examID][$student->srstudentID]['totalSubjectMark'] += $studentPosition[$examID][$student->srstudentID]['subjectMark'][$subjectID];
                            $studentSubjectPositionArray[$subjectID][$examID][$student->srstudentID] = $studentPosition[$examID][$student->srstudentID]['subjectMark'][$subjectID];
                        }
                    }
                }

                if(customCompute($studentPosition[$examID][$student->srstudentID]['subjectMark'])) {
                    $subjectCount = customCompute($studentPosition[$examID][$student->srstudentID]['subjectMark']);
                    if($subjectCount > 0) {
                        $studentPosition[$examID][$student->srstudentID]['classPositionMark'] = ($studentPosition[$examID][$student->srstudentID]['totalSubjectMark'] / $subjectCount);
                        $studentClassPositionArray[$examID][$student->srstudentID] = $studentPosition[$examID][$student->srstudentID]['classPositionMark'];
                    }
                }
            }
        }

        foreach($studentClassPositionArray as $examID => $array) {
            $studentClassPositionArray[$examID] = $this->sortArray($array);
        }

        if(customCompute($studentSubjectPositionArray)) {
            foreach($studentSubjectPositionArray as $subjectID => $examWise) {
                foreach($examWise as $examID => $studentSubjectPositionMark) {
                    arsort($studentSubjectPositionMark);
                    $studentPosition['studentSubjectPositionMark'][$subjectID][$examID] = $studentSubjectPositionMark;
                }
            }
        }

        $studentPosition['studentClassPositionArray'] = $studentClassPositionArray;

        if($examrankingID > 0) {
            $examranking = $this->examranking_m->get_examranking($examrankingID);
            if(customCompute($examranking)) {
                $studentPosition['rankingSubjects'] = explode(',', $examranking->subjects);
            } else {
                $studentPosition['rankingSubjects'] = array();
            }
        }

        return $studentPosition;
    }

    private function sortArray($array) {
        arsort($array);
        return $array;
    }

    private function calculateRankingPositions($sortedMarks) {
        $positions    = array();
        $rank         = 0;
        $currentRank  = 0;
        $previousMark = NULL;

        if(customCompute($sortedMarks)) {
            foreach($sortedMarks as $studentID => $mark) {
                $rank++;
                if($previousMark === NULL || $mark < $previousMark) {
                    $currentRank  = $rank;
                    $previousMark = $mark;
                }
                $positions[$studentID] = $currentRank;
            }
        }

        return $positions;
    }

    private function resolveGradeLetter($grades, $mark) {
        if($mark === NULL || !customCompute($grades)) {
            return NULL;
        }

        $numericMark = (float) $mark;
        foreach($grades as $grade) {
            $gradeFrom = isset($grade->gradefrom) ? (float) $grade->gradefrom : 0;
            $gradeTo   = isset($grade->gradeupto) ? (float) $grade->gradeupto : 0;
            if($numericMark >= $gradeFrom && $numericMark <= $gradeTo) {
                return isset($grade->grade) ? $grade->grade : NULL;
            }
        }

        return NULL;
    }

    private function getMark($marks) {
        $retMark = array();

        if(customCompute($marks)) {
            foreach($marks as $mark) {
                $retMark[$mark->examID][$mark->studentID][$mark->subjectID][$mark->markpercentageID] = $mark->mark;
                if(!isset($retMark[$mark->examID][$mark->studentID][$mark->subjectID]['teacher_comment']) || $retMark[$mark->examID][$mark->studentID][$mark->subjectID]['teacher_comment'] === '') {
                    $retMark[$mark->examID][$mark->studentID][$mark->subjectID]['teacher_comment'] = (string) $mark->teacher_comment;
                }
            }
        }

        return $retMark;
    }

    private function getRemark($remarks) {
        $retRemark = array();
        if(customCompute($remarks)) {
            foreach($remarks as $remark) {
                $retRemark[$remark->studentID] = $remark;
            }
        }
        return $retRemark;
    }

    private function get_student_attendance($queryArray, $subjects, $studentlists) {
        unset($queryArray['examID']);
        $newArray = array();
        $attendanceArray = array();
        $getWeekendDay = $this->getWeekendDays();
        $getHoliday    = explode('","', $this->getHolidays());

        if($this->data['siteinfos']->attendance == 'subject') {
            $attendances = $this->subjectattendance_m->get_order_by_sub_attendance($queryArray);

            if(customCompute($attendances)) {
                foreach ($attendances as $attendance) {
                    $monthyearArray = explode('-', $attendance->monthyear);
                    $monthDay = date('t', mktime(0, 0, 0, $monthyearArray['0'], 1, $monthyearArray['1']));
                    for($i=1; $i<=$monthDay; $i++) {
                        $currentDate = sprintf("%02d", $i).'-'.$attendance->monthyear;
                        if(in_array($currentDate, $getHoliday)) {
                            continue;
                        } elseif(in_array($currentDate, $getWeekendDay)) {
                            continue;
                        } else {
                            $day = 'a'.$i;
                            if($attendance->$day == 'P' || $attendance->$day == 'L' || $attendance->$day == 'LE') {
                                if(!isset($newArray[$attendance->studentID][$attendance->subjectID]['pCount'])) {
                                    $newArray[$attendance->studentID][$attendance->subjectID]['pCount'] = 1;
                                } else {
                                    $newArray[$attendance->studentID][$attendance->subjectID]['pCount'] += 1;
                                }
                            } else {
                                if(!isset($newArray[$attendance->studentID][$attendance->subjectID]['aCount'])) {
                                    $newArray[$attendance->studentID][$attendance->subjectID]['aCount'] = 1;
                                } else {
                                    $newArray[$attendance->studentID][$attendance->subjectID]['aCount'] += 1;
                                }
                            }
                            if(!isset($newArray[$attendance->studentID][$attendance->subjectID]['tCount'])) {
                                $newArray[$attendance->studentID][$attendance->subjectID]['tCount'] = 1;
                            } else {
                                $newArray[$attendance->studentID][$attendance->subjectID]['tCount'] += 1;
                            }
                        }
                    }
                }
            }

            $studentlistsArray = pluck($studentlists,'sroptionalsubjectID','srstudentID');
            $subjects  = pluck($subjects,'obj','subjectID');

            if(customCompute($newArray)) {
                foreach($newArray as $studentID => $array) {
                    $str = '';
                    if(customCompute($subjects)) {
                        foreach ($subjects as $subjectID => $subject) {
                            if($subject->type == '1') {
                                $pCount = isset($array[$subjectID]['pCount']) ? $array[$subjectID]['pCount'] : '0';
                                $tCount = isset($array[$subjectID]['tCount']) ? $array[$subjectID]['tCount'] : '0';
                                $str .= $subjects[$subjectID]->subject .":".$pCount."/".$tCount.',';
                            }
                        }
                    }

                    if(isset($studentlistsArray[$studentID]) && $studentlistsArray[$studentID] != '0' ) {
                        $optionalID = $studentlistsArray[$studentID];
                        $pCount = isset($newArray[$studentID][$optionalID]['pCount']) ? $newArray[$studentID][$optionalID]['pCount'] : '0';
                        $tCount = isset($newArray[$studentID][$optionalID]['tCount']) ? $newArray[$studentID][$optionalID]['tCount'] : '0';
                        if(isset($subjects[$optionalID])) {
                            $str .= $subjects[$optionalID]->subject .":".$pCount."/".$tCount.',';
                        }
                    }

                    $attendanceArray[$studentID] = trim($str, ',');
                }
            }
        } else {
            $attendances = $this->sattendance_m->get_order_by_attendance($queryArray);
            if(customCompute($attendances)) {
                foreach($attendances as $attendance) {
                    $monthyearArray = explode('-', $attendance->monthyear);
                    $monthDay = date('t', mktime(0, 0, 0, $monthyearArray['0'], 1, $monthyearArray['1']));
                    for($i=1; $i<=$monthDay; $i++) {
                        $currentDate = sprintf("%02d", $i).'-'.$attendance->monthyear;
                        if(in_array($currentDate, $getHoliday)) {
                            continue;
                        } elseif(in_array($currentDate, $getWeekendDay)) {
                            continue;
                        } else {
                            $day = 'a'.$i;
                            if($attendance->$day == 'P' || $attendance->$day == 'L' || $attendance->$day == 'LE') {
                                if(!isset($newArray[$attendance->studentID]['pCount'])) {
                                    $newArray[$attendance->studentID]['pCount'] = 1;
                                } else {
                                    $newArray[$attendance->studentID]['pCount'] += 1;
                                }
                            } else {
                                if(!isset($newArray[$attendance->studentID]['aCount'])) {
                                    $newArray[$attendance->studentID]['aCount'] = 1;
                                } else {
                                    $newArray[$attendance->studentID]['aCount'] += 1;
                                }
                            }
                            if(!isset($newArray[$attendance->studentID]['tCount'])) {
                                $newArray[$attendance->studentID]['tCount'] = 1;
                            } else {
                                $newArray[$attendance->studentID]['tCount'] += 1;
                            }
                        }
                    }
                    $pCount = isset($newArray[$attendance->studentID]['pCount']) ? $newArray[$attendance->studentID]['pCount'] : '0';
                    $tCount = isset($newArray[$attendance->studentID]['tCount']) ? $newArray[$attendance->studentID]['tCount'] : '0';
                    $attendanceArray[$attendance->studentID] = $pCount."/".$tCount;
                }
            }
        }

        return $attendanceArray;
    }

    public function getExam() {
        $classesID = $this->input->post('classesID');
        echo "<option value='0'>", $this->lang->line("examtranscriptreport_none"),"</option>";
        if((int)$classesID) {
            $schoolID = $this->session->userdata('schoolID');
            $exams    = pluck($this->marksetting_m->get_exam(array('marktypeID' => $this->data['siteinfos']->marktypeID, 'classesID' => $classesID, 'schoolID' => $schoolID)), 'obj', 'examID');
            if(customCompute($exams)) {
                foreach ($exams as $exam) {
                    echo "<option value=".$exam->examID.">".$exam->exam." (". $exam->date .")</option>";
                }
            }
        }
    }

    public function getExamranking() {
        $classesID = $this->input->post('classesID');
        echo "<option value='0'>". $this->lang->line("examtranscriptreport_please_select"). "</option>";
        if((int)$classesID) {
            $schoolID = $this->session->userdata('schoolID');
            $examrankings = pluck($this->examranking_m->get_order_by_examranking(array('classesID' => $classesID, 'schoolID' => $schoolID)), 'obj', 'examrankingID');
            if(customCompute($examrankings)) {
                foreach ($examrankings as $examranking) {
                    echo "<option value='".$examranking->examrankingID."'>".$examranking->examranking."</option>";
                }
            }
        }
    }

    public function getSection() {
        $classesID = $this->input->post('classesID');
        if((int)$classesID) {
            $schoolID = $this->session->userdata('schoolID');
            $sections = $this->section_m->general_get_order_by_section(array('classesID' => $classesID, 'schoolID' => $schoolID));
            echo "<option value='0'>". $this->lang->line("examtranscriptreport_please_select") . "</option>";
            if(customCompute($sections)) {
                foreach ($sections as $section) {
                    echo "<option value='".$section->sectionID."'>".$section->section."</option>";
                }
            }
        }
    }

    public function getStudent() {
        $classesID = $this->input->post('classesID');
        $sectionID = $this->input->post('sectionID');
        $schoolID = $this->session->userdata('schoolID');
        $schoolyearID = $this->input->post('schoolyearID');
        if(!$schoolyearID) {
            $schoolyearID = $this->session->userdata('defaultschoolyearID');
        }
        $studentQuery['srschoolyearID'] = $schoolyearID;
        $studentQuery['srschoolID'] = $schoolID;
        if((int)$classesID) {
            $studentQuery['srclassesID'] = $classesID;
        }
        if((int)$sectionID) {
            $studentQuery['srsectionID'] = $sectionID;
        }
        if($this->session->userdata('usertypeID') == 4) {
            $studentQuery['parentID'] = $this->session->userdata('loginuserID');
        }
        $students = $this->studentrelation_m->general_get_order_by_student($studentQuery);
        echo "<option value='0'>". $this->lang->line("examtranscriptreport_please_select") . "</option>";
        if(customCompute($students)) {
            foreach ($students as $student) {
                echo "<option value='".$student->srstudentID."'>".$student->srname."</option>";
            }
        }
    }

    public function unique_data($data) {
        if($data !== '') {
            if($data === '0') {
                $this->form_validation->set_message('unique_data', 'The %s field is required.');
                return FALSE;
            }
        }
        return TRUE;
    }

    public function validate_datasets() {
        $this->datasetValidationContext = array();

        $datasetMode = $this->input->post('datasetMode');
        $datasetMode = ($datasetMode === 'exam_compilation') ? 'exam_compilation' : 'exam';

        $examIDs = $this->input->post('examID');
        if(!is_array($examIDs)) {
            $examIDs = $this->input->post('examID[]');
        }
        $examIDs = array_values(array_unique(array_filter(array_map('intval', (array) $examIDs))));

        $examCompilationIDs = $this->input->post('examcompilationIDs');
        if(!is_array($examCompilationIDs)) {
            $examCompilationIDs = $this->input->post('examcompilationIDs[]');
        }
        $examCompilationIDs = array_values(array_unique(array_filter(array_map('intval', (array) $examCompilationIDs))));

        $examComparisonEnabled = (int) $this->input->post('enableExamComparison') === 1;
        $examCompilationComparisonEnabled = (int) $this->input->post('enableExamCompilationComparison') === 1;

        $primaryField = 'examID[]';
        $secondaryField = 'examcompilationIDs[]';

        if($datasetMode === 'exam') {
            $activeExamIDs = $examIDs;
            $activeCompilationIDs = $examCompilationComparisonEnabled ? $examCompilationIDs : array();
            $primaryCount = customCompute($activeExamIDs);
            $secondaryCount = customCompute($activeCompilationIDs);
            $secondaryEnabled = $examCompilationComparisonEnabled;
        } else {
            $primaryField = 'examcompilationIDs[]';
            $secondaryField = 'examID[]';
            $activeCompilationIDs = $examCompilationIDs;
            $activeExamIDs = $examComparisonEnabled ? $examIDs : array();
            $primaryCount = customCompute($activeCompilationIDs);
            $secondaryCount = customCompute($activeExamIDs);
            $secondaryEnabled = $examComparisonEnabled;
        }

        if($primaryCount === 0) {
            $message = $this->lang->line('examtranscriptreport_dataset_required');
            $this->datasetValidationContext = array('message' => $message, 'fields' => array($primaryField));
            $this->form_validation->set_message('validate_datasets', $message);
            return FALSE;
        }

        $datasetCount = $primaryCount + $secondaryCount;
        if($datasetCount > $this->datasetLimit) {
            $fields = array($primaryField);
            if($secondaryEnabled) {
                $fields[] = $secondaryField;
            }
            $message = sprintf($this->lang->line('examtranscriptreport_dataset_limit'), $this->datasetLimit);
            $this->datasetValidationContext = array('message' => $message, 'fields' => $fields);
            $this->form_validation->set_message('validate_datasets', $message);
            return FALSE;
        }

        $this->datasetValidationContext = array('message' => '', 'fields' => array());
        return TRUE;
    }

    public function pdf() {
        if(permissionChecker('examtranscriptreport')) {
            $examSegment       = $this->uri->segment(3);
            $examIDs           = json_decode(base64_decode($examSegment), TRUE);
            $compilationSegment = $this->uri->segment(4);
            $examCompilationIDs = json_decode(base64_decode($compilationSegment), TRUE);
            $classesID         = (int) htmlentities(escapeString($this->uri->segment(5)));
            $sectionID         = (int) htmlentities(escapeString($this->uri->segment(6)));
            $studentID         = (int) htmlentities(escapeString($this->uri->segment(7)));
            $examrankingID     = (int) htmlentities(escapeString($this->uri->segment(8)));
            $schoolyearID      = (int) htmlentities(escapeString($this->uri->segment(9)));
            $includeGuardianSegment = $this->uri->segment(10);
            $includeAddressSegment  = $this->uri->segment(11);
            $includeStudentAddressSegment = $this->uri->segment(12);
            $datasetModeSegment = $this->uri->segment(13);
            $enableExamComparisonSegment = $this->uri->segment(14);
            $enableExamCompilationComparisonSegment = $this->uri->segment(15);
            $showSubjectPositionSegment = $this->uri->segment(16);
            $showClassPositionSegment   = $this->uri->segment(17);

            $includeGuardian = TRUE;
            if($includeGuardianSegment !== NULL) {
                $includeGuardian = ((int) htmlentities(escapeString($includeGuardianSegment)) === 1);
            }

            $includeAddress = TRUE;
            if($includeAddressSegment !== NULL) {
                $includeAddress = ((int) htmlentities(escapeString($includeAddressSegment)) === 1);
            }

            $includeStudentAddress = FALSE;
            if($includeStudentAddressSegment !== NULL) {
                $includeStudentAddress = ((int) htmlentities(escapeString($includeStudentAddressSegment)) === 1);
            }

            $datasetMode = ($datasetModeSegment === 'exam_compilation') ? 'exam_compilation' : 'exam';
            if($datasetModeSegment !== NULL) {
                $datasetModeCandidate = htmlentities(escapeString($datasetModeSegment));
                $datasetMode = ($datasetModeCandidate === 'exam_compilation') ? 'exam_compilation' : 'exam';
            }

            $enableExamComparison = FALSE;
            if($enableExamComparisonSegment !== NULL) {
                $enableExamComparison = ((int) htmlentities(escapeString($enableExamComparisonSegment)) === 1);
            }

            $enableExamCompilationComparison = FALSE;
            if($enableExamCompilationComparisonSegment !== NULL) {
                $enableExamCompilationComparison = ((int) htmlentities(escapeString($enableExamCompilationComparisonSegment)) === 1);
            }

            $showSubjectPosition = TRUE;
            if($showSubjectPositionSegment !== NULL) {
                $showSubjectPosition = ((int) htmlentities(escapeString($showSubjectPositionSegment)) === 1);
            }

            $showClassPosition = TRUE;
            if($showClassPositionSegment !== NULL) {
                $showClassPosition = ((int) htmlentities(escapeString($showClassPositionSegment)) === 1);
            }

            $examIDs = array_map('intval', (array) $examIDs);
            $examCompilationIDs = array_map('intval', (array) $examCompilationIDs);
            $schoolyearID = $schoolyearID > 0 ? $schoolyearID : $this->session->userdata('defaultschoolyearID');

            $prepare = $this->prepareReportData($examIDs, $examCompilationIDs, $examrankingID, $classesID, $sectionID, $studentID, $schoolyearID, 'report/examtranscript/ExamtranscriptReportPDF', $includeGuardian, $includeAddress, $includeStudentAddress, $datasetMode, $enableExamComparison, $enableExamCompilationComparison, $showSubjectPosition, $showClassPosition);

            if($prepare['status']) {
                $this->reportPDF('studentexamreport.css', $this->data, $prepare['view']);
            } else {
                $this->data['message'] = $prepare['message'];
                $this->load->view('report/reporterror', $this->data);
            }
        } else {
            $this->data['message'] = $this->lang->line('examtranscriptreport_permission');
            $this->load->view('report/reporterror', $this->data);
        }
    }

    public function send_pdf_to_mail() {
        $retArray['status'] = FALSE;
        if(permissionChecker('examtranscriptreport')) {
            if($_POST) {
                $rules = $this->send_pdf_to_mail_rules();
                $this->form_validation->set_rules($rules);
                $datasetMode = $this->input->post('datasetMode');
                $datasetMode = ($datasetMode === 'exam_compilation') ? 'exam_compilation' : 'exam';
                $enableExamComparison = (int) $this->input->post('enableExamComparison') === 1;
                $enableExamCompilationComparison = (int) $this->input->post('enableExamCompilationComparison') === 1;

                if($this->form_validation->run() == FALSE) {
                    $retArray = $this->form_validation->error_array();
                    if(isset($this->datasetValidationContext['fields']) && isset($this->datasetValidationContext['message'])) {
                        foreach((array) $this->datasetValidationContext['fields'] as $fieldName) {
                            $retArray[$fieldName] = $this->datasetValidationContext['message'];
                        }
                    }
                    $retArray['status'] = FALSE;
                    $retArray['datasetMode'] = $datasetMode;
                    $retArray['enableExamComparison'] = $enableExamComparison ? 1 : 0;
                    $retArray['enableExamCompilationComparison'] = $enableExamCompilationComparison ? 1 : 0;
                    echo json_encode($retArray);
                    exit;
                } else {
                    $examIDs = $this->input->post('examID');
                    if(!is_array($examIDs)) {
                        $examIDs = $this->input->post('examID[]');
                    }
                    $examIDs           = array_map('intval', (array) $examIDs);
                    $examCompilationIDs = $this->input->post('examcompilationIDs');
                    if(!is_array($examCompilationIDs)) {
                        $examCompilationIDs = $this->input->post('examcompilationIDs[]');
                    }
                    $examCompilationIDs = array_map('intval', (array) $examCompilationIDs);
                    $examrankingID     = (int) $this->input->post('examrankingID');
                    $classesID         = (int) $this->input->post('classesID');
                    $sectionID         = (int) $this->input->post('sectionID');
                    $studentID         = (int) $this->input->post('studentID');
                    $schoolyearID      = (int) $this->input->post('schoolyearID');
                    $to                = $this->input->post('to');
                    $subject           = $this->input->post('subject');
                    $message           = $this->input->post('message');
                    $includeGuardian   = (int) $this->input->post('include_guardian') === 1 ? TRUE : FALSE;
                    $includeAddress    = (int) $this->input->post('include_address') === 1 ? TRUE : FALSE;
                    $includeStudentAddress = (int) $this->input->post('include_student_address') === 1 ? TRUE : FALSE;
                    $showSubjectPosition = (int) $this->input->post('show_subject_position') === 1;
                    $showClassPosition   = (int) $this->input->post('show_class_position') === 1;

                    if($datasetMode === 'exam') {
                        if(!$enableExamCompilationComparison) {
                            $examCompilationIDs = array();
                        }
                    } else {
                        if(!$enableExamComparison) {
                            $examIDs = array();
                        }
                    }

                    $prepare = $this->prepareReportData($examIDs, $examCompilationIDs, $examrankingID, $classesID, $sectionID, $studentID, $schoolyearID, 'report/examtranscript/ExamtranscriptReportPDF', $includeGuardian, $includeAddress, $includeStudentAddress, $datasetMode, $enableExamComparison, $enableExamCompilationComparison, $showSubjectPosition, $showClassPosition);

                    if($prepare['status']) {
                        $this->reportSendToMail('studentexamreport.css', $this->data, $prepare['view'], $to, $subject, $message);
                        $retArray['status'] = TRUE;
                        echo json_encode($retArray);
                        exit;
                    } else {
                        $retArray['message'] = $prepare['message'];
                        echo json_encode($retArray);
                        exit;
                    }
                }
            } else {
                $retArray['message'] = $this->lang->line('examtranscriptreport_permissionmethod');
                echo json_encode($retArray);
                exit;
            }
        } else {
            $retArray['message'] = $this->lang->line('examtranscriptreport_permission');
            echo json_encode($retArray);
            exit;
        }
    }


}
