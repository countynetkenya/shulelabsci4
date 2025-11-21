<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Remark extends Admin_Controller {

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
    $this->load->model('remark_m');

		$language = $this->session->userdata('lang');
		$this->lang->load('remark', $language);
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

		$this->data['classes'] = $this->classes_m->general_get_classes();
		$this->data["subview"] = "remark/RemarkView";
		$this->load->view('_layout_main', $this->data);
	}

  protected function rules() {
		$rules = array(
			array(
				'field'=>'examID',
				'label'=>$this->lang->line('remark_exam'),
				'rules' => 'trim|required|xss_clean|numeric|callback_unique_data'
			),
			array(
				'field'=>'classesID',
				'label'=>$this->lang->line('remark_class'),
				'rules' => 'trim|required|xss_clean|numeric|callback_unique_data'
			),
			array(
				'field'=>'sectionID',
				'label'=>$this->lang->line('remark_section'),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field'=>'studentID',
				'label'=>$this->lang->line('remark_student'),
				'rules' => 'trim|xss_clean'
			)
		);
		return $rules;
	}

	protected function remarkRules()
	{
			$rules = [
					[
							'field' => 'remarkitems',
							'label' => $this->lang->line("remark_remarkitem"),
							'rules' => 'trim|xss_clean|required|callback_unique_remarkitems'
					],
			];

			return $rules;
	}

  public function getTabulatonsheetReport() {
		$retArray['render'] = '';
		$retArray['status'] = FALSE;
		if(permissionChecker('tabulationsheetreport')) {
			$examID      = $this->input->post('examID');
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
					$schoolyearID = $this->session->userdata('defaultschoolyearID');
					$studentQuery = [];
					$studentQuery['srclassesID']     = $classesID;
					if($sectionID > 0) {
						$studentQuery['srsectionID'] = $sectionID;
					}
					if($studentID > 0) {
						$studentQuery['srstudentID'] = $studentID;
					}
					$studentQuery['srschoolyearID']  = $schoolyearID;

					$this->data['mandatorysubjects'] = $this->subject_m->general_get_order_by_subject(array('classesID'=>$classesID, 'type' => 1));
					$this->data['optionalsubjects']  = $this->subject_m->general_get_order_by_subject(array('classesID'=>$classesID, 'type' => 0));
					$this->data['students']          = $this->studentrelation_m->general_get_order_by_student($studentQuery);
					$this->data['classes']           = pluck($this->classes_m->general_get_classes(), 'classes', 'classesID');
					$this->data['sections']          = pluck($this->section_m->general_get_section(), 'section', 'sectionID');
					$this->data['grades']            = $this->grade_m->get_grade();
					$this->data['percentageArr']     = pluck($this->markpercentage_m->get_markpercentage(), 'obj', 'markpercentageID');
					$marks                           = $this->mark_m->get_order_by_all_student_mark_with_markrelation(['schoolyearID' => $schoolyearID, 'classesID' => $classesID, 'examID' => $examID]);
          $this->data['marks']             = $this->getMark($marks);
          $remarks                         = $this->remark_m->get_order_by_remark(['examID' => $examID]);
          $this->data['remarks']           = $this->getRemark($remarks);

					$markpercentagesmainArr          = $this->marksetting_m->get_marksetting_markpercentages();
					$markpercentagesArr              = isset($markpercentagesmainArr[$classesID][$examID]) ? $markpercentagesmainArr[$classesID][$examID] : [];
					$settingmarktypeID               = $this->data['siteinfos']->marktypeID;

					$this->data['settingmarktypeID'] = $settingmarktypeID;
					$this->data['markpercentagesArr']= $markpercentagesArr;

					$this->data['examID']          = $examID;
					$this->data['classesID']       = $classesID;
					$this->data['sectionID']       = $sectionID;
					$this->data['studentID']       = $studentID;

					reset($markpercentagesArr);
          $firstindex                    = key($markpercentagesArr);
          $uniquepercentageArr           = isset($markpercentagesArr[$firstindex]) ? $markpercentagesArr[$firstindex] : [];
          $markpercentages               = $uniquepercentageArr[(($settingmarktypeID==4) || ($settingmarktypeID==6)) ? 'unique' : 'own'];
					$this->data['markpercentages'] = $markpercentages;

					$retArray['render'] = $this->load->view('remark/Remark',$this->data,true);
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

  public function add()
  {
    if(permissionChecker('tabulationsheetreport')) {
        if($_POST) {
					$rules = $this->remarkRules();
					$this->form_validation->set_rules($rules);
					if ($this->form_validation->run() == FALSE) {
						$retArray['error'] = $this->form_validation->error_array();
						$retArray['status'] = FALSE;
						echo json_encode($retArray);
						exit;
					} else {
            $remarkitems = json_decode($this->input->post('remarkitems'));
            if(customCompute($remarkitems)) {
              foreach($remarkitems as $remarkitem) {
								if($remarkitem->form_teacher != "" || $remarkitem->house_teacher != "" || $remarkitem->principal_teacher != "") {
                  $remark = $this->remark_m->get_remark(array('examID' => $remarkitem->exam, 'studentID' => $remarkitem->student));
                  if(!customCompute($remark)) {
                    $array = array();
                    $array['examID'] = $remarkitem->exam;
                    $array['studentID'] = $remarkitem->student;
                    $array['form_teacher'] = $remarkitem->form_teacher;
                    $array['house_teacher'] = $remarkitem->house_teacher;
                    $array['principal_teacher'] = $remarkitem->principal_teacher;
                    $array['create_date'] = date("Y-m-d h:i:s");
                    $array['modify_date'] = date("Y-m-d h:i:s");
                    $array['create_userID'] = $this->session->userdata('loginuserID');
                    $array['create_username'] = $this->session->userdata('name');
                    $array['create_usertype'] = $this->session->userdata('usertypeID');
                    $this->remark_m->insert_remark($array);
                  }
                  else {
                    $array = array();
                    $array['form_teacher'] = $remarkitem->form_teacher;
                    $array['house_teacher'] = $remarkitem->house_teacher;
                    $array['principal_teacher'] = $remarkitem->principal_teacher;
                    $array['modify_date'] = date("Y-m-d h:i:s");
                    $this->remark_m->update_remark($array, $remark->remarkID);
                  }
								}
              }
            }
					}

          $this->session->set_flashdata('success', $this->lang->line('menu_success'));
					$retArray['status']  = TRUE;
          $retArray['message'] = 'Success';
          echo json_encode($retArray);
          exit;
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

  private function getMark($marks) {
		$retMark = [];
		if(customCompute($marks)) {
			foreach ($marks as $mark) {
				$retMark[$mark->studentID][$mark->subjectID][$mark->markpercentageID] = $mark->mark;
			}
		}
		return $retMark;
	}

	public function getExam() {
		$classesID = $this->input->post('classesID');
		echo "<option value='0'>", $this->lang->line("remark_please_select"),"</option>";
		if((int)$classesID) {
			$exams    = pluck($this->marksetting_m->get_exam($this->data['siteinfos']->marktypeID, $classesID), 'obj', 'examID');
			if(customCompute($exams)) {
				foreach ($exams as $exam) {
					echo "<option value=".$exam->examID.">".$exam->exam."</option>";
				}
			}
		}
	}

	public function getSection() {
		$classesID = $this->input->post('classesID');
		if((int)$classesID) {
			$sections = $this->section_m->general_get_order_by_section(array('classesID' => $classesID));
			echo "<option value='0'>". $this->lang->line("remark_please_select") . "</option>";
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
		$schoolyearID = $this->session->userdata('defaultschoolyearID');
		if((int)$classesID && (int)$sectionID) {
			$students = $this->studentrelation_m->general_get_order_by_student(array('srclassesID'=>$classesID,'srsectionID'=>$sectionID,'srschoolyearID'=>$schoolyearID));
			echo "<option value='0'>". $this->lang->line("remark_please_select") . "</option>";
			if(customCompute($students)) {
				foreach ($students as $student) {
					echo "<option value='".$student->srstudentID."'>".$student->srname."</option>";
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

	public function unique_remarkitems()
	{
			$remarkitems = json_decode($this->input->post('remarkitems'));
			$status       = [];
			if(customCompute($remarkitems)) {
					foreach($remarkitems as $remarkitem) {
							if((int)$remarkitem->exam > 0) {
								$this->form_validation->set_message("unique_remarkitems", "Remark item exam is required.");
								return FALSE;
							}
							if((int)$remarkitem->student > 0) {
								$this->form_validation->set_message("unique_remarkitems", "Remark item student is required.");
								return FALSE;
							}
							if(strlen($remarkitem->form_teacher) > 160 || strlen($remarkitem->house_teacher) > 160 || strlen($remarkitem->principal_teacher) > 160) {
								$this->form_validation->set_message("unique_remarkitems", "Remark item remark should be 160 characters or less.");
								return FALSE;
							}
					}
			} else {
					$this->form_validation->set_message("unique_remarkitems", "Nothing to submit.");
					return FALSE;
			}

			return TRUE;
	}
}
