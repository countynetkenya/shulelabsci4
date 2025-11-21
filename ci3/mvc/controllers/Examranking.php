<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Examranking extends Admin_Controller {
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
    $this->load->model("student_m");
		$this->load->model("subject_m");
		$this->load->model("examranking_m");

		$language = $this->session->userdata('lang');
		$this->lang->load('examranking', $language);
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

    $id                            = htmlentities(escapeString($this->uri->segment(3)));
    $schoolID 								     = $this->session->userdata('schoolID');
    if((int)$id) {
			$this->data['set'] = $id;
      $this->data['classes'] = $this->student_m->get_classes(array("schoolID" => $schoolID));
			$fetchClass = pluck($this->data['classes'], 'classesID', 'classesID');
			if(isset($fetchClass[$id])) {
				$this->data['examrankings'] = $this->examranking_m->get_order_by_examranking(array('classesID' => $id, 'schoolID' => $schoolID));
        $this->data['subjectsArr'] = pluck($this->subject_m->general_get_order_by_subject(array('classesID' => $id, 'schoolID' => $schoolID)), 'subject', 'subjectID');
				$this->data["subview"] = "examranking/index";
				$this->load->view('_layout_main', $this->data);
			} else {
				$this->data['set'] = 0;
				$this->data['examrankings'] = [];
				$this->data['classes'] = $this->student_m->get_classes(array("schoolID" => $schoolID));
				$this->data["subview"] = "examranking/index";
				$this->load->view('_layout_main', $this->data);
			}
		} else {
			$this->data['set'] = 0;
			$this->data['examrankings'] = $this->examranking_m->get_order_by_examranking(array('schoolID' => $schoolID));
			$this->data['subjectsArr'] = pluck($this->subject_m->general_get_order_by_subject(array('schoolID' => $schoolID)), 'subject', 'subjectID');
			$this->data['classes'] = $this->student_m->get_classes(array("schoolID" => $schoolID));
			$this->data["subview"] = "examranking/index";
			$this->load->view('_layout_main', $this->data);
		}
	}

	protected function rules() {
		$rules = array(
			array(
				'field' => 'examranking',
				'label' => $this->lang->line("examranking_examranking"),
				'rules' => 'trim|required|xss_clean|max_length[60]|callback_unique_examranking'
			),
      array(
				'field' => 'classesID',
				'label' => $this->lang->line("examranking_classes"),
				'rules' => 'trim|numeric|required|xss_clean|max_length[11]|callback_unique_classes'
			),
			array(
				'field' => 'subjects[]',
				'label' => $this->lang->line("examranking_subjects"),
				'rules' => 'trim|required|xss_clean|callback_unique_subject'
			)
		);
		return $rules;
	}

	public function add() {
		$this->data['headerassets'] = array(
			'css' => array(
				'assets/select2/css/select2.css',
				'assets/select2/css/select2-bootstrap.css'
			),
			'js' => array(
				'assets/select2/select2.js'
			)
		);

    $id                            = htmlentities(escapeString($this->uri->segment(3)));
		$schoolID 								     = $this->session->userdata('schoolID');
		$ex_class                      = $this->data['siteinfos']->ex_class;
		$this->data['classes']         = $this->classes_m->general_get_order_by_classes(['classesID !='=> $ex_class, 'schoolID' => $schoolID]);
    if((int)$id) {
			$this->data['set'] = $id;
      $this->data['mandatorySubjects'] = $this->subject_m->general_get_order_by_subject(array('classesID' => $id, 'type' => 1, 'schoolID' => $schoolID));
      $this->data['optionalSubjects']  = $this->subject_m->general_get_order_by_subject(array('classesID' => $id, 'type' => 0, 'schoolID' => $schoolID));
      $this->data['nonexaminableSubjects']  = $this->subject_m->general_get_order_by_subject(array('classesID' => $id, 'type' => 2, 'schoolID' => $schoolID));
    }

		if($_POST) {
			$rules = $this->rules();
			$this->form_validation->set_rules($rules);
			if ($this->form_validation->run() == FALSE) {
				$errors  = $this->form_validation->error_array();
				$message = '';
				if(customCompute($errors)) {
					foreach ($errors as $error) {
						$message .= $error.'<br/>';
					}
				}
				$this->session->set_flashdata('error', $message);
				$this->data["subview"]          = "examranking/add";
				$this->load->view('_layout_main', $this->data);
			} else {
        $array = [
            "classesID" => $this->input->post('classesID'),
            "examranking" => $this->input->post('examranking'),
            "subjects" => implode(",", $this->input->post('subjects')),
						"mandatory_top" => $this->input->post('mandatoryTopNumber'),
						"optional_top" => $this->input->post('optionalTopNumber'),
						"nonexaminable_top" => $this->input->post('nonexaminableTopNumber'),
            "schoolID"    => $schoolID,
        ];

        $this->examranking_m->insert_examranking($array);

				$this->session->set_flashdata('success', "Success");
				redirect(base_url('examranking/index'));
			}
		} else {
			$this->data["subview"]          = "examranking/add";
			$this->load->view('_layout_main', $this->data);
		}

	}

  public function edit() {
		$this->data['headerassets'] = array(
			'css' => array(
				'assets/select2/css/select2.css',
				'assets/select2/css/select2-bootstrap.css'
			),
			'js' => array(
				'assets/select2/select2.js'
			)
		);
		$id = htmlentities(escapeString($this->uri->segment(3)));
		$url = htmlentities(escapeString($this->uri->segment(4)));
		if((int)$id && (int)$url) {
			$schoolID = $this->session->userdata('schoolID');
			$this->data['classes'] = $this->classes_m->get_order_by_classes(array("schoolID" => $schoolID));
			$fetchClass = pluck($this->data['classes'], 'classesID', 'classesID');
			if(isset($fetchClass[$url])) {
				$this->data['examranking'] = $this->examranking_m->get_single_examranking(array('examrankingID' => $id, 'classesID' => $url, 'schoolID' => $schoolID));
				if(customCompute($this->data['examranking'])) {
					$this->data['set'] = $url;
          $this->data['mandatorySubjects'] = $this->subject_m->general_get_order_by_subject(array('classesID' => $url, 'type' => 1, 'schoolID' => $schoolID));
          $this->data['optionalSubjects']  = $this->subject_m->general_get_order_by_subject(array('classesID' => $url, 'type' => 0, 'schoolID' => $schoolID));
          $this->data['nonexaminableSubjects']  = $this->subject_m->general_get_order_by_subject(array('classesID' => $url, 'type' => 2, 'schoolID' => $schoolID));
					if($_POST) {
						$rules = $this->rules();
						$this->form_validation->set_rules($rules);
						if ($this->form_validation->run() == FALSE) {
							$this->data["subview"] = "examranking/edit";
							$this->load->view('_layout_main', $this->data);
						} else {
              $array = [
                  "classesID" => $this->input->post('classesID'),
                  "examranking" => $this->input->post('examranking'),
                  "subjects" => implode(",", $this->input->post('subjects')),
									"mandatory_top" => $this->input->post('mandatoryTopNumber'),
									"optional_top" => $this->input->post('optionalTopNumber'),
									"nonexaminable_top" => $this->input->post('nonexaminableTopNumber'),
                  "schoolID"    => $schoolID,
              ];

							$this->examranking_m->update_examranking($array, $id);

							$this->session->set_flashdata('success', $this->lang->line('menu_success'));
							redirect(base_url("examranking/index/$url"));
						}
					} else {
						$this->data["subview"] = "examranking/edit";
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
		$schoolID = $this->session->userdata('schoolID');
		$id = htmlentities(escapeString($this->uri->segment(3)));
		$url = htmlentities(escapeString($this->uri->segment(4)));
		if((int)$id && (int)$url) {
			$schoolID = $this->session->userdata('schoolID');
			$fetchClass = pluck($this->classes_m->get_order_by_classes(array("schoolID" => $schoolID)), 'classesID', 'classesID');
			if(isset($fetchClass[$url])) {
				$examranking = $this->examranking_m->get_single_examranking(array('examrankingID' => $id, 'classesID' => $url, 'schoolID' => $schoolID));
				if(customCompute($examranking)) {
					$this->examranking_m->delete_examranking($id);
					$this->session->set_flashdata('success', $this->lang->line('menu_success'));
					redirect(base_url("examranking/index/$url"));
				} else {
					redirect(base_url("examranking/index"));
				}
			} else {
				redirect(base_url("examranking/index"));
			}
		} else {
			redirect(base_url("examranking/index"));
		}
	}

  public function unique_examranking() {
		$schoolID = $this->session->userdata('schoolID');
		$id = htmlentities(escapeString($this->uri->segment(3)));
		if((int)$id) {
			$examranking = $this->examranking_m->get_order_by_examranking(array("examranking" => $this->input->post("examranking"), "examrankingID !=" => $id, "classesID" => $this->input->post("classesID"), "schoolID" => $schoolID));
			if(customCompute($examranking)) {
				$this->form_validation->set_message("unique_examranking", "%s already exists");
				return FALSE;
			}
			return TRUE;
		} else {
			$examranking = $this->examranking_m->get_order_by_examranking(array("examranking" => $this->input->post("examranking"), "classesID" => $this->input->post("classesID"), "schoolID" => $schoolID));

			if(customCompute($examranking)) {
				$this->form_validation->set_message("unique_examranking", "%s already exists");
				return FALSE;
			}
			return TRUE;
		}
	}

  public function unique_classes() {
		if($this->input->post('classesID') == 0) {
			$this->form_validation->set_message("unique_classes", "The %s field is required");
	    return FALSE;
		}
		return TRUE;
	}

  public function unique_subject() {
		$schoolID = $this->session->userdata('schoolID');

    $subjects = $this->input->post('subjects');
    if(customCompute($subjects)) {
      foreach ($subjects as $subject) {
        $subject = $this->subject_m->get_single_subject(array("subjectID" => $subject, "classesID" => $this->input->post("classesID"), "schoolID" => $schoolID));
        if(!$subject) {
          $this->form_validation->set_message("unique_subject", "The subject(s) is not valid");
          return FALSE;
        }
      }
    }
    return TRUE;
	}

  public function subject_list() {
		$classID = $this->input->post('id');
    if(strpos($_SERVER['HTTP_REFERER'], "edit") !== false)
      $page = "edit";
    elseif(strpos($_SERVER['HTTP_REFERER'], "add") !== false)
      $page = "add";
    else
      $page = "index";
		if((int)$classID) {
			$string = base_url("examranking/$page/$classID");
			//echo $string;
		} else {
			$string = base_url("examranking/$page");
		}
		echo $string;
	}
}
