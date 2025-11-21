<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH . '../vendor/autoload.php');
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Customer;

class Student extends Admin_Controller {
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
	function __construct () {
		parent::__construct();
		$this->load->model("student_m");
		$this->load->model("parents_m");
		$this->load->model("section_m");
		$this->load->model("classes_m");
		$this->load->model("setting_m");
		$this->load->model('studentrelation_m');
		$this->load->model('studentgroup_m');
		$this->load->model('studentextend_m');
		$this->load->model('subject_m');
		$this->load->model('routine_m');
		$this->load->model('teacher_m');
		$this->load->model('subjectattendance_m');
		$this->load->model('sattendance_m');
		$this->load->model('invoice_m');
		$this->load->model('payment_m');
		$this->load->model('weaverandfine_m');
		$this->load->model('feetypes_m');
		$this->load->model('exam_m');
		$this->load->model('grade_m');
		$this->load->model('markpercentage_m');
		$this->load->model('markrelation_m');
		$this->load->model('mark_m');
		$this->load->model('document_m');
		$this->load->model('leaveapplication_m');
		$this->load->model('marksetting_m');
		$this->load->model('quickbookslog_m');
		$language = $this->session->userdata('lang');
		$this->lang->load('student', $language);
	}

	public function send_mail_rules() {
		$rules = array(
			array(
				'field' => 'to',
				'label' => $this->lang->line("student_to"),
				'rules' => 'trim|required|max_length[60]|valid_email|xss_clean'
			),
			array(
				'field' => 'subject',
				'label' => $this->lang->line("student_subject"),
				'rules' => 'trim|required|xss_clean'
			),
			array(
				'field' => 'message',
				'label' => $this->lang->line("student_message"),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field' => 'studentID',
				'label' => $this->lang->line("student_studentID"),
				'rules' => 'trim|required|max_length[10]|xss_clean|callback_unique_data'
			),
			array(
				'field' => 'classesID',
				'label' => $this->lang->line("student_classesID"),
				'rules' => 'trim|required|max_length[10]|xss_clean|callback_unique_data'
			)
		);
		return $rules;
	}

	private function getView($id, $url) {
		$schoolyearID = $this->session->userdata('defaultschoolyearID');
		$schoolID = $this->session->userdata('schoolID');
		$fetchClasses = pluck($this->classes_m->get_classes(array('schoolID' => $schoolID)), 'classesID', 'classesID');

		//if(isset($fetchClasses[$url])) {
			if((int)$id && (int)$url) {
				$studentInfo = $this->studentrelation_m->get_single_student(array('srstudentID' => $id, 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID), TRUE);

				$this->pluckInfo();
				$this->basicInfo($studentInfo);
				$this->parentInfo($studentInfo);
				$this->routineInfo($studentInfo);
				$this->attendanceInfo($studentInfo);
				$this->markInfo($studentInfo);
				$this->invoiceInfo($studentInfo);
				$this->paymentInfo($studentInfo);
				$this->documentInfo($studentInfo);
				$this->examInfo($studentInfo);

				if(customCompute($studentInfo)) {
					$this->data['set']     = $url;
					$this->data['leaveapplications'] = $this->leave_applications_date_list_by_user_and_schoolyear($id,$schoolyearID,$studentInfo->usertypeID);
					$this->data["subview"] = "student/getView";
					$this->load->view('_layout_main', $this->data);
				} else {
					$this->data["subview"] = "error";
					$this->load->view('_layout_main', $this->data);
				}
			}
		/*} else {
			$this->data["subview"] = "error";
			$this->load->view('_layout_main', $this->data);
		}*/
	}

	private function allPaymentByInvoice($payments) {
		$retPaymentArr = [];
		if($payments) {
			foreach ($payments as $payment) {
				if(isset($retPaymentArr[$payment->invoiceID])) {
					$retPaymentArr[$payment->invoiceID] += $payment->paymentamount;
				} else {
					$retPaymentArr[$payment->invoiceID] = $payment->paymentamount;
				}
			}
		}
		return $retPaymentArr;
	}

	private function allWeaverAndFineByInvoice($weaverandfines) {
		$retWeaverAndFineArr = [];
		if($weaverandfines) {
			foreach ($weaverandfines as $weaverandfine) {
				if(isset($retWeaverAndFineArr[$weaverandfine->invoiceID]['weaver'])) {
					$retWeaverAndFineArr[$weaverandfine->invoiceID]['weaver'] += $weaverandfine->weaver;
				} else {
					$retWeaverAndFineArr[$weaverandfine->invoiceID]['weaver'] = $weaverandfine->weaver;
				}

				if(isset($retWeaverAndFineArr[$weaverandfine->invoiceID]['fine'])) {
					$retWeaverAndFineArr[$weaverandfine->invoiceID]['fine'] += $weaverandfine->fine;
				} else {
					$retWeaverAndFineArr[$weaverandfine->invoiceID]['fine'] = $weaverandfine->fine;
				}
			}
		}
		return $retWeaverAndFineArr;
	}


	private function getMark($studentID, $classesID) {
		if((int)$studentID && (int)$classesID) {
			$schoolID     = $this->session->userdata('schoolID');
			$schoolyearID = $this->session->userdata('defaultschoolyearID');
			$student      = $this->studentrelation_m->get_single_student(array('srstudentID' => $studentID, 'srclassesID' => $classesID, 'srschoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
			$classes      = $this->classes_m->get_single_classes(array('classesID' => $classesID, 'schoolID' => $schoolID));

			if(customCompute($student) && customCompute($classes)) {
				$queryArray = [
					'classesID'    => $student->srclassesID,
					'sectionID'    => $student->srsectionID,
					'studentID'    => $student->srstudentID,
					'schoolyearID' => $schoolyearID,
					'schoolID'     => $schoolID,
				];

				$exams             = pluck($this->exam_m->get_order_by_exam(array('schoolID' => $schoolID)), 'exam', 'examID');
				$grades            = $this->grade_m->get_order_by_grade(array('schoolID' => $schoolID));
				$marks             = $this->mark_m->student_all_mark_array($queryArray);
				$markpercentages   = $this->markpercentage_m->get_order_by_markpercentage(array('schoolID' => $schoolID));

				$subjects          = $this->subject_m->general_get_order_by_subject(array('classesID' => $classesID, 'schoolID' => $schoolID));
				$subjectArr        = [];
				$optionalsubjectArr= [];
				if(customCompute($subjects)) {
					foreach ($subjects as $subject) {
						if($subject->type == 0) {
							$optionalsubjectArr[$subject->subjectID] = $subject->subjectID;
						}
						$subjectArr[$subject->subjectID] = $subject;
					}
				}

				$retMark = [];
				if(customCompute($marks)) {
					foreach ($marks as $mark) {
						$retMark[$mark->examID][$mark->subjectID][$mark->markpercentageID] = $mark->mark;
					}
				}

				$allStudentMarks = $this->mark_m->student_all_mark_array(array('classesID' => $classesID, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
				$highestMarks    = [];
				foreach ($allStudentMarks as $value) {
					if(!isset($highestMarks[$value->examID][$value->subjectID][$value->markpercentageID])) {
						$highestMarks[$value->examID][$value->subjectID][$value->markpercentageID] = -1;
					}
					$highestMarks[$value->examID][$value->subjectID][$value->markpercentageID] = max($value->mark, $highestMarks[$value->examID][$value->subjectID][$value->markpercentageID]);
				}
				$marksettings  = $this->marksetting_m->get_marksetting_markpercentages();

				$this->data['settingmarktypeID'] = $this->data['siteinfos']->marktypeID;
				$this->data['subjects']          = $subjectArr;
				$this->data['exams']             = $exams;
				$this->data['grades']            = $grades;
				$this->data['markpercentages']   = pluck($markpercentages, 'obj', 'markpercentageID');
				$this->data['optionalsubjectArr']= $optionalsubjectArr;
				$this->data['marks']             = $retMark;
				$this->data['highestmarks']      = $highestMarks;
				$this->data['marksettings']      = isset($marksettings[$classesID]) ? $marksettings[$classesID] : [];
			} else {
				$this->data['settingmarktypeID'] = 0;
				$this->data['subjects']          = [];
				$this->data['exams']             = [];
				$this->data['grades']            = [];
				$this->data['markpercentages']   = [];
				$this->data['optionalsubjectArr']= [];
				$this->data['marks']             = [];
				$this->data['highestmarks']      = [];
				$this->data['marksettings']      = [];
			}
		} else {
			$this->data['settingmarktypeID'] = 0;
			$this->data['subjects']          = [];
			$this->data['exams']             = [];
			$this->data['grades']            = [];
			$this->data['markpercentages']   = [];
			$this->data['optionalsubjectArr']= [];
			$this->data['marks']             = [];
			$this->data['highestmarks']      = [];
			$this->data['marksettings']      = [];
		}
	}

	private function sortArray($array) {
		arsort($array);
		return $array;
	}

	private function getMark2($marks) {
		$retMark = [];
		if(customCompute($marks)) {
			foreach ($marks as $mark) {
				$retMark[$mark->studentID][$mark->examID][$mark->subjectID][$mark->markpercentageID] = $mark->mark;
			}
		}
		return $retMark;
	}

	private function pluckInfo() {
		$schoolID               = $this->session->userdata('schoolID');
		$this->data['subjects'] = pluck($this->subject_m->general_get_order_by_subject(array('schoolID' => $schoolID)), 'subject', 'subjectID');
		$this->data['teachers'] = pluck($this->teacher_m->get_order_by_teacher(array('schoolID' => $schoolID)), 'name', 'teacherID');
		$this->data['feetypes'] = pluck($this->feetypes_m->get_order_by_feetypes(array('schoolID' => $schoolID)), 'feetypes', 'feetypesID');
	}

	private function basicInfo($studentInfo) {
		if(customCompute($studentInfo)) {
			$this->data['profile'] = $studentInfo;
			$this->data['usertype'] = $this->usertype_m->get_single_usertype(array('usertypeID' => 3));
			$this->data['class'] = $this->classes_m->get_single_classes(array('classesID' => $studentInfo->srclassesID));
			$this->data['section'] = $this->section_m->general_get_single_section(array('sectionID' => $studentInfo->srsectionID));
			$this->data['group'] = $this->studentgroup_m->get_single_studentgroup(array('studentgroupID' => $studentInfo->srstudentgroupID));
			$this->data['optionalsubject'] = $this->subject_m->general_get_single_subject(array('subjectID' => $studentInfo->sroptionalsubjectID));
		} else {
			$this->data['profile'] = [];
		}
	}

	private function parentInfo($studentInfo) {
		if(customCompute($studentInfo)) {
			$this->data['parents'] = $this->parents_m->get_single_parents(array('parentsID' => $studentInfo->parentID));
		} else {
			$this->data['parents'] = [];
		}
	}

	private function routineInfo($studentInfo) {
		$settingWeekends = [];
		if($this->data['siteinfos']->weekends != '') {
			$settingWeekends = explode(',', $this->data['siteinfos']->weekends);
		}
		$this->data['routineweekends'] = $settingWeekends;

		$this->data['routines'] = [];
		if(customCompute($studentInfo)) {
			$schoolyearID           = $this->session->userdata('defaultschoolyearID');
			$this->data['routines'] = pluck_multi_array($this->routine_m->get_order_by_routine(array('classesID'=>$studentInfo->srclassesID, 'sectionID'=>$studentInfo->srsectionID, 'schoolyearID'=> $schoolyearID)), 'obj', 'day');
		}
	}

	private function attendanceInfo($studentInfo) {
		$this->data['holidays'] =  $this->getHolidaysSession();
		$this->data['getWeekendDays'] =  $this->getWeekendDaysSession();
		if(customCompute($studentInfo)) {
			$this->data['setting'] = $this->setting_m->get_setting($studentInfo->schoolID);
			if($this->data['setting']->attendance == "subject") {
				$this->data["attendancesubjects"] = $this->subject_m->general_get_order_by_subject(array("classesID" => $studentInfo->srclassesID));
			}

			if($this->data['setting']->attendance == "subject") {
				$attendances = $this->subjectattendance_m->get_order_by_sub_attendance(array("studentID" => $studentInfo->srstudentID, "classesID" => $studentInfo->srclassesID));
				$this->data['attendances_subjectwisess'] = pluck_multi_array_key($attendances, 'obj', 'subjectID', 'monthyear');
			} else {
				$attendances = $this->sattendance_m->get_order_by_attendance(array("studentID" => $studentInfo->srstudentID, "classesID" => $studentInfo->srclassesID));
				$this->data['attendancesArray'] = pluck($attendances,'obj','monthyear');
			}
		} else {
			$this->data['setting'] = [];
			$this->data['attendancesubjects'] = [];
			$this->data['attendances_subjectwisess'] = [];
			$this->data['attendancesArray'] = [];
		}
	}

	private function markInfo($studentInfo) {
		if(customCompute($studentInfo)) {
			$this->getMark($studentInfo->srstudentID, $studentInfo->srclassesID);
		} else {
			$this->data['set'] 				= [];
			$this->data["exams"] 			= [];
			$this->data["grades"] 			= [];
			$this->data['markpercentages']	= [];
			$this->data['validExam'] 		= [];
			$this->data['separatedMarks'] 	= [];
			$this->data["highestMarks"] 	= [];
			$this->data["section"] 			= [];
		}
	}

	private function invoiceInfo($studentInfo) {
		$schoolyearID = $this->session->userdata('defaultschoolyearID');
		if(customCompute($studentInfo)) {
			$this->data['invoices'] = $this->invoice_m->get_order_by_invoice(array('schoolyearID' => $schoolyearID, 'studentID' => $studentInfo->srstudentID,'deleted_at' => 1));

			$payments = $this->payment_m->get_order_by_payment(array('schoolyearID' => $schoolyearID, 'studentID' => $studentInfo->srstudentID));
			$weaverandfines = $this->weaverandfine_m->get_order_by_weaverandfine(array('schoolyearID' => $schoolyearID, 'studentID' => $studentInfo->srstudentID));

			$this->data['allpaymentbyinvoice'] = $this->allPaymentByInvoice($payments);
			$this->data['allweaverandpaymentbyinvoice'] = $this->allWeaverAndFineByInvoice($weaverandfines);
		} else {
			$this->data['invoices'] = [];
			$this->data['allpaymentbyinvoice'] = [];
			$this->data['allweaverandpaymentbyinvoice'] = [];
		}
	}

	private function paymentInfo($studentInfo) {
		$schoolyearID = $this->session->userdata('defaultschoolyearID');
		if(customCompute($studentInfo)) {
			$this->data['payments'] = $this->payment_m->get_payment_with_studentrelation_by_studentID_and_schoolyearID($studentInfo->srstudentID, $schoolyearID);
		} else {
			$this->data['payments'] = [];
		}
	}

	private function examInfo($studentInfo) {
		if(customCompute($studentInfo)) {
			$schoolyearID = $this->session->userdata('defaultschoolyearID');
			$studentID = $studentInfo->srstudentID;
			$classesID = $studentInfo->srclassesID;
			$sectionID = $studentInfo->srsectionID;
			$studentQuery = [];
			$studentQuery['srclassesID']     = $classesID;
			$queryArray['schoolyearID']      = $schoolyearID;
			if($classesID > 0) {
				$queryArray['classesID'] = $classesID;
			}
			if($sectionID > 0) {
				$queryArray['sectionID'] = $sectionID;
				$studentQuery['srsectionID'] = $sectionID;
			}
			$studentQuery['srschoolyearID']  = $schoolyearID;

			$mandatorySubjects               = $this->subject_m->general_get_order_by_subject(array('classesID' => $classesID, 'type' => 1));
			$optionalSubjects                = $this->subject_m->general_get_order_by_subject(array('classesID' => $classesID, 'type' => 0));
			$this->data['student']           = $this->studentrelation_m->general_get_single_student(array('srclassesID' => $classesID, 'srschoolyearID' => $schoolyearID, 'srstudentID' => $studentID));
			$students                        = $this->studentrelation_m->general_get_order_by_student(array('srclassesID' => $classesID, 'srschoolyearID' => $schoolyearID));
			$this->data['classes']           = pluck($this->classes_m->general_get_order_by_classes(array('schoolID' => $schoolID)), 'classes', 'classesID');
			$this->data['sections']          = pluck($this->section_m->general_get_order_by_section(array('schoolID' => $schoolID)), 'section', 'sectionID');
			$this->data['groups']            = pluck($this->studentgroup_m->get_order_by_studentgroup(array('schoolID' => $schoolID)),'group','studentgroupID');
			$this->data['grades']            = $this->grade_m->get_order_by_grade(array('schoolID' => $schoolID));
			$this->data['percentageArr']     = pluck($this->markpercentage_m->get_order_by_markpercentage(array('schoolID' => $schoolID)), 'obj', 'markpercentageID');
			$exams                           = $this->exam_m->get_last_six_exams(array('studentID' => $studentID, 'schoolID' => $schoolID));
			$examID 												 = [];
			if(customCompute($exams)) {
				foreach($exams as $exam)
					$examID[] = $exam->examID;
				$marks                         = $this->mark_m->get_order_by_all_student_mark_with_markrelation2(['schoolyearID' => $schoolyearID, 'classesID' => $classesID, 'examID' => $examID]);
			}
			$this->data['marks']             = $this->getMark2($marks);
			$marks                  				 = $this->mark_m->student_all_mark_array($queryArray);

			$markpercentagesmainArr          = $this->marksetting_m->get_marksetting_markpercentages();
			$markpercentagesArr              = isset($markpercentagesmainArr[$classesID][$examID[0]]) ? $markpercentagesmainArr[$classesID][$examID[0]] : [];
			$settingmarktypeID               = $this->data['siteinfos']->marktypeID;

			$this->data['settingmarktypeID'] = $settingmarktypeID;
			$this->data['markpercentagesArr']= $markpercentagesArr;

			$this->data['classesID']       = $classesID;
			$this->data['sectionID']       = $sectionID;
			$this->data['studentID']       = $studentID;

			reset($markpercentagesArr);
			$firstindex                    = key($markpercentagesArr);
			$uniquepercentageArr           = isset($markpercentagesArr[$firstindex]) ? $markpercentagesArr[$firstindex] : [];
			$markpercentages               = $uniquepercentageArr[(($settingmarktypeID==4) || ($settingmarktypeID==6)) ? 'unique' : 'own'];
			$this->data['markpercentages'] = $markpercentages;

			$retMark = [];
			if(customCompute($marks)) {
				foreach ($marks as $mark) {
					$retMark[$mark->examID][$mark->studentID][$mark->subjectID][$mark->markpercentageID] = $mark->mark;
				}
			}

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
												if(isset($retMark[$exam->examID][$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID]) && $f) {
													$studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID] += $retMark[$exam->examID][$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID];
												} else {
													$studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID] += 0;
												}
											} else {
												if(isset($retMark[$exam->examID][$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID]) && $f) {
													$studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID] = $retMark[$exam->examID][$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID];
												} else {
													$studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID] = 0;
												}
											}

											if(isset($retMark[$exam->examID][$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID]) && $f) {
												$studentPosition[$exam->examID][$student->srstudentID]['markpercentageMark'][$mandatorySubject->subjectID][$markpercentageID] = $retMark[$exam->examID][$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID];

												if(isset($studentPosition[$exam->examID][$student->srstudentID][$student->srstudentID]['markpercentagetotalmark'][$markpercentageID])) {
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
														if(isset($retMark[$exam->examID][$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID])) {
															$studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID] += $retMark[$exam->examID][$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID];
														} else {
															$studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID] += 0;
														}
													} else {
														if(isset($retMark[$exam->examID][$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID])) {
															$studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID] = $retMark[$exam->examID][$student->srstudentID][$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID];
														} else {
															$studentPosition[$exam->examID][$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID] = 0;
														}
													}

													if(isset($retMark[$exam->examID][$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID])) {
														$studentPosition[$exam->examID][$student->srstudentID]['markpercentageMark'][$student->sroptionalsubjectID][$markpercentageID] = $retMark[$exam->examID][$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID];

														if(isset($studentPosition[$exam->examID][$student->srstudentID][$student->srstudentID]['markpercentagetotalmark'][$markpercentageID])) {
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
			$this->data['optionalsubjects']  = $optionalSubjects;
			$this->data['studentPosition']   = $studentPosition;
			$this->data['exams2']            = $exams;
			$this->data['students']          = $students;
		}
	}

	protected function rules_documentupload() {
		$rules = array(
			array(
				'field' => 'title',
				'label' => $this->lang->line("student_name"),
				'rules' => 'trim|required|xss_clean|max_length[128]'
			),
			array(
				'field' => 'file',
				'label' => $this->lang->line("student_file"),
				'rules' => 'trim|xss_clean|max_length[200]|callback_unique_document_upload'
			)
		);

		return $rules;
	}

	public function unique_document_upload() {
		$new_file = '';
		if($_FILES["file"]['name'] !="") {
			$file_name = $_FILES["file"]['name'];
			$random = random19();
	    $makeRandom = hash('sha512', $random.(strtotime(date('Y-m-d H:i:s'))). config_item("encryption_key"));
			$file_name_rename = $makeRandom;
      $explode = explode('.', $file_name);
      if(customCompute($explode) >= 2) {
        $new_file = $file_name_rename.'.'.end($explode);
				$config['upload_path'] = "./uploads/documents";
				$config['allowed_types'] = "gif|jpg|png|jpeg|pdf|doc|xml|docx|GIF|JPG|PNG|JPEG|PDF|DOC|XML|DOCX|xls|xlsx|txt|ppt|csv";
				$config['file_name'] = $new_file;
				$config['max_size'] = '5120';
				$config['max_width'] = '10000';
				$config['max_height'] = '10000';
				$this->load->library('upload', $config);
				if(!$this->upload->do_upload("file")) {
					$this->form_validation->set_message("unique_document_upload", $this->upload->display_errors());
	     		return FALSE;
				} else {
					$this->upload_data['file'] =  $this->upload->data();
					return TRUE;
				}
			} else {
				$this->form_validation->set_message("unique_document_upload", "Invalid file");
	     	return FALSE;
			}
		} else {
			$this->form_validation->set_message("unique_document_upload", "The file is required.");
			return FALSE;
		}
	}

	public function documentUpload() {
		$retArray['status'] = FALSE;
		$retArray['render'] = '';

		if(permissionChecker('student_add')) {
			if($_POST) {
				$rules = $this->rules_documentupload();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {
					$retArray['errors'] = $this->form_validation->error_array();
					$retArray['status'] = FALSE;
			    echo json_encode($retArray);
			    exit;
				} else {
					$title = $this->input->post('title');
					$file = $this->upload_data['file']['file_name'];
					$userID = $this->input->post('studentID');

					$array = array(
						'title' => $title,
						'file' => $file,
						'userID' => $userID,
						'usertypeID' => 3,
						"create_date" => date("Y-m-d H:i:s"),
						"create_userID" => $this->session->userdata('loginuserID'),
						"create_usertypeID" => $this->session->userdata('usertypeID')
					);

					$this->document_m->insert_document($array);
					$this->session->set_flashdata('success', $this->lang->line('menu_success'));

					$retArray['status'] = TRUE;
					$retArray['render'] = 'Success';
			    echo json_encode($retArray);
			    exit;
				}
			} else {
				$retArray['status'] = FALSE;
				$retArray['render'] = 'Error';
		    echo json_encode($retArray);
		    exit;
			}
		} else {
			$retArray['status'] = FALSE;
			$retArray['render'] = 'Permission Denay.';
	    echo json_encode($retArray);
	    exit;
		}
	}

	private function documentInfo($studentInfo) {
		if(customCompute($studentInfo)) {
			$this->data['documents'] = $this->document_m->get_order_by_document(array('usertypeID' => 3, 'userID' => $studentInfo->srstudentID));
		} else {
			$this->data['documents'] = [];
		}
	}

	public function download_document() {
		$id 		= htmlentities(escapeString($this->uri->segment(3)));
		$studentID 	= htmlentities(escapeString($this->uri->segment(4)));
		$classesID 	= htmlentities(escapeString($this->uri->segment(5)));
		if((int)$id && (int)$studentID && (int)$classesID) {
			if((permissionChecker('student_add') && permissionChecker('student_delete')) || ($this->session->userdata('usertypeID') == 3 && $this->session->userdata('loginuserID') == $studentID)) {
				$document = $this->document_m->get_single_document(array('documentID' => $id));
				if(customCompute($document)) {
					$file = realpath('uploads/documents/'.$document->file);
				    if (file_exists($file)) {
				    	$expFileName = explode('.', $file);
							$originalname = ($document->title).'.'.end($expFileName);
				    	header('Content-Description: File Transfer');
					    header('Content-Type: application/octet-stream');
					    header('Content-Disposition: attachment; filename="'.basename($originalname).'"');
					    header('Expires: 0');
					    header('Cache-Control: must-revalidate');
					    header('Pragma: public');
					    header('Content-Length: ' . filesize($file));
					    readfile($file);
					    exit;
				    } else {
				    	redirect(base_url('student/view/'.$studentID.'/'.$classesID));
				    }
				} else {
					redirect(base_url('student/view/'.$studentID.'/'.$classesID));
				}
			} else {
				redirect(base_url('student/view/'.$studentID.'/'.$classesID));
			}
		} else {
			redirect(base_url('student/index'));
		}
	}

	public function delete_document() {
		$id 		= htmlentities(escapeString($this->uri->segment(3)));
		$studentID 	= htmlentities(escapeString($this->uri->segment(4)));
		$classesID 	= htmlentities(escapeString($this->uri->segment(5)));
		if((int)$id && (int)$studentID && (int)$classesID) {
			if(permissionChecker('student_add') && permissionChecker('student_delete')) {
				$document = $this->document_m->get_single_document(array('documentID' => $id));
				if(customCompute($document)) {
					if(config_item('demo') == FALSE) {
						if(file_exists(FCPATH.'uploads/document/'.$document->file)) {
							unlink(FCPATH.'uploads/document/'.$document->file);
						}
					}

					$this->document_m->delete_document($id);
					$this->session->set_flashdata('success', $this->lang->line('menu_success'));
					redirect(base_url('student/view/'.$studentID.'/'.$classesID));
				} else {
					redirect(base_url('student/view/'.$studentID.'/'.$classesID));
				}
			} else {
				redirect(base_url('student/view/'.$studentID.'/'.$classesID));
			}
		} else {
			redirect(base_url('student/index'));
		}
	}

	protected function rules() {
		$rules = array(
			array(
				'field' => 'name',
				'label' => $this->lang->line("student_name"),
				'rules' => 'trim|required|xss_clean|max_length[60]'
			),
			array(
				'field' => 'dob',
				'label' => $this->lang->line("student_dob"),
				'rules' => 'trim|max_length[10]|callback_date_valid|xss_clean'
			),
			array(
				'field' => 'sex',
				'label' => $this->lang->line("student_sex"),
				'rules' => 'trim|required|max_length[10]|xss_clean'
			),
			array(
				'field' => 'bloodgroup',
				'label' => $this->lang->line("student_bloodgroup"),
				'rules' => 'trim|max_length[5]|xss_clean'
			),
			array(
				'field' => 'religion',
				'label' => $this->lang->line("student_religion"),
				'rules' => 'trim|max_length[25]|xss_clean'
			),
			array(
				'field' => 'email',
				'label' => $this->lang->line("student_email"),
				'rules' => 'trim|max_length[40]|valid_email|xss_clean|callback_unique_email'
			),
			array(
				'field' => 'phone',
				'label' => $this->lang->line("student_phone"),
				'rules' => 'trim|max_length[25]|min_length[5]|xss_clean'
			),
			array(
				'field' => 'address',
				'label' => $this->lang->line("student_address"),
				'rules' => 'trim|max_length[200]|xss_clean'
			),
			array(
				'field' => 'state',
				'label' => $this->lang->line("student_state"),
				'rules' => 'trim|max_length[128]|xss_clean'
			),
			array(
				'field' => 'country',
				'label' => $this->lang->line("student_country"),
				'rules' => 'trim|max_length[128]|xss_clean'
			),
			array(
				'field' => 'classesID',
				'label' => $this->lang->line("student_classes"),
				'rules' => 'trim|required|numeric|max_length[11]|xss_clean|callback_unique_classesID'
			),
			array(
				'field' => 'sectionID',
				'label' => $this->lang->line("student_section"),
				'rules' => 'trim|required|numeric|max_length[11]|xss_clean|callback_unique_sectionID|callback_unique_capacity'
			),
			/*array(
				'field' => 'registerNO',
				'label' => $this->lang->line("student_registerNO"),
				'rules' => 'trim|required|max_length[40]|callback_unique_registerNO|xss_clean'
			),*/
			array(
				'field' => 'roll',
				'label' => $this->lang->line("student_roll"),
				'rules' => 'trim|required|max_length[11]|numeric|callback_unique_roll|xss_clean'
			),
			array(
				'field' => 'guargianID',
				'label' => $this->lang->line("student_guargian"),
				'rules' => 'trim|required|numeric|max_length[11]|xss_clean'
			),
			array(
				'field' => 'photo',
				'label' => $this->lang->line("student_photo"),
				'rules' => 'trim|max_length[200]|xss_clean|callback_photoupload'
			),
      array(
          'field' => 'studentGroupID',
          'label' => $this->lang->line("student_studentgroup"),
          'rules' => 'trim|max_length[11]|xss_clean|numeric'
      ),

      array(
          'field' => 'optionalSubjectID',
          'label' => $this->lang->line("student_optionalsubject"),
					'rules' => 'trim|xss_clean|callback_valid_optionalsubject'
			),

			array(
          'field' => 'nonexaminableSubjectID',
          'label' => $this->lang->line("student_nonexaminablesubject"),
          'rules' => 'trim|xss_clean|callback_valid_nonexaminablesubject'
      ),

      array(
          'field' => 'extraCurricularActivities',
          'label' => $this->lang->line("student_extracurricularactivities"),
          'rules' => 'trim|max_length[128]|xss_clean'
      ),

      array(
          'field' => 'remarks',
          'label' => $this->lang->line("student_remarks"),
          'rules' => 'trim|max_length[128]|xss_clean'
      ),
			array(
				'field' => 'admission_date',
				'label' => $this->lang->line("student_admission_date"),
				'rules' => 'trim|max_length[10]|callback_date_valid|xss_clean'
			),
			array(
				'field' => 'username',
				'label' => $this->lang->line("student_username"),
				'rules' => 'trim|required|min_length[4]|max_length[40]|xss_clean|callback_lol_username'
			),
			/*array(
				'field' => 'password',
				'label' => $this->lang->line("student_password"),
				'rules' => 'trim|required|min_length[4]|max_length[40]|xss_clean'
			)*/

			array(
				'field' => 'parent_name',
				'label' => $this->lang->line("parent_guargian_name"),
				'rules' => 'trim|xss_clean|max_length[60]|callback_parent_name'
			),
			array(
				'field' => 'father_name',
				'label' => $this->lang->line("parent_father_name"),
				'rules' => 'trim|xss_clean|max_length[60]'
			),
			array(
				'field' => 'mother_name',
				'label' => $this->lang->line("parent_mother_name"),
				'rules' => 'trim|xss_clean|max_length[60]'
			),
			array(
				'field' => 'father_profession',
				'label' => $this->lang->line("parent_father_name"),
				'rules' => 'trim|xss_clean|max_length[40]'
			),
			array(
				'field' => 'mother_profession',
				'label' => $this->lang->line("parent_mother_name"),
				'rules' => 'trim|xss_clean|max_length[40]'
			),
			array(
				'field' => 'parent_email',
				'label' => $this->lang->line("parent_email"),
				'rules' => 'trim|max_length[40]|valid_email|xss_clean|callback_unique_email'
			),
			array(
				'field' => 'parent_phone',
				'label' => $this->lang->line("parent_phone"),
				'rules' => 'trim|min_length[5]|max_length[25]|xss_clean'
			),
			array(
				'field' => 'parent_address',
				'label' => $this->lang->line("parent_address"),
				'rules' => 'trim|max_length[200]|xss_clean'
			),
			array(
				'field' => 'parent_photo',
				'label' => $this->lang->line("student_photo"),
				'rules' => 'trim|max_length[200]|xss_clean|callback_parentphotoupload'
			),
			array(
				'field' => 'parent_username',
				'label' => $this->lang->line("parent_username"),
				'rules' => 'trim|min_length[4]|max_length[40]|xss_clean|callback_parent_username'
			),
			array(
				'field' => 'parent_password',
				'label' => $this->lang->line("student_password"),
				'rules' => 'trim|min_length[4]|max_length[40]|xss_clean|callback_parent_password'
			)
		);
		return $rules;
	}

	public function photoupload() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		$student = array();
		if((int)$id) {
			$student = $this->student_m->general_get_single_student(array('studentID' => $id));
		}

		$new_file = "default.png";
		if($_FILES["photo"]['name'] !="") {
			$file_name = $_FILES["photo"]['name'];
			$random = random19();
	    $makeRandom = hash('sha512', $random.$this->input->post('username') . config_item("encryption_key"));
			$file_name_rename = $makeRandom;
      $explode = explode('.', $file_name);
      if(customCompute($explode) >= 2) {
        $new_file = $file_name_rename.'.'.end($explode);
				$config['upload_path'] = "./uploads/images";
				$config['allowed_types'] = "gif|jpg|png";
				$config['file_name'] = $new_file;
				$config['max_size'] = '1024';
				$config['max_width'] = '3000';
				$config['max_height'] = '3000';
				$this->load->library('upload', $config);
				if(!$this->upload->do_upload("photo")) {
					$this->form_validation->set_message("photoupload", $this->upload->display_errors());
	     		return FALSE;
				} else {
					$this->upload_data['file'] =  $this->upload->data();
					return TRUE;
				}
			} else {
				$this->form_validation->set_message("photoupload", "Invalid file");
	     	return FALSE;
			}
		} else {
			if(customCompute($student)) {
				$this->upload_data['file'] = array('file_name' => $student->photo);
				return TRUE;
			} else {
				$this->upload_data['file'] = array('file_name' => $new_file);
				return TRUE;
			}
		}
	}

	public function parentphotoupload() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		$user = [];
		if((int)$id) {
			$user = $this->parents_m->get_single_parents(array('parentsID' => $id));
		}

		$new_file = "default.png";
		if($_FILES["parent_photo"]['name'] !="") {
			$file_name = $_FILES["parent_photo"]['name'];
			$random = random19();
	    $makeRandom = hash('sha512', $random.$this->input->post('parent_username') . config_item("encryption_key"));
			$file_name_rename = $makeRandom;
      $explode = explode('.', $file_name);
      if(customCompute($explode) >= 2) {
        $new_file = $file_name_rename.'.'.end($explode);
				$config['upload_path'] = "./uploads/images";
				$config['allowed_types'] = "gif|jpg|png";
				$config['file_name'] = $new_file;
				$config['max_size'] = '1024';
				$config['max_width'] = '3000';
				$config['max_height'] = '3000';
				$this->load->library('upload', $config);
				if(!$this->upload->do_upload("parent_photo")) {
					$this->form_validation->set_message("parentphotoupload", $this->upload->display_errors());
	     		return FALSE;
				} else {
					$this->upload_data['parent_file'] =  $this->upload->data();
					return TRUE;
				}
			} else {
				$this->form_validation->set_message("parentphotoupload", "Invalid file");
	     	return FALSE;
			}
		} else {
			if(customCompute($user)) {
				$this->upload_data['parent_file'] = array('file_name' => $user->photo);
				return TRUE;
			} else {
				$this->upload_data['parent_file'] = array('file_name' => $new_file);
				return TRUE;
			}
		}
	}

	public function index() {
		$this->data['headerassets'] = array(
			'css' => array(
				'assets/select2/css/select2.css',
				'assets/select2/css/select2-bootstrap.css'
			),
			'js' => array(
				'assets/select2/select2.js'
			)
		);

		$myProfile = false;
		$schoolID = $this->session->userdata('schoolID');
		$schoolyearID = $this->session->userdata('defaultschoolyearID');
		if($this->session->userdata('usertypeID') == 3) {
			$id = $this->data['myclass'];
			if(!permissionChecker('student_view')) {
				$myProfile = true;
			}
		} else {
			$id = htmlentities(escapeString($this->uri->segment(3)));
		}

		if($this->session->userdata('usertypeID') == 3 && $myProfile) {
			$url = $id;
			$id = $this->session->userdata('loginuserID');
			$this->getView($id, $url);
		} else {
			$this->data['set'] = $id;
			$this->data['classes'] = $this->classes_m->get_order_by_classes(array('schoolID' => $schoolID));

			if((int)$id)
				$this->data['students'] = $this->studentrelation_m->get_order_by_student(array('srclassesID' => $id, 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID));
			else
				$this->data['students'] = $this->studentrelation_m->get_order_by_student(array('srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID));
			if(customCompute($this->data['students'])) {
				$sections = $this->section_m->general_get_order_by_section(array("classesID" => $id, 'schoolID' => $schoolID));
				$this->data['sections'] = $sections;
				foreach ($sections as $key => $section) {
					$this->data['allsection'][$section->sectionID] = $this->studentrelation_m->get_order_by_student(array('srclassesID' => $id, "srsectionID" => $section->sectionID, 'srschoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
				}
			} else {
				$this->data['students'] = [];
			}

			$this->data["authUrl"] = isset($_SESSION['authUrl']) ? $_SESSION['authUrl'] : '';
			$this->data['config'] = $this->quickbooksConfig();
			//} else {
			//	$this->data['students'] = [];
			//}

			$this->data["subview"] = "student/index";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function add() {

		if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1)) {
			$this->data['headerassets'] = array(
				'css' => array(
					'assets/datepicker/datepicker.css',
					'assets/select2/css/select2.css',
					'assets/select2/css/select2-bootstrap.css'
				),
				'js' => array(
					'assets/datepicker/datepicker.js',
					'assets/select2/select2.js'
				)
			);

			$schoolyearID = $this->session->userdata('defaultschoolyearID');
			$schoolID = $this->session->userdata('schoolID');
			$this->data['classes'] = $this->classes_m->get_order_by_classes(array('schoolID' => $schoolID));
			$this->data['sections'] = $this->section_m->general_get_order_by_section(array('schoolID' => $schoolID));
			$this->data['parents'] = $this->parents_m->get_order_by_parents(array('schoolID' => $schoolID));
			$this->data['studentgroups'] = $this->studentgroup_m->get_order_by_studentgroup(array('schoolID' => $schoolID));

			$classesID = $this->input->post("classesID");

			if($classesID > 0) {
				$this->data['sections'] = $this->section_m->general_get_order_by_section(array("classesID" =>$classesID, "schoolID" =>$schoolID));
	      $this->data['optionalSubjects'] = $this->subject_m->general_get_order_by_subject(array("classesID" =>$classesID, 'type' => 0, 'schoolID' => $schoolID));
				$this->data['nonexaminableSubjects'] = $this->subject_m->general_get_order_by_subject(array("classesID" =>$classesID, 'type' => 2, 'schoolID' => $schoolID));
			} else {
				$this->data['sections'] = [];
				$this->data['optionalSubjects'] = [];
				$this->data['nonexaminableSubjects'] = [];
			}

			$this->data['sectionID'] = $this->input->post("sectionID");
	    //$this->data['optionalSubjectID'] = 0;

			if($_POST) {
				$rules = $this->rules();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {
					$this->data["subview"] = "student/add";
					$this->load->view('_layout_main', $this->data);
				} else {
					$sectionID = $this->input->post("sectionID");
					if($sectionID == 0) {
						$this->data['sectionID'] = 0;
					} else {
						$this->data['sections'] = $this->section_m->general_get_order_by_section(array('classesID' => $classesID, 'schoolID' => $schoolID));
						$this->data['sectionID'] = $this->input->post("sectionID");
					}

					if($this->input->post('optionalSubjectID')) {
              $this->data['optionalSubjectID'] = $this->input->post('optionalSubjectID');
          }

					$guargianID = $this->input->post('guargianID');

					if ($guargianID == 0) {
						$array = array();
						$array['name'] = $this->input->post("parent_name");
						$array['father_name'] = $this->input->post("father_name");
						$array['mother_name'] = $this->input->post("mother_name");
						$array['father_profession'] = $this->input->post("father_profession");
						$array['mother_profession'] = $this->input->post("mother_profession");
						$array['email'] = $this->input->post("parent_email");
						$array['phone'] = $this->input->post("countrycode") . $this->input->post("parent_phone");
						$array['address'] = $this->input->post("parent_address");
						$array['username'] = $this->input->post("parent_username");
						$array['password'] = $this->student_m->hash($this->input->post("parent_password"));
						$array['usertypeID'] = 4;
						$array["create_date"] = date("Y-m-d h:i:s");
						$array["modify_date"] = date("Y-m-d h:i:s");
						$array["create_userID"] = $this->session->userdata('loginuserID');
						$array["create_username"] = $this->session->userdata('username');
						$array["create_usertype"] = $this->session->userdata('usertype');
						$array["active"] = 1;
						$array['photo'] = $this->upload_data['parent_file']['file_name'];
						$array['schoolID'] = $schoolID;

						// For Email
						$this->usercreatemail($this->input->post('parent_email'), $this->input->post('parent_username'), $this->input->post('parent_password'));

						$this->parents_m->insert_parents($array);
						$guargianID = $this->db->insert_id();
					}

					$parent = $this->parents_m->get_single_parents(array('parentsID' => $guargianID));

					$array = array();
					$array["name"] = $this->input->post("name");
					$array["sex"] = $this->input->post("sex");
					$array["religion"] = $this->input->post("religion");
					$array["email"] = $this->input->post("email");
					$array["phone"] = $this->input->post("phone");
					$array["address"] = $this->input->post("address");
					$array["classesID"] = $this->input->post("classesID");
					$array["sectionID"] = $this->input->post("sectionID");
					$array["roll"] = $this->input->post("roll");
					$array["bloodgroup"] = $this->input->post("bloodgroup");
					$array["state"] = $this->input->post("state");
					$array["country"] = $this->input->post("country");
					//$array["registerNO"] = $this->input->post("registerNO");
					$array["username"] = $this->input->post("username");
					$array['password'] = $this->student_m->hash($this->input->post("password"));
					$array['usertypeID'] = 3;
					$array['parentID'] = $guargianID;
					$array['library'] = 0;
					$array['hostel'] = 0;
					$array['transport'] = 0;
					$array['createschoolyearID'] = $schoolyearID;
					$array['schoolyearID'] = $schoolyearID;
					$array["create_date"] = date("Y-m-d H:i:s");
					$array["modify_date"] = date("Y-m-d H:i:s");
					$array["create_userID"] = $this->session->userdata('loginuserID');
					$array["create_username"] = $this->session->userdata('username');
					$array["create_usertype"] = $this->session->userdata('usertype');
					$array['schoolID'] = $schoolID;
					$array["active"] = 1;

					if($this->input->post('dob')) {
						$array["dob"] = date("Y-m-d", strtotime($this->input->post("dob")));
					}
					if($this->input->post('admission_date')) {
						$array["admission_date"] = date("Y-m-d", strtotime($this->input->post("admission_date")));
					}
					$array['photo'] = $this->upload_data['file']['file_name'];
					$this->usercreatemail($this->input->post('email'), $this->input->post('username'), $this->input->post('password'));

					$this->student_m->insert_student($array);
					$studentID = $this->db->insert_id();

					$section = $this->section_m->general_get_single_section(array("sectionID" => $this->input->post("sectionID"), "schoolID" => $schoolID));
					$classes = $this->classes_m->get_classes($this->input->post("classesID"));

					if(customCompute($classes)) {
						$setClasses = $classes->classes;
					} else {
						$setClasses = NULL;
					}

					if(customCompute($section)) {
						$setSection = $section->section;
					} else {
						$setSection = NULL;
					}

					$optionalSubjectID = $this->input->post("optionalSubjectID");
					$nonexaminableSubjectID = $this->input->post("nonexaminableSubjectID");

					$arrayStudentRelation = array(
						'srstudentID' => $studentID,
						'srname' => $this->input->post("name"),
						'srclassesID' => $this->input->post("classesID"),
						'srclasses' => $setClasses,
						'srroll' => $this->input->post("roll"),
						//'srregisterNO' => $this->input->post("registerNO"),
						'srsectionID' => $this->input->post("sectionID"),
						'srsection' => $setSection,
						'sroptionalsubjectID' => !empty($optionalSubjectID) ? implode(",", $optionalSubjectID) : NULL,
						'srnonexaminablesubjectID' => !empty($nonexaminableSubjectID) ? implode(",", $nonexaminableSubjectID) : NULL,
						'srschoolyearID' => $schoolyearID,
						'srschoolID' => $schoolID,
					);

          $studentExtendArray = array(
              'studentID' => $studentID,
              'studentgroupID' => $this->input->post('studentGroupID'),
							'optionalsubjectID' => !empty($optionalSubjectID) ? implode(",", $optionalSubjectID) : NULL,
							'nonexaminablesubjectID' => !empty($nonexaminableSubjectID) ? implode(",", $nonexaminableSubjectID) : NULL,
              'extracurricularactivities' => $this->input->post('extraCurricularActivities'),
              'remarks' => $this->input->post('remarks')
          );

	        $this->studentextend_m->insert_studentextend($studentExtendArray);
					$this->studentrelation_m->insert_studentrelation($arrayStudentRelation);

					$this->data['studentgroups'] = pluck($this->studentgroup_m->get_order_by_studentgroup(array('schoolID' => $schoolID)), 'group', 'studentgroupID');
					$this->data['optionalSubjects'] = pluck($this->subject_m->general_get_order_by_subject(array('type' => 0, 'schoolID' => $schoolID)), 'subject', 'subjectID');
					$this->data["student"] = $this->studentrelation_m->get_single_student(array('srstudentID' => $studentID, 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID), TRUE);
					if(customCompute($this->data["student"])) {
						$this->data['usertype'] = $this->usertype_m->get_single_usertype(array('usertypeID' => $this->data['student']->usertypeID));
						$this->data["class"] = $this->classes_m->general_get_classes($this->data['student']->srclassesID);
						$this->data["section"] = $this->section_m->general_get_section($this->data['student']->srsectionID);
					}
					$email = $parent->email;
					$subject = "ADMISSION FORM";
					$message = "Hi ". $parent->name .", \n\nPlease find attached admission form for ". $this->input->post("name") .".\n\nWarm regards\nAdmin";
					$this->reportSendToMail('studentmodule.css', $this->data, 'student/print_preview', $email, $subject, $message);

					$this->session->set_flashdata('success', $this->lang->line('menu_success'));
					redirect(base_url("student/view/".$studentID));
				}
			} else {
				$this->data["subview"] = "student/add";
				$this->load->view('_layout_main', $this->data);
			}
		} else {
			$this->data["subview"] = "error";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function import() {

		if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1)) {

				$this->data["subview"] = "student/import";
				$this->load->view('_layout_main', $this->data);
		} else {
			$this->data["subview"] = "error";
			$this->load->view('_layout_main');
		}
	}

	public function edit() {
		if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1)) {
			$this->data['headerassets'] = array(
				'css' => array(
					'assets/datepicker/datepicker.css',
					'assets/select2/css/select2.css',
					'assets/select2/css/select2-bootstrap.css'
				),
				'js' => array(
					'assets/datepicker/datepicker.js',
					'assets/select2/select2.js'
				)
			);
			$usertype = $this->session->userdata("usertype");
			$schoolyearID = $this->session->userdata('defaultschoolyearID');
			$schoolID = $this->session->userdata('schoolID');
			$studentID = htmlentities(escapeString($this->uri->segment(3)));
			$url = htmlentities(escapeString($this->uri->segment(3)));
			if((int)$studentID && (int)$url) {
				$this->data['classes'] = $this->classes_m->get_order_by_classes(array('schoolID' => $schoolID));
				$this->data['student'] = $this->studentrelation_m->get_single_student(array('srstudentID' => $studentID, 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID), TRUE);

				$this->data['parents'] = $this->parents_m->get_order_by_parents(array('schoolID' => $schoolID));
	      $this->data['studentgroups'] = $this->studentgroup_m->get_order_by_studentgroup(array('schoolID' => $schoolID));

				if(customCompute($this->data['student'])) {
					$classesID = $this->data['student']->srclassesID;
					$this->data['sections'] = $this->section_m->general_get_order_by_section(array('classesID' => $classesID, 'schoolID' => $schoolID));
          $this->data['optionalSubjects'] = $this->subject_m->general_get_order_by_subject(array("classesID" =>$classesID, 'type' => 0, 'schoolID' => $schoolID));
					$this->data['nonexaminableSubjects'] = $this->subject_m->general_get_order_by_subject(array("classesID" =>$classesID, 'type' => 2, 'schoolID' => $schoolID));
          if($this->input->post('optionalSubjectID')) {
              $this->data['optionalSubjectID'] = $this->input->post('optionalSubjectID');
          } else {
              $this->data['optionalSubjectID'] = 0;
          }
				}

				$this->data['set'] = $url;
				if(customCompute($this->data['student'])) {
					if($_POST) {
						$rules = $this->rules();
						unset($rules[22]);
						$this->form_validation->set_rules($rules);
						if ($this->form_validation->run() == FALSE) {
							$this->data["subview"] = "student/edit";
							$this->load->view('_layout_main', $this->data);
						} else {
							$array = array();
							$array["name"] = $this->input->post("name");
							$array["sex"] = $this->input->post("sex");
							$array["religion"] = $this->input->post("religion");
							$array["email"] = $this->input->post("email");
							$array["phone"] = $this->input->post("phone");
							$array["address"] = $this->input->post("address");
							$array["classesID"] = $this->input->post("classesID");
							$array["sectionID"] = $this->input->post("sectionID");
							$array["roll"] = $this->input->post("roll");
							$array["bloodgroup"] = $this->input->post("bloodgroup");
							$array["state"] = $this->input->post("state");
							$array["country"] = $this->input->post("country");
							//$array["registerNO"] = $this->input->post("registerNO");
							$array["parentID"] = $this->input->post("guargianID");
							$array["username"] = $this->input->post("username");
							$array["modify_date"] = date("Y-m-d H:i:s");
							$array['photo'] = $this->upload_data['file']['file_name'];

							if($this->input->post('dob')) {
								$array["dob"] 	= date("Y-m-d", strtotime($this->input->post("dob")));
							} else {
								$array["dob"] = NULL;
							}

							if($this->input->post('admission_date')) {
								$array["admission_date"] 	= date("Y-m-d", strtotime($this->input->post("admission_date")));
							} else {
								$array["admission_date"] = NULL;
							}


							$studentReletion = $this->studentrelation_m->general_get_order_by_student(array('srstudentID' => $studentID, 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID));
							$section = $this->section_m->general_get_order_by_section($this->input->post("sectionID"));
							$classes = $this->classes_m ->get_order_by_classes($this->input->post("classesID"));

							if(customCompute($classes)) {
								$setClasses = $classes->classes;
							} else {
								$setClasses = NULL;
							}

							if(customCompute($section)) {
								$setSection = $section->section;
							} else {
								$setSection = NULL;
							}

							$optionalSubjectID = $this->input->post("optionalSubjectID");
							$nonexaminableSubjectID = $this->input->post("nonexaminableSubjectID");

							if(!customCompute($studentReletion)) {
								$arrayStudentRelation = array(
									'srstudentID' => $studentID,
									'srname' => $this->input->post("name"),
									'srclassesID' => $this->input->post("classesID"),
									'srclasses' => $setClasses,
									'srroll' => $this->input->post("roll"),
									//'srregisterNO' => $this->input->post("registerNO"),
									'srsectionID' => $this->input->post("sectionID"),
									'srsection' => $setSection,
									'srstudentgroupID' => $this->input->post("studentGroupID"),
									'sroptionalsubjectID' => !empty($optionalSubjectID) ? implode(",", $optionalSubjectID) : NULL,
									'srnonexaminablesubjectID' => !empty($nonexaminableSubjectID) ? implode(",", $nonexaminableSubjectID) : NULL,
									'srschoolyearID' => $schoolyearID,
									'srschoolID' => $schoolID,
								);
								$this->studentrelation_m->insert_studentrelation($arrayStudentRelation);
							} else {
								if (!empty($optionalSubjectID) && ($key = array_search("0", $optionalSubjectID)) !== false) {
    							unset($optionalSubjectID[$key]);
								}

								if (!empty($nonexaminableSubjectID) && ($key = array_search("0", $nonexaminableSubjectID)) !== false) {
    							unset($nonexaminableSubjectID[$key]);
								}

								$arrayStudentRelation = array(
									'srname' => $this->input->post("name"),
									'srclassesID' => $this->input->post("classesID"),
									'srclasses' => $setClasses,
									'srroll' => $this->input->post("roll"),
									'srregisterNO' => $this->input->post("registerNO"),
									'srsectionID' => $this->input->post("sectionID"),
									'srsection' => $setSection,
									'srstudentgroupID' => $this->input->post("studentGroupID"),
									'sroptionalsubjectID' => !empty($optionalSubjectID) ? implode(",", $optionalSubjectID) : NULL,
									'srnonexaminablesubjectID' => !empty($nonexaminableSubjectID) ? implode(",", $nonexaminableSubjectID) : NULL,
								);
								$this->studentrelation_m->update_studentrelation_with_multicondition($arrayStudentRelation, array('srstudentID' => $studentID, 'srschoolyearID' => $schoolyearID));
							}

              $studentExtendArray = array(
                  'studentgroupID' => $this->input->post('studentGroupID'),
                  'optionalsubjectID' => !empty($optionalSubjectID) ? implode(",", $optionalSubjectID) : NULL,
									'nonexaminablesubjectID' => !empty($nonexaminableSubjectID) ? implode(",", $nonexaminableSubjectID) : NULL,
                  'extracurricularactivities' => $this->input->post('extraCurricularActivities'),
                  'remarks' => $this->input->post('remarks')
              );

	            $this->studentextend_m->update_studentextend_by_studentID($studentExtendArray, $studentID);
							$this->student_m->update_student($array, $studentID);
							$this->session->set_flashdata('success', $this->lang->line('menu_success'));
							redirect(base_url("student/index"));
						}
					} else {
						$this->data["subview"] = "student/edit";
						$this->load->view('_layout_main', $this->data);
					}
				} else {
					$this->data["subview"] = "error";
					$this->load->view('_layout_main', $this->data);
				}
			} else {
				$this->data["subview"] = "error";
				$this->load->view('_layout_main', $this->data);
			}
		} else {
			$this->data["subview"] = "error";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function view() {
		$this->data['headerassets'] = array(
			'css' => array(
				'assets/custom-scrollbar/jquery.mCustomScrollbar.css'
			),
			'js' => array(
				'assets/custom-scrollbar/jquery.mCustomScrollbar.concat.min.js'
			)
		);

		$schoolyearID = $this->session->userdata('defaultschoolyearID');
		$id = htmlentities(escapeString($this->uri->segment(3)));
		$url = htmlentities(escapeString($this->uri->segment(3)));
		$this->getView($id, $url);
	}

	public function print_preview() {
		if(permissionChecker('student_view') || (($this->session->userdata('usertypeID') == 3) && permissionChecker('student') && ($this->session->userdata('loginuserID') == htmlentities(escapeString($this->uri->segment(3)))))) {
			$schoolID = $this->session->userdata('schoolID');
			$usertypeID = $this->session->userdata('usertypeID');
			$schoolyearID = $this->session->userdata('defaultschoolyearID');
			$this->data['studentgroups'] = pluck($this->studentgroup_m->get_order_by_studentgroup(array('schoolID' => $schoolID)), 'group', 'studentgroupID');
			$this->data['optionalSubjects'] = pluck($this->subject_m->general_get_order_by_subject(array('type' => 0, 'schoolID' => $schoolID)), 'subject', 'subjectID');
			$id = htmlentities(escapeString($this->uri->segment(3)));
			if ((int)$id) {
				$this->data["student"] = $this->studentrelation_m->get_single_student(array('srstudentID' => $id, 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID), TRUE);
				if(customCompute($this->data["student"])) {
					$this->data['usertype'] = $this->usertype_m->get_single_usertype(array('usertypeID' => $this->data['student']->usertypeID));
					$this->data["class"] = $this->classes_m->general_get_classes($this->data['student']->srclassesID);
					$this->data["section"] = $this->section_m->general_get_section($this->data['student']->srsectionID);
					$this->reportPDF('studentmodule.css',$this->data, 'student/print_preview');
				} else {
					$this->data["subview"] = "error";
					$this->load->view('_layout_main', $this->data);
				}
			} else {
				$this->data["subview"] = "error";
				$this->load->view('_layout_main', $this->data);
			}
		} else {
			$this->data["subview"] = "error";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function send_mail() {
		$retArray['status'] = FALSE;
		$retArray['message'] = '';
		if(permissionChecker('student_view') || (($this->session->userdata('usertypeID') == 3) && permissionChecker('student') && ($this->session->userdata('loginuserID') == $this->input->post('studentID')))) {
			if($_POST) {
				$rules = $this->send_mail_rules();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {
					$retArray = $this->form_validation->error_array();
					$retArray['status'] = FALSE;
			    echo json_encode($retArray);
			    exit;
				} else {
					$schoolID = $this->session->userdata('schoolID');
					$this->data['studentgroups'] = pluck($this->studentgroup_m->get_order_by_studentgroup(array("schoolID" => $schoolID)), 'group', 'studentgroupID');
					$this->data['optionalSubjects'] = pluck($this->subject_m->general_get_order_by_subject(array('type' => 0, 'schoolID' => $schoolID)), 'subject', 'subjectID');
					$id = $this->input->post('studentID');
					$url = $this->input->post('classesID');
					if ((int)$id && (int)$url) {
						$schoolyearID = $this->session->userdata('defaultschoolyearID');
						$this->data["student"] = $this->studentrelation_m->get_single_student(array('srstudentID' => $id, 'srschoolyearID' => $schoolyearID, 'schoolID' => $schoolID), TRUE);
						if(customCompute($this->data["student"])) {
							$this->data['usertype'] = $this->usertype_m->get_single_usertype(array('usertypeID' => $this->data['student']->usertypeID));
							$this->data["class"] = $this->classes_m->general_get_single_classes(array('classesID' => $this->data['student']->srclassesID));
							$this->data["section"] = $this->section_m->general_get_single_section(array('sectionID' => $this->data['student']->srsectionID));
							$email = $this->input->post('to');
							$subject = $this->input->post('subject');
							$message = $this->input->post('message');
							$this->reportSendToMail('studentmodule.css', $this->data, 'student/print_preview', $email, $subject, $message);
							$retArray['message'] = "Message";
							$retArray['status'] = TRUE;
							echo json_encode($retArray);
						  exit;
						} else {
							$retArray['message'] = $this->lang->line('student_data_not_found');
							echo json_encode($retArray);
							exit;
						}
					} else {
						$retArray['message'] = $this->lang->line('student_data_not_found');
						echo json_encode($retArray);
						exit;
					}
				}
			} else {
				$retArray['message'] = $this->lang->line('student_permissionmethod');
				echo json_encode($retArray);
				exit;
			}
		} else {
			$retArray['message'] = $this->lang->line('student_permission');
			echo json_encode($retArray);
			exit;
		}
	}

	public function delete() {
		if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1)) {
			$schoolyearID = $this->session->userdata('defaultschoolyearID');
			$schoolID = $this->session->userdata('schoolID');
			$id = htmlentities(escapeString($this->uri->segment(3)));
			$url = htmlentities(escapeString($this->uri->segment(4)));
			if((int)$id && (int)$url) {
				$this->data['student'] = $this->studentrelation_m->get_single_student(array('srstudentID' => $id, 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID));
				if(customCompute($this->data['student'])) {
					if(config_item('demo') == FALSE) {
						if($this->data['student']->photo != 'default.png' && $this->data['student']->photo != 'defualt.png') {
							if(file_exists(FCPATH.'uploads/images/'.$this->data['student']->photo)) {
								unlink(FCPATH.'uploads/images/'.$this->data['student']->photo);
							}
						}
					}
					$this->student_m->delete_student($id);
					$this->studentextend_m->delete_studentextend_by_studentID($id);
					$this->session->set_flashdata('success', $this->lang->line('menu_success'));
					redirect(base_url("student/index/$url"));
				} else {
					redirect(base_url("student/index"));
				}
			} else {
				redirect(base_url("student/index/$url"));
			}
		} else {
			$this->data["subview"] = "error";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function unique_roll() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		$schoolyearID = $this->session->userdata('defaultschoolyearID');
		$schoolID = $this->session->userdata('schoolID');
		if((int)$id) {
			$student = $this->studentrelation_m->general_get_order_by_student(array("srroll" => $this->input->post("roll"), "srstudentID !=" => $id, "srclassesID" => $this->input->post('classesID'), 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID));
			if(customCompute($student)) {
				$this->form_validation->set_message("unique_roll", "The %s is already exists.");
				return FALSE;
			}
			return TRUE;
		} else {
			$student = $this->studentrelation_m->general_get_order_by_student(array("srroll" => $this->input->post("roll"), "srclassesID" => $this->input->post('classesID'), 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID));

			if(customCompute($student)) {
				$this->form_validation->set_message("unique_roll", "The %s is already exists.");
				return FALSE;
			}
			return TRUE;
		}
	}

	public function lol_username() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		if((int)$id) {
			$student = $this->student_m->general_get_single_student(array('studentID' => $id));
			$tables = array('student' => 'student', 'parents' => 'parents', 'teacher' => 'teacher', 'user' => 'user', 'systemadmin' => 'systemadmin');
			$array = array();
			$i = 0;
			foreach ($tables as $table) {
				$user = $this->student_m->get_username($table, array("username" => $this->input->post('username'), "username !=" => $student->username));
				if(customCompute($user)) {
					$this->form_validation->set_message("lol_username", "%s already exists");
					$array['permition'][$i] = 'no';
				} else {
					$array['permition'][$i] = 'yes';
				}
				$i++;
			}
			if(in_array('no', $array['permition'])) {
				return FALSE;
			} else {
				return TRUE;
			}
		} else {
			$tables = array('student' => 'student', 'parents' => 'parents', 'teacher' => 'teacher', 'user' => 'user', 'systemadmin' => 'systemadmin');
			$array = array();
			$i = 0;
			foreach ($tables as $table) {
				$user = $this->student_m->get_username($table, array("username" => $this->input->post('username')));
				if(customCompute($user)) {
					$this->form_validation->set_message("lol_username", "%s already exists");
					$array['permition'][$i] = 'no';
				} else {
					$array['permition'][$i] = 'yes';
				}
				$i++;
			}

			if(in_array('no', $array['permition'])) {
				return FALSE;
			} else {
				return TRUE;
			}
		}
	}

	public function parent_username() {
		if($this->input->post('guargianID') == "") {
			$id = htmlentities(escapeString($this->uri->segment(3)));
			if((int)$id) {
				$parents_info = $this->parents_m->get_single_parents(array('parentsID' => $id));
				$tables = array('student' => 'student', 'parents' => 'parents', 'teacher' => 'teacher', 'user' => 'user', 'systemadmin' => 'systemadmin');
				$array = array();
				$i = 0;
				foreach ($tables as $table) {
					$user = $this->student_m->get_username($table, array("username" => $this->input->post('username'), "username !=" => $parents_info->username ));
					if(customCompute($user)) {
						$this->form_validation->set_message("lol_username", "%s already exists");
						$array['permition'][$i] = 'no';
					} else {
						$array['permition'][$i] = 'yes';
					}
					$i++;
				}
				if(in_array('no', $array['permition'])) {
					return FALSE;
				} else {
					return TRUE;
				}
			} else {
				$tables = array('student' => 'student', 'parents' => 'parents', 'teacher' => 'teacher', 'user' => 'user', 'systemadmin' => 'systemadmin');
				$array = array();
				$i = 0;
				foreach ($tables as $table) {
					$user = $this->student_m->get_username($table, array("username" => $this->input->post('username')));
					if(customCompute($user)) {
						$this->form_validation->set_message("parent_username", "%s already exists");
						$array['permition'][$i] = 'no';
					} else {
						$array['permition'][$i] = 'yes';
					}
					$i++;
				}

				if(in_array('no', $array['permition'])) {
					return FALSE;
				} else {
					return TRUE;
				}
			}
		}
		return TRUE;
	}

	public function date_valid($date) {
		if($date) {
			if(strlen($date) <10) {
				$this->form_validation->set_message("date_valid", "%s is not valid dd-mm-yyyy");
		    return FALSE;
			} else {
		   		$arr = explode("-", $date);
	        $dd = $arr[0];
	        $mm = $arr[1];
	        $yyyy = $arr[2];
	      	if(checkdate($mm, $dd, $yyyy)) {
	      		return TRUE;
	      	} else {
	      		$this->form_validation->set_message("date_valid", "%s is not valid dd-mm-yyyy");
	     			return FALSE;
	      	}
		    }
		}
		return TRUE;
	}

	public function parent_name($name) {
		if($this->input->post('guargianID') == "") {
			if(strlen($name) == 0) {
				$this->form_validation->set_message("parent_name", "The %s field is required");
		    return FALSE;
			}
			return TRUE;
		}
		return TRUE;
	}

	public function parent_password($password) {
		if($this->input->post('guargianID') == "") {
			if(strlen($password) == 0) {
				$this->form_validation->set_message("parent_password", "The %s field is required");
		    return FALSE;
			}
			return TRUE;
		}
		return TRUE;
	}

	public function unique_classesID() {
		if($this->input->post('classesID') == 0) {
			$this->form_validation->set_message("unique_classesID", "The %s field is required");
	    return FALSE;
		}
		return TRUE;
	}

	public function unique_sectionID() {
		if($this->input->post('sectionID') == 0) {
			$this->form_validation->set_message("unique_sectionID", "The %s field is required");
	    return FALSE;
		}
		return TRUE;
	}

	public function student_list() {
		$classID = $this->input->post('id');
		if((int)$classID) {
			$string = base_url("student/index/$classID");
			echo $string;
		} else {
			redirect(base_url("student/index"));
		}
	}

	public function unique_email() {
		if($this->input->post('email')) {
			$id = htmlentities(escapeString($this->uri->segment(3)));
			if((int)$id) {
				$student_info = $this->student_m->general_get_single_student(array('studentID' => $id));
				$tables = array('student' => 'student', 'parents' => 'parents', 'teacher' => 'teacher', 'user' => 'user', 'systemadmin' => 'systemadmin');
				$array = array();
				$i = 0;
				foreach ($tables as $table) {
					$user = $this->student_m->get_username($table, array("email" => $this->input->post('email'), 'username !=' => $student_info->username ));
					if(customCompute($user)) {
						$this->form_validation->set_message("unique_email", "%s already exists");
						$array['permition'][$i] = 'no';
					} else {
						$array['permition'][$i] = 'yes';
					}
					$i++;
				}
				if(in_array('no', $array['permition'])) {
					return FALSE;
				} else {
					return TRUE;
				}
			} else {
				$tables = array('student' => 'student', 'parents' => 'parents', 'teacher' => 'teacher', 'user' => 'user', 'systemadmin' => 'systemadmin');
				$array = array();
				$i = 0;
				foreach ($tables as $table) {
					$user = $this->student_m->get_username($table, array("email" => $this->input->post('email')));
					if(customCompute($user)) {
						$this->form_validation->set_message("unique_email", "%s already exists");
						$array['permition'][$i] = 'no';
					} else {
						$array['permition'][$i] = 'yes';
					}
					$i++;
				}

				if(in_array('no', $array['permition'])) {
					return FALSE;
				} else {
					return TRUE;
				}
			}
		}
		return TRUE;
	}

	public function sectioncall() {
		$classesID = $this->input->post('id');
		$schoolID = $this->session->userdata('schoolID');
		if((int)$classesID) {
			$allsection = $this->section_m->general_get_order_by_section(array('classesID' => $classesID, 'schoolID' => $schoolID));
			echo "<option value='0'>", $this->lang->line("student_select_section"),"</option>";
			foreach ($allsection as $value) {
				echo "<option value=\"$value->sectionID\">",$value->section,"</option>";
			}
		}
	}

  public function optionalsubjectcall() {
      $classesID = $this->input->post('id');
			$schoolID = $this->session->userdata('schoolID');
      if((int)$classesID) {
          $allOptionalSubjects = $this->subject_m->general_get_order_by_subject(array("classesID" =>$classesID, 'type' => 0, 'schoolID' => $schoolID));
          echo "<option value='0'>", $this->lang->line("student_select_optionalsubject"),"</option>";
          foreach ($allOptionalSubjects as $value) {
              echo "<option value=\"$value->subjectID\">",$value->subject,"</option>";
          }
      }
  }

	public function unique_capacity() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		if((int)$id) {
			if($this->input->post('sectionID')) {
				$sectionID = $this->input->post('sectionID');
				$classesID = $this->input->post('classesID');
				$schoolyearID = $this->data['siteinfos']->school_year;
				$schoolID = $this->session->userdata('schoolID');
				$section = $this->section_m->general_get_section($this->input->post('sectionID'));
				$student = $this->studentrelation_m->general_get_order_by_student(array('srclassesID' => $classesID, 'srsectionID' => $sectionID, 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID, 'srstudentID !=' => $id));
				if(customCompute($student) >= $section->capacity) {
					$this->form_validation->set_message("unique_capacity", "The %s capacity is full.");
		     	return FALSE;
				}
				return TRUE;
			} else {
				$this->form_validation->set_message("unique_capacity", "The %s field is required.");
		    return FALSE;
			}
		} else {
			if($this->input->post('sectionID')) {
				$sectionID = $this->input->post('sectionID');
				$classesID = $this->input->post('classesID');
				$schoolyearID = $this->data['siteinfos']->school_year;
				$schoolID = $this->session->userdata('schoolID');
				$section = $this->section_m->general_get_section($this->input->post('sectionID'));
				$student = $this->studentrelation_m->general_get_order_by_student(array('srclassesID' => $classesID, 'srsectionID' => $sectionID, 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID));
				if(customCompute($student) >= $section->capacity) {
					$this->form_validation->set_message("unique_capacity", "The %s capacity is full.");
		     	return FALSE;
				}
				return TRUE;
			} else {
				$this->form_validation->set_message("unique_capacity", "The %s field is required.");
		    return FALSE;
			}
		}
	}

 	public function unique_registerNO() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		if((int)$id) {
			$student = $this->studentrelation_m->general_get_single_student(array("srregisterNO" => $this->input->post("registerNO"), "srstudentID !=" => $id));
			if(customCompute($student)) {
				$this->form_validation->set_message("unique_registerNO", "The %s is already exists.");
				return FALSE;
			}
			return TRUE;
		} else {
			$student = $this->studentrelation_m->general_get_single_student(array("srregisterNO" => $this->input->post("registerNO")));
			if(customCompute($student)) {
				$this->form_validation->set_message("unique_registerNO", "The %s is already exists.");
				return FALSE;
			}
			return TRUE;
		}
	}

	public function active() {
		if(permissionChecker('student_edit')) {
			$id     = $this->input->post('id');
			$status = $this->input->post('status');
			if($id != '' && $status != '') {
				if((int)$id) {
					$schoolyearID = $this->session->userdata('defaultschoolyearID');
					$schoolID = $this->session->userdata('schoolID');
					$student = $this->studentrelation_m->get_single_studentrelation(array('srstudentID' => $id, 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID));
					if(customCompute($student)) {
						if($status == 'chacked') {
							$this->student_m->update_student(array('active' => 1), $id);
							echo $this->customerActive($student, 'true');
						} elseif($status == 'unchacked') {
							$this->student_m->update_student(array('active' => 0), $id);
							echo $this->customerActive($student, 'false');
						} else {
							echo "Error";
						}
					} else {
						echo 'Error';
					}
				} else {
					echo "Error";
				}
			} else {
				echo "Error";
			}
		} else {
			echo "Error";
		}
	}

	public function customerActive($student, $status) {
		// Create SDK instance
		$config = $this->quickbooksConfig();

		if ($config['active'] == "1") {
			$dataService = DataService::Configure(array(
				'auth_mode' => 'oauth2',
				'ClientID' => $config['client_id'],
				'ClientSecret' =>  $config['client_secret'],
				'RedirectURI' => base_url() . "quickbooks/callback",
				'scope' => 'com.intuit.quickbooks.accounting openid profile email phone address',
				'baseUrl' => $config['stage']
			));

			/*
      * Retrieve the accessToken value from session variable
      */
      $accessToken = unserialize($config['sessionAccessToken']);

      $dataService->throwExceptionOnError(true);

      /*
      * Update the OAuth2Token of the dataService object
      */
      $dataService->updateOAuth2Token($accessToken);
      $path = APPPATH . '../mvc/logs/quickbooks/'. date('Y-m-d');
      if (!file_exists($path)) {
        mkdir($path, 0777, true);
      }
      $dataService->setLogLocation($path);

			$customerName = $student->srstudentID ."-". $student->srname;

			try {
				$customerArray = $dataService->Query("select * from Customer where Active IN (true, false) and DisplayName='" . addslashes($customerName) . "'");
				$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Customer where Active IN (true, false) and DisplayName=" . addslashes($customerName), "status" => "OK", 'schoolID' => $student->srschoolID));
			} catch (Exception $e) {
				$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Customer where Active IN (true, false) and DisplayName=" . addslashes($customerName), "message" => $e->getMessage(), "status" => "ERROR", 'schoolID' => $student->srschoolID));
			}

			if(customCompute($customerArray)) {
				foreach ($customerArray as $k=>$theCustomer) {
					$updateCustomer = Customer::update($theCustomer, [
						'sparse' => 'true',
						"SyncToken" => $theCustomer->SyncToken,
						'Id' => $theCustomer->Id,
						'Active' => $status
					]);

					try {
						$dataService->Add($updateCustomer);
						$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "Update customer Active", "status" => "OK", 'schoolID' => $student->srschoolID));
						return "Success";
					}
					catch (Exception $e) {
						$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "Update customer Active", "message" => $e->getMessage(), "status" => "ERROR", 'schoolID' => $student->srschoolID));
						return $e->getMessage();
					}
				}
			}
		}

		return "Success";
	}

	private function leave_applications_date_list_by_user_and_schoolyear($userID, $schoolyearID, $usertypeID) {
		$schoolID = $this->session->userdata('schoolID');
		$leaveapplications = $this->leaveapplication_m->get_order_by_leaveapplication(array('create_userID'=>$userID,'create_usertypeID'=>$usertypeID,'schoolyearID'=>$schoolyearID,'status'=>1,'schoolID'=>$schoolID));

		$retArray = [];
		if(customCompute($leaveapplications)) {
			$oneday    = 60*60*24;
			foreach($leaveapplications as $leaveapplication) {
			    for($i=strtotime($leaveapplication->from_date); $i<= strtotime($leaveapplication->to_date); $i= $i+$oneday) {
			        $retArray[] = date('d-m-Y', $i);
			    }
			}
		}
		return $retArray;
	}

	public function valid_optionalsubject()
	{
			$optionalSubjectID = $this->input->post('optionalSubjectID');
			$status       = [];
			if(customCompute($optionalSubjectID)) {
					foreach($optionalSubjectID as $value) {
							$result = $this->subject_m->get_single_subject(array('schoolID' => $this->session->userdata('schoolID'), 'subjectID' => $value, 'type' => 0));
							if(!customCompute($result)) {
									$status[] = FALSE;
							}
					}
			}

			if(in_array(FALSE, $status)) {
					$this->form_validation->set_message("valid_optionalsubject", "This subject is invalid.");
					return FALSE;
			}
			return TRUE;
	}

	public function valid_nonexaminablesubject()
	{
			$nonexaminableSubjectID = $this->input->post('nonexaminableSubjectID');
			$status       = [];
			if(customCompute($nonexaminableSubjectID)) {
					foreach($nonexaminableSubjectID as $value) {
							$result = $this->subject_m->get_single_subject(array('schoolID' => $this->session->userdata('schoolID'), 'subjectID' => $value, 'type' => 2));
							if(!customCompute($result)) {
									$status[] = FALSE;
							}
					}
			}

			if(in_array(FALSE, $status)) {
					$this->form_validation->set_message("valid_nonexaminablesubject", "This subject is invalid.");
					return FALSE;
			}
			return TRUE;
	}

	public function unique_data($data) {
		if($data != '') {
			if($data == '0') {
				$this->form_validation->set_message('unique_data', 'The %s field is required.');
				return FALSE;
			}
		}
		return TRUE;
	}

}
