<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH . '../vendor/autoload.php');
use QuickBooksOnline\API\DataService\DataService;

const PAYMENT_ACCOUNT_TYPE = "Bank";

class Paymenttypes extends Admin_Controller {
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
		$this->load->model("paymenttypes_m");
		$this->load->model("quickbookssettings_m");
		$this->load->model("quickbookslog_m");
		$language = $this->session->userdata('lang');
		$this->lang->load('paymenttypes', $language);
	}

	public function index() {
		$this->data['paymenttypes'] = $this->paymenttypes_m->get_order_by_paymenttypes(array('schoolID' => $this->session->userdata('schoolID')));
		$this->data["subview"] = "paymenttypes/index";
		$this->load->view('_layout_main', $this->data);
	}

	protected function rules() {
		$rules = array(
				array(
					'field' => 'paymenttypes',
					'label' => $this->lang->line("paymenttypes_name"),
					'rules' => 'trim|required|xss_clean|max_length[60]|callback_unique_paymenttypes'
				),
				array(
					'field' => 'note',
					'label' => $this->lang->line("paymenttypes_note"),
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
				$this->data["subview"] = "paymenttypes/add";
				$this->load->view('_layout_main', $this->data);
			} else {
				$deposittoaccountref = $this->input->post('deposittoaccountref');
				$deposittoaccountrefArray = explode(",", $deposittoaccountref);
        $array = [
            "paymenttypes" => $this->input->post("paymenttypes"),
            "note" => $this->input->post("note"),
						"deposittoaccountrefID" => is_numeric($deposittoaccountrefArray[0]) ? $deposittoaccountrefArray[0] : NULL,
						"deposittoaccountref" => !empty($deposittoaccountrefArray[1]) ? $deposittoaccountrefArray[1] : NULL,
						"schoolID" => $this->session->userdata('schoolID'),
        ];

        $this->paymenttypes_m->insert_paymenttypes($array);

				$this->session->set_flashdata('success', $this->lang->line('menu_success'));
				redirect(base_url("paymenttypes/index"));
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
					$this->data["deposittoaccountrefs"] = $this->getDepositToAccountRefObj($dataService);
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

			$this->data["subview"] = "paymenttypes/add";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function edit() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		if((int)$id) {
			$this->data['paymenttypes'] = $this->paymenttypes_m->get_single_paymenttypes(array('paymenttypesID' => $id, 'schoolID' => $this->session->userdata('schoolID')));
			if($this->data['paymenttypes']) {
				if($_POST) {
					$rules = $this->rules();
					$this->form_validation->set_rules($rules);
					if ($this->form_validation->run() == FALSE) {
						$this->data["subview"] = "paymenttypes/edit";
						$this->load->view('_layout_main', $this->data);
					} else {
						$deposittoaccountref = $this->input->post('deposittoaccountref');
						$deposittoaccountrefArray = explode(",", $deposittoaccountref);
						$array = array(
							"paymenttypes" => $this->input->post("paymenttypes"),
							"note" => $this->input->post("note"),
							"deposittoaccountrefID" => is_numeric($deposittoaccountrefArray[0]) ? $deposittoaccountrefArray[0] : NULL,
							"deposittoaccountref"  => !empty($deposittoaccountrefArray[1]) ? $deposittoaccountrefArray[1] : NULL,
						);

						$this->paymenttypes_m->update_paymenttypes($array, $id);
						$this->session->set_flashdata('success', $this->lang->line('menu_success'));
						redirect(base_url("paymenttypes/index"));
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

							$this->data["deposittoaccountrefs"] = $this->getDepositToAccountRefObj($dataService);
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

					$this->data["subview"] = "paymenttypes/edit";
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
			$paymenttypes = $this->paymenttypes_m->get_single_paymenttypes(array('paymenttypesID' => $id, 'schoolID' => $this->session->userdata('schoolID')));
			if(customCompute($paymenttypes)) {
				$this->paymenttypes_m->delete_paymenttypes($id);
				$this->session->set_flashdata('success', $this->lang->line('menu_success'));
			}
			redirect(base_url("paymenttypes/index"));
		} else {
			redirect(base_url("paymenttypes/index"));
		}
	}

	public function unique_paymenttypes() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		$schoolID = $this->session->userdata('schoolID');
		if((int)$id) {
			$paymenttypes = $this->paymenttypes_m->get_order_by_paymenttypes(array("paymenttypes" => $this->input->post("paymenttypes"), "schoolID" => $schoolID, "paymenttypesID !=" => $id));
			if(customCompute($paymenttypes)) {
				$this->form_validation->set_message("unique_paymenttypes", "%s already exists");
				return FALSE;
			}
			return TRUE;
		} else {
			$paymenttypes = $this->paymenttypes_m->get_order_by_paymenttypes(array("paymenttypes" => $this->input->post("paymenttypes"), "schoolID" => $schoolID));
			if(customCompute($paymenttypes)) {
				$this->form_validation->set_message("unique_paymenttypes", "%s already exists");
				return FALSE;
			}
			return TRUE;
		}
	}

  function getDepositToAccountRefObj($dataService) {
		$schoolID = $this->session->userdata('schoolID');
		try {
			$accountArray = $dataService->Query("select * from Account where AccountType='" . PAYMENT_ACCOUNT_TYPE . "'");
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Account where AccountType=" . PAYMENT_ACCOUNT_TYPE, "status" => "OK", 'schoolID' => $schoolID));
		} catch (Exception $e) {
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Account where AccountType=" . PAYMENT_ACCOUNT_TYPE, "message" => $e->getMessage(), "status" => "ERROR", 'schoolID' => $schoolID));
		}

		$error = $dataService->getLastError();
		if ($error) {
			logError($error);
			$retArray['error']  = $error;
			$retArray['status'] = FALSE;
			echo json_encode($retArray);
			exit;
		} else {
			if (is_array($accountArray) && sizeof($accountArray) > 0) {
					$result = array();
					foreach ($accountArray as $item) {
						$result[$item->Id] = $item->Name;
					}
					return $result;
			}
		}
	}
}
