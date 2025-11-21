<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Register extends Admin_Controller {
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
		$this->load->model("systemadmin_m");
		$this->load->model("usertype_m");
		$language = $this->session->userdata('lang');
		$this->lang->load('register', $language);
	}

	protected function rules() {
		$rules = array(
			array(
				'field' => 'name',
				'label' => $this->lang->line("systemadmin_name"),
				'rules' => 'trim|required|xss_clean|max_length[60]'
			),
			array(
				'field' => 'dob',
				'label' => $this->lang->line("systemadmin_dob"),
				'rules' => 'trim|required|max_length[10]|callback_date_valid|xss_clean'
			),
			array(
				'field' => 'email',
				'label' => $this->lang->line("systemadmin_email"),
				'rules' => 'trim|required|max_length[40]|valid_email|xss_clean|callback_unique_email'
			),
			array(
				'field' => 'username',
				'label' => $this->lang->line("systemadmin_username"),
				'rules' => 'trim|required|min_length[4]|max_length[40]|xss_clean|callback_lol_username'
			),
			array(
				'field' => 'password',
				'label' => $this->lang->line("systemadmin_password"),
				'rules' => 'trim|required|min_length[4]|max_length[40]|min_length[4]|xss_clean'
			)
		);
		return $rules;
	}

	public function index() {
		$this->data['headerassets'] = array(
			'css' => array(
				'assets/datepicker/datepicker.css'
			),
			'js' => array(
				'assets/datepicker/datepicker.js'
			)
		);
		if($_POST) {
			$rules = $this->rules();
			$this->form_validation->set_rules($rules);
			if ($this->form_validation->run() == FALSE) {
				$this->data["subview"] = "register/index";
				$this->load->view('_layout_register', $this->data);
			} else {
				$array = array();
				$array["name"] = $this->input->post("name");
				$array["dob"] = date("Y-m-d", strtotime($this->input->post("dob")));
				$array["email"] = $this->input->post("email");
				$array["jod"] = date("Y-m-d");
				$array["username"] = $this->input->post("username");
				$array['password'] = $this->systemadmin_m->hash($this->input->post("password"));
				$array["usertypeID"] = 1;
				$array["create_date"] = date("Y-m-d h:i:s");
				$array["modify_date"] = date("Y-m-d h:i:s");
				$array["create_userID"] = 0;
				$array["create_username"] = $this->input->post("username");
				$array["create_usertype"] = "Admin";
				$array["active"] = 1;

				$this->usercreatemail($this->input->post('email'), $this->input->post('username'), $this->input->post('password'));

				$this->systemadmin_m->insert_systemadmin($array);
				$userID = $this->db->insert_id();
				$user = $this->systemadmin_m->get_systemadmin($userID);
				$userType = $this->usertype_m->get_single_usertype([ 'usertypeID' => $user->usertypeID ]);
				$session = [
						"loginuserID"         => $userID,
						"name"                => $user->name,
						"email"               => $user->email,
						"usertypeID"          => $user->usertypeID,
						'usertype'            => $userType->usertype,
						"username"            => $user->username,
						"varifyvaliduser"     => true,
						"loggedin"            => true
				];
				//$this->data["siteinfos"] = $siteInfo;
				$this->session->set_userdata($session);
				$this->session->set_flashdata('success', $this->lang->line('menu_success'));
				redirect(base_url("school/add"));
			}
		} else {
			$this->data["subview"] = "register/index";
			$this->load->view('_layout_register', $this->data);
		}
	}

	public function lol_username() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		if((int)$id) {
			$systemadmin_info = $this->systemadmin_m->get_systemadmin($id);
			$tables = array('student' => 'student', 'parents' => 'parents', 'teacher' => 'teacher', 'user' => 'user', 'systemadmin' => 'systemadmin');
			$array = array();
			$i = 0;
			foreach ($tables as $table) {
				$user = $this->systemadmin_m->get_username($table, array("username" => $this->input->post('username'), "username !=" => $systemadmin_info->username));
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
				$user = $this->systemadmin_m->get_username($table, array("username" => $this->input->post('username')));
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

	public function date_valid($date) {
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

	public function unique_email() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		if((int)$id) {
			$systemadmin_info = $this->systemadmin_m->get_systemadmin($id);
			$tables = array('student' => 'student', 'parents' => 'parents', 'teacher' => 'teacher', 'user' => 'user', 'systemadmin' => 'systemadmin');
			$array = array();
			$i = 0;
			foreach ($tables as $table) {
				$user = $this->systemadmin_m->get_username($table, array("email" => $this->input->post('email'), 'username !=' => $systemadmin_info->username ));
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
				$user = $this->systemadmin_m->get_username($table, array("email" => $this->input->post('email')));
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
}

/* End of file user.php */
/* Location: .//D/xampp/htdocs/school/mvc/controllers/user.php */
