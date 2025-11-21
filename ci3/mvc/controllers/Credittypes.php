<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH . '../vendor/autoload.php');
use QuickBooksOnline\API\DataService\DataService;

const INCOME_ACCOUNT_TYPE = "Income";

class Credittypes extends Admin_Controller {
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
		$this->load->model("credittypes_m");
		$this->load->model("quickbookssettings_m");
		$this->load->model("quickbookslog_m");
		$language = $this->session->userdata('lang');
		$this->lang->load('credittypes', $language);
	}

	public function index() {
		$this->data['credittypes'] = $this->credittypes_m->get_order_by_credittypes(array('schoolID' => $this->session->userdata('schoolID')));
		$this->data["subview"] = "credittypes/index";
		$this->load->view('_layout_main', $this->data);
	}

	protected function rules() {
		$rules = array(
				array(
					'field' => 'credittypes',
					'label' => $this->lang->line("credittypes_name"),
					'rules' => 'trim|required|xss_clean|max_length[60]|callback_unique_credittypes'
				),
				array(
					'field' => 'note',
					'label' => $this->lang->line("credittypes_note"),
					'rules' => 'trim|xss_clean|max_length[200]'
				),
				array(
        	'field' => 'monthly',
        	'label' => $this->lang->line("credittypes_monthly"),
        	'rules' => 'trim|xss_clean|max_length[11]|numeric',
      	)
			);
		return $rules;
	}

	public function add() {
		if($_POST) {
			$rules = $this->rules();
			$this->form_validation->set_rules($rules);
			if ($this->form_validation->run() == FALSE) {
				$this->data["subview"] = "credittypes/add";
				$this->load->view('_layout_main', $this->data);
			} else {
				$incomeaccount = $this->input->post('incomeaccount');
				$incomeaccountArray = explode(",", $incomeaccount);
				$monthly = $this->input->post('monthly');
				$schoolID = $this->session->userdata('schoolID');
        if($monthly) {
            for($i = 1; $i<=12; $i++) {
                $month = date('M', mktime(0, 0, 0, $i));
                $array = [
                    'credittypes' => $this->input->post('credittypes'). ' ['.$month.']',
                    "note"     => $this->input->post("note"),
										"incomeaccountID"  => is_numeric($incomeaccountArray[0]) ? $incomeaccountArray[0] : NULL,
										"incomeaccount"    => !empty($incomeaccountArray[1]) ? $incomeaccountArray[1] : NULL,
										"schoolID" => $schoolID,
                ];
                $this->credittypes_m->insert_credittypes($array);
            }
        } else {
            $array = [
                "credittypes" => $this->input->post("credittypes"),
                "note"     => $this->input->post("note"),
								"incomeaccountID"  => is_numeric($incomeaccountArray[0]) ? $incomeaccountArray[0] : NULL,
								"incomeaccount"    => !empty($incomeaccountArray[1]) ? $incomeaccountArray[1] : NULL,
								"schoolID" => $schoolID,
            ];

            $this->credittypes_m->insert_credittypes($array);
        }

				$this->session->set_flashdata('success', $this->lang->line('menu_success'));
				redirect(base_url("credittypes/index"));
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
					$this->data["incomeaccounts"] = $this->getIncomeAccountObj($dataService);
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

			$this->data["subview"] = "credittypes/add";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function edit() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		if((int)$id) {
			$this->data['credittypes'] = $this->credittypes_m->get_single_credittypes(array('credittypesID' => $id, 'schoolID' => $this->session->userdata('schoolID')));
			if($this->data['credittypes']) {
				if($_POST) {
					$rules = $this->rules();
					$this->form_validation->set_rules($rules);
					if ($this->form_validation->run() == FALSE) {
						$this->data["subview"] = "credittypes/edit";
						$this->load->view('_layout_main', $this->data);
					} else {
						$incomeaccount = $this->input->post('incomeaccount');
						$incomeaccountArray = explode(",", $incomeaccount);
						$array = array(
							"credittypes" => $this->input->post("credittypes"),
							"note" => $this->input->post("note"),
							"incomeaccountID"  => is_numeric($incomeaccountArray[0]) ? $incomeaccountArray[0] : NULL,
							"incomeaccount"    => !empty($incomeaccountArray[1]) ? $incomeaccountArray[1] : NULL,
						);

						$this->credittypes_m->update_credittypes($array, $id);
						$this->session->set_flashdata('success', $this->lang->line('menu_success'));
						redirect(base_url("credittypes/index"));
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

							$this->data["incomeaccounts"] = $this->getIncomeAccountObj($dataService);
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

					$this->data["subview"] = "credittypes/edit";
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
			$credittypes = $this->credittypes_m->get_single_credittypes(array('credittypesID' => $id, 'schoolID' => $this->session->userdata('schoolID')));
			if(customCompute($credittypes)) {
				$this->credittypes_m->delete_credittypes($id);
				$this->session->set_flashdata('success', $this->lang->line('menu_success'));
			}
			redirect(base_url("credittypes/index"));
		} else {
			redirect(base_url("credittypes/index"));
		}
	}

	public function unique_credittypes() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		$schoolID = $this->session->userdata('schoolID');
		if((int)$id) {
			$credittypes = $this->credittypes_m->get_order_by_credittypes(array("credittypes" => $this->input->post("credittypes"), 'schoolID' => $schoolID, "credittypesID !=" => $id));
			if(customCompute($credittypes)) {
				$this->form_validation->set_message("unique_credittypes", "%s already exists");
				return FALSE;
			}
			return TRUE;
		} else {
			$monthly = $this->input->post('monthly');
			if($monthly) {
				for($i = 1; $i<=12; $i++) {
          $month = date('M', mktime(0, 0, 0, $i));
          $array = [
              'credittypes' => $this->input->post('credittypes'). ' ['.$month.']',
							'schoolID'    => $schoolID,
          ];
					$credittypes = $this->credittypes_m->get_order_by_credittypes($array);

					if(customCompute($credittypes)) {
						$this->form_validation->set_message("unique_credittypes", "The ".$this->input->post('credittypes'). ' ['.$month.']' ." already exists");
						return FALSE;
					}
        }
				return TRUE;
			} else {
				$credittypes = $this->credittypes_m->get_order_by_credittypes(array("credittypes" => $this->input->post("credittypes"), 'schoolID' => $schoolID));
				if(customCompute($credittypes)) {
					$this->form_validation->set_message("unique_credittypes", "%s already exists");
					return FALSE;
				}
				return TRUE;
			}
		}
	}

	function getIncomeAccountObj($dataService) {
		$schoolID = $this->session->userdata('schoolID');
		try {
			$accountArray = $dataService->Query("select Name from Account where AccountType='" . INCOME_ACCOUNT_TYPE . "'");
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select Name from Account where AccountType=" . INCOME_ACCOUNT_TYPE, "status" => "OK", 'schoolID' => $schoolID));
		} catch (Exception $e) {
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select Name from Account where AccountType=" . INCOME_ACCOUNT_TYPE, "message" => $e->getMessage(), "status" => "ERROR", 'schoolID' => $schoolID));
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
