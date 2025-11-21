<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Studentexamreport extends Admin_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('exam_m');
		$this->load->model('classes_m');
		$this->load->model('section_m');
		$this->load->model('subject_m');
		$this->load->model('schoolyear_m');
		$this->load->model('studentrelation_m');
		$this->load->model('markpercentage_m');
		$this->load->model('setting_m');
		$this->load->model('mark_m');
		$this->load->model('grade_m');
		$this->load->model('marksetting_m');
		$this->load->model("studentgroup_m");

		$language = $this->session->userdata('lang');
		$this->lang->load('studentexamreport', $language);
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

		$schoolID                  = $this->session->userdata('schoolID');
		$this->data['schoolyears'] = $this->schoolyear_m->get_order_by_schoolyear(array('schoolID' => $schoolID));
		$this->data['classes']     = $this->classes_m->general_get_order_by_classes(array('schoolID' => $schoolID));
		$this->data["subview"]     = "report/studentexam/StudentexamReportView";
		$this->load->view('_layout_main', $this->data);
	}

  protected function rules() {
		$rules = array(
			array(
				'field'=>'examID[]',
				'label'=>$this->lang->line('studentexamreport_exam'),
				'rules' => 'trim|required|xss_clean|callback_multiple_select'
			),
			array(
				'field'=>'classesID',
				'label'=>$this->lang->line('studentexamreport_class'),
				'rules' => 'trim|required|xss_clean|numeric|callback_unique_data'
			),
			array(
				'field'=>'sectionID',
				'label'=>$this->lang->line('studentexamreport_section'),
				'rules' => 'trim|xss_clean|numeric'
			),
			array(
				'field'=>'studentID',
				'label'=>$this->lang->line('studentexamreport_student'),
				'rules' => 'trim|xss_clean|numeric'
			)
		);
		return $rules;
	}

  public function getStudentexamReport() {
		$retArray['render'] = '';
		$retArray['status'] = FALSE;
		if(permissionChecker('studentexamreport')) {
			$examID      = $this->input->post('examID[]');
			$schoolyearID= $this->input->post('schoolyearID');
			$classesID   = $this->input->post('classesID');
			$sectionID   = $this->input->post('sectionID');
			$studentID   = $this->input->post('studentID');

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
					$studentQuery['srschoolyearID']  = $schoolyearID;
					$studentQuery['srschoolID']      = $schoolID;
					$markQuery['schoolyearID']       = $schoolyearID;
					$markQuery['schoolID']           = $schoolID;
					$subjectQuery['schoolID']        = $schoolID;
					$subjectQuery['type']            = 1;
					if($classesID > 0) {
						$studentQuery['srclassesID']   = $classesID;
						$markQuery['classesID']        = $classesID;
						$subjectQuery['classesID']     = $classesID;
					}
					if($sectionID > 0) {
						$studentQuery['srsectionID']   = $sectionID;
					}
					if((int)$studentID > 0) {
						$studentQuery['srstudentID']   = $studentID;
					}

					$mandatorySubjects               = $this->subject_m->general_get_order_by_subject($subjectQuery);
					$students                        = $this->studentrelation_m->general_get_order_by_student($studentQuery);
					$this->data['classes']           = pluck($this->classes_m->general_get_order_by_classes(array('schoolID' => $schoolID)), 'classes', 'classesID');
					$this->data['sections']          = pluck($this->section_m->general_get_order_by_section(array('schoolID' => $schoolID)), 'section', 'sectionID');
					$this->data['groups']            = pluck($this->studentgroup_m->get_order_by_studentgroup(array('schoolID' => $schoolID)),'group','studentgroupID');
					$this->data['grades']            = $this->grade_m->get_order_by_grade(array('schoolID' => $schoolID));
					$this->data['percentageArr']     = pluck($this->markpercentage_m->get_order_by_markpercentage(array('schoolID' => $schoolID)), 'obj', 'markpercentageID');
					$exams                           = $this->exam_m->get_exam_wherein(array('schoolID' => $schoolID, 'examID' => $examID));
					if(customCompute($examID)) {
						$markQuery['examID']           = $examID;
            $marks                         = $this->mark_m->get_order_by_all_student_mark_with_markrelation2($markQuery);
          }
					$marks                           = $this->getMark($marks);
          $this->data['marks']             = $marks;

					$markpercentagesmainArr          = $this->marksetting_m->get_marksetting_markpercentages();
					$markpercentagesArr              = isset($markpercentagesmainArr[$classesID][$examID[0]]) ? $markpercentagesmainArr[$classesID][$examID[0]] : [];
					$settingmarktypeID               = $this->data['siteinfos']->marktypeID;

					$this->data['settingmarktypeID'] = $settingmarktypeID;
					$this->data['markpercentagesArr']= $markpercentagesArr;

					$this->data['classesID']       = $classesID;
					$this->data['sectionID']       = $sectionID;

					reset($markpercentagesArr);
          $firstindex                    = key($markpercentagesArr);
          $uniquepercentageArr           = isset($markpercentagesArr[$firstindex]) ? $markpercentagesArr[$firstindex] : [];
          $markpercentages               = $uniquepercentageArr[(($settingmarktypeID==4) || ($settingmarktypeID==6)) ? 'unique' : 'own'];
					$this->data['markpercentages'] = $markpercentages;

					$studentPosition             = [];
					$studentChecker              = [];
					$studentClassPositionArray   = [];
					$studentSubjectPositionArray = [];
					$markpercentagesCount        = 0;

					if(customCompute($exams)) {
						foreach ($exams as $exam) {
							if(customCompute($students)) {
								foreach ($students as $student) {
									$opuniquepercentageArr = [];
									if($student->sroptionalsubjectID > 0) {
										$opuniquepercentageArr = isset($markpercentagesArr[$student->sroptionalsubjectID]) ? $markpercentagesArr[$student->sroptionalsubjectID] : [];
									}

									$studentPosition[$exam->examID][$student->srstudentID]['totalSubjectMark'] = 0;
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

													if(isset($studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID])) {
														if(isset($marks[$exam->examID][$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID]) && $f) {
															$studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID] += $marks[$exam->examID][$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID];
														} else {
															$studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID] += 0;
														}
													} else {
														if(isset($marks[$exam->examID][$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID]) && $f) {
															$studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID] = $marks[$exam->examID][$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID];
														} else {
															$studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID] = 0;
														}
													}

													if(isset($marks[$exam->examID][$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID]) && $f) {
														$studentPosition[$exam->examID][$student->srstudentID]['markpercentageMark'][$mandatorySubject->subjectID][$markpercentageID] = $marks[$exam->examID][$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID];

														if(isset($studentPosition[$exam->examID][$student->srstudentID]['markpercentagetotalmark'][$markpercentageID])) {
															$studentPosition[$exam->examID][$student->srstudentID]['markpercentagetotalmark'][$markpercentageID] += $studentPosition[$exam->examID][$student->srstudentID]['markpercentageMark'][$mandatorySubject->subjectID][$markpercentageID];
														} else {
															$studentPosition[$exam->examID][$student->srstudentID]['markpercentagetotalmark'][$markpercentageID] = $studentPosition[$exam->examID][$student->srstudentID]['markpercentageMark'][$mandatorySubject->subjectID][$markpercentageID];
														}
													}

													$f = false;
													if(customCompute($opuniquepercentageArr)) {
														if(isset($opuniquepercentageArr['own']) && in_array($markpercentageID, $opuniquepercentageArr['own'])) {
																$f = true;
														}
													}

													if(!isset($studentChecker['subject'][$exam->examID][$student->srstudentID][$markpercentageID]) && $f) {
														if($student->sroptionalsubjectID != 0) {
															if(isset($studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID])) {
																if(isset($marks[$exam->examID][$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID])) {
																	$studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID] += $marks[$exam->examID][$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID];
																} else {
																	$studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID] += 0;
																}
															} else {
																if(isset($marks[$exam->examID][$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID])) {
																	$studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID] = $marks[$exam->examID][$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID];
																} else {
																	$studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID] = 0;
																}
															}

															if(isset($marks[$exam->examID][$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID])) {
																$studentPosition[$exam->examID][$student->srstudentID]['markpercentageMark'][$student->sroptionalsubjectID][$markpercentageID] = $marks[$exam->examID][$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID];

																if(isset($studentPosition[$exam->examID][$student->srstudentID]['markpercentagetotalmark'][$markpercentageID])) {
																	$studentPosition[$exam->examID][$student->srstudentID]['markpercentagetotalmark'][$markpercentageID] += $studentPosition[$exam->examID][$student->srstudentID]['markpercentageMark'][$student->sroptionalsubjectID][$markpercentageID];
																} else {
																	if($f) {
																		$studentPosition[$exam->examID][$student->srstudentID]['markpercentagetotalmark'][$markpercentageID] = $studentPosition[$exam->examID][$student->srstudentID]['markpercentageMark'][$student->sroptionalsubjectID][$markpercentageID];
																	}
																}

															}
														}
														$studentChecker['subject'][$exam->examID][$student->srstudentID][$markpercentageID] = TRUE;
													}
												}
											}

											$studentPosition[$exam->examID][$student->srstudentID]['totalSubjectMark'] += $studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID];

											if(!isset($studentChecker['totalSubjectMark'][$exam->examID][$student->srstudentID])) {
												if($student->sroptionalsubjectID != 0) {
													$studentPosition[$exam->examID][$student->srstudentID]['totalSubjectMark'] += $studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID];
												}
												$studentChecker['totalSubjectMark'][$exam->examID][$student->srstudentID] = TRUE;
											}

											$studentSubjectPositionArray[$mandatorySubject->subjectID][$exam->examID][$student->srstudentID] = $studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID];
											if(!isset($studentChecker['studentSubjectPositionArray'][$exam->examID][$student->srstudentID])) {
												if($student->sroptionalsubjectID != 0) {
													$studentSubjectPositionArray[$student->sroptionalsubjectID][$exam->examID][$student->srstudentID] = $studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID];
												}
											}
										}
									}

									$studentPosition[$exam->examID][$student->srstudentID]['classPositionMark'] = ($studentPosition[$exam->examID][$student->srstudentID]['totalSubjectMark'] / customCompute($studentPosition[$exam->examID][$student->srstudentID]['subjectMark']));
									$studentClassPositionArray[$exam->examID][$student->srstudentID]            = $studentPosition[$exam->examID][$student->srstudentID]['classPositionMark'];

									if(isset($studentPosition['totalStudentMarkAverage'][$exam->examID])) {
										$studentPosition['totalStudentMarkAverage'][$exam->examID] += $studentPosition[$exam->examID][$student->srstudentID]['classPositionMark'];
									} else {
										$studentPosition['totalStudentMarkAverage'][$exam->examID]  = $studentPosition[$exam->examID][$student->srstudentID]['classPositionMark'];
									}
								}
							}
						}
					}

					foreach($studentClassPositionArray as $key=>$array) {
						$studentClassPositionArray[$key] = $this->sortArray($array);
					}
					$studentPosition['studentClassPositionArray'] = $studentClassPositionArray;
					if(customCompute($studentSubjectPositionArray)) {
						foreach($studentSubjectPositionArray as $subjectID => $studentSubjectPositionMark) {
							arsort($studentSubjectPositionMark);
							$studentPosition['studentSubjectPositionMark'][$subjectID] = $studentSubjectPositionMark;
						}
					}

					$this->data['mandatorysubjects'] = $mandatorySubjects;
					$this->data['studentPosition']   = $studentPosition;
					$this->data['exams']             = $exams;
					$this->data['students']          = $students;

					$retArray['render'] = $this->load->view('report/studentexam/StudentexamReport',$this->data,true);
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
				$retMark[$mark->examID][$mark->studentID][$mark->subjectID][$mark->markpercentageID] = $mark->mark;
			}
		}
		return $retMark;
	}

	public function getExam() {
		$classesID = $this->input->post('classesID');
		echo "<option value='0'>", $this->lang->line("studentexamreport_please_select"),"</option>";
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

	public function getSection() {
		$classesID = $this->input->post('classesID');
		if((int)$classesID) {
			$schoolID = $this->session->userdata('schoolID');
			$sections = $this->section_m->general_get_order_by_section(array('classesID' => $classesID, 'schoolID' => $schoolID));
			echo "<option value='0'>". $this->lang->line("studentexamreport_please_select") . "</option>";
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
		$schoolyearID = $this->session->userdata('defaultschoolyearID');
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
		echo "<option value='0'>". $this->lang->line("studentexamreport_please_select") . "</option>";
		if(customCompute($students)) {
			foreach ($students as $student) {
				echo "<option value='".$student->srstudentID."'>".$student->srname."</option>";
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
