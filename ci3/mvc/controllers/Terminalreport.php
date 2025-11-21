<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Terminalreport extends Admin_Controller {
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
		$this->load->model("examcompilation_m");
		$this->load->model("examcompilation_exams_m");
		$this->load->model("examranking_m");
		$this->load->model("markpercentage_m");
		$this->load->model("subject_m");
		$this->load->model("setting_m");
		$this->load->model("mark_m");
		$this->load->model("grade_m");
		$this->load->model("studentrelation_m");
		$this->load->model("sattendance_m");
		$this->load->model("subjectattendance_m");
		$this->load->model("studentgroup_m");
		$this->load->model("marksetting_m");
		$this->load->model("remark_m");

		$language = $this->session->userdata('lang');
		$this->lang->load('terminalreport', $language);
	}

	protected function rules() {
		$rules = array(
			array(
				'field' => 'examID',
				'label' => $this->lang->line("terminalreport_exam"),
				'rules' => 'trim|required|xss_clean|callback_unique_data'
			),
			array(
				'field' => 'classesID',
				'label' => $this->lang->line("terminalreport_class"),
				'rules' => 'trim|required|xss_clean|callback_unique_data'
			),
			array(
				'field' => 'sectionID',
				'label' => $this->lang->line("terminalreport_section"),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field' => 'studentID',
				'label' => $this->lang->line("terminalreport_student"),
				'rules' => 'trim|xss_clean'
			),
		);
		return $rules;
	}

	protected function send_pdf_to_mail_rules() {
		$rules = array(
			array(
				'field' => 'examID',
				'label' => $this->lang->line("terminalreport_exam"),
				'rules' => 'trim|required|xss_clean|callback_unique_data'
			),
			array(
				'field' => 'classesID',
				'label' => $this->lang->line("terminalreport_class"),
				'rules' => 'trim|required|xss_clean|callback_unique_data'
			),
			array(
				'field' => 'sectionID',
				'label' => $this->lang->line("terminalreport_section"),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field' => 'studentID',
				'label' => $this->lang->line("terminalreport_student"),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field' => 'to',
				'label' => $this->lang->line("terminalreport_to"),
				'rules' => 'trim|required|xss_clean|valid_email'
			),
			array(
				'field' => 'subject',
				'label' => $this->lang->line("terminalreport_subject"),
				'rules' => 'trim|required|xss_clean'
			),
			array(
				'field' => 'message',
				'label' => $this->lang->line("terminalreport_message"),
				'rules' => 'trim|xss_clean'
			),
		);
		return $rules;
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

		$schoolID 		                   = $this->session->userdata('schoolID');
		$settingmarktypeID               = $this->data['siteinfos']->marktypeID;
		$this->data['exams']             = $this->marksetting_m->get_exam(array('marktypeID' => $this->data['siteinfos']->marktypeID, 'schoolID' => $schoolID));
		$this->data['examcompilations']  = $this->examcompilation_m->get_order_by_examcompilation(array('schoolID' => $schoolID));
		$this->data['classes']           = $this->classes_m->general_get_order_by_classes(array('schoolID' => $schoolID));
		$this->data['settingmarktypeID'] = $settingmarktypeID;

		$this->data["subview"]           = "report/terminal/TerminalReportView";
		$this->load->view('_layout_main', $this->data);
	}

	public function getTerminalreport() {
		$retArray['status'] = FALSE;
		$retArray['render'] = '';
		if(permissionChecker('terminalreport')) {
			if($_POST) {
				$examID                = $this->input->post('examID');
				$examcompilationID     = $this->input->post('examcompilationID');
				$examcompilation_exams = [];
				if($examcompilationID > 0) {
					$examcompilation_exams = $this->examcompilation_exams_m->get_order_by_examcompilation_exams(array('examcompilationID' => $examcompilationID));
					if(customCompute($examcompilation_exams)) {
						foreach($examcompilation_exams as $examcompilation_exam) {
							$examID = $examcompilation_exam->examID;
						}
					}
				}
				$classesID     = $this->input->post('classesID');
				$sectionID     = $this->input->post('sectionID');
				$studentID     = $this->input->post('studentID');
				$schoolyearID  = $this->session->userdata('defaultschoolyearID');
				$schoolID      = $this->session->userdata('schoolID');
				$examrankingID = $this->input->post('examrankingID');

				$rules = $this->rules();
				$this->form_validation->set_rules($rules);
				if($this->form_validation->run() == FALSE) {
					$retArray = $this->form_validation->error_array();
					$retArray['status'] = FALSE;
			    echo json_encode($retArray);
			    exit;
				} else {
					$this->data['examID']            = $examID;
					$this->data['examcompilationID'] = $examcompilationID;
					$this->data['classesID']         = $classesID;
					$this->data['sectionID']         = $sectionID;
					$this->data['studentIDD']        = $studentID;
					$this->data['examrankingID']     = $examrankingID;

					$queryArray        = array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID);
					$studentQueryArray = array('srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID);

					if((int)$classesID > 0) {
						$queryArray['classesID'] = $classesID;
						$studentQueryArray['srclassesID'] = $classesID;
					}
					if((int)$sectionID > 0) {
						$queryArray['sectionID'] = $sectionID;
						$studentQueryArray['srsectionID'] = $sectionID;
					}
					if((int)$studentID > 0) {
						$studentQueryArray['srstudentID'] = $studentID;
					}

					$this->data['grades']       			= $this->grade_m->get_order_by_grade(['schoolID' => $schoolID]);
					$this->data['classes']      			= pluck($this->classes_m->general_get_order_by_classes(['schoolID' => $schoolID]),'classes','classesID');
					$this->data['sections']     			= pluck($this->section_m->general_get_order_by_section(['schoolID' => $schoolID]),'section','sectionID');
					$this->data['groups']       			= pluck($this->studentgroup_m->get_order_by_studentgroup(['schoolID' => $schoolID]),'group','studentgroupID');
					$this->data['studentLists'] 			= $this->studentrelation_m->general_get_order_by_student($studentQueryArray);
					$students                         = $this->studentrelation_m->general_get_order_by_student(array('srclassesID' => $classesID, 'srschoolyearID' => $schoolyearID));
					$subjects                					= $this->subject_m->get_order_by_subject_with_subjectteacher(array('classesID' => $classesID, 'schoolID' => $schoolID));
					$subjecttype                			= pluck($subjects,'type','subjectID');
					$this->data['subjects'] 					= $subjects;

					$exam      												= $this->exam_m->get_single_exam(['examID'=> $examID, 'schoolID' => $schoolID]);
					$examcompilation      						= $this->examcompilation_m->get_single_examcompilation(['examcompilationID'=> $examcompilationID, 'schoolID' => $schoolID]);
					$this->data['examName']     			= $exam->exam;
					if($examcompilationID > 0)
						$this->data['examName']         = $examcompilation->examcompilation;
					$exams                      			= $this->exam_m->get_last_six_exams(array('classesID' => $classesID, 'examID <=' => $examID, 'schoolID' => $schoolID));
					$examIDArray 							  			= [];
					if(customCompute($exams)) {
						foreach($exams as $exam)
							$examIDArray[] 								= $exam->examID;
						$queryArray['examID']           = $examIDArray;
            $marks                    			= $this->mark_m->get_order_by_all_student_mark_with_markrelation2($queryArray);
          }
					$settingmarktypeID      					= $this->data['siteinfos']->marktypeID;
					$markpercentagesmainArr			 			= $this->marksetting_m->get_marksetting_markpercentages();
					$markpercentagesArr     				  = isset($markpercentagesmainArr[$classesID][$examID]) ? $markpercentagesmainArr[$classesID][$examID] : [];
					$compareStudentPosition           = 0;
					$examIDArray                      = [];
					if($examcompilationID > 0) {
						$marks                  			  = $this->getMarkWithExam($marks, $examcompilationID);
						if($examcompilation->compare_examID != NULL) {
							$examIDArray[] 				  = $examcompilation->compare_examID;
							$queryArray['examID']   = $examIDArray;
							$compareStudentPosition = $this->getStudentPosition($queryArray, $students, $subjects, $markpercentagesArr);
						}
						if($examcompilation->compare_examcompilationID != NULL) {
							if(customCompute($examcompilation_exams)) {
								foreach($examcompilation_exams as $examcompilation_exam) {
									$examIDArray[] = $examcompilation_exam->examID;
								}
								$queryArray['examID']   = $examIDArray;
								$compareStudentPosition = $this->getStudentPosition($queryArray, $students, $subjects, $markpercentagesArr);
							}
						}
					} else
						$marks                  			      = $this->getMarkWithExam($marks);
					$this->data['marks']    					    = $marks;
					$this->data['compareStudentPosition'] = $compareStudentPosition;

					$this->data['outof']    					    = count($students);
					$mandatorySubjects      					    = $this->subject_m->general_get_order_by_subject(array('classesID' => $classesID, 'type' => 1, 'schoolID' => $schoolID));

					$this->data['markpercentagesArr']     = $markpercentagesArr;
					$this->data['settingmarktypeID']      = $settingmarktypeID;
					$remarks                              = $this->remark_m->get_order_by_remark(['examID' => $examID, 'schoolID' => $schoolID]);
          $this->data['remarks']                = $this->getRemark($remarks);
					$examranking                          = $this->examranking_m->get_examranking($examrankingID);
					$rankingSubjects                      = [];
					if(customCompute($examranking)) {
						$rankingSubjects                    = explode(",", $examranking->subjects);
					}

					$studentPosition             			= [];
					$studentChecker              			= [];
					$studentClassPositionArray   			= [];
					$studentSubjectPositionArray 			= [];
					$studentSubjectRanked             = [];
					$markpercentagesCount        			= 0;
					if(customCompute($exams)) {
						foreach($exams as $exam) {
							if(customCompute($students)) {
								foreach($students as $student) {
									/*$opuniquepercentageArr = [];
									if($student->sroptionalsubjectID > 0) {
										$opuniquepercentageArr = isset($markpercentagesArr[$student->sroptionalsubjectID]) ? $markpercentagesArr[$student->sroptionalsubjectID] : [];
									}*/

									if($student->sroptionalsubjectID != NULL) {
										$optionalSubjects = explode(",", $student->sroptionalsubjectID);
									}

									if($student->srnonexaminablesubjectID != NULL) {
										$nonexaminableSubjects = explode(",", $student->srnonexaminablesubjectID);
									}

									$studentPosition[$exam->examID][$student->srstudentID]['totalSubjectMark'] = 0;
									if(customCompute($subjects)) {
										foreach ($subjects as $subject) {
											if($subject->type == 1 || (isset($optionalSubjects) && in_array($subject->subjectID, $optionalSubjects)) || (isset($nonexaminableSubjects) && in_array($subject->subjectID, $nonexaminableSubjects))) {
												$uniquepercentageArr = isset($markpercentagesArr[$subject->subjectID]) ? $markpercentagesArr[$subject->subjectID] : [];

												$markpercentages = $uniquepercentageArr[(($settingmarktypeID==4) || ($settingmarktypeID==6)) ? 'unique' : 'own'];
												$markpercentagesCount = customCompute($markpercentages);
												if(customCompute($markpercentages)) {
													foreach ($markpercentages as $markpercentageID) {
														$f = false;
	                          if(isset($uniquepercentageArr['own']) && in_array($markpercentageID, $uniquepercentageArr['own'])) {
	                              $f = true;
	                          }

														if(isset($studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$subject->subjectID])) {
															if(isset($marks[$exam->examID][$student->srstudentID][$subject->subjectID][$markpercentageID]) && $f) {
																$studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$subject->subjectID] += $marks[$exam->examID][$student->srstudentID][$subject->subjectID][$markpercentageID];
															} else {
																$studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$subject->subjectID] += 0;
															}
														} else {
															if(isset($marks[$exam->examID][$student->srstudentID][$subject->subjectID][$markpercentageID]) && $f) {
																$studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$subject->subjectID] = $marks[$exam->examID][$student->srstudentID][$subject->subjectID][$markpercentageID];
															} else {
																$studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$subject->subjectID] = 0;
															}
														}

														if($examrankingID == 0 || ($examrankingID == "mandatory" && $subject->type == 1) || ($examrankingID == "optional" && $subject->type == 0) || ($examrankingID == "nonexaminable" && $subject->type == 2) || ($examrankingID == "mandatory_and_optional" && ($subject->type == 1 || $subject->type == 0)) || (customCompute($rankingSubjects) && in_array($subject->subjectID, $rankingSubjects))) {
															if(isset($studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$subject->subjectID])) {
																if(isset($marks[$exam->examID][$student->srstudentID][$subject->subjectID][$markpercentageID]) && $f) {
																	$studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$subject->subjectID] += $marks[$exam->examID][$student->srstudentID][$subject->subjectID][$markpercentageID];
																} else {
																	$studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$subject->subjectID] += 0;
																}
															} else {
																if(isset($marks[$exam->examID][$student->srstudentID][$subject->subjectID][$markpercentageID]) && $f) {
																	$studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$subject->subjectID] = $marks[$exam->examID][$student->srstudentID][$subject->subjectID][$markpercentageID];
																} else {
																	$studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$subject->subjectID] = 0;
																}
															}

															if($examrankingID == "mandatory_and_optional") {
																$optionalTop = [];
																if(count($studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark']) > 4) {
																	foreach($studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'] as $key => $value) {
																		if($subjecttype[$key] == 0) {
																			$optionalTop[$key] = $studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$key];
																			unset($studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$key]);
																		}
																	}
																	if(count($optionalTop) >= 4) {
																		$optionalTop = $this->max($optionalTop, 4);
																	}
																	foreach($optionalTop as $key => $value) {
																		$studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$key] = $value;
																	}
																}
															}

															if((customCompute($rankingSubjects) && in_array($subject->subjectID, $rankingSubjects))) {
																$mandatoryTop = $optionalTop = $nonexaminableTop = [];
																foreach($studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'] as $key => $value) {
																	if($subjecttype[$key] == 1) {
																		$mandatoryTop[$key] = $studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$key];
																		unset($studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$key]);
																	}
																	elseif($subjecttype[$key] == 0) {
																		$optionalTop[$key] = $studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$key];
																		unset($studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$key]);
																	}
																	elseif($subjecttype[$key] == 2) {
																		$nonexaminableTop[$key] = $studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$key];
																		unset($studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$key]);
																	}
																}
																if(count($mandatoryTop) >= $examranking->mandatory_top && $examranking->mandatory_top > 0) {
																	$mandatoryTop = $this->max($mandatoryTop, $examranking->mandatory_top);
																}
																foreach($mandatoryTop as $key => $value) {
																	$studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$key] = $value;
																}
																if(count($optionalTop) >= $examranking->optional_top && $examranking->optional_top > 0) {
																	$optionalTop = $this->max($optionalTop, $examranking->optional_top);
																}
																foreach($optionalTop as $key => $value) {
																	$studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$key] = $value;
																}
																if(count($nonexaminableTop) >= $examranking->nonexaminable_top && $examranking->nonexaminable_top > 0) {
																	$nonexaminableTop = $this->max($nonexaminableTop, $examranking->nonexaminable_top);
																}
																foreach($nonexaminableTop as $key => $value) {
																	$studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$key] = $value;
																}
															}
														}

														if(isset($marks[$exam->examID][$student->srstudentID][$subject->subjectID][$markpercentageID]) && $f) {
															$studentPosition[$exam->examID][$student->srstudentID]['markpercentageMark'][$subject->subjectID][$markpercentageID] = $marks[$exam->examID][$student->srstudentID][$subject->subjectID][$markpercentageID];

															if(isset($studentPosition[$exam->examID][$student->srstudentID]['markpercentagetotalmark'][$markpercentageID])) {
																$studentPosition[$exam->examID][$student->srstudentID]['markpercentagetotalmark'][$markpercentageID] += $studentPosition[$exam->examID][$student->srstudentID]['markpercentageMark'][$subject->subjectID][$markpercentageID];
															} else {
																$studentPosition[$exam->examID][$student->srstudentID]['markpercentagetotalmark'][$markpercentageID] = $studentPosition[$exam->examID][$student->srstudentID]['markpercentageMark'][$subject->subjectID][$markpercentageID];
															}
														}

														/*$f = false;
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

																if(!customCompute($rankingSubjects) || in_array($student->sroptionalsubjectID, $rankingSubjects)) {
																	if(isset($studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$student->sroptionalsubjectID])) {
																		if(isset($marks[$exam->examID][$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID])) {
																			$studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$student->sroptionalsubjectID] += $marks[$exam->examID][$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID];
																		} else {
																			$studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$student->sroptionalsubjectID] += 0;
																		}
																	} else {
																		if(isset($marks[$exam->examID][$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID])) {
																			$studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$student->sroptionalsubjectID] = $marks[$exam->examID][$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID];
																		} else {
																			$studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$student->sroptionalsubjectID] = 0;
																		}
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
														}*/
													}
												}

												$studentPosition[$exam->examID][$student->srstudentID]['totalSubjectMark'] += $studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$subject->subjectID];

												if(!isset($studentChecker['totalSubjectMark'][$exam->examID][$student->srstudentID])) {
													/*if($student->sroptionalsubjectID != 0) {
														$studentPosition[$exam->examID][$student->srstudentID]['totalSubjectMark'] += $studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID];
													}*/
													$studentChecker['totalSubjectMark'][$exam->examID][$student->srstudentID] = TRUE;
												}

												$studentSubjectPositionArray[$subject->subjectID][$exam->examID][$student->srstudentID] = $studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$subject->subjectID];
												/*if(!isset($studentChecker['studentSubjectPositionArray'][$exam->examID][$student->srstudentID])) {
													if($student->sroptionalsubjectID != 0) {
														$studentSubjectPositionArray[$student->sroptionalsubjectID][$exam->examID][$student->srstudentID] = $studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID];
													}
												}*/
										  }
										}
									}

									$rankingSubjectMarks = $studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'];
									foreach($rankingSubjectMarks as $key => $value) {
										$studentPosition[$exam->examID][$student->srstudentID]['totalRankingSubjectMark'] += $value;
									}

									try {
										//$studentPosition[$exam->examID][$student->srstudentID]['classPositionMark'] = ($studentPosition[$exam->examID][$student->srstudentID]['totalSubjectMark'] / customCompute($studentPosition[$exam->examID][$student->srstudentID]['subjectMark']));
										$studentPosition[$exam->examID][$student->srstudentID]['classPositionMark'] = ($studentPosition[$exam->examID][$student->srstudentID]['totalRankingSubjectMark'] / (customCompute($studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'])));
									} catch (DivisionByZeroError $e) {
    								$studentPosition[$exam->examID][$student->srstudentID]['classPositionMark'] = 0;
									}

									$studentClassPositionArray[$exam->examID][$student->srstudentID]             = $studentPosition[$exam->examID][$student->srstudentID]['classPositionMark'];

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
						foreach($studentSubjectPositionArray as $subjectID => $value) {
							foreach($studentSubjectPositionArray[$subjectID] as $examID => $studentSubjectPositionMark) {
								arsort($studentSubjectPositionMark);
								$studentPosition['studentSubjectPositionMark'][$subjectID][$examID] = $studentSubjectPositionMark;
							}
						}
					}
					if((int)$studentID > 0) {
						$queryArray['studentID'] = $studentID;
					}

					$this->data['col']               = 5 + $markpercentagesCount;
					$this->data['attendance']        = $this->get_student_attendance($queryArray, $this->data['subjects'], $this->data['studentLists']);
					$this->data['studentPosition']   = $studentPosition;
					$this->data['percentageArr']     = pluck($this->markpercentage_m->get_order_by_markpercentage(['schoolID' => $schoolID]), 'obj', 'markpercentageID');
					$this->data['students']          = $students;
					$this->data['exams']             = $exams;
					$this->data['mandatorysubjects'] = $mandatorySubjects;
					$this->data['subjectRanked']     = $studentSubjectRanked;
					$this->data['rankingSubjects']   = $rankingSubjects;

					$retArray['render'] = $this->load->view('report/terminal/TerminalReport',$this->data,true);
					$retArray['status'] = TRUE;
					echo json_encode($retArray);
					exit();
				}
			} else {
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

	/*private function max($array){
		$a = $b = $c = $d = null;
		foreach($array as $v) {
		  if(!isset($a) || $v > $a) {
		    $d = $c;
		    $c = $b;
		    $b = $a;
		    $a = $v;
		  }elseif(!isset($b) || $v > $b) {
		    $d = $c;
		    $c = $b;
		    $b = $v;
		  }elseif(!isset($c) || $v > $c) {
		    $d = $c;
		    $c = $v;
		  }elseif(!isset($d) || $v > $d) {
		    $d = $v;
		  }
		}

		$result = $array($a, $b, $c, $d);
		return $result;
	}*/

	private function max($array, $size){
		$result = [];
		$reuslt = arsort($array, -$size);
		return $result;
	}

	private function getStudentPosition($queryArray, $students, $subjects, $markpercentagesArr, $examcompilationID=NULL) {
		$marks = $this->mark_m->get_order_by_all_student_mark_with_markrelation2($queryArray);
		if(isset($examcompilationID))
			$marks = $this->getMark($marks, NULL, $examcompilationID);
		else
			$marks = $this->getMark($marks, $queryArray['examID'][0]);
		$studentPosition             			= [];
		$studentChecker              			= [];
		$studentClassPositionArray   			= [];
		$studentSubjectPositionArray      = [];
		$markpercentagesCount        			= 0;
		$settingmarktypeID      					= $this->data['siteinfos']->marktypeID;
		if(customCompute($students)) {
			foreach($students as $student) {
				if($student->sroptionalsubjectID != NULL) {
					$optionalSubjects = explode(",", $student->sroptionalsubjectID);
				}

				if($student->srnonexaminablesubjectID != NULL) {
					$nonexaminableSubjects = explode(",", $student->srnonexaminablesubjectID);
				}

				$studentPosition[$student->srstudentID]['totalSubjectMark'] = 0;
				if(customCompute($subjects)) {
					foreach ($subjects as $subject) {
						if($subject->type == 1 || (isset($optionalSubjects) && in_array($subject->subjectID, $optionalSubjects)) || (isset($nonexaminableSubjects) && in_array($subject->subjectID, $nonexaminableSubjects))) {
							$uniquepercentageArr = isset($markpercentagesArr[$subject->subjectID]) ? $markpercentagesArr[$subject->subjectID] : [];

							$markpercentages = $uniquepercentageArr[(($settingmarktypeID==4) || ($settingmarktypeID==6)) ? 'unique' : 'own'];
							$markpercentagesCount = customCompute($markpercentages);
							if(customCompute($markpercentages)) {
								foreach ($markpercentages as $markpercentageID) {
									$f = false;
									if(isset($uniquepercentageArr['own']) && in_array($markpercentageID, $uniquepercentageArr['own'])) {
											$f = true;
									}

									if(isset($studentPosition[$student->srstudentID]['subjectMark'][$subject->subjectID])) {
										if(isset($marks[$student->srstudentID][$subject->subjectID][$markpercentageID]) && $f) {
											$studentPosition[$student->srstudentID]['subjectMark'][$subject->subjectID] += $marks[$student->srstudentID][$subject->subjectID][$markpercentageID];
										} else {
											$studentPosition[$student->srstudentID]['subjectMark'][$subject->subjectID] += 0;
										}
									} else {
										if(isset($marks[$student->srstudentID][$subject->subjectID][$markpercentageID]) && $f) {
											$studentPosition[$student->srstudentID]['subjectMark'][$subject->subjectID] = $marks[$student->srstudentID][$subject->subjectID][$markpercentageID];
										} else {
											$studentPosition[$student->srstudentID]['subjectMark'][$subject->subjectID] = 0;
										}
									}

									if(isset($marks[$student->srstudentID][$subject->subjectID][$markpercentageID]) && $f) {
										$studentPosition[$student->srstudentID]['markpercentageMark'][$subject->subjectID][$markpercentageID] = $marks[$student->srstudentID][$subject->subjectID][$markpercentageID];

										if(isset($studentPosition[$student->srstudentID]['markpercentagetotalmark'][$markpercentageID])) {
											$studentPosition[$student->srstudentID]['markpercentagetotalmark'][$markpercentageID] += $studentPosition[$student->srstudentID]['markpercentageMark'][$subject->subjectID][$markpercentageID];
										} else {
											$studentPosition[$student->srstudentID]['markpercentagetotalmark'][$markpercentageID] = $studentPosition[$student->srstudentID]['markpercentageMark'][$subject->subjectID][$markpercentageID];
										}
									}
								}
							}

							$studentPosition[$student->srstudentID]['totalSubjectMark'] += $studentPosition[$student->srstudentID]['subjectMark'][$subject->subjectID];

							if(!isset($studentChecker['totalSubjectMark'][$student->srstudentID])) {
								$studentChecker['totalSubjectMark'][$student->srstudentID] = TRUE;
							}

							$studentSubjectPositionArray[$subject->subjectID][$student->srstudentID] = $studentPosition[$student->srstudentID]['subjectMark'][$subject->subjectID];
						}
					}
				}

				try {
					$studentPosition[$student->srstudentID]['classPositionMark'] = ($studentPosition[$student->srstudentID]['totalSubjectMark'] / customCompute($studentPosition[$student->srstudentID]['subjectMark']));
				} catch (DivisionByZeroError $e) {
					$studentPosition[$student->srstudentID]['classPositionMark'] = 0;
				}

				$studentClassPositionArray[$student->srstudentID]            = $studentPosition[$student->srstudentID]['classPositionMark'];

				if(isset($studentPosition['totalStudentMarkAverage'])) {
					$studentPosition['totalStudentMarkAverage'] += $studentPosition[$student->srstudentID]['classPositionMark'];
				} else {
					$studentPosition['totalStudentMarkAverage']  = $studentPosition[$student->srstudentID]['classPositionMark'];
				}
			}
		}
		return $studentPosition;
	}

	private function getExamcompilationMarkAverage($examcompilationID) {
		$marks = $this->getMark($marks, $examcompilationID);
	}

	private function get_student_attendance($queryArray, $subjects, $studentlists) {
		unset($queryArray['examID']);
		$newArray = [];
		$attendanceArray = [];
		$getWeekendDay = $this->getWeekendDays();
		$getHoliday    = explode('","', $this->getHolidays());

		if($this->data['siteinfos']->attendance == 'subject') {
			$attendances   = $this->subjectattendance_m->get_order_by_sub_attendance($queryArray);

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
							$pCount = isset($newArray[$studentID][$studentlistsArray[$studentID]]['pCount']) ? $newArray[$studentID][$studentlistsArray[$studentID]]['pCount'] : '0';
							$tCount = isset($newArray[$studentID][$studentlistsArray[$studentID]]['tCount']) ? $newArray[$studentID][$studentlistsArray[$studentID]]['tCount'] : '0';
							$str .= $subjects[$subjectID]->subject .":".$pCount."/".$tCount.',';
						}

						$attendanceArray[$studentID] = $str;
					}
				}
			}
		} else {
			$attendances   = $this->sattendance_m->get_order_by_attendance($queryArray);
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

	public function pdf() {
		if(permissionChecker('terminalreport')) {
			$examID 			= htmlentities(escapeString($this->uri->segment(3)));
			$examcompilationID = htmlentities(escapeString($this->uri->segment(4)));
			$classesID  	= htmlentities(escapeString($this->uri->segment(5)));
			$sectionID  	= htmlentities(escapeString($this->uri->segment(6)));
			$studentID  	= htmlentities(escapeString($this->uri->segment(7)));
			$examrankingID  = htmlentities(escapeString($this->uri->segment(8)));
			$schoolyearID = $this->session->userdata('defaultschoolyearID');
			$schoolID     = $this->session->userdata('schoolID');

			if($examcompilationID > 0) {
				$examcompilation_exams = $this->examcompilation_exams_m->get_order_by_examcompilation_exams(array('examcompilationID' => $examcompilationID));
				if(customCompute($examcompilation_exams)) {
					foreach($examcompilation_exams as $examcompilation_exam) {
						$examID = $examcompilation_exam->examID;
					}
				}
			}

			if(((int)$examID || (int)$examcompilationID) && (int)$classesID && ((int)$sectionID || $sectionID >= 0) && ((int)$studentID || $studentID >= 0)) {
                $this->data['examID']            = $examID;
                $this->data['examcompilationID'] = $examcompilationID;
                $this->data['classesID']         = $classesID;
                $this->data['sectionID']         = $sectionID;
                $this->data['studentIDD']        = $studentID;
                $this->data['examrankingID']     = $examrankingID;

                $queryArray        = array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID);
                $studentQueryArray = array('srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID);

                if((int)$classesID > 0) {
                    $queryArray['classesID'] = $classesID;
                    $studentQueryArray['srclassesID'] = $classesID;
                }
                if((int)$sectionID > 0) {
                    $queryArray['sectionID'] = $sectionID;
                    $studentQueryArray['srsectionID'] = $sectionID;
                }
                if((int)$studentID > 0) {
                    $studentQueryArray['srstudentID'] = $studentID;
                }

                $this->data['grades']       			= $this->grade_m->get_order_by_grade(['schoolID' => $schoolID]);
                $this->data['classes']      			= pluck($this->classes_m->general_get_order_by_classes(['schoolID' => $schoolID]),'classes','classesID');
                $this->data['sections']     			= pluck($this->section_m->general_get_order_by_section(['schoolID' => $schoolID]),'section','sectionID');
                $this->data['groups']       			= pluck($this->studentgroup_m->get_order_by_studentgroup(['schoolID' => $schoolID]),'group','studentgroupID');
                $this->data['studentLists'] 			= $this->studentrelation_m->general_get_order_by_student($studentQueryArray);
                $students                         = $this->studentrelation_m->general_get_order_by_student(array('srclassesID' => $classesID, 'srschoolyearID' => $schoolyearID));
                $subjects                					= $this->subject_m->get_order_by_subject_with_subjectteacher(array('classesID' => $classesID, 'schoolID' => $schoolID));
                $subjecttype                			= pluck($subjects,'type','subjectID');
                $this->data['subjects'] 					= $subjects;

                $exam      												= $this->exam_m->get_single_exam(['examID'=> $examID, 'schoolID' => $schoolID]);
                $examcompilation      						= $this->examcompilation_m->get_single_examcompilation(['examcompilationID'=> $examcompilationID, 'schoolID' => $schoolID]);
                if($exam) {
                    $this->data['examName']     			= $exam->exam;
                } else {
                    $this->data['examName'] = '';
                }

                if($examcompilationID > 0 && customCompute($examcompilation)) {
                    $this->data['examName']         = $examcompilation->examcompilation;
                }

                $exams                      			= $this->exam_m->get_last_six_exams(array('classesID' => $classesID, 'examID <=' => $examID, 'schoolID' => $schoolID));
                $examIDArray 							  			= [];
                if(customCompute($exams)) {
                    foreach($exams as $exam)
                        $examIDArray[] 								= $exam->examID;
                    $queryArray['examID']           = $examIDArray;
                    $marks                    			= $this->mark_m->get_order_by_all_student_mark_with_markrelation2($queryArray);
                } else {
                    $marks = [];
                }
                $settingmarktypeID      					= $this->data['siteinfos']->marktypeID;
                $markpercentagesmainArr			 			= $this->marksetting_m->get_marksetting_markpercentages();
                $markpercentagesArr     				  = isset($markpercentagesmainArr[$classesID][$examID]) ? $markpercentagesmainArr[$classesID][$examID] : [];
                $compareStudentPosition           = 0;
                $examIDArray                      = [];
                if($examcompilationID > 0) {
                    $marks                  			  = $this->getMarkWithExam($marks, $examcompilationID);
                    if(customCompute($examcompilation) && $examcompilation->compare_examID != NULL) {
                        $examIDArray[] 				  = $examcompilation->compare_examID;
                        $queryArray['examID']   = $examIDArray;
                        $compareStudentPosition = $this->getStudentPosition($queryArray, $students, $subjects, $markpercentagesArr);
                    }
                    if(customCompute($examcompilation) && $examcompilation->compare_examcompilationID != NULL) {
                        $examcompilation_exams = $this->examcompilation_exams_m->get_order_by_examcompilation_exams(array('examcompilationID' => $examcompilationID));
                        if(customCompute($examcompilation_exams)) {
                            foreach($examcompilation_exams as $examcompilation_exam) {
                                $examIDArray[] = $examcompilation_exam->examID;
                            }
                            $queryArray['examID']   = $examIDArray;
                            $compareStudentPosition = $this->getStudentPosition($queryArray, $students, $subjects, $markpercentagesArr);
                        }
                    }
                } else {
                    $marks                  			      = $this->getMarkWithExam($marks);
                }
                $this->data['marks']    					    = $marks;
                $this->data['compareStudentPosition'] = $compareStudentPosition;

                $this->data['outof']    					    = count($students);
                $mandatorySubjects      					    = $this->subject_m->general_get_order_by_subject(array('classesID' => $classesID, 'type' => 1, 'schoolID' => $schoolID));

                $this->data['markpercentagesArr']     = $markpercentagesArr;
                $this->data['settingmarktypeID']      = $settingmarktypeID;
                $remarks                              = $this->remark_m->get_order_by_remark(['examID' => $examID, 'schoolID' => $schoolID]);
                $this->data['remarks']                = $this->getRemark($remarks);
                $examranking                          = $this->examranking_m->get_examranking($examrankingID);
                $rankingSubjects                      = [];
                if(customCompute($examranking)) {
                    $rankingSubjects                    = explode(",", $examranking->subjects);
                }

                $studentPosition             			= [];
                $studentChecker              			= [];
                $studentClassPositionArray   			= [];
                $studentSubjectPositionArray 			= [];
                $studentSubjectRanked             = [];
                $markpercentagesCount        			= 0;
                if(customCompute($exams)) {
                    foreach($exams as $exam) {
                        if(customCompute($students)) {
                            foreach($students as $student) {
                                if($student->sroptionalsubjectID != NULL) {
                                    $optionalSubjects = explode(",", $student->sroptionalsubjectID);
                                } else {
                                    $optionalSubjects = [];
                                }

                                if($student->srnonexaminablesubjectID != NULL) {
                                    $nonexaminableSubjects = explode(",", $student->srnonexaminablesubjectID);
                                } else {
                                    $nonexaminableSubjects = [];
                                }

                                $studentPosition[$exam->examID][$student->srstudentID]['totalSubjectMark'] = 0;
                                if(customCompute($subjects)) {
                                    foreach ($subjects as $subject) {
                                        if($subject->type == 1 || in_array($subject->subjectID, $optionalSubjects) || in_array($subject->subjectID, $nonexaminableSubjects)) {
                                            $uniquepercentageArr = isset($markpercentagesArr[$subject->subjectID]) ? $markpercentagesArr[$subject->subjectID] : [];

                                            $markpercentages = $uniquepercentageArr[(($settingmarktypeID==4) || ($settingmarktypeID==6)) ? 'unique' : 'own'];
                                            $markpercentagesCount = customCompute($markpercentages);
                                            if(customCompute($markpercentages)) {
                                                foreach ($markpercentages as $markpercentageID) {
                                                    $f = false;
                                                    if(isset($uniquepercentageArr['own']) && in_array($markpercentageID, $uniquepercentageArr['own'])) {
                                                        $f = true;
                                                    }

                                                    if(isset($studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$subject->subjectID])) {
                                                        if(isset($marks[$exam->examID][$student->srstudentID][$subject->subjectID][$markpercentageID]) && $f) {
                                                            $studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$subject->subjectID] += $marks[$exam->examID][$student->srstudentID][$subject->subjectID][$markpercentageID];
                                                        } else {
                                                            $studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$subject->subjectID] += 0;
                                                        }
                                                    } else {
                                                        if(isset($marks[$exam->examID][$student->srstudentID][$subject->subjectID][$markpercentageID]) && $f) {
                                                            $studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$subject->subjectID] = $marks[$exam->examID][$student->srstudentID][$subject->subjectID][$markpercentageID];
                                                        } else {
                                                            $studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$subject->subjectID] = 0;
                                                        }
                                                    }

                                                    if($examrankingID == 0 || ($examrankingID == "mandatory" && $subject->type == 1) || ($examrankingID == "optional" && $subject->type == 0) || ($examrankingID == "nonexaminable" && $subject->type == 2) || ($examrankingID == "mandatory_and_optional" && ($subject->type == 1 || $subject->type == 0)) || (customCompute($rankingSubjects) && in_array($subject->subjectID, $rankingSubjects))) {
                                                        if(isset($studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$subject->subjectID])) {
                                                            if(isset($marks[$exam->examID][$student->srstudentID][$subject->subjectID][$markpercentageID]) && $f) {
                                                                $studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$subject->subjectID] += $marks[$exam->examID][$student->srstudentID][$subject->subjectID][$markpercentageID];
                                                            } else {
                                                                $studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$subject->subjectID] += 0;
                                                            }
                                                        } else {
                                                            if(isset($marks[$exam->examID][$student->srstudentID][$subject->subjectID][$markpercentageID]) && $f) {
                                                                $studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$subject->subjectID] = $marks[$exam->examID][$student->srstudentID][$subject->subjectID][$markpercentageID];
                                                            } else {
                                                                $studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$subject->subjectID] = 0;
                                                            }
                                                        }

                                                        if($examrankingID == "mandatory_and_optional") {
                                                            $optionalTop = [];
                                                            if(count($studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark']) > 4) {
                                                                foreach($studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'] as $key => $value) {
                                                                    if($subjecttype[$key] == 0) {
                                                                        $optionalTop[$key] = $studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$key];
                                                                        unset($studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$key]);
                                                                    }
                                                                }
                                                                if(count($optionalTop) >= 4) {
                                                                    $optionalTop = $this->max($optionalTop, 4);
                                                                }
                                                                foreach($optionalTop as $key => $value) {
                                                                    $studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$key] = $value;
                                                                }
                                                            }
                                                        }

                                                        if((customCompute($rankingSubjects) && in_array($subject->subjectID, $rankingSubjects))) {
                                                            $mandatoryTop = $optionalTop = $nonexaminableTop = [];
                                                            foreach($studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'] as $key => $value) {
                                                                if($subjecttype[$key] == 1) {
                                                                    $mandatoryTop[$key] = $studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$key];
                                                                    unset($studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$key]);
                                                                }
                                                                elseif($subjecttype[$key] == 0) {
                                                                    $optionalTop[$key] = $studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$key];
                                                                    unset($studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$key]);
                                                                }
                                                                elseif($subjecttype[$key] == 2) {
                                                                    $nonexaminableTop[$key] = $studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$key];
                                                                    unset($studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$key]);
                                                                }
                                                            }
                                                            if(count($mandatoryTop) >= $examranking->mandatory_top && $examranking->mandatory_top > 0) {
                                                                $mandatoryTop = $this->max($mandatoryTop, $examranking->mandatory_top);
                                                            }
                                                            foreach($mandatoryTop as $key => $value) {
                                                                $studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$key] = $value;
                                                            }
                                                            if(count($optionalTop) >= $examranking->optional_top && $examranking->optional_top > 0) {
                                                                $optionalTop = $this->max($optionalTop, $examranking->optional_top);
                                                            }
                                                            foreach($optionalTop as $key => $value) {
                                                                $studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$key] = $value;
                                                            }
                                                            if(count($nonexaminableTop) >= $examranking->nonexaminable_top && $examranking->nonexaminable_top > 0) {
                                                                $nonexaminableTop = $this->max($nonexaminableTop, $examranking->nonexaminable_top);
                                                            }
                                                            foreach($nonexaminableTop as $key => $value) {
                                                                $studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'][$key] = $value;
                                                            }
                                                        }
                                                    }
                                                    if(isset($marks[$exam->examID][$student->srstudentID][$subject->subjectID][$markpercentageID]) && $f) {
                                                        $studentPosition[$exam->examID][$student->srstudentID]['markpercentageMark'][$subject->subjectID][$markpercentageID] = $marks[$exam->examID][$student->srstudentID][$subject->subjectID][$markpercentageID];
                                                        if(isset($studentPosition[$exam->examID][$student->srstudentID]['markpercentagetotalmark'][$markpercentageID])) {
                                                            $studentPosition[$exam->examID][$student->srstudentID]['markpercentagetotalmark'][$markpercentageID] += $studentPosition[$exam->amID][$student->srstudentID]['markpercentageMark'][$subject->subjectID][$markpercentageID];
                                                        } else {
                                                            $studentPosition[$exam->examID][$student->srstudentID]['markpercentagetotalmark'][$markpercentageID] = $studentPosition[$exam->examID][$student->srstudentID]['markpercentageMark'][$subject->subjectID][$markpercentageID];
                                                        }
                                                    }
                                                }
                                            }
                                            $studentPosition[$exam->examID][$student->srstudentID]['totalSubjectMark'] += $studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$subject->subjectID];
                                            if(!isset($studentChecker['totalSubjectMark'][$exam->examID][$student->srstudentID])) {
                                                $studentChecker['totalSubjectMark'][$exam->examID][$student->srstudentID] = TRUE;
                                            }
                                            $studentSubjectPositionArray[$subject->subjectID][$exam->examID][$student->srstudentID] = $studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$subject->subjectID];
                                        }
                                    }
                                }
                                $rankingSubjectMarks = $studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'];
                                foreach($rankingSubjectMarks as $key => $value) {
                                    $studentPosition[$exam->examID][$student->srstudentID]['totalRankingSubjectMark'] += $value;
                                }
                                try {
                                    $studentPosition[$exam->examID][$student->srstudentID]['classPositionMark'] = ($studentPosition[$exam->examID][$student->srstudentID]['totalRankingSubjectMark'] / (customCompute($studentPosition[$exam->examID][$student->srstudentID]['rankingSubjectMark'])));
                                } catch (DivisionByZeroError $e) {
                                    $studentPosition[$exam->examID][$student->srstudentID]['classPositionMark'] = 0;
                                }
                                $studentClassPositionArray[$exam->examID][$student->srstudentID]             = $studentPosition[$exam->examID][$student->srstudentID]['classPositionMark'];
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
                    foreach($studentSubjectPositionArray as $subjectID => $value) {
                        foreach($studentSubjectPositionArray[$subjectID] as $examID => $studentSubjectPositionMark) {
                            arsort($studentSubjectPositionMark);
                            $studentPosition['studentSubjectPositionMark'][$subjectID][$examID] = $studentSubjectPositionMark;
                        }
                    }
                }
                if((int)$studentID > 0) {
                    $queryArray['studentID'] = $studentID;
                }
                $this->data['col']               = 5 + $markpercentagesCount;
                $this->data['attendance']        = $this->get_student_attendance($queryArray, $this->data['subjects'], $this->data['studentLists']);
                $this->data['studentPosition']   = $studentPosition;
                $this->data['percentageArr']     = pluck($this->markpercentage_m->get_order_by_markpercentage(['schoolID' => $schoolID]), 'obj', 'markpercentageID');
                $this->data['students']          = $students;
                $this->data['exams']             = $exams;
                $this->data['mandatorysubjects'] = $mandatorySubjects;
                $this->data['subjectRanked']     = $studentSubjectRanked;
                $this->data['rankingSubjects']   = $rankingSubjects;
				$this->reportPDF('terminalreport.css', $this->data, 'report/terminal/TerminalReportPDF');
			} else {
				$this->data["subview"] = "error";
				$this->load->view('_layout_main', $this->data);
			}
		} else {
			$this->data["subview"] = "errorpermission";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function send_pdf_to_mail() {
		$retArray['status'] = FALSE;
		$retArray['message'] = '';

		if(permissionChecker('terminalreport')) {
			if($_POST) {
				$to           = $this->input->post('to');
				$subject      = $this->input->post('subject');
				$message      = $this->input->post('message');
				$examID       = $this->input->post('examID');
				$classesID    = $this->input->post('classesID');
				$sectionID    = $this->input->post('sectionID');
				$studentID    = $this->input->post('studentID');
				$schoolyearID = $this->session->userdata('defaultschoolyearID');
				$schoolID     = $this->session->userdata('schoolID');

				$rules = $this->send_pdf_to_mail_rules();
				$this->form_validation->set_rules($rules);
				if($this->form_validation->run() == FALSE) {
					$retArray = $this->form_validation->error_array();
					$retArray['status'] = FALSE;
			    echo json_encode($retArray);
			    exit;
				} else {
					$this->data['examID']     = $examID;
					$this->data['classesID']  = $classesID;
					$this->data['sectionID']  = $sectionID;
					$this->data['studentIDD'] = $studentID;

					$queryArray        = ['schoolyearID' => $schoolyearID, 'schoolID' => $schoolID];
					$studentQueryArray = ['srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID];

					if((int)$examID > 0) {
						$queryArray['examID'] = $examID;
					}
					if((int)$classesID > 0) {
						$queryArray['classesID'] = $classesID;
						$studentQueryArray['srclassesID'] = $classesID;
					}
					if((int)$sectionID > 0) {
						$queryArray['sectionID'] = $sectionID;
						$studentQueryArray['srsectionID'] = $sectionID;
					}
					if((int)$studentID > 0) {
						$studentQueryArray['srstudentID'] = $studentID;
					}

					$exam                       = $this->exam_m->get_single_exam(['examID'=> $examID, 'schoolID' => $schoolID]);
					$this->data['examName']     = $exam->exam;
					$this->data['grades']       = $this->grade_m->get_order_by_grade(['schoolID' => $schoolID]);
					$this->data['sections']     = pluck($this->section_m->general_get_order_by_section(['schoolID' => $schoolID]),'section','sectionID');
					$this->data['classes']      = pluck($this->classes_m->general_get_order_by_classes(['schoolID' => $schoolID]),'classes','classesID');
					$this->data['groups']       = pluck($this->studentgroup_m->get_order_by_studentgroup(['schoolID' => $schoolID]),'group','studentgroupID');
					$this->data['studentLists'] = $this->studentrelation_m->general_get_order_by_student($studentQueryArray);
					$students               		= $this->studentrelation_m->general_get_order_by_student(array('srclassesID' => $classesID, 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID));
					$marks                  		= $this->mark_m->student_all_mark_array($queryArray);
					$mandatorySubjects      		= $this->subject_m->general_get_order_by_subject(array('classesID' => $classesID, 'type' => 1, 'schoolID' => $schoolID));

					$this->subject_m->order('type DESC');
					$this->data['subjects'] = $this->subject_m->general_get_order_by_subject(array('classesID' => $classesID, 'schoolID' => $schoolID));

					$settingmarktypeID      = $this->data['siteinfos']->marktypeID;
					$markpercentagesmainArr = $this->marksetting_m->get_marksetting_markpercentages();
					$markpercentagesArr     = isset($markpercentagesmainArr[$classesID][$examID]) ? $markpercentagesmainArr[$classesID][$examID] : [];
					$this->data['markpercentagesArr']  = $markpercentagesArr;
					$this->data['settingmarktypeID']   = $settingmarktypeID;

					$retMark = [];
					if(customCompute($marks)) {
						foreach ($marks as $mark) {
							$retMark[$mark->studentID][$mark->subjectID][$mark->markpercentageID] = $mark->mark;
						}
					}

					$studentPosition             = [];
					$studentChecker              = [];
					$studentClassPositionArray   = [];
					$studentSubjectPositionArray = [];
					$markpercentagesCount        = 0;
					if(customCompute($students)) {
						foreach ($students as $student) {
							$opuniquepercentageArr = [];
							if($student->sroptionalsubjectID > 0) {
								$opuniquepercentageArr = isset($markpercentagesArr[$student->sroptionalsubjectID]) ? $markpercentagesArr[$student->sroptionalsubjectID] : [];
							}

							$studentPosition[$student->srstudentID]['totalSubjectMark'] = 0;
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

											if(isset($studentPosition[$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID])) {
												if(isset($retMark[$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID]) && $f) {
													$studentPosition[$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID] += $retMark[$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID];
												} else {
													$studentPosition[$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID] += 0;
												}
											} else {
												if(isset($retMark[$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID]) && $f) {
													$studentPosition[$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID] = $retMark[$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID];
												} else {
													$studentPosition[$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID] = 0;
												}
											}

											if(isset($retMark[$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID]) && $f) {
												$studentPosition[$student->srstudentID]['markpercentageMark'][$mandatorySubject->subjectID][$markpercentageID] = $retMark[$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID];

												if(isset($studentPosition[$student->srstudentID]['markpercentagetotalmark'][$markpercentageID])) {
													$studentPosition[$student->srstudentID]['markpercentagetotalmark'][$markpercentageID] += $studentPosition[$student->srstudentID]['markpercentageMark'][$mandatorySubject->subjectID][$markpercentageID];
												} else {
													$studentPosition[$student->srstudentID]['markpercentagetotalmark'][$markpercentageID] = $studentPosition[$student->srstudentID]['markpercentageMark'][$mandatorySubject->subjectID][$markpercentageID];

												}
											}

											$f = false;
											if(customCompute($opuniquepercentageArr)) {
                        if(isset($opuniquepercentageArr['own']) && in_array($markpercentageID, $opuniquepercentageArr['own'])) {
                            $f = true;
                        }
											}

											if(!isset($studentChecker['subject'][$student->srstudentID][$markpercentageID]) && $f) {
												if($student->sroptionalsubjectID != 0) {
													if(isset($studentPosition[$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID])) {
														if(isset($retMark[$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID])) {
															$studentPosition[$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID] += $retMark[$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID];
														} else {
															$studentPosition[$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID] += 0;
														}
													} else {
														if(isset($retMark[$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID])) {
															$studentPosition[$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID] = $retMark[$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID];
														} else {
															$studentPosition[$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID] = 0;
														}
													}

													if(isset($retMark[$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID])) {
														$studentPosition[$student->srstudentID]['markpercentageMark'][$student->sroptionalsubjectID][$markpercentageID] = $retMark[$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID];

														if(isset($studentPosition[$student->srstudentID]['markpercentagetotalmark'][$markpercentageID])) {
															$studentPosition[$student->srstudentID]['markpercentagetotalmark'][$markpercentageID] += $studentPosition[$student->srstudentID]['markpercentageMark'][$student->sroptionalsubjectID][$markpercentageID];
														} else {
															if($f) {
																$studentPosition[$student->srstudentID]['markpercentagetotalmark'][$markpercentageID] = $studentPosition[$student->srstudentID]['markpercentageMark'][$student->sroptionalsubjectID][$markpercentageID];
															}
														}

													}
												}
												$studentChecker['subject'][$student->srstudentID][$markpercentageID] = TRUE;
											}
										}
									}

									$studentPosition[$student->srstudentID]['totalSubjectMark'] += $studentPosition[$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID];

									if(!isset($studentChecker['totalSubjectMark'][$student->srstudentID])) {
										if($student->sroptionalsubjectID != 0) {
											$studentPosition[$student->srstudentID]['totalSubjectMark'] += $studentPosition[$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID];
										}
										$studentChecker['totalSubjectMark'][$student->srstudentID] = TRUE;
									}

									$studentSubjectPositionArray[$mandatorySubject->subjectID][$student->srstudentID] = $studentPosition[$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID];
									if(!isset($studentChecker['studentSubjectPositionArray'][$student->srstudentID])) {
										if($student->sroptionalsubjectID != 0) {
											$studentSubjectPositionArray[$student->sroptionalsubjectID][$student->srstudentID] = $studentPosition[$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID];
										}
									}
								}
							}


							$studentPosition[$student->srstudentID]['classPositionMark'] = ($studentPosition[$student->srstudentID]['totalSubjectMark'] / customCompute($studentPosition[$student->srstudentID]['subjectMark']));
							$studentClassPositionArray[$student->srstudentID]             = $studentPosition[$student->srstudentID]['classPositionMark'];

							if(isset($studentPosition['totalStudentMarkAverage'])) {
								$studentPosition['totalStudentMarkAverage'] += $studentPosition[$student->srstudentID]['classPositionMark'];
							} else {
								$studentPosition['totalStudentMarkAverage']  = $studentPosition[$student->srstudentID]['classPositionMark'];
							}
						}
					}

					arsort($studentClassPositionArray);
					$studentPosition['studentClassPositionArray'] = $studentClassPositionArray;
					if(customCompute($studentSubjectPositionArray)) {
						foreach($studentSubjectPositionArray as $subjectID => $studentSubjectPositionMark) {
							arsort($studentSubjectPositionMark);
							$studentPosition['studentSubjectPositionMark'][$subjectID] = $studentSubjectPositionMark;
						}
					}
					if((int)$studentID > 0) {
						$queryArray['studentID'] = $studentID;
					}

					$this->data['col']             = 5 + $markpercentagesCount;
					$this->data['attendance']      = $this->get_student_attendance($queryArray, $this->data['subjects'], $this->data['studentLists']);
					$this->data['studentPosition'] = $studentPosition;
					$this->data['percentageArr']   = pluck($this->markpercentage_m->get_order_by_markpercentage(['schoolID' => $schoolID]), 'obj', 'markpercentageID');

					$this->reportSendToMail('terminalreport.css', $this->data, 'report/terminal/TerminalReportPDF',$to, $subject,$message);
					$retArray['status'] = TRUE;
					echo json_encode($retArray);
    			exit;
				}
			} else {
				$retArray['message'] = $this->lang->line('terminalreport_permissionmethod');
				echo json_encode($retArray);
				exit;
			}
		} else {
			$retArray['message'] = $this->lang->line('terminalreport_permission');
			echo json_encode($retArray);
			exit;
		}
	}

	private function sortArray($array) {
		arsort($array);
		return $array;
	}

	private function getMark($marks, $examID, $examcompilationID=NULL) {
		$retMark = [];
		$examcompilation_examArray = [];
		if(customCompute($marks)) {
			$examcompilation_exams = $this->examcompilation_exams_m->get_order_by_examcompilation_exams(array('examcompilationID' => $examcompilationID));
			if(customCompute($examcompilation_exams)) {
				foreach($examcompilation_exams as $examcompilation_exam) {
					$examcompilation_examArray[$examcompilation_exam->examID]['weight'] = $examcompilation_exam->weight;
				}
			}
			foreach($marks as $mark) {
				if(array_key_exists($mark->examID, $examcompilation_examArray)) {
					$examID = max(array_keys($examcompilation_examArray));
					$retMark[$mark->studentID][$mark->subjectID][$mark->markpercentageID] += round($mark->mark*$examcompilation_examArray[$mark->examID]['weight']/100);
					$retMark[$mark->studentID][$mark->subjectID]['teacher_comment'] = $mark->teacher_comment;
				} elseif($examID == $mark->examID) {
					$retMark[$mark->studentID][$mark->subjectID][$mark->markpercentageID] = $mark->mark;
					$retMark[$mark->studentID][$mark->subjectID]['teacher_comment'] = $mark->teacher_comment;
				}
			}
		}
		return $retMark;
	}

  private function getMarkWithExam($marks, $examcompilationID=NULL) {
		$retMark = [];
		$examcompilation_examArray = [];
		if(customCompute($marks)) {
			$examcompilation_exams = $this->examcompilation_exams_m->get_order_by_examcompilation_exams(array('examcompilationID' => $examcompilationID));
			if(customCompute($examcompilation_exams)) {
				foreach($examcompilation_exams as $examcompilation_exam) {
					$examcompilation_examArray[$examcompilation_exam->examID]['weight'] = $examcompilation_exam->weight;
				}
			}
			foreach($marks as $mark) {
				if(array_key_exists($mark->examID, $examcompilation_examArray)) {
					$examID = max(array_keys($examcompilation_examArray));
					$retMark[$examID][$mark->studentID][$mark->subjectID][$mark->markpercentageID] += round($mark->mark*$examcompilation_examArray[$mark->examID]['weight']/100);
					$retMark[$examID][$mark->studentID][$mark->subjectID]['teacher_comment'] = $mark->teacher_comment;
				} else {
					$retMark[$mark->examID][$mark->studentID][$mark->subjectID][$mark->markpercentageID] = $mark->mark;
					$retMark[$mark->examID][$mark->studentID][$mark->subjectID]['teacher_comment'] = $mark->teacher_comment;
				}
			}
		}
		return $retMark;
	}

	public function getExam() {
		$classesID = $this->input->post('classesID');
		echo "<option value='0' selected>", $this->lang->line("terminalreport_please_select"),"</option>
		<option value='-1'>", $this->lang->line("terminalreport_select_examcompilation"),"</option>";

		if((int)$classesID) {
			$schoolID = $this->session->userdata('schoolID');
			$exams = pluck($this->marksetting_m->get_exam(array('marktypeID' => $this->data['siteinfos']->marktypeID, 'classesID' => $classesID, 'schoolID' => $schoolID)), 'obj', 'examID');
			if(customCompute($exams)) {
				foreach ($exams as $exam) {
					echo "<option value=".$exam->examID.">".$exam->exam."</option>";
				}
			}
		}
	}

	public function getExamranking() {
		$classesID = $this->input->post('classesID');
		echo "<option value='0' selected>", $this->lang->line("terminalreport_please_select"),"</option>";
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

	private function getRemark($remarks) {
		$retRemark = [];
		if(customCompute($remarks)) {
			foreach ($remarks as $remark) {
				$retRemark[$remark->studentID] = $remark;
			}
		}
		return $retRemark;
	}

	public function getSection() {
		$classesID = $this->input->post('classesID');
		if((int)$classesID) {
			$schoolID = $this->session->userdata('schoolID');
			$sections = $this->section_m->general_get_order_by_section(array('classesID' => $classesID, 'schoolID' => $schoolID));
			echo "<option value='0'>", $this->lang->line("terminalreport_please_select"),"</option>";
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
		$schoolID = $this->session->userdata('schoolID');
		$schoolyearID = $this->session->userdata('defaultschoolyearID');
		$studentQuery['srschoolID'] = $schoolID;
		$studentQuery['srschoolyearID'] = $schoolyearID;
		if((int)$classesID) {
			$studentQuery['srclassesID'] = $classesID;
		}
		if((int)$sectionID) {
			$studentQuery['srsectionID'] = $sectionID;
		}
		$students = $this->studentrelation_m->general_get_order_by_student($studentQuery);
		echo "<option value='0'>". $this->lang->line("terminalreport_please_select") . "</option>";
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
}
