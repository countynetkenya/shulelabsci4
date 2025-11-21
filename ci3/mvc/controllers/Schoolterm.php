<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Schoolterm extends Admin_Controller {
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
		$this->load->model("schoolterm_m");
		$language = $this->session->userdata('lang');
		$this->lang->load('schoolterm', $language);
	}

	protected function rules() {
		$rules = array(
			array(
				'field' => 'schooltermtitle',
				'label' => $this->lang->line("schoolterm_schooltermtitle"),
				'rules' => 'trim|xss_clean|max_length[128]|callback_unique_schoolterm',
			),
			array(
				'field' => 'startingdate',
				'label' => $this->lang->line("schoolterm_startingdate"),
				'rules' => 'trim|required|xss_clean|max_length[10]|callback_date_valid'
			),
			array(
				'field' => 'endingdate',
				'label' => $this->lang->line("schoolterm_endingdate"),
				'rules' => 'trim|required|xss_clean|max_length[10]|callback_date_valid|callback_unique_endingdate'
			)
		);
		return $rules;
	}

	public function index() {
		$schoolID = $this->session->userdata('schoolID');
		$this->data['schoolterms'] = $this->schoolterm_m->get_order_by_schoolterm(array('schoolID' => $schoolID));
		$this->data["subview"]     = "schoolterm/index";
		$this->load->view('_layout_main', $this->data);
	}

	public function schoolyear_list() {
		$schooltermID = $this->input->post('schooltermID');
		if($schooltermID) {
			$string = base_url("schoolterm/index/$schooltermID");
			echo $string;
		} else {
			redirect(base_url("schoolterm/index"));
		}
	}

	public function add() {

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

		$schoolID = $this->session->userdata('schoolID');

		if($_POST) {
			$rules = $this->rules();
			$this->form_validation->set_rules($rules);
			if ($this->form_validation->run() == FALSE) {
				$this->data["subview"] = "schoolterm/add";
				$this->load->view('_layout_main', $this->data);
			} else {
				$array = array(
					"schoolyearID" => $this->input->post("schoolYearID"),
					"schooltermtitle" => $this->input->post("schooltermtitle"),
					"create_date" => date("Y-m-d h:i:s"),
					"modify_date" => date("Y-m-d h:i:s"),
					"create_userID" => $this->session->userdata('loginuserID'),
					"create_username" => $this->session->userdata('username'),
					"create_usertype" => $this->session->userdata('usertype'),
					"schoolID" => $schoolID,
				);

				if($this->input->post('startingdate')) {
					$array["startingdate"] = date("Y-m-d", strtotime($this->input->post("startingdate")));
				}

				if($this->input->post('endingdate')) {
					$array["endingdate"] = date("Y-m-d", strtotime($this->input->post("endingdate")));
				}

				$this->schoolterm_m->insert_schoolterm($array);
				$this->session->set_flashdata('success', $this->lang->line('menu_success'));
				redirect(base_url("schoolterm/index"));
			}
		} else {
			$this->data['schoolYears']      = $this->schoolyear_m->get_order_by_schoolyear(array('schoolID' => $schoolID));
			$this->data["subview"] = "schoolterm/add";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function edit() {

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

		$id = htmlentities(escapeString($this->uri->segment(3)));
		if((int)$id) {
			$schoolID = $this->session->userdata('schoolID');
			$this->data['schoolterm'] = $this->schoolterm_m->get_single_schoolterm(array('schooltermID' => $id, 'schoolID' => $schoolID));
			if($this->data['schoolterm']) {
				if($_POST) {
					$rules = $this->rules();
					$this->form_validation->set_rules($rules);
					if ($this->form_validation->run() == FALSE) {
						$this->data["subview"] = "schoolterm/edit";
						$this->load->view('_layout_main', $this->data);
					} else {
						$array = array
							("schooltermtitle" => $this->input->post("schooltermtitle"),
							"modify_date" => date("Y-m-d h:i:s")
						);

						if($this->input->post('startingdate')) {
							$array["startingdate"] = date("Y-m-d", strtotime($this->input->post("startingdate")));
						}

						if($this->input->post('endingdate')) {
							$array["endingdate"] = date("Y-m-d", strtotime($this->input->post("endingdate")));
						}

						$this->schoolterm_m->update_schoolterm($array, $id);
						$this->session->set_flashdata('success', $this->lang->line('menu_success'));
						redirect(base_url("schoolterm/index"));
					}
				} else {
					$this->data['schoolYears']      = $this->schoolyear_m->get_order_by_schoolyear(array('schoolID' => $schoolID));
					$this->data["subview"] = "schoolterm/edit";
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
		$id = htmlentities(escapeString($this->uri->segment(3)));
		if((int)$id) {
			$schoolID = $this->session->userdata('schoolID');
			$schoolterm = $this->schoolterm_m->get_single_schoolterm(array('schooltermID' => $id, 'schoolID' => $schoolID));
			if($schoolterm) {
				if($schoolterm->schooltermID != 1) {
					$this->schoolterm_m->delete_schoolterm($id);
					$this->session->set_flashdata('success', $this->lang->line('menu_success'));
					redirect(base_url("schoolterm/index"));
				} else {
					redirect(base_url("schoolterm/index"));
				}
			} else {
				redirect(base_url("schoolterm/index"));
			}
		} else {
			redirect(base_url("schoolterm/index"));
		}
	}

	public function valid_number() {
		if($this->input->post('semestercode') < 0) {
			$this->form_validation->set_message("valid_number", "%s is invalid number");
			return FALSE;
		}
		return TRUE;
	}

	public function unique_schoolterm() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		$schoolID = $this->session->userdata('schoolID');
		if((int)$id) {
			$schoolterm = $this->schoolterm_m->get_order_by_schoolterm(array("schooltermtitle" => $this->input->post("schoolterm"), "schooltermID !=" => $id, 'schoolID' => $schoolID));
			if(customCompute($divisions)) {
				$this->form_validation->set_message("unique_schoolterm", "%s already exists");
				return FALSE;
			}
			return TRUE;
		} else {
			$schoolterm = $this->schoolterm_m->get_order_by_schoolterm(array("schooltermtitle" => $this->input->post("schoolterm"), 'schoolID' => $schoolID));
			if(customCompute($divisions)) {
				$this->form_validation->set_message("unique_schoolterm", "%s already exists");
				return FALSE;
			}
			return TRUE;
		}
	}

	public function date_valid($date) {
		if($date) {
			if(strlen($date) < 10) {
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

	public function unique_endingdate() {

		$startingdate = strtotime($this->input->post('startingdate'));
		$endingdate   = strtotime($this->input->post('endingdate'));

		if($startingdate && $endingdate) {
			if($startingdate >= $endingdate) {
				$this->form_validation->set_message("unique_endingdate", "%s cannot be less than starting date");
		     	return FALSE;
			}
			return TRUE;
		}
		return TRUE;

	}

}

/* End of file class.php */
/* Location: .//D/xampp/htdocs/school/mvc/controllers/class.php */
