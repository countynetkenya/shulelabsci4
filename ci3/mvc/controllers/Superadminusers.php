<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Superadminusers extends Admin_Controller {
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
		$this->load->model("school_m");
		$this->load->model("usertype_m");
		$this->load->model("manage_salary_m");
		$this->load->model("salaryoption_m");
		$this->load->model("salary_template_m");
		$this->load->model("hourly_template_m");
		$this->load->model("make_payment_m");
		$this->load->model("document_m");
		$language = $this->session->userdata('lang');
		$this->lang->load('superadminusers', $language);
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
				'field' => 'sex',
				'label' => $this->lang->line("systemadmin_sex"),
				'rules' => 'trim|max_length[10]|xss_clean'
			),
			array(
				'field' => 'religion',
				'label' => $this->lang->line("systemadmin_religion"),
				'rules' => 'trim|max_length[25]|xss_clean'
			),
			array(
				'field' => 'email',
				'label' => $this->lang->line("systemadmin_email"),
				'rules' => 'trim|required|max_length[40]|valid_email|xss_clean|callback_unique_email'
			),
			array(
				'field' => 'phone',
				'label' => $this->lang->line("systemadmin_phone"),
				'rules' => 'trim|min_length[5]|max_length[25]|xss_clean'
			),
			array(
				'field' => 'address',
				'label' => $this->lang->line("systemadmin_address"),
				'rules' => 'trim|max_length[200]|xss_clean'
			),
			array(
				'field' => 'jod',
				'label' => $this->lang->line("systemadmin_jod"),
				'rules' => 'trim|required|max_length[10]|callback_date_valid|xss_clean'
			),
			array(
				'field' => 'photo',
				'label' => $this->lang->line("systemadmin_photo"),
				'rules' => 'trim|max_length[200]|xss_clean|callback_photoupload'
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
			),
			array(
				'field' => 'schoolID',
				'label' => $this->lang->line("systemadmin_schoolID"),
				'rules' => 'trim|xss_clean|callback_valid_schoolID'
			)
		);
		return $rules;
	}


	public function send_mail_rules() {
		$rules = array(
			array(
				'field' => 'to',
				'label' => $this->lang->line("systemadmin_to"),
				'rules' => 'trim|required|max_length[60]|valid_email|xss_clean'
			),
			array(
				'field' => 'subject',
				'label' => $this->lang->line("systemadmin_subject"),
				'rules' => 'trim|required|xss_clean'
			),
			array(
				'field' => 'message',
				'label' => $this->lang->line("systemadmin_message"),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field' => 'systemadminID',
				'label' => $this->lang->line("systemadmin_systemadminID"),
				'rules' => 'trim|required|max_length[10]|xss_clean|callback_unique_data'
			)
		);
		return $rules;
	}

	public function unique_data($data) {
		if($data != '') {
			if($data == '0') {
				$this->form_validation->set_message('unique_data', 'The %s field is required.');
				return FALSE;
			}
			return TRUE;
		}
		return TRUE;
	}

	public function photoupload() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		$user = array();
		if((int)$id) {
			$user = $this->systemadmin_m->get_systemadmin($id);
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
			if(customCompute($user)) {
				$this->upload_data['file'] = array('file_name' => $user->photo);
				return TRUE;
			} else {
				$this->upload_data['file'] = array('file_name' => $new_file);
				return TRUE;
			}
		}
	}

	public function index() {
		$this->data['systemadmins'] = $this->systemadmin_m->get_order_by_systemadmin(array('usertypeID' => 1));
		$this->data["subview"] = "superadmin/users/index";
		$this->load->view('_layout_main', $this->data);
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
		if($_POST) {
			$rules = $this->rules();
			$this->form_validation->set_rules($rules);
			if ($this->form_validation->run() == FALSE) {
				$this->data["subview"] = "superadmin/users/add";
				$this->load->view('_layout_main', $this->data);
			} else {
				$array = array();
				$array["name"] = $this->input->post("name");
				$array["dob"] = date("Y-m-d", strtotime($this->input->post("dob")));
				$array["sex"] = $this->input->post("sex");
				$array["religion"] = $this->input->post("religion");
				$array["email"] = $this->input->post("email");
				$array["phone"] = $this->input->post("phone");
				$array["address"] = $this->input->post("address");
				$array["jod"] = date("Y-m-d", strtotime($this->input->post("jod")));
				$array["username"] = $this->input->post("username");
				$array['schoolID'] = !empty($this->input->post("schoolID")) ? implode(",", $this->input->post("schoolID")) : NULL;
				$array['password'] = $this->systemadmin_m->hash($this->input->post("password"));
				$array["usertypeID"] = 1;
				$array["create_date"] = date("Y-m-d h:i:s");
				$array["modify_date"] = date("Y-m-d h:i:s");
				$array["create_userID"] = $this->session->userdata('loginuserID');
				$array["create_username"] = $this->session->userdata('username');
				$array["create_usertype"] = $this->session->userdata('usertype');
				$array["active"] = 1;
				$array['photo'] = $this->upload_data['file']['file_name'];

				$this->usercreatemail($this->input->post('email'), $this->input->post('username'), $this->input->post('password'));

				$this->systemadmin_m->insert_systemadmin($array);
				$this->session->set_flashdata('success', $this->lang->line('menu_success'));
				redirect(base_url("superadmin/users"));
			}
		} else {
			$this->data['schools'] = $this->school_m->get_order_by_school(array('schoolID !=' => 0));
			$this->data["subview"] = "superadmin/users/add";
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
		if ((int)$id) {
			$this->data['systemadmin'] = $this->systemadmin_m->get_systemadmin($id);
			$this->data['schools'] = $this->school_m->get_order_by_school(array('schoolID !=' => 0));
			if($this->data['systemadmin']) {
				if($_POST) {
					$rules = $this->rules();
					unset($rules[10]);
					$this->form_validation->set_rules($rules);
					if ($this->form_validation->run() == FALSE) {
						$this->data["subview"] = "superadmin/users/edit";
						$this->load->view('_layout_main', $this->data);
					} else {
						$array = array();
						$array["name"] = $this->input->post("name");
						$array["dob"] = date("Y-m-d", strtotime($this->input->post("dob")));
						$array["sex"] = $this->input->post("sex");
						$array["religion"] = $this->input->post("religion");
						$array["email"] = $this->input->post("email");
						$array["phone"] = $this->input->post("phone");
						$array["address"] = $this->input->post("address");
						$array["jod"] = date("Y-m-d", strtotime($this->input->post("jod")));
						$array["modify_date"] = date("Y-m-d h:i:s");
						$array['username'] = $this->input->post('username');
						$array['schoolID'] = !empty($this->input->post("schoolID")) ? implode(",", $this->input->post("schoolID")) : NULL;
						$array['photo'] = $this->upload_data['file']['file_name'];

						$this->systemadmin_m->update_systemadmin($array, $id);
						$this->session->set_flashdata('success', $this->lang->line('menu_success'));
						redirect(base_url("superadmin/users"));

					}
				} else {
					$this->data["subview"] = "superadmin/users/edit";
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
			$this->data['systemadmin'] = $this->systemadmin_m->get_systemadmin($id);
			if($this->data['systemadmin']) {
				if(config_item('demo') == FALSE) {
					if($this->data['systemadmin']->photo != 'default.png' && $this->data['systemadmin']->photo != 'defualt.png') {
						unlink(FCPATH.'uploads/images/'.$this->data['systemadmin']->photo);
					}
				}
				$this->systemadmin_m->delete_systemadmin($id);
				$this->session->set_flashdata('success', $this->lang->line('menu_success'));
				redirect(base_url("superadmin/users"));
			} else {
				redirect(base_url("superadmin/users"));
			}
		} else {
			redirect(base_url("superadmin/users"));
		}
	}

	public function view() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		if((int)$id) {
			$this->getView($id);
		} else {
			$this->data["subview"] = "error";
			$this->load->view('_layout_main', $this->data);
		}

	}

	private function getView($systemadminID) {
		if((int)$systemadminID) {
			$systemadmin = $this->systemadmin_m->get_systemadmin($systemadminID);
			$this->pluckInfo();
			$this->basicInfo($systemadmin);;
			if(customCompute($systemadmin)) {
				$this->data["subview"] = "superadmin/users/getView";
				$this->load->view('_layout_main', $this->data);
			} else {
				$this->data["subview"] = "error";
				$this->load->view('_layout_main', $this->data);
			}
		} else {
			$this->data["subview"] = "error";
			$this->load->view('_layout_main', $this->data);
		}
	}

	private function pluckInfo() {
		$this->data['usertypes'] = pluck($this->usertype_m->get_order_by_usertype(array('usertypeID >' => 0, 'usertypeID <=' => 8)),'usertype','usertypeID');
	}

	private function basicInfo($systemadmin) {
		if(customCompute($systemadmin)) {
			$this->data['profile'] = $systemadmin;
		} else {
			$this->data['profile'] = [];
		}
	}

	public function print_preview() {
		if(permissionChecker('admin_view')) {
			$id = htmlentities(escapeString($this->uri->segment(3)));
			if ((int)$id) {
				$this->data['systemadmin'] = $this->systemadmin_m->get_systemadmin($id);
				if(customCompute($this->data['systemadmin'])) {
					if($id != 1 && $this->session->userdata('loginuserID') != $this->data['systemadmin']->systemadminID) {
						$this->data['usertype'] = $this->usertype_m->get_single_usertype(array('usertypeID' => $this->data['systemadmin']->usertypeID));
						$this->data['panel_title'] = $this->lang->line('panel_title');
						$this->reportPDF('systemadminmodule.css', $this->data, 'systemadmin/print_preview');
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
		} else {
			$this->data["subview"] = "error";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function send_mail() {
		$retArray['status'] = FALSE;
		$retArray['message'] = '';
		if(permissionChecker('admin_view')) {
			if($_POST) {
				$rules = $this->send_mail_rules();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {
					$retArray = $this->form_validation->error_array();
					$retArray['status'] = FALSE;
			    echo json_encode($retArray);
			    exit;
				} else {
					$systemadminID = $this->input->post('systemadminID');
					if ((int)$systemadminID) {
						$this->data['systemadmin'] = $this->systemadmin_m->get_systemadmin($id);
						if(customCompute($this->data["systemadmin"])) {
							$this->data['panel_title'] = $this->lang->line('panel_title');
							$email = $this->input->post('to');
							$subject = $this->input->post('subject');
							$message = $this->input->post('message');
							$this->data['usertype'] = $this->usertype_m->get_single_usertype(array('usertypeID' => $this->data['systemadmin']->usertypeID));
							$this->reportSendToMail('systemadminmodule.css', $this->data, 'systemadmin/print_preview', $email, $subject, $message);
							$retArray['message'] = "Message";
							$retArray['status'] = TRUE;
							echo json_encode($retArray);
						   exit;
						} else {
							$retArray['message'] = $this->lang->line('systemadmin_data_not_found');
							echo json_encode($retArray);
							exit;
						}
					} else {
						$retArray['message'] = $this->lang->line('systemadmin_data_not_found');
						echo json_encode($retArray);
						exit;
					}
				}
			} else {
				$retArray['message'] = $this->lang->line('systemadmin_permissionmethod');
				echo json_encode($retArray);
				exit;
			}
		} else {
			$retArray['message'] = $this->lang->line('systemadmin_permission');
			echo json_encode($retArray);
			exit;
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

	public function valid_schoolID()
	{
			$schoolID = $this->input->post('schoolID');
			$status       = [];
			if(customCompute($schoolID)) {
					foreach($schoolID as $value) {
							$result = $this->school_m->get_single_school(array('schoolID !=' => 0, 'schoolID' => $value));
							if(!customCompute($result)) {
									$status[] = FALSE;
							}
					}
			}

			if(in_array(FALSE, $status)) {
					$this->form_validation->set_message("valid_schoolID", "This school does not exist.");
					return FALSE;
			}
			return TRUE;
	}

	public function active() {
		if(permissionChecker('admin_edit')) {
			$id = $this->input->post('id');
			$this->data['systemadmin'] = $this->systemadmin_m->get_systemadmin($id);
			$status = $this->input->post('status');
			if($id != '' && $status != '') {
				if((int)$id) {
					if($status == 'chacked') {
						$this->systemadmin_m->update_systemadmin(array('active' => 1), $id);
						echo 'Success';
					} elseif($status == 'unchacked') {
						$this->systemadmin_m->update_systemadmin(array('active' => 0), $id);
						echo 'Success';
					} else {
						echo "Error";
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
}

/* End of file user.php */
/* Location: .//D/xampp/htdocs/school/mvc/controllers/user.php */
