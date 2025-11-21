<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Teacherexamreport extends Admin_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('exam_m');
		$this->load->model('classes_m');
		$this->load->model('subject_m');
		$this->load->model('schoolyear_m');
		$this->load->model('markpercentage_m');
		$this->load->model('setting_m');
		$this->load->model('mark_m');
		$this->load->model('grade_m');
		$this->load->model('marksetting_m');
		$this->load->model('teacher_m');

		$language = $this->session->userdata('lang');
		$this->lang->load('teacherexamreport', $language);
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

		$schoolID              = $this->session->userdata('schoolID');
		$this->data['classes'] = $this->classes_m->general_get_order_by_classes(array('schoolID' => $schoolID));
		$this->data["subview"] = "report/teacherexam/TeacherexamReportView";
		$this->load->view('_layout_main', $this->data);
	}

  protected function rules() {
		$rules = array(
			array(
				'field'=>'examID[]',
				'label'=>$this->lang->line('teacherexamreport_exam'),
				'rules' => 'trim|required|xss_clean|callback_multiple_select'
			),
			array(
				'field'=>'classesID',
				'label'=>$this->lang->line('teacherexamreport_class'),
				'rules' => 'trim|required|xss_clean|numeric|callback_unique_data'
			),
			array(
				'field'=>'teacherID',
				'label'=>$this->lang->line('teacherexamreport_student'),
				'rules' => 'trim|xss_clean|numeric'
			)
		);
		return $rules;
	}

  public function getTeacherexamReport() {
		$retArray['render'] = '';
		$retArray['status'] = FALSE;
		if(permissionChecker('teacherexamreport')) {
			$examID      = $this->input->post('examID[]');
			$classesID   = $this->input->post('classesID');
			$teacherID   = $this->input->post('teacherID');

			if($_POST) {
				$rules = $this->rules();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {
					$retArray = $this->form_validation->error_array();
					$retArray['status'] = FALSE;
			    echo json_encode($retArray);
			    exit;
				} else {
					$schoolID = $this->session->userdata('schoolID');
					$schoolyearID = $this->session->userdata('defaultschoolyearID');
					$queryArray['schoolID'] = $schoolID;
					if($classesID > 0) {
						$queryArray['classesID'] = $classesID;
					}
					if((int)$teacherID > 0) {
						$queryArray['teacherID'] = $teacherID;
					}

					$mandatorySubjects               = $this->subject_m->general_get_order_by_subject(array('classesID' => $classesID, 'schoolID' => $schoolID, 'type' => 1));
					$teacherSubjects                 = $this->subject_m->get_order_by_subject_with_subjectteacher(array('classesID' => $classesID, 'type' => 1, 'schoolID' => $schoolID));
					$teachers                        = $this->teacher_m->get_order_by_teacher_with_subjectteacher($queryArray);
					$this->data['classes']           = pluck($this->classes_m->general_get_order_by_classes(array('schoolID' => $schoolID)), 'classes', 'classesID');
					$this->data['grades']            = $this->grade_m->get_order_by_grade(array('schoolID' => $schoolID));
					$this->data['percentageArr']     = pluck($this->markpercentage_m->get_order_by_markpercentage(array('schoolID' => $schoolID)), 'obj', 'markpercentageID');
					$exams                           = $this->exam_m->get_exam_wherein(array('schoolID' => $schoolID, 'examID' => $examID));
					if(customCompute($examID)) {
            $marks                         = $this->mark_m->get_order_by_all_student_mark_with_markrelation2(['schoolID' => $schoolID, 'schoolyearID' => $schoolyearID, 'classesID' => $classesID, 'examID' => $examID]);
          }
					$marks             							 = $this->getMark($marks);
					$this->data['marks']             = $marks;

					$markpercentagesmainArr          = $this->marksetting_m->get_marksetting_markpercentages();
					$markpercentagesArr              = isset($markpercentagesmainArr[$classesID][$examID[0]]) ? $markpercentagesmainArr[$classesID][$examID[0]] : [];
					$settingmarktypeID               = $this->data['siteinfos']->marktypeID;

					$this->data['settingmarktypeID'] = $settingmarktypeID;
					$this->data['markpercentagesArr']= $markpercentagesArr;

					$this->data['classesID']         = $classesID;
					$this->data['teacherID']         = $teacherID;

					reset($markpercentagesArr);
          $firstindex                    = key($markpercentagesArr);
          $uniquepercentageArr           = isset($markpercentagesArr[$firstindex]) ? $markpercentagesArr[$firstindex] : [];
          $markpercentages               = $uniquepercentageArr[(($settingmarktypeID==4) || ($settingmarktypeID==6)) ? 'unique' : 'own'];
					$this->data['markpercentages'] = $markpercentages;

					$teacherPosition             = [];
					$teacherChecker              = [];
					$teacherClassPositionArray   = [];
					$markpercentagesCount        = 0;

					if(customCompute($exams)) {
						foreach ($exams as $exam) {
							if(customCompute($teachers)) {
								foreach ($teachers as $teacher) {
									$opuniquepercentageArr = [];

									$teacherPosition[$exam->examID][$teacher->teacherID]['totalSubjectMark'] = $teacherPosition[$exam->examID][$teacher->teacherID]['totalSubjectStudent'] = 0;
									if(customCompute($mandatorySubjects)) {
										foreach ($mandatorySubjects as $mandatorySubject) {
											$uniquepercentageArr = isset($markpercentagesArr[$mandatorySubject->subjectID]) ? $markpercentagesArr[$mandatorySubject->subjectID] : [];

											$markpercentages = $uniquepercentageArr[(($settingmarktypeID==4) || ($settingmarktypeID==6)) ? 'unique' : 'own'];
											$markpercentagesCount = customCompute($markpercentages);
											if(customCompute($markpercentages)) {
												foreach ($markpercentages as $markpercentageID) {
													$f = false;
													if(isset($uniquepercentageArr['own']) && in_array($markpercentageID, $uniquepercentageArr['own'])) {
															$f = true;
													}

													if(isset($teacherPosition[$exam->examID][$teacher->teacherID]['subjectMark'][$mandatorySubject->subjectID])) {
														if(isset($marks[$exam->examID][$teacher->teacherID]['subjectMark'][$mandatorySubject->subjectID]) && $f) {
															$teacherPosition[$exam->examID][$teacher->teacherID]['subjectMark'][$mandatorySubject->subjectID] += $marks[$exam->examID][$teacher->teacherID]['subjectMark'][$mandatorySubject->subjectID];
														} else {
															$teacherPosition[$exam->examID][$teacher->teacherID]['subjectMark'][$mandatorySubject->subjectID] += 0;
														}
													} else {
														if(isset($marks[$exam->examID][$teacher->teacherID]['subjectMark'][$mandatorySubject->subjectID]) && $f) {
															$teacherPosition[$exam->examID][$teacher->teacherID]['subjectMark'][$mandatorySubject->subjectID] = $marks[$exam->examID][$teacher->teacherID]['subjectMark'][$mandatorySubject->subjectID];
														} else {
															$teacherPosition[$exam->examID][$teacher->teacherID]['subjectMark'][$mandatorySubject->subjectID] = 0;
														}
													}

													if(isset($teacherPosition[$exam->examID][$teacher->teacherID]['subjectStudent'][$mandatorySubject->subjectID])) {
														if(isset($marks[$exam->examID][$teacher->teacherID]['subjectStudent'][$mandatorySubject->subjectID]) && $f) {
															$teacherPosition[$exam->examID][$teacher->teacherID]['subjectStudent'][$mandatorySubject->subjectID] += $marks[$exam->examID][$teacher->teacherID]['subjectStudent'][$mandatorySubject->subjectID];
														} else {
															$teacherPosition[$exam->examID][$teacher->teacherID]['subjectStudent'][$mandatorySubject->subjectID] += 0;
														}
													} else {
														if(isset($marks[$exam->examID][$teacher->teacherID]['subjectStudent'][$mandatorySubject->subjectID]) && $f) {
															$teacherPosition[$exam->examID][$teacher->teacherID]['subjectStudent'][$mandatorySubject->subjectID] = $marks[$exam->examID][$teacher->teacherID]['subjectStudent'][$mandatorySubject->subjectID];
														} else {
															$teacherPosition[$exam->examID][$teacher->teacherID]['subjectStudent'][$mandatorySubject->subjectID] = 0;
														}
													}

													$f = false;
													if(customCompute($opuniquepercentageArr)) {
														if(isset($opuniquepercentageArr['own']) && in_array($markpercentageID, $opuniquepercentageArr['own'])) {
																$f = true;
														}
													}
												}
											}

											$teacherPosition[$exam->examID][$teacher->teacherID]['totalSubjectMark'] += $teacherPosition[$exam->examID][$teacher->teacherID]['subjectMark'][$mandatorySubject->subjectID];
											$teacherPosition[$exam->examID][$teacher->teacherID]['totalSubjectStudent'] += $teacherPosition[$exam->examID][$teacher->teacherID]['subjectStudent'][$mandatorySubject->subjectID];

											if(!isset($teacherChecker['totalSubjectMark'][$exam->examID][$teacher->teacherID])) {
												$teacherChecker['totalSubjectMark'][$exam->examID][$teacher->teacherID] = TRUE;
											}
										}
									}

									if(isset($teacherPosition[$exam->examID][$teacher->teacherID]['totalSubjectMark'])) {
			              if($teacherPosition[$exam->examID][$teacher->teacherID]['totalSubjectStudent'] > 0)
											$teacherPosition[$exam->examID][$teacher->teacherID]['classPositionMark'] = ini_round($teacherPosition[$exam->examID][$teacher->teacherID]['totalSubjectMark'] / ($teacherPosition[$exam->examID][$teacher->teacherID]['totalSubjectStudent']));
										else
											$teacherPosition[$exam->examID][$teacher->teacherID]['classPositionMark'] = 0;
									}

									$teacherClassPositionArray[$exam->examID][$teacher->teacherID]            = $teacherPosition[$exam->examID][$teacher->teacherID]['classPositionMark'];

									if(isset($teacherPosition['totalTeacherMarkAverage'][$exam->examID])) {
										$teacherPosition['totalTeacherMarkAverage'][$exam->examID] += $teacherPosition[$exam->examID][$teacher->teacherID]['classPositionMark'];
									} else {
										$teacherPosition['totalTeacherMarkAverage'][$exam->examID]  = $teacherPosition[$exam->examID][$teacher->teacherID]['classPositionMark'];
									}
								}
							}
						}
					}

					foreach($teacherClassPositionArray as $key=>$array) {
						$teacherClassPositionArray[$key] = $this->sortArray($array);
					}
					$teacherPosition['teacherClassPositionArray'] = $teacherClassPositionArray;

					$this->data['mandatorySubjects'] = $mandatorySubjects;
					$this->data['teacherSubjects']   = $teacherSubjects;
					$this->data['teacherPosition']   = $teacherPosition;
					$this->data['exams']             = $exams;
					$this->data['teachers']          = $teachers;

					$retArray['render'] = $this->load->view('report/teacherexam/TeacherexamReport',$this->data,true);
					$retArray['status'] = TRUE;
					echo json_encode($retArray);
		    	exit;
				}
			} else {
				$retArray['status'] = FALSE;
				echo json_encode($retArray);
		    exit;
			}
		} else {
			$retArray['render'] =  $this->load->view('report/reporterror', $this->data, true);
			$retArray['status'] = TRUE;
			echo json_encode($retArray);
		  exit;
		}
	}

	private function sortArray($array) {
		arsort($array);
		return $array;
	}

  private function getMark($marks) {
		$retMark = [];
		if(customCompute($marks)) {
			foreach ($marks as $mark) {
				if(isset($retMark[$mark->examID][$mark->teacherID]['subjectMark'][$mark->subjectID]))
					$retMark[$mark->examID][$mark->teacherID]['subjectMark'][$mark->subjectID] += $mark->mark;
				else
				  $retMark[$mark->examID][$mark->teacherID]['subjectMark'][$mark->subjectID] = $mark->mark;

				if(isset($retMark[$mark->examID][$mark->teacherID]['subjectStudent'][$mark->subjectID]))
					$retMark[$mark->examID][$mark->teacherID]['subjectStudent'][$mark->subjectID] += 1;
				else
				  $retMark[$mark->examID][$mark->teacherID]['subjectStudent'][$mark->subjectID] = 1;
			}
		}
		return $retMark;
	}

	public function getExam() {
		$classesID = $this->input->post('classesID');
		echo "<option value='0'>", $this->lang->line("teacherexamreport_please_select"),"</option>";
		if((int)$classesID) {
			$schoolID = $this->session->userdata('schoolID');
			$exams    = pluck($this->marksetting_m->get_exam(array('marktypeID' => $this->data['siteinfos']->marktypeID, 'classesID' => $classesID, 'schoolID' => $schoolID)), 'obj', 'examID');
			if(customCompute($exams)) {
				foreach ($exams as $exam) {
					echo "<option value=".$exam->examID.">".$exam->exam."</option>";
				}
			}
		}
	}

	public function getTeacher() {
		$classesID = $this->input->post('classesID');
		$schoolID = $this->session->userdata('schoolID');
		if((int)$classesID) {
			$teachers = $this->teacher_m->get_order_by_teacher_with_subjectteacher(array('classesID' => $classesID, 'schoolID' => $schoolID));
			echo "<option value='0'>". $this->lang->line("teacherexamreport_please_select") . "</option>";
			if(customCompute($teachers)) {
				foreach ($teachers as $teacher) {
					echo "<option value='".$teacher->teacherID."'>".$teacher->name."</option>";
				}
			}
		}
	}

  public function unique_data($data) {
		if($data != "") {
			if($data === "0") {
				$this->form_validation->set_message('unique_data', 'The %s field is required.');
				return FALSE;
			}
		}
		return TRUE;
	}

  public function multiple_select() {
     $arr_exam = $this->input->post('examID[]');
     if(empty($arr_exam)):
        $this->form_validation->set_message('multiple_select', 'Select at least one exam.');
        return false;
     endif;
  }
}
