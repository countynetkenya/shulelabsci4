<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Examcompilation extends Admin_Controller {
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
		$this->load->model("examcompilation_m");
		$this->load->model("examcompilation_exams_m");
		$this->load->model("exam_m");
		$language = $this->session->userdata('lang');
		$this->lang->load('examcompilation', $language);
	}

	public function index() {
		$this->data['examcompilations'] = $this->examcompilation_m->get_order_by_examcompilation(array('schoolID' => $this->session->userdata('schoolID')));
		$this->data["subview"] = "examcompilation/index";
		$this->load->view('_layout_main', $this->data);
	}

	protected function rules() {
		$rules = array(
				array(
					'field' => 'examcompilation',
					'label' => $this->lang->line("examcompilation_examcompilation"),
					'rules' => 'trim|required|xss_clean|max_length[60]|callback_unique_examcompilation'
				),
				array(
					'field' => 'examitems',
					'label' => $this->lang->line("examcompilation_exam"),
					'rules' => 'trim|required|xss_clean|callback_unique_examitems'
				),
				array(
					'field' => 'note',
					'label' => $this->lang->line("examcompilation_note"),
					'rules' => 'trim|xss_clean|max_length[200]'
				),
				array(
					'field' => 'compare_examID',
					'label' => $this->lang->line("examcompilation_compare_exam"),
					'rules' => 'trim|xss_clean|numeric'
				),
				array(
					'field' => 'compare_examcompilationID',
					'label' => $this->lang->line("examcompilation_compare_examcompilation"),
					'rules' => 'trim|xss_clean|numeric'
				)
			);

		return $rules;
	}

	public function add() {
		if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1)) {
			$this->data['headerassets'] = array(
				'css' => array(
					'assets/select2/css/select2.css',
					'assets/select2/css/select2-bootstrap.css'
				),
				'js' => array(
					'assets/select2/select2.js'
				)
			);

			$schoolID = $this->session->userdata('schoolID');
			$this->data['exams'] = $this->exam_m->get_order_by_exam(array("schoolID" => $schoolID));
			$this->data['examcompilations'] = $this->examcompilation_m->get_order_by_examcompilation(array("schoolID" => $schoolID));
			$exam                = pluck($this->exam_m->get_order_by_exam(array("schoolID" => $schoolID)), 'exam', 'examID');

			if($_POST) {
				$rules = $this->rules();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {
					$this->data["subview"] = "examcompilation/add";
					$this->load->view('_layout_main', $this->data);
				} else {
					$compare_examID = $this->input->post("compare_examID");
					$compare_examcompilationID = $this->input->post("compare_examcompilationID");

	        $array = [
	            "examcompilation" => $this->input->post("examcompilation"),
	            "note"           => $this->input->post("note"),
							"schoolID"       => $schoolID,
							"compare_examID" => ((int)$compare_examID && $compare_examID > 0) ? $compare_examID : NULL,
							"compare_examcompilationID" => ((int)$compare_examcompilationID && $compare_examcompilationID > 0) ? $compare_examcompilationID : NULL,
	        ];

	        $this->examcompilation_m->insert_examcompilation($array);
					$id = $this->db->insert_id();

					$examitems = json_decode($this->input->post('examitems'));

					$examArray         = [];

					if(customCompute($examitems)) {
							foreach($examitems as $examitem) {
									$examArray[] = [
											'examID' => isset($examitem->examID) ? $examitem->examID : 0,
											'exam' => isset($exam[$examitem->examID]) ? $exam[$examitem->examID] : '',
											'weight' => isset($examitem->weight) ? $examitem->weight : 0,
											'examcompilationID' => $id,
											'schoolID' => $schoolID,
									];
							}
					}

					$this->examcompilation_exams_m->insert_batch_examcompilation_exams($examArray);

					$this->session->set_flashdata('success', $this->lang->line('menu_success'));

					$retArray['status']  = TRUE;
					$retArray['message'] = 'Success';
					echo json_encode($retArray);
				}
			} else {
				$this->data["subview"] = "examcompilation/add";
				$this->load->view('_layout_main', $this->data);
			}
		} else {
			$this->data["subview"] = "error";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function edit() {
		if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1)) {
			$this->data['headerassets'] = array(
				'css' => array(
					'assets/select2/css/select2.css',
					'assets/select2/css/select2-bootstrap.css'
				),
				'js' => array(
					'assets/select2/select2.js'
				)
			);

			$schoolID                       = $this->session->userdata('schoolID');
			$this->data['exams']            = $this->exam_m->get_order_by_exam(array("schoolID" => $schoolID));
			$this->data['examcompilations'] = $this->examcompilation_m->get_order_by_examcompilation(array("schoolID" => $schoolID));
			$exam                           = pluck($this->exam_m->get_order_by_exam(array("schoolID" => $schoolID)), 'exam', 'examID');

			$id = htmlentities(escapeString($this->uri->segment(3)));
			if((int)$id) {
				$this->data['examcompilationID'] = $id;
				$this->data['examcompilation'] = $this->examcompilation_m->get_single_examcompilation(array('examcompilationID' => $id, 'schoolID' => $schoolID));
				$this->data['examcompilation_exams'] = $this->examcompilation_exams_m->get_order_by_examcompilation_exams(array("examcompilationID" => $id, 'schoolID' => $schoolID));
				if($this->data['examcompilation']) {
					if($_POST) {
						$rules = $this->rules();
						$this->form_validation->set_rules($rules);
						if ($this->form_validation->run() == FALSE) {
							$this->data["subview"] = "examcompilation/edit";
							$this->load->view('_layout_main', $this->data);
						} else {
							$array = array(
								"examcompilation" => $this->input->post("examcompilation"),
								"note" => $this->input->post("note")
							);

							$this->examcompilation_m->update_examcompilation($array, $id);

							$examitems = json_decode($this->input->post('examitems'));

							$examArray         = [];

							if(customCompute($examitems)) {
									foreach($examitems as $examitem) {
											$examArray[] = [
													'examID'  => isset($examitem->examID) ? $examitem->examID : 0,
													'exam'    => isset($exam[$examitem->examID]) ? $exam[$examitem->examID] : '',
													'weight'  => isset($examitem->weight) ? $examitem->weight : 0,
													'examcompilationID' => $id,
													'schoolID' => $schoolID,
											];
									}
							}

							$this->examcompilation_exams_m->delete_examcompilation_exams_by_examcompilationID($id);
							$this->examcompilation_exams_m->insert_batch_examcompilation_exams($examArray);

							$this->session->set_flashdata('success', $this->lang->line('menu_success'));

							$retArray['status']  = TRUE;
							$retArray['message'] = 'Success';
							echo json_encode($retArray);
						}
					} else {
						$this->data["subview"] = "examcompilation/edit";
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

	public function delete() {
		if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1)) {
			$id = htmlentities(escapeString($this->uri->segment(3)));
			if((int)$id) {
				$examcompilation = $this->examcompilation_m->get_single_examcompilation(array('examcompilationID' => $id, 'schoolID' => $this->session->userdata('schoolID')));
				if(customCompute($examcompilation)) {
					$this->examcompilation_m->delete_examcompilation($id);
					$this->session->set_flashdata('success', $this->lang->line('menu_success'));
				}
				redirect(base_url("examcompilation/index"));
			} else {
				redirect(base_url("examcompilation/index"));
			}
		} else {
			$this->data["subview"] = "error";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function unique_examcompilation() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		$schoolID = $this->session->userdata('schoolID');
		if((int)$id) {
			$examcompilation = $this->examcompilation_m->get_order_by_examcompilation(array("examcompilation" => $this->input->post("examcompilation"), 'schoolID' => $schoolID, "examcompilationID !=" => $id));
			if(customCompute($examcompilation)) {
				$this->form_validation->set_message("unique_examcompilation", "%s already exists");
				return FALSE;
			}
			return TRUE;
		}
		else {
			$examcompilation = $this->examcompilation_m->get_order_by_examcompilation(array("examcompilation" => $this->input->post("examcompilation"), 'schoolID' => $schoolID));
			if(customCompute($examcompilation)) {
				$this->form_validation->set_message("unique_examcompilation", "%s already exists");
				return FALSE;
			}
			return TRUE;
		}
	}

	public function unique_examitems()
	{
			$examitems = json_decode($this->input->post('examitems'));
			$status       = [];
			if(customCompute($examitems)) {
					foreach($examitems as $examitem) {
							if($examitem->weight == '') {
									$status[] = FALSE;
							}
					}
			} else {
					$this->form_validation->set_message("unique_examitems", "The exam item is required.");
					return FALSE;
			}

			if(in_array(FALSE, $status)) {
					$this->form_validation->set_message("unique_examitems", "The exam weight is required.");
					return FALSE;
			}
			return TRUE;
	}

	public function getexams()
	{
			$examcompilationID    = $this->input->post('examcompilationID');

			$exams = $this->examcompilation_exams_m->get_order_by_examcompilation_exams(array("examcompilationID" => $examcompilationID, 'schoolID' => $this->session->userdata('schoolID')));

			echo json_encode($exams);
	}
}
