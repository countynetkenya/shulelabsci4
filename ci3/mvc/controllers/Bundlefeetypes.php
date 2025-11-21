<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bundlefeetypes extends Admin_Controller {
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
		$this->load->model("bundlefeetypes_m");
		$this->load->model("bundlefeetype_feetypes_m");
		$this->load->model("feetypes_m");
		$language = $this->session->userdata('lang');
		$this->lang->load('bundlefeetypes', $language);
	}

	public function index() {
		$this->data['bundlefeetypes'] = $this->bundlefeetypes_m->get_order_by_bundlefeetypes(array('schoolID' => $this->session->userdata('schoolID')));
		$this->data["subview"] = "bundlefeetypes/index";
		$this->load->view('_layout_main', $this->data);
	}

	protected function rules() {
		$rules = array(
				array(
					'field' => 'bundlefeetypes',
					'label' => $this->lang->line("feetypes_name"),
					'rules' => 'trim|required|xss_clean|max_length[60]|callback_unique_bundlefeetypes'
				),
				array(
					'field' => 'feetypeitems',
					'label' => $this->lang->line("bundlefeetypes_feetype"),
					'rules' => 'trim|required|xss_clean|callback_unique_feetypeitems'
				),
				array(
					'field' => 'note',
					'label' => $this->lang->line("feetypes_note"),
					'rules' => 'trim|xss_clean|max_length[200]'
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
			$this->data['feetypes'] = $this->feetypes_m->get_order_by_feetypes(array("schoolID" => $schoolID));
			$feetype                = pluck($this->feetypes_m->get_order_by_feetypes(array("schoolID" => $schoolID)), 'feetypes', 'feetypesID');

			if($_POST) {
				$rules = $this->rules();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {
					$this->data["subview"] = "bundlefeetypes/add";
					$this->load->view('_layout_main', $this->data);
				} else {
	        $array = [
	            "bundlefeetypes" => $this->input->post("bundlefeetypes"),
	            "note"           => $this->input->post("note"),
							"schoolID"       => $schoolID
	        ];

	        $this->bundlefeetypes_m->insert_bundlefeetypes($array);
					$id = $this->db->insert_id();

					$feetypeitems = json_decode($this->input->post('feetypeitems'));

					$feetypesArray         = [];

					if(customCompute($feetypeitems)) {
							foreach($feetypeitems as $feetypeitem) {
									$feetypesArray[] = [
											'feetypesID' => isset($feetypeitem->feetypeID) ? $feetypeitem->feetypeID : 0,
											'feetypes' => isset($feetype[$feetypeitem->feetypeID]) ? $feetype[$feetypeitem->feetypeID] : '',
											'amount' => isset($feetypeitem->amount) ? $feetypeitem->amount : 0,
											'bundlefeetypesID' => $id,
											'schoolID' => $schoolID,
									];
							}
					}

					$this->bundlefeetype_feetypes_m->insert_batch_bundlefeetype_feetypes($feetypesArray);

					$this->session->set_flashdata('success', $this->lang->line('menu_success'));

					$retArray['status']  = TRUE;
					$retArray['message'] = 'Success';
					echo json_encode($retArray);
				}
			} else {
				$this->data["subview"] = "bundlefeetypes/add";
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

			$schoolID               = $this->session->userdata('schoolID');
			$this->data['feetypes'] = $this->feetypes_m->get_order_by_feetypes(array("schoolID" => $schoolID));
			$feetype                = pluck($this->feetypes_m->get_order_by_feetypes(array("schoolID" => $schoolID)), 'feetypes', 'feetypesID');

			$id = htmlentities(escapeString($this->uri->segment(3)));
			if((int)$id) {
				$this->data['bundlefeetypeID'] = $id;
				$this->data['bundlefeetypes'] = $this->bundlefeetypes_m->get_single_bundlefeetypes(array('bundlefeetypesID' => $id, 'schoolID' => $schoolID));
				$this->data['bundlefeetype_feetypes'] = $this->bundlefeetype_feetypes_m->get_order_by_bundlefeetype_feetypes(array("bundlefeetypesID" => $id, 'schoolID' => $schoolID));
				if($this->data['bundlefeetypes']) {
					if($_POST) {
						$rules = $this->rules();
						$this->form_validation->set_rules($rules);
						if ($this->form_validation->run() == FALSE) {
							$this->data["subview"] = "bundlefeetypes/edit";
							$this->load->view('_layout_main', $this->data);
						} else {
							$array = array(
								"bundlefeetypes" => $this->input->post("bundlefeetypes"),
								"note" => $this->input->post("note")
							);

							$this->bundlefeetypes_m->update_bundlefeetypes($array, $id);

							$feetypeitems = json_decode($this->input->post('feetypeitems'));

							$feetypesArray         = [];

							if(customCompute($feetypeitems)) {
									foreach($feetypeitems as $feetypeitem) {
											$feetypesArray[] = [
													'feetypesID'  => isset($feetypeitem->feetypeID) ? $feetypeitem->feetypeID : 0,
													'feetypes'    => isset($feetype[$feetypeitem->feetypeID]) ? $feetype[$feetypeitem->feetypeID] : '',
													'amount'        => isset($feetypeitem->amount) ? $feetypeitem->amount : 0,
													'bundlefeetypesID' => $id,
													'schoolID' => $schoolID,
											];
									}
							}

							$this->bundlefeetype_feetypes_m->delete_bundlefeetype_feetypes_by_bundlefeetypeID($id);
							$this->bundlefeetype_feetypes_m->insert_batch_bundlefeetype_feetypes($feetypesArray);

							$this->session->set_flashdata('success', $this->lang->line('menu_success'));

							$retArray['status']  = TRUE;
							$retArray['message'] = 'Success';
							echo json_encode($retArray);
						}
					} else {
						$this->data["subview"] = "bundlefeetypes/edit";
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
				$bundlefeetypes = $this->bundlefeetypes_m->get_single_bundlefeetypes(array('bundlefeetypesID' => $id, 'schoolID' => $this->session->userdata('schoolID')));
				if(customCompute($bundlefeetypes)) {
					$this->bundlefeetypes_m->delete_bundlefeetypes($id);
					$this->session->set_flashdata('success', $this->lang->line('menu_success'));
				}
				redirect(base_url("bundlefeetypes/index"));
			} else {
				redirect(base_url("bundlefeetypes/index"));
			}
		} else {
			$this->data["subview"] = "error";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function unique_bundlefeetypes() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		$schoolID = $this->session->userdata('schoolID');
		if((int)$id) {
			$bundlefeetypes = $this->bundlefeetypes_m->get_order_by_bundlefeetypes(array("bundlefeetypes" => $this->input->post("bundlefeetypes"), 'schoolID' => $schoolID, "bundlefeetypesID !=" => $id));
			if(customCompute($bundlefeetypes)) {
				$this->form_validation->set_message("unique_bundlefeetypes", "%s already exists");
				return FALSE;
			}
			return TRUE;
		}
		else {
			$bundlefeetypes = $this->bundlefeetypes_m->get_order_by_bundlefeetypes(array("bundlefeetypes" => $this->input->post("bundlefeetypes"), 'schoolID' => $schoolID));
			if(customCompute($bundlefeetypes)) {
				$this->form_validation->set_message("unique_bundlefeetypes", "%s already exists");
				return FALSE;
			}
			return TRUE;
		}
	}

	public function unique_feetypeitems()
	{
			$feetypeitems = json_decode($this->input->post('feetypeitems'));
			$status       = [];
			if(customCompute($feetypeitems)) {
					foreach($feetypeitems as $feetypeitem) {
							if($feetypeitem->amount == '') {
									$status[] = FALSE;
							}
					}
			} else {
					$this->form_validation->set_message("unique_feetypeitems", "The fee type item is required.");
					return FALSE;
			}

			if(in_array(FALSE, $status)) {
					$this->form_validation->set_message("unique_feetypeitems", "The fee type amount is required.");
					return FALSE;
			}
			return TRUE;
	}

	public function getfeetypes()
	{
			$bundlefeetypeID    = $this->input->post('bundlefeetypeID');

			$feetypes = $this->bundlefeetype_feetypes_m->get_order_by_bundlefeetype_feetypes(array("bundlefeetypesID" => $bundlefeetypeID, 'schoolID' => $this->session->userdata('schoolID')));

			echo json_encode($feetypes);
	}
}
