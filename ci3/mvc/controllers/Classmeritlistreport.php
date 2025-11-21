<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Classmeritlistreport extends Admin_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('exam_m');
		$this->load->model('classes_m');
		$this->load->model('section_m');
		$this->load->model('subject_m');
		$this->load->model('schoolyear_m');
		$this->load->model('studentrelation_m');
		$this->load->model('setting_m');
		$this->load->model('mark_m');
		$this->load->model('grade_m');
		$this->load->model('marksetting_m');
		$this->load->model("studentgroup_m");

		$language = $this->session->userdata('lang');
		$this->lang->load('classmeritlistreport', $language);
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
		$this->data['exams']   = $this->exam_m->get_order_by_exam(array('schoolID' => $schoolID));
		$this->data["subview"] = "report/classmeritlist/ClassmeritlistReportView";
		$this->load->view('_layout_main', $this->data);
	}

  protected function rules() {
		$rules = array(
			array(
				'field'=>'examID',
				'label'=>$this->lang->line('classmeritlistreport_exam'),
				'rules' => 'trim|required|xss_clean|numeric|callback_unique_data'
			),
			/*array(
				'field'=>'subjectID',
				'label'=>$this->lang->line('classmeritlistreport_subject'),
				'rules' => 'trim|xss_clean|numeric'
			)*/
		);
		return $rules;
	}

  public function getClassmeritlistReport() {
		$retArray['render'] = '';
		$retArray['status'] = FALSE;
		if(permissionChecker('classmeritlistreport')) {
			$examID      = $this->input->post('examID');
			$classesID   = $this->input->post('classesID');
			//$subjectID   = $this->input->post('subjectID');

			if($_POST) {
				$rules = $this->rules();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {
					$retArray = $this->form_validation->error_array();
					$retArray['status'] = FALSE;
			    echo json_encode($retArray);
			    exit;
				} else {
					$schoolID     = $this->session->userdata('schoolID');
					$schoolyearID = $this->session->userdata('defaultschoolyearID');
					$classQuery   = array('schoolID' => $schoolID);
					$queryArray   = array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID);
          $subjectQuery = array('type' => 1, 'schoolID' => $schoolID);
					$studentQuery = array('srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID);
					$markQuery    = array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID);
					if((int)$classesID > 0) {
						$queryArray['classesID']    = $classesID;
            $classQuery['classesID']    = $classesID;
            $subjectQuery['classesID']  = $classesID;
						$markQuery['classesID']     = $classesID;
						$studentQuery['classesID']  = $classesID;
					}
          if((int)$subjectID > 0) {
						$subjectQuery['subjectID'] = $subjectID;
					}

					$mandatorySubjects               = $this->subject_m->general_get_order_by_subject($subjectQuery);
					$classes              					 = $this->classes_m->general_get_order_by_classes($classQuery);
					$this->data['classes']           = pluck($this->classes_m->general_get_classes($classQuery), 'classes', 'classesID');
					$students                        = pluck($this->studentrelation_m->general_get_order_by_student($studentQuery), 'srclassesID', 'srstudentID');
					$this->data['students']          = array_count_values($students);
					$subjects                        = pluck($this->subject_m->general_get_subject(array('schoolID' => $schoolID)), 'classesID', 'subjectID');
					$this->data['subjects']          = array_count_values($subjects);
					$this->data['grades']            = $this->grade_m->get_order_by_grade(array('schoolID' => $schoolID));
					$exams                           = $this->exam_m->get_exam_with_previous(array('examID <=' => $examID, 'schoolID' => $schoolID));
					$examIDArray                     = [];
					if(customCompute($exams)) {
						foreach($exams as $exam) {
							$examIDArray[] = $exam->examID;
							if($exam->examID != $examID)
								$preExamID = $exam->examID;
						}
						$this->data['preExamID'] = $preExamID;
						$markQuery['examID']     = $examIDArray;
            $marks                   = $this->mark_m->get_order_by_all_student_mark_with_markrelation2($markQuery);
          }
					$marks                           = $this->getMark($marks);
          $this->data['marks']       			 = $marks;
					$this->data['settingmarktypeID'] = $settingmarktypeID;
					$this->data['classesID']         = $classesID;
					$this->data['examID']            = $examID;

					$this->data['settingmarktypeID'] = $settingmarktypeID;
					$markpercentagesmainArr			 		 = $this->marksetting_m->get_marksetting_markpercentages();

					$classPosition                 = [];
					$classChecker                  = [];
					$classPositionArray            = [];
					$classSubjectPositionArray     = [];

					if(customCompute($exams)) {
						foreach ($exams as $exam) {
							if(customCompute($classes)) {
								foreach ($classes as $class) {
									$markpercentagesArr     				 = isset($markpercentagesmainArr[$class->classesID][$examID]) ? $markpercentagesmainArr[$class->classesID][$examID] : [];
									$classPosition[$exam->examID][$class->classesID]['totalSubjectMark'] = 0;
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

													if(isset($classPosition[$exam->examID][$class->classesID]['subjectMark'][$mandatorySubject->subjectID])) {
														if(isset($marks[$exam->examID][$class->classesID][$mandatorySubject->subjectID][$markpercentageID])) {
															$classPosition[$exam->examID][$class->classesID]['subjectMark'][$mandatorySubject->subjectID] += $marks[$exam->examID][$class->classesID][$mandatorySubject->subjectID][$markpercentageID];
														} else {
															$classPosition[$exam->examID][$class->classesID]['subjectMark'][$mandatorySubject->subjectID] += 0;
														}
													} else {
														if(isset($marks[$exam->examID][$class->classesID][$mandatorySubject->subjectID][$markpercentageID])) {
															$classPosition[$exam->examID][$class->classesID]['subjectMark'][$mandatorySubject->subjectID] = $marks[$exam->examID][$class->classesID][$mandatorySubject->subjectID][$markpercentageID];
														} else {
															$classPosition[$exam->examID][$class->classesID]['subjectMark'][$mandatorySubject->subjectID] = 0;
														}
													}

													if(isset($marks[$exam->examID][$class->classesID][$mandatorySubject->subjectID][$markpercentageID])) {
														$classPosition[$exam->examID][$class->classesID]['markpercentageMark'][$mandatorySubject->subjectID] = $marks[$exam->examID][$class->classesID][$mandatorySubject->subjectID][$markpercentageID];

														if(isset($classPosition[$exam->examID][$class->classesID]['markpercentagetotalmark'])) {
															$classPosition[$exam->examID][$class->classesID]['markpercentagetotalmark'] += $classPosition[$exam->examID][$class->classesID]['markpercentageMark'][$mandatorySubject->subjectID];
														} else {
															$classPosition[$exam->examID][$class->classesID]['markpercentagetotalmark'] = $classPosition[$exam->examID][$class->classesID]['markpercentageMark'][$mandatorySubject->subjectID];
														}
													}

													$classPosition[$exam->examID][$class->classesID]['totalSubjectMark'] += $classPosition[$exam->examID][$class->classesID]['subjectMark'][$mandatorySubject->subjectID];

													if(!isset($classChecker['totalSubjectMark'][$exam->examID][$class->classesID])) {
														$classChecker['totalSubjectMark'][$exam->examID][$class->classesID] = TRUE;
													}

													$classSubjectPositionArray[$mandatorySubject->subjectID][$exam->examID][$class->classesID] = $classPosition[$exam->examID][$class->classesID]['subjectMark'][$mandatorySubject->subjectID];
												}
											}
										}
									}

									try {
											$classPosition[$exam->examID][$class->classesID]['classPositionMark']       = ($classPosition[$exam->examID][$class->classesID]['totalSubjectMark'] / customCompute($classPosition[$exam->examID][$class->classesID]['subjectMark']));
									} catch (DivisionByZeroError $e) {
    									$classPosition[$exam->examID][$class->classesID]['classPositionMark'] = 0;
									}
									$classPositionArray[$exam->examID][$class->classesID]                       = $classPosition[$exam->examID][$class->classesID]['classPositionMark'];

									if(isset($classPosition['totalClassMarkAverage'][$exam->examID])) {
										$classPosition['totalClassMarkAverage'][$exam->examID] += $classPosition[$exam->examID][$class->classesID]['classPositionMark'];
									} else {
										$classPosition['totalClassMarkAverage'][$exam->examID]  = $classPosition[$exam->examID][$class->classesID]['classPositionMark'];
									}
								}
							}
						}
					}

					foreach($classPositionArray as $key=>$array) {
						$classPositionArray[$key] = $this->sortArray($array);
					}
					$classPosition['classPositionArray'] = $classPositionArray;
					if(customCompute($classSubjectPositionArray)) {
						foreach($classSubjectPositionArray as $subjectID => $classSubjectPositionMark) {
							arsort($classSubjectPositionMark);
							$classPosition['classSubjectPositionMark'][$subjectID] = $classSubjectPositionMark;
						}
					}

					$this->data['mandatorysubjects'] = $mandatorySubjects;
					$this->data['classPosition']     = $classPosition;
					$this->data['exams']             = $exams;
					$this->data['classesList']       = $classes;

					$retArray['render'] = $this->load->view('report/classmeritlist/ClassmeritlistReport',$this->data,true);
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
				$retMark[$mark->examID][$mark->classesID][$mark->subjectID][$mark->markpercentageID] = $mark->mark;
			}
		}
		return $retMark;
	}

	public function getExam() {
		$classesID = $this->input->post('classesID');
		echo "<option value='0'>", $this->lang->line("classmeritlistreport_please_select"),"</option>";
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

  /*public function getSubject() {
		$classesID = $this->input->post('classesID');
		if((int)$classesID) {
			$subjects = $this->subject_m->get_order_by_subject(array("classes.classesID" => $classesID));
			echo "<option value='0'>", $this->lang->line("studentmeritlistreport_please_select"),"</option>";
      if(customCompute($subjects)) {
        foreach ($subjects as $value) {
  				echo "<option value=\"$value->subjectID\">",$value->subject,"</option>";
  			}
      }
		}
	}*/

  public function unique_data($data) {
		if($data != "") {
			if($data === "0") {
				$this->form_validation->set_message('unique_data', 'The %s field is required.');
				return FALSE;
			}
		}
		return TRUE;
	}
}
