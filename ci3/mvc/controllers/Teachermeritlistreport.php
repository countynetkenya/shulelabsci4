<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Teachermeritlistreport extends Admin_Controller {

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
		$this->load->model('subjectteacher_m');
		$this->load->model('studentrelation_m');

		$language = $this->session->userdata('lang');
		$this->lang->load('teachermeritlistreport', $language);
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
		$this->data['exams']   = $this->exam_m->get_order_by_exam(array('schoolID' => $schoolID));
		$this->data["subview"] = "report/teachermeritlist/TeachermeritlistReportView";
		$this->load->view('_layout_main', $this->data);
	}

  protected function rules() {
		$rules = array(
			array(
				'field'=>'examID[]',
				'label'=>$this->lang->line('teachermeritlistreport_exam'),
				'rules' => 'trim|required|xss_clean|callback_multiple_select'
			),
			array(
				'field'=>'classesID',
				'label'=>$this->lang->line('teachermeritlistreport_class'),
				'rules' => 'trim|xss_clean|numeric'
			),
			array(
				'field'=>'subjectID',
				'label'=>$this->lang->line('teachermeritlistreport_subject'),
				'rules' => 'trim|xss_clean|numeric'
			)
		);
		return $rules;
	}

  public function getTeachermeritlistReport() {
		$retArray['render'] = '';
		$retArray['status'] = FALSE;
		if(permissionChecker('teachermeritlistreport')) {
			$examID      = $this->input->post('examID[]');
			$classesID   = $this->input->post('classesID');
			$subjectID   = $this->input->post('subjectID');

			if($_POST) {
				$rules = $this->rules();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {
					$retArray = $this->form_validation->error_array();
					$retArray['status'] = FALSE;
			    echo json_encode($retArray);
			    exit;
				} else {
					$schoolID    		 				= $this->session->userdata('schoolID');
					$schoolyearID 					= $this->session->userdata('defaultschoolyearID');
					$queryArray['schoolID'] = $schoolID;
					$subjectQuery 					= array('type' => 1, 'schoolID' => $schoolID);
					$markQuery  						= array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID);
					if((int)$classesID > 0) {
						$queryArray['classesID'] = $classesID;
						$subjectQuery['classesID'] = $classesID;
						$markQuery['classesID'] = $classesID;
					}
					if((int)$teacherID > 0) {
						$queryArray['teacherID'] = $teacherID;
					}
					if((int)$subjectID > 0) {
						$subjectQuery['subjectID'] = $subjectID;
					}

					$mandatorySubjects               = $this->subject_m->general_get_order_by_subject($subjectQuery);
					$teacherSubjects                 = $this->subject_m->get_order_by_subject_with_subjectteacher($subjectQuery);
					$teachers                        = $this->teacher_m->get_order_by_teacher_with_subjectteacher($queryArray);
					$students                        = pluck($this->studentrelation_m->general_get_order_by_student(array('srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID)), 'srclassesID', 'srstudentID');
					$subjectteachers							   = pluck_multi_values($this->subjectteacher_m->get_order_by_subjectteacher(array('schoolID' => $schoolID)), ['subjectID', 'classesID', 'teacherID'], 'subjectteacherID');
					$subjectteacherArray             = [];
					$counts 												 = array_count_values($students);

					foreach($subjectteachers as $subjectteacher) {
						if(isset($subjectteacherArray[$subjectteacher['teacherID']])) {
							if(!in_array($subjectteacher['classesID'], $subjectteacherArray[$subjectteacher['teacherID']]['classes']))
								$subjectteacherArray[$subjectteacher['teacherID']]['classes'][] = $subjectteacher['classesID'];
							if(!in_array($subjectteacher['subjectID'], $subjectteacherArray[$subjectteacher['teacherID']]['subjects']))
								$subjectteacherArray[$subjectteacher['teacherID']]['subjects'][] = $subjectteacher['subjectID'];
						} else {
							$subjectteacherArray[$subjectteacher['teacherID']]['classes'][] = $subjectteacher['classesID'];
							$subjectteacherArray[$subjectteacher['teacherID']]['subjects'][] = $subjectteacher['subjectID'];
						}

						if(in_array($subjectteacher['classesID'], $students)) {
							if(isset($subjectteacherArray[$subjectteacher['teacherID']]['students']))
								$subjectteacherArray[$subjectteacher['teacherID']]['students'] += $counts[$subjectteacher['classesID']];
							else
								$subjectteacherArray[$subjectteacher['teacherID']]['students'] = $counts[$subjectteacher['classesID']];
						}
					}

					$this->data['subjectteacher']    = $subjectteacherArray;
					$this->data['classes']           = pluck($this->classes_m->general_get_order_by_classes(array('schoolID' => $schoolID)), 'classes', 'classesID');
					$this->data['grades']            = $this->grade_m->get_order_by_grade(array('schoolID' => $schoolID));
					$exams                           = $this->exam_m->get_exam_wherein(array('schoolID' => $schoolID, 'examID' => $examID));
					if(customCompute($examID)) {
						$markQuery['examID']           = $examID;
            $marks                         = $this->mark_m->get_order_by_all_student_mark_with_markrelation2($markQuery);
          }
					$marks             							 = $this->getMark($marks);
					$this->data['marks']             = $marks;

					$this->data['classesID']         = $classesID;
					$this->data['examID']            = $examID;

					$teacherPosition             = [];
					$teacherChecker              = [];
					$teacherClassPositionArray   = [];

					if(customCompute($exams)) {
						foreach ($exams as $exam) {
							if(customCompute($teachers)) {
								foreach ($teachers as $teacher) {
									$teacherPosition[$exam->examID][$teacher->teacherID]['totalSubjectMark'] = $teacherPosition[$exam->examID][$teacher->teacherID]['totalSubjectStudent'] = 0;
									if(customCompute($teacherSubjects)) {
										foreach ($teacherSubjects as $teacherSubject) {
											if($teacherSubject->teacherID == $teacher->teacherID) {
												if(isset($teacherPosition[$exam->examID][$teacher->teacherID]['subjectMark'][$teacherSubject->subjectID])) {
													if(isset($marks[$exam->examID][$teacher->teacherID]['subjectMark'][$teacherSubject->subjectID])) {
														$teacherPosition[$exam->examID][$teacher->teacherID]['subjectMark'][$teacherSubject->subjectID] += $marks[$exam->examID][$teacher->teacherID]['subjectMark'][$teacherSubject->subjectID];
													} else {
														$teacherPosition[$exam->examID][$teacher->teacherID]['subjectMark'][$teacherSubject->subjectID] += 0;
													}
												} else {
													if(isset($marks[$exam->examID][$teacher->teacherID]['subjectMark'][$teacherSubject->subjectID])) {
														$teacherPosition[$exam->examID][$teacher->teacherID]['subjectMark'][$teacherSubject->subjectID] = $marks[$exam->examID][$teacher->teacherID]['subjectMark'][$teacherSubject->subjectID];
													} else {
														$teacherPosition[$exam->examID][$teacher->teacherID]['subjectMark'][$teacherSubject->subjectID] = 0;
													}
												}

												if(isset($teacherPosition[$exam->examID][$teacher->teacherID]['subjectStudent'][$teacherSubject->subjectID])) {
													if(isset($marks[$exam->examID][$teacher->teacherID]['subjectStudent'][$teacherSubject->subjectID])) {
														$teacherPosition[$exam->examID][$teacher->teacherID]['subjectStudent'][$teacherSubject->subjectID] += $marks[$exam->examID][$teacher->teacherID]['subjectStudent'][$teacherSubject->subjectID];
													} else {
														$teacherPosition[$exam->examID][$teacher->teacherID]['subjectStudent'][$teacherSubject->subjectID] += 0;
													}
												} else {
													if(isset($marks[$exam->examID][$teacher->teacherID]['subjectStudent'][$teacherSubject->subjectID])) {
														$teacherPosition[$exam->examID][$teacher->teacherID]['subjectStudent'][$teacherSubject->subjectID] = $marks[$exam->examID][$teacher->teacherID]['subjectStudent'][$teacherSubject->subjectID];
													} else {
														$teacherPosition[$exam->examID][$teacher->teacherID]['subjectStudent'][$teacherSubject->subjectID] = 0;
													}
												}

												$teacherPosition[$exam->examID][$teacher->teacherID]['totalSubjectMark'] += $teacherPosition[$exam->examID][$teacher->teacherID]['subjectMark'][$teacherSubject->subjectID];
												$teacherPosition[$exam->examID][$teacher->teacherID]['totalSubjectStudent'] += $teacherPosition[$exam->examID][$teacher->teacherID]['subjectStudent'][$teacherSubject->subjectID];

												if(!isset($teacherChecker['totalSubjectMark'][$exam->examID][$teacher->teacherID])) {
													$teacherChecker['totalSubjectMark'][$exam->examID][$teacher->teacherID] = TRUE;
												}
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
					$this->data['teacherSubjects']   = $teacherSubjects;
					$this->data['teacherPosition']   = $teacherPosition;
					$this->data['exams']             = $exams;
					$this->data['teachers']          = $teachers;

					$retArray['render'] = $this->load->view('report/teachermeritlist/TeachermeritlistReport',$this->data,true);
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
		echo "<option value='0'>", $this->lang->line("teachermeritlistreport_please_select"),"</option>";
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

	public function getSubject() {
		$classesID = $this->input->post('classesID');
		if((int)$classesID) {
			$schoolID = $this->session->userdata('schoolID');
			$subjects = $this->subject_m->get_order_by_subject(array('classesID' => $classesID, 'schoolID' => $schoolID));
			echo "<option value='0'>", $this->lang->line("teachermeritlistreport_please_select"),"</option>";
      if(customCompute($subjects)) {
        foreach ($subjects as $value) {
  				echo "<option value=\"$value->subjectID\">",$value->subject,"</option>";
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
