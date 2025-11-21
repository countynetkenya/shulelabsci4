<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH . '../vendor/autoload.php');
use QuickBooksOnline\API\DataService\DataService;

class Divisions extends Admin_Controller {
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
		$this->load->model("divisions_m");
		$this->load->model("quickbookssettings_m");
		$this->load->model("quickbookslog_m");
		$language = $this->session->userdata('lang');
		$this->lang->load('divisions', $language);
	}

	public function index() {
		$this->data['divisions'] = $this->divisions_m->get_order_by_divisions(array('schoolID' => $this->session->userdata('schoolID')));
		$this->data["subview"] = "divisions/index";
		$this->load->view('_layout_main', $this->data);
	}

	protected function rules() {
		$rules = array(
				array(
					'field' => 'divisions',
					'label' => $this->lang->line("divisions_name"),
					'rules' => 'trim|required|xss_clean|max_length[60]|callback_unique_divisions'
				),
				array(
					'field' => 'note',
					'label' => $this->lang->line("divisions_note"),
					'rules' => 'trim|xss_clean|max_length[200]'
				)
			);
		return $rules;
	}

	public function add() {
		if($_POST) {
			$rules = $this->rules();
			$this->form_validation->set_rules($rules);
			if ($this->form_validation->run() == FALSE) {
				$this->data["subview"] = "divisions/add";
				$this->load->view('_layout_main', $this->data);
			} else {
				$schoolID = $this->session->userdata('schoolID');
				$quickbooksclass = $this->input->post('quickbooksclass');
				$quickbooksclassArray = explode(",", $quickbooksclass);
        $array = [
            "divisions" => $this->input->post("divisions"),
            "note"     => $this->input->post("note"),
						"quickbooksclassID" => is_numeric($quickbooksclassArray[0]) ? $quickbooksclassArray[0] : NULL,
						"quickbooksclass"  => !empty($quickbooksclassArray[1]) ? $quickbooksclassArray[1] : NULL,
						"schoolID" => $schoolID,
        ];

        $this->divisions_m->insert_divisions($array);

				$this->session->set_flashdata('success', $this->lang->line('menu_success'));
				redirect(base_url("divisions/index"));
			}
		} else {
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

				$OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
				$authUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();

				// Store the url in PHP Session Object;
				$_SESSION['authUrl'] = $authUrl;
				$this->data["authUrl"] = $authUrl;
				$this->data["config"] = $config;

				if (!empty($config['sessionAccessToken']) && now() < $config['sessionAccessTokenExpiry']) {
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
					$this->data["quickbooksclasses"] = $this->getClassObj($dataService);
				}
		  }

			$this->data['headerassets'] = [
					'css' => [
							'assets/select2/css/select2.css',
							'assets/select2/css/select2-bootstrap.css'
					],
					'js'  => [
							'assets/select2/select2.js'
					]
			];

			$this->data["subview"] = "divisions/add";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function edit() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		if((int)$id) {
			$schoolID = $this->session->userdata('schoolID');
			$this->data['divisions'] = $this->divisions_m->get_single_divisions(array('divisionsID' => $id, 'schoolID' => $schoolID));
			if($this->data['divisions']) {
				if($_POST) {
					$rules = $this->rules();
					$this->form_validation->set_rules($rules);
					if ($this->form_validation->run() == FALSE) {
						$this->data["subview"] = "divisions/edit";
						$this->load->view('_layout_main', $this->data);
					} else {
						$quickbooksclass = $this->input->post('quickbooksclass');
						$quickbooksclassArray = explode(",", $quickbooksclass);
						$array = array(
							"divisions" => $this->input->post("divisions"),
							"note" => $this->input->post("note"),
							"quickbooksclassID" => is_numeric($quickbooksclassArray[0]) ? $quickbooksclassArray[0] : NULL,
							"quickbooksclass"  => !empty($quickbooksclassArray[1]) ? $quickbooksclassArray[1] : NULL,
						);

						$this->divisions_m->update_divisions($array, $id);
						$this->session->set_flashdata('success', $this->lang->line('menu_success'));
						redirect(base_url("divisions/index"));
					}
				} else {
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

						$OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
						$authUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();

						// Store the url in PHP Session Object;
						$_SESSION['authUrl'] = $authUrl;
						$this->data["authUrl"] = $authUrl;
						$this->data["config"] = $config;

						if (!empty($config['sessionAccessToken']) && now() < $config['sessionAccessTokenExpiry']) {
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

							$this->data["quickbooksclasses"] = $this->getClassObj($dataService);
						}
					}

					$this->data['headerassets'] = [
							'css' => [
									'assets/select2/css/select2.css',
									'assets/select2/css/select2-bootstrap.css'
							],
							'js'  => [
									'assets/select2/select2.js'
							]
					];

					$this->data["subview"] = "divisions/edit";
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
			$division = $this->divisions_m->get_single_division(array('divisionsID' => $id, 'schoolID' => $schoolID));
			if(customCompute($division)) {
				$this->divisions_m->delete_divisions($id);
				$this->session->set_flashdata('success', $this->lang->line('menu_success'));
			}
			redirect(base_url("divisions/index"));
		} else {
			redirect(base_url("divisions/index"));
		}
	}

	public function unique_divisions() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		$schoolID = $this->session->userdata('schoolID');
		if((int)$id) {
			$divisions = $this->divisions_m->get_order_by_divisions(array("divisions" => $this->input->post("divisions"), "divisionsID !=" => $id, 'schoolID' => $schoolID));
			if(customCompute($divisions)) {
				$this->form_validation->set_message("unique_divisions", "%s already exists");
				return FALSE;
			}
			return TRUE;
		} else {
			$divisions = $this->divisions_m->get_order_by_divisions(array("divisions" => $this->input->post("divisions"), 'schoolID' => $schoolID));
			if(customCompute($divisions)) {
				$this->form_validation->set_message("unique_divisions", "%s already exists");
				return FALSE;
			}
			return TRUE;
		}
	}

  function getClassObj($dataService) {
		$schoolID = $this->session->userdata('schoolID');
		try {
			$classArray = $dataService->Query("select * from Class");
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Class", "status" => "OK", 'schoolID' => $schoolID));
		} catch (Exception $e) {
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Class", $e->getMessage(), "status" => "ERROR", 'schoolID' => $schoolID));
		}

		$error = $dataService->getLastError();
		if ($error) {
			logError($error);
			$retArray['error']  = $error;
			$retArray['status'] = FALSE;
			echo json_encode($retArray);
			exit;
		} else {
			if (is_array($classArray) && sizeof($classArray) > 0) {
					$result = array();
					foreach ($classArray as $item) {
						$result[$item->Id] = $item->FullyQualifiedName;
					}
					return $result;
			}
		}
	}
}
