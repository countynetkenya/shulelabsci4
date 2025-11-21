<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH . '../vendor/autoload.php');
use QuickBooksOnline\API\DataService\DataService;

class Quickbookssettings extends Admin_Controller {
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

    $this->load->model('quickbookssettings_m');
    $language = $this->session->userdata('lang');
		$this->lang->load('quickbookssettings', $this->session->userdata('lang'));
  }

  protected function rules() {
		$rules = array(
			array(
				'field' => 'client_id',
				'label' => $this->lang->line("quickbookssettings_client_id"),
				'rules' => 'trim|xss_clean|max_length[255]|required'
			),
			array(
				'field' => 'client_secret',
				'label' => $this->lang->line("quickbookssettings_client_secret"),
				'rules' => 'trim|xss_clean|max_length[255]|required'
			)
		);
		return $rules;
	}

  public function index() {
		$this->data['headerassets'] = [
			 'css' => [
					 'assets/select2/css/select2.css',
					 'assets/select2/css/select2-bootstrap.css'
			 ],
			 'js'  => [
					 'assets/select2/select2.js'
			 ]
		];

		$schoolID = $this->session->userdata('schoolID');
		$array = array('client_id', 'client_secret', 'stage', 'active');
		$config = $this->quickbooksConfig();
		$quickbookssettings = $this->quickbookssettings_m->get_quickbooksetting_values(array('schoolID' => $schoolID));
		$i = 0;
		foreach($quickbookssettings as $quickbookssetting) {
				if(!in_array($quickbookssetting->field_names, $array)) {
					unset($quickbookssettings[$i]);
				}
				$i++;
		}
		$this->data['set_quickbooks'] = $quickbookssettings;
		$this->data['config'] = $config;

		if($config['client_id'] != "" && $config['client_secret'] != "" && ($config['stage'] == "development" || $config['stage'] == "production")) {
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
		}

    if($_POST) {
				$rules = $this->rules();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {
					$this->data["subview"] = "quickbookssettings/index";
					$this->load->view('_layout_main', $this->data);
				} else {
					$clientID = $this->input->post('client_id');
					$secret = $this->input->post('client_secret');
					$stage = $this->input->post('stage');
					$active = $this->input->post('active');

					$array = array(
					   array(
					      'quickbookssettingsID'  => 1,
					      'field_values' => $clientID,
								'schoolID'     => $schoolID
					   ),
					   array(
					      'quickbookssettingsID'  => 2,
					      'field_values' => $secret,
								'schoolID'     => $schoolID
					   ),
						 array(
					      'quickbookssettingsID'  => 5,
					      'field_values' => $stage,
								'schoolID'     => $schoolID
					   ),
						 array(
					      'quickbookssettingsID'  => 6,
					      'field_values' => $active,
								'schoolID'     => $schoolID
					   )
					);

					$this->quickbookssettings_m->update_batch_quickbookssetting_values($array, 'quickbookssettingsID');
					$this->data["subview"] = "quickbookssettings/index";
					$this->load->view('_layout_main', $this->data);
				}
     }
     else {
			  $this->data['headerassets'] = [
					 'css' => [
							 'assets/select2/css/select2.css',
							 'assets/select2/css/select2-bootstrap.css'
					 ],
					 'js'  => [
							 'assets/select2/select2.js'
					 ]
			  ];

        $this->data["subview"] = "quickbookssettings/index";
        $this->load->view('_layout_main', $this->data);
     }
  }
}
