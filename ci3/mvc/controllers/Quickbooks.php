<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH . '../vendor/autoload.php');
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Item;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Facades\CreditMemo;
use QuickBooksOnline\API\Facades\Payment;
use QuickBooksOnline\API\Facades\Account;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Exception\SdkException;

class Quickbooks extends Admin_Controller {
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

    $this->load->model("bundlefeetype_feetypes_m");
    $this->load->model('quickbookssettings_m');
		$this->load->model('invoice_m');
		$this->load->model('creditmemo_m');
		$this->load->model('payment_m');
		$this->load->model('student_m');
		$this->load->model("feetypes_m");
		$this->load->model("credittypes_m");
                $this->load->model("paymenttypes_m");
                $this->load->model("quickbookslog_m");
                $this->load->model("classes_m");
                $this->load->model("section_m");
                $this->load->model("studentrelation_m");
                $this->load->library('quickbooksexportservice');
                $language = $this->session->userdata('lang');
                $this->lang->load('quickbooks', $language);
  }

	protected function rules()
	{
			$rules = [
					[
							'field' => 'classesID',
							'label' => $this->lang->line("class"),
							'rules' => 'trim|required|xss_clean|max_length[11]',
					],
					[
							'field' => 'sectionID',
							'label' => $this->lang->line("section"),
							'rules' => 'trim|xss_clean|max_length[11]',
					],
					[
							'field' => 'studentID',
							'label' => $this->lang->line("student"),
							'rules' => 'trim|xss_clean|max_length[11]',
					],
			];

			return $rules;
	}

	public function index() {
		$this->data['headerassets'] = [
				'css' => [
						'assets/datepicker/datepicker.css',
						'assets/select2/css/select2.css',
						'assets/select2/css/select2-bootstrap.css'
				],
				'js'  => [
					  'assets/datepicker/datepicker.js',
						'assets/select2/select2.js'
				]
		];

		$config = $this->quickbooksConfig();

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
			$this->data["config"] = $config;

			$schoolID                  = $this->session->userdata('schoolID');
			$schoolyearID              = $this->session->userdata('defaultschoolyearID');
			$this->data['classes']     = $this->classes_m->get_order_by_classes(array('schoolID' => $schoolID));
			$this->data['invoices']    = $this->invoice_m->get_invoice_with_student(array('deleted_at' => 1, 'student.studentID !=' => NULL, 'schoolID' => $schoolID));
			$this->data['creditmemos'] = $this->creditmemo_m->get_creditmemo_with_student(array('deleted_at' => 1, 'student.studentID !=' => NULL, 'schoolID' => $schoolID));
			$this->data['payments']    = $this->payment_m->get_payment_with_student(array('schoolID' => $schoolID,  'student.studentID !=' => NULL));
			$this->data['logs']        = $this->quickbookslog_m->get_order_by_quickbookslog(array('schoolID' => $schoolID));

			if($_POST) {
				$rules = $this->rules();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {
						$this->data["subview"]  = "quickbooks/index";
						$this->load->view('_layout_main', $this->data);
				} else {
						/*
						* Retrieve the accessToken value from session variable
						*/
						$accessToken = unserialize($config['sessionAccessToken']);

						$dataService->throwExceptionOnError(true);

						/*
						* Update the OAuth2Token of the dataService object
						*/
						$dataService->updateOAuth2Token($accessToken);

						$result                         = [];
						$classesID                      = $this->input->post('classesID');
						$sectionID                      = $this->input->post('sectionID');
						$studentID                      = $this->input->post('studentID');

						$this->data['set_classesID']    = $classesID;
						$this->data['set_sectionID']    = $sectionID;

						$studentArray = array('srstudentID' => $studentID, 'srschoolID' => $schoolID, 'srschoolyearID' => $schoolyearID);
						$studentsArray = array('srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID);
						if ($classesID > 0) {
							$studentsArray['srclassesID'] = $classesID;
							$this->data['sections'] = $this->section_m->get_order_by_section([ "classesID" => $classesID, "schoolID" => $schoolID]);
						} if ((int)$studentID > 0) {
							$this->data['set_studentID'] = $studentID;
							$students = $this->studentrelation_m->general_get_order_by_student($studentArray);
						} else {
						  $students = $this->studentrelation_m->general_get_order_by_student($studentsArray);
						}
						$this->data['students'] = $students;

						if (customCompute($students)) {
							foreach ($students as $student) {
								// payment
								$paymentArray = array('studentID' => $student->srstudentID, 'schoolID' => $schoolID);
								$payments = $this->payment_m->get_order_by_payment($paymentArray);
								// invoice and creditmemo
								$invoiceAndCreditmemoArray = array('studentID' => $student->srstudentID, 'deleted_at' => 1, 'schoolID' => $schoolID);
								$invoices = $this->invoice_m->get_order_by_invoice($invoiceAndCreditmemoArray);
								$creditmemos = $this->creditmemo_m->get_order_by_creditmemo($invoiceAndCreditmemoArray);

								$allPaymentList = $this->payment_m->get_order_by_payment(['studentID' => $student->srstudentID]);
								$totalPaymentAmount += $this->generateAllPaymentAmount($allPaymentList);
								$invoices = $this->invoice_m->get_order_by_invoice(array('studentID' => $student->srstudentID, 'deleted_at' => 1));
								$totalInvoiceAmount += $this->generateAllInvoiceAmount($invoices);
								$creditmemos = $this->creditmemo_m->get_order_by_creditmemo(array('studentID' => $student->srstudentID, 'deleted_at' => 1));
								$totalCreditmemoAmount += $this->generateAllCreditmemoAmount($creditmemos);
								$balance = $totalInvoiceAmount - ($totalPaymentAmount + $totalCreditmemoAmount);

								/** QuickBooks

								**/
								// payment
								$qbPaymentTotalAmount = $this->QBPayments($dataService, $student);
								// invoice and creditmemo
								$qbInvoiceTotalAmount = $this->QBInvoices($dataService, $student);
								$qbCreditmemoTotalAmount = $this->QBCreditmemos($dataService, $student);
								$qbBalance = $qbInvoiceTotalAmount - ($qbPaymentTotalAmount + $qbCreditmemoTotalAmount);
								$result[$student->srstudentID]['shulelabs'] = $balance;
								$result[$student->srstudentID]['quickbooks'] = $qbBalance;
							}
							$this->data['students'] = $students;
							$this->data['balances'] = $result;
							$this->data['tab'] = "balances";
						}
				}
			}

			$this->data["subview"] = "quickbooks/index";
			$this->load->view('_layout_main', $this->data);
		}
		else {
			redirect("quickbookssettings");
		}
	}

	public function sync() {
		 $schoolID = $this->session->userdata('schoolID');
		 $start = $this->input->post('start');
		 $end = $this->input->post('end');
		 $ids = json_decode($this->input->post('ids'));
		 $type = $this->input->post('type');
		 $invoiceAndCreditmemoArray['schoolID'] = $schoolID;
		 $paymentArray['schoolID'] = $schoolID;
		 $results = [];

		 if (customCompute($ids)) {
			 if($type == "invoice") {
				 foreach ($ids as $id) {
					  $invoice = $this->invoice_m->get_single_invoice(array('invoiceID' => $id, 'schoolID' => $schoolID));
					  $student = $this->student_m->get_student($invoice->studentID);
		 				if (!empty($invoice->bundlefeetypeID)) {
		 						$bundlefeetype_feetypes = $this->bundlefeetype_feetypes_m->get_order_by_bundlefeetype_feetypes(array("bundlefeetypesID" => $invoice->bundlefeetypeID));
		 						foreach ($bundlefeetype_feetypes as $bundlefeetype_feetype) {
		 								$array[] = [
		 									'invoiceID' => $invoice->invoiceID,
		 									'feetypeID' => $bundlefeetype_feetype->feetypesID,
		 									'feetype' => $bundlefeetype_feetype->feetypes,
		 									'amount' => $bundlefeetype_feetype->amount,
		 									'date' => $invoice->date
		 								];
		 								$result = $this->invoiceAndBilling($student, $array);
		 						}
		 				}
		 				else {
		 						$result = $this->invoiceAndBilling($student, $invoice);
		 				}

						$retArray['status'] = $result['status'];
						$retArray['id'] = $invoice->invoiceID;
						if(isset($result['error']))
							$retArray['error'] = $result['error'];
						$results[] = $retArray;
				 }
			 }
			 elseif($type == "creditmemo") {
				 foreach ($ids as $id) {
					  $creditmemo = $this->creditmemo_m->get_single_creditmemo(array('creditmemoID' => $id, 'schoolID' => $schoolID));
					 	$student = $this->student_m->get_student($creditmemo->studentID);
	 					$result = $this->creditmemoAndBilling($student, $creditmemo);

						$retArray['status'] = $result['status'];
						$retArray['id'] = $creditmemo->creditmemoID;
						if(isset($result['error']))
							$retArray['error'] = $result['error'];
						$results[] = $retArray;
				 }
			 }
			 elseif($type == "payment") {
				 foreach ($ids as $id) {
					  $payment = $this->payment_m->get_single_payment(array('paymentID' => $id, 'schoolID' => $schoolID));
					 	$student = $this->student_m->get_student($payment->studentID);
	 					$result = $this->createPayment($student, $payment);

						$retArray['status'] = $result['status'];
						$retArray['id'] = $payment->paymentID;
						if(isset($result['error']))
							$retArray['error'] = $result['error'];
						$results[] = $retArray;
				 }
			 }
		 }

		 echo json_encode($results);
		 exit;
	}

  public function callback() {
    $result = $this->processCode();
  }

  function processCode()  {
		$schoolID = $this->session->userdata('schoolID');
    // Create SDK instance
    $config = $this->quickbooksConfig();

    $dataService = DataService::Configure(array(
        'auth_mode' => 'oauth2',
        'ClientID' => $config['client_id'],
        'ClientSecret' =>  $config['client_secret'],
        'RedirectURI' => base_url() . "quickbooks/callback",
        'scope' => 'com.intuit.quickbooks.accounting openid profile email phone address',
        'baseUrl' => $config['stage']
    ));

    $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
    $parseUrl = $this->parseAuthRedirectUrl($_SERVER['QUERY_STRING']);

    /*
    * Update the OAuth2Token
    */
    $accessToken = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($parseUrl['code'], $parseUrl['realmId']);
    $dataService->updateOAuth2Token($accessToken);

		try {
			$companyinfoArray = $dataService->Query("select * from CompanyInfo");
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from CompanyInfo", "status" => "OK", 'schoolID' => $schoolID));
		} catch (Exception $e) {
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from CompanyInfo", "message" => $e->getMessage(), "status" => "ERROR", 'schoolID' => $schoolID));
		}

		$sessionAccessTokenExpiry = now()+60*60;

		$array = array(
			 array(
					'quickbookssettingsID' => 3,
					'field_values' => serialize($accessToken),
					'schoolID' => $schoolID
			 ),
			 array(
					'quickbookssettingsID' => 4,
					'field_values' => $sessionAccessTokenExpiry,
					'schoolID' => $schoolID
			 ),
			 array(
					'quickbookssettingsID' => 7,
					'field_values' => $parseUrl['realmId'],
					'schoolID' => $schoolID
			 ),
			 array(
					'quickbookssettingsID' => 8,
					'field_values' => $companyinfoArray[0]->CompanyName,
					'schoolID' => $schoolID
			 ),
		);

		$this->quickbookssettings_m->update_batch_quickbookssetting_values($array, 'quickbookssettingsID');
  }

	function QBPayments($dataService, $student) {
		$totalAmount = 0;
		$customerName = $student->srstudentID ."-". $student->srname;

		try {
			$customerArray = $dataService->Query("select * from Customer where DisplayName='" . addslashes($customerName) . "'");
			if (is_array($customerArray) && sizeof($customerArray) > 0) {
					$paymentArray = $dataService->Query("select * from Payment WHERE CustomerRef='" . $customerArray[0]->Id . "'");
					foreach($paymentArray as $payment) {
						$totalAmount += $payment->TotalAmt;
					}
			}
		}
		catch (Exception $e) {}
		catch (SdkException $e) {}

		return $totalAmount;
	}

	function QBInvoices($dataService, $student) {
		$totalAmount = 0;
		$customerName = $student->srstudentID ."-". $student->srname;

		try {
			$customerArray = $dataService->Query("select * from Customer where DisplayName='" . addslashes($customerName) . "'");
			if (is_array($customerArray) && sizeof($customerArray) > 0) {
					$invoiceArray = $dataService->Query("select * from Invoice WHERE CustomerRef='" . $customerArray[0]->Id . "'");
					foreach($invoiceArray as $invoice) {
						$totalAmount += $invoice->TotalAmt;
					}
			}
		}
		catch (Exception $e) {}
		catch (SdkException $e) {}

		return $totalAmount;
	}

	function QBCreditmemos($dataService, $student) {
		$totalAmount = 0;
		$customerName = $student->srstudentID ."-". $student->srname;

		try {
			$customerArray = $dataService->Query("select * from Customer where DisplayName='" . addslashes($customerName) . "'");
			if (is_array($customerArray) && sizeof($customerArray) > 0) {
					$creditmemoArray = $dataService->Query("select * from Creditmemo WHERE CustomerRef='" . $customerArray[0]->Id . "'");
					foreach($creditmemoArray as $creditmemo) {
						$totalAmount += $creditmemo->TotalAmt;
					}
			}
		}
		catch (Exception $e) {}
		catch (SdkException $e) {}

		return $totalAmount;
	}

  function parseAuthRedirectUrl($url) {
    parse_str($url,$qsArray);
    return array(
        'code' => $qsArray['code'],
        'realmId' => $qsArray['realmId']
    );
  }

    public function export_skeleton()
    {
        $input = $this->input->raw_input_stream;
        $data = json_decode($input, true);
        if (!is_array($data)) {
            $data = $this->input->post(NULL, true);
        }
        if (!is_array($data)) {
            $data = [];
        }

        $idempotencyKey = isset($data['idempotency_key']) ? $data['idempotency_key'] : '';
        unset($data['idempotency_key']);

        $schoolID = $this->session->userdata('schoolID');
        $result = $this->quickbooksexportservice->startExport($idempotencyKey, $data, $schoolID);

        $statusCode = 200;
        $status = 'ok';
        $payload = [
            'status' => $status,
            'data' => $result,
        ];

        if (isset($result['error'])) {
            $status = 'error';
            $statusCode = isset($result['state']) && $result['state'] === 'conflict' ? 409 : 400;
            $payload['status'] = $status;
            $payload['error'] = $result['error'];
        }

        return $this->output
            ->set_status_header($statusCode)
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }

        function refreshToken()
        {
     /*
     * Retrieve the accessToken value from session variable
     */
		$schoolID = $this->session->userdata('schoolID');
		$config = $this->quickbooksConfig();

 		$accessToken = unserialize($config['sessionAccessToken']);
    $oauth2LoginHelper = new OAuth2LoginHelper($accessToken->getclientID(),$accessToken->getClientSecret());
    $newAccessTokenObj = $oauth2LoginHelper->
                    refreshAccessTokenWithRefreshToken($accessToken->getRefreshToken());
    $newAccessTokenObj->setRealmID($accessToken->getRealmID());
    $newAccessTokenObj->setBaseURL($accessToken->getBaseURL());
		$sessionAccessTokenExpiry = now()+60*60;

    $dataService = DataService::Configure(array(
        'auth_mode' => 'oauth2',
        'ClientID' => $config['client_id'],
        'ClientSecret' =>  $config['client_secret'],
        'RedirectURI' => base_url() . "quickbooks/callback",
        'scope' => 'com.intuit.quickbooks.accounting openid profile email phone address',
        'baseUrl' => $config['stage']
    ));

		/*
		* Update the OAuth2Token of the dataService object
		*/
		$dataService->updateOAuth2Token($newAccessTokenObj);

		try {
			$companyinfoArray = $dataService->Query("select * from CompanyInfo");
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from CompanyInfo", "status" => "OK", 'schoolID' => $schoolID));
		} catch (Exception $e) {
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from CompanyInfo", "message" => $e->getMessage(), "status" => "ERROR", 'schoolID' => $schoolID));
		}

		$array = array(
			 array(
					'quickbookssettingsID' => 3,
					'field_values' => serialize($newAccessTokenObj),
					'schoolID' => $schoolID
			 ),
			 array(
					'quickbookssettingsID' => 4,
					'field_values' => $sessionAccessTokenExpiry,
					'schoolID' => $schoolID
			 ),
			 array(
					'quickbookssettingsID' => 7,
					'field_values' => $accessToken->getRealmID(),
					'schoolID' => $schoolID
			 ),
			 array(
					'quickbookssettingsID' => 8,
					'field_values' => $companyinfoArray[0]->CompanyName,
					'schoolID' => $schoolID
			 ),
		);

		$this->quickbookssettings_m->update_batch_quickbookssetting_values($array, 'quickbookssettingsID');

		header('Location: ' . $_SERVER['HTTP_REFERER']);
		exit;
	}

	public function launch()
	{

	}

	public function disconnect()
	{

	}

	function invoiceAndBilling($student, $invoice)
	{
		// Create SDK instance
		$config = $this->quickbooksConfig();

		$dataService = DataService::Configure(array(
			'auth_mode' => 'oauth2',
			'ClientID' => $config['client_id'],
			'ClientSecret' =>  $config['client_secret'],
			'RedirectURI' => base_url() . "quickbooks/callback",
			'scope' => 'com.intuit.quickbooks.accounting openid profile email phone address',
			'baseUrl' => $config['stage']
		));

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

		$result = $this->getInvoiceObj($dataService, $student, $invoice);
		return $result;
	}

	function creditmemoAndBilling($student, $creditmemo)
	{
		// Create SDK instance
		$config = $this->quickbooksConfig();

		$dataService = DataService::Configure(array(
			'auth_mode' => 'oauth2',
			'ClientID' => $config['client_id'],
			'ClientSecret' =>  $config['client_secret'],
			'RedirectURI' => base_url() . "quickbooks/callback",
			'scope' => 'com.intuit.quickbooks.accounting openid profile email phone address',
			'baseUrl' => $config['stage']
		));

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

		$result = $this->getCreditmemoObj($dataService, $student, $creditmemo);
		return $result;
	}

	function createPayment($student, $payment)
	{
		// Create SDK instance
		$config = $this->quickbooksConfig();

		$dataService = DataService::Configure(array(
			'auth_mode' => 'oauth2',
			'ClientID' => $config['client_id'],
			'ClientSecret' =>  $config['client_secret'],
			'RedirectURI' => base_url() . "quickbooks/callback",
			'scope' => 'com.intuit.quickbooks.accounting openid profile email phone address',
			'baseUrl' => $config['stage']
		));

		$dataService->throwExceptionOnError(true);

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

		$result = $this->getPaymentObj($dataService, $student, $payment);
		return $result;
	}

	/*
	Generate GUID to associate with the sample account names
	*/
	function getGUID()
	{
		if (function_exists('com_create_guid')) {
			return com_create_guid();
		}else{
			mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
			$charid = strtoupper(md5(uniqid(rand(), true)));
			$hyphen = chr(45);// "-"
			$uuid = // "{"
					$hyphen.substr($charid, 0, 8);
			return $uuid;
		}
	}

	function getInvoiceObj($dataService, $student, $invoice) {
		$result;
		$invoiceID = $invoice->invoiceID;
		try {
			$invoiceArray = $dataService->Query("select * from Invoice WHERE DocNumber='" . $invoiceID . "'");
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Invoice WHERE DocNumber=" . $invoiceID, "status" => "OK", 'schoolID' => $invoice->schoolID));
		} catch (Exception $e) {
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Invoice WHERE DocNumber=" . $invoiceID, "message" => $e->getMessage(), "status" => "ERROR", 'schoolID' => $invoice->schoolID));
		} catch (SdkException $e) {
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Invoice WHERE DocNumber=" . $invoiceID, "message" => $e->getMessage(), "status" => "ERROR", 'schoolID' => $invoice->schoolID));
		}

		$error = $dataService->getLastError();
		if ($error) {
			logError($error);
			$retArray['error']  = $error;
			$retArray['status'] = FALSE;
			$result = $retArray;
		} else {
			if (is_array($invoiceArray) && sizeof($invoiceArray) > 0) {
					$this->updateInvoice($invoiceID);
					$retArray['status'] = TRUE;
					$result = $retArray;
			}
			else {
					$customerRef = $this->getCustomerObj($dataService, $student);
					$itemRef = $this->getInvoiceItemObj($dataService, $invoice);

					if(is_array($customerRef) && array_key_exists('error', $customerRef)) {
						$result = $customerRef;
					}
					elseif(is_array($itemRef) && array_key_exists('error', $itemRef)) {
						$result = $itemRef;
					}
					else {
						try {
							// create invoice
							$invoiceObj = Invoice::create([
								"DocNumber" => $invoiceID,
								"Line" => [
										"Amount" => $invoice->amount,
										"DetailType" => "SalesItemLineDetail",
										"SalesItemLineDetail" => [
												"ItemRef" => [
														"value" => $itemRef->Id
												]
										]
								],
								"CustomerRef"=> [
										"value"=> $customerRef->Id
								],
								/*"BillEmail" => [
										"Address" => $customerRef->PrimaryEmailAddr
								],*/
								"CustomerMemo" => $invoiceID
							]);

							$resultingInvoiceObj = $dataService->Add($invoiceObj);
							$this->updateInvoice($invoiceID);
							$retArray['status'] = TRUE;
							$result = $retArray;
						}
						catch (Exception $e) {}
						catch (SdkException $e) {}

						$error = $dataService->getLastError();
						if ($error) {
							logError($error);
							$retArray['error']  = $error;
							$retArray['status'] = FALSE;
							$result = $retArray;
						}
					}
			}
		}
		return $result;
	}

	function getCreditmemoObj($dataService, $student, $creditmemo) {
		$result;
		$creditmemoID = $creditmemo->creditmemoID;
		try {
			$creditmemoArray = $dataService->Query("select * from CreditMemo WHERE DocNumber='" . $creditmemoID . "'");
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from CreditMemo WHERE DocNumber=" . $creditmemoID, "status" => "OK", 'schoolID' => $creditmemo->schoolID));
		} catch (Exception $e) {
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from CreditMemo WHERE DocNumber=" . $creditmemoID, "message" => $e->getMessage(), "status" => "ERROR", 'schoolID' => $creditmemo->schoolID));
		} catch (SdkException $e) {
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from CreditMemo WHERE DocNumber=" . $creditmemoID, "message" => $e->getMessage(), "status" => "ERROR", 'schoolID' => $creditmemo->schoolID));
		}

		$error = $dataService->getLastError();
		if ($error) {
			logError($error);
			$retArray['error']  = $error;
			$retArray['status'] = FALSE;
			$result = $retArray;
		} else {
			if (is_array($creditmemoArray) && sizeof($creditmemoArray) > 0) {
					$this->updateCreditmemo($creditmemoID);
					$retArray['status'] = TRUE;
					$result = $retArray;
			}
			else {
					$customerRef = $this->getCustomerObj($dataService, $student);
					$itemRef = $this->getCreditmemoItemObj($dataService, $creditmemo);

					if(is_array($customerRef) && array_key_exists('error', $customerRef)) {
						$result = $customerRef;
					}
					elseif(is_array($itemRef) && array_key_exists('error', $itemRef)) {
						$result = $itemRef;
					}
					else {
						try {
							// create creditmemo
							$creditmemoObj = CreditMemo::create([
								"DocNumber" => $creditmemoID,
								"Line" => [
										"Amount" => $creditmemo->amount,
										"DetailType" => "SalesItemLineDetail",
										"SalesItemLineDetail" => [
												"ItemRef" => [
														"value" => $itemRef->Id
												]
										]
								],
								"CustomerRef"=> [
										"value"=> $customerRef->Id
								],
								/*"BillEmail" => [
										"Address" => $customerRef->PrimaryEmailAddr
								],*/
								"CustomerMemo" => $creditmemoID
							]);
							$resultingCreditmemoObj = $dataService->Add($creditmemoObj);
							$this->updateCreditmemo($creditmemoID);
							$retArray['status'] = TRUE;
							$result = $retArray;
						}
						catch (Exception $e) {}
						catch (SdkException $e) {}

						$error = $dataService->getLastError();
						if ($error) {
							logError($error);
							$retArray['error']  = $error;
							$retArray['status'] = FALSE;
							$result = $retArray;
						}
					}
			}
		}

		return $result;
	}

	function getPaymentObj($dataService, $student, $payment) {
		$result;
		$paymentID = $payment->paymentID;

		try {
			$paymentArray = $dataService->Query("select * from Payment WHERE PaymentRefNum='" . $paymentID . "'");
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Payment WHERE PaymentRefNum=" . $paymentID, "status" => "OK", 'schoolID' => $payment->schoolID));
		} catch (Exception $e) {
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Payment WHERE PaymentRefNum=" . $paymentID, "message" => $e->getMessage(), "status" => "ERROR", 'schoolID' => $payment->schoolID));
		} catch (SdkException $e) {
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Payment WHERE PaymentRefNum=" . $paymentID, "message" => $e->getMessage(), "status" => "ERROR", 'schoolID' => $payment->schoolID));
		}

		$error = $dataService->getLastError();
		if ($error) {
			logError($error);
			$retArray['error']  = $error;
			$retArray['status'] = FALSE;
			$result = $retArray;
		} else {
			if (is_array($paymentArray) && sizeof($paymentArray) > 0) {
					$this->updatePayment($paymentID);
					$retArray['status'] = TRUE;
					$result = $retArray;
			}
			else {
				// Fetch DepositAccount Ref
	      $paymenttype = $this->paymenttypes_m->get_paymenttypes($payment->paymenttypeID);
	      if (empty($paymenttype->deposittoaccountrefID)) {
	  			$retArray['error'] = 'Please add a deposit account for '. $paymenttype->paymenttypes;
	  			$retArray['status'] = FALSE;
	  			$result = $retArray;
	  		}
				else {
					$customerRef = $this->getCustomerObj($dataService, $student);

					if(is_array($customerRef) && array_key_exists('error', $customerRef)) {
						$result = $customerRef;
					}
					else {
						try {
							// create payment
							$paymentObj = Payment::create([
								"TotalAmt"=> $payment->paymentamount,
								"CustomerRef"=> [
										"value"=> $customerRef->Id
								],
								"DepositToAccountRef"=> [
				            "value"=> $paymenttype->deposittoaccountrefID
				        ],
								"PaymentRefNum"=> $paymentID,
								"TxnDate"=> $payment->paymentdate
							]);
							$resultingPaymentObj = $dataService->Add($paymentObj);
							$this->updatePayment($paymentID);
							$retArray['status'] = TRUE;
							$result = $retArray;
						}
						catch (Exception $e) {}
						catch (SdkException $e) {}

						$error = $dataService->getLastError();
						if ($error) {
							logError($error);
							$retArray['error']  = $error;
							$retArray['status'] = FALSE;
							$result = $retArray;
						}
					}
				}
			}
		}

		return $result;
	}

	/*
	Find if a customer with DisplayName if not, create one and return
	*/
	function getCustomerObj($dataService, $student) {
		$result;
		$customerName = $student->studentID ."-". $student->name;
		try {
			$customerArray = $dataService->Query("select * from Customer where DisplayName='" . addslashes($customerName) . "'");
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Customer where DisplayName=" . addslashes($customerName), "status" => "OK", 'schoolID' => $student->schoolID));
		} catch (Exception $e) {
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Customer where DisplayName=" . addslashes($customerName), "message" => $e->getMessage(), "status" => "ERROR", 'schoolID' => $student->schoolID));
		} catch (SdkException $e) {
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Customer where DisplayName=" . addslashes($customerName), "message" => $e->getMessage(), "status" => "ERROR", 'schoolID' => $student->schoolID));
		}

		$error = $dataService->getLastError();
		if ($error) {
			logError($error);
			$retArray['error']  = $error;
			$retArray['status'] = FALSE;
			$result = $retArray;
		} else {
			if (is_array($customerArray) && sizeof($customerArray) > 0) {
					$result = current($customerArray);
			}
			else {
				// Create Customer
				$customerRequestObj = Customer::create([
					"FullyQualifiedName" => $student->name,
					"PrimaryEmailAddr" => [
							"Address" => $student->email
					],
					"DisplayName" => $customerName,
					"PrimaryPhone" => [
							"FreeFormNumber" => $student->phone
					]
				]);

				try {
					$customerResponseObj = $dataService->Add($customerRequestObj);
					$result = $customerResponseObj;
				}
				catch (Exception $e) {}
				catch (SdkException $e) {}

				$error = $dataService->getLastError();
				if ($error) {
					logError($error);
					$retArray['error']  = $error;
					$retArray['status'] = FALSE;
					$result = $retArray;
				}
			}
		}
		return $result;
	}

	function getInvoiceItemObj($dataService, $invoice) {
		$result;
		try {
			$itemArray = $dataService->Query("select * from Item WHERE Name='" . $invoice->feetype . "'");
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Item WHERE Name=" . $invoice->feetype, "status" => "OK", 'schoolID' => $invoice->schoolID));
		} catch (Exception $e) {
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Item WHERE Name=" . $invoice->feetype, "message" => $e->getMessage(), "status" => "ERROR", 'schoolID' => $invoice->schoolID));
		} catch (SdkException $e) {
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Item WHERE Name=" . $invoice->feetype, "message" => $e->getMessage(), "status" => "ERROR", 'schoolID' => $invoice->schoolID));
		}

		$error = $dataService->getLastError();
		if ($error) {
			logError($error);
			$retArray['error']  = $error;
			$retArray['status'] = FALSE;
			$result = $retArray;
		} else {
			if (is_array($itemArray) && sizeof($itemArray) > 0) {
					$result = current($itemArray);
			}
			else {
				// Fetch IncomeAccount and ExoenseAccount Refs needed to create an Item
				$feetype = $this->feetypes_m->get_feetypes($invoice->feetypeID);
				if (empty($feetype->incomeaccountID)) {
					$retArray['error'] = 'Please add an income account for '. $feetype->feetypes;
					$retArray['status'] = FALSE;
					$result = $retArray;
				} else {
					// Create Item
					$dateTime = new \DateTime($invoice->date);
					$ItemObj = Item::create([
						"Name" => $invoice->feetype,
						"Active" => true,
						"FullyQualifiedName" => $invoice->feetype,
						"Taxable" => false,
						"Type" => "Service",
						"IncomeAccountRef"=> [
								"value"=>  $feetype->incomeaccountID
						],
						"InvStartDate"=> $dateTime
					]);

					try {
						$resultingItemObj = $dataService->Add($ItemObj);
						$result = $resultingItemObj;
					}
					catch (Exception $e) {}
					catch (SdkException $e) {}

					$error = $dataService->getLastError();
					if ($error) {
						logError($error);
						$retArray['error']  = $error;
						$retArray['status'] = FALSE;
						$result = $retArray;
					}
				}
			}
		}

		return $result;
	}

	function getCreditmemoItemObj($dataService, $creditmemo) {
		$result;
		try {
			$itemArray = $dataService->Query("select * from Item WHERE Name='" . $creditmemo->credittype . "'");
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Item WHERE Name=" . $creditmemo->credittype, "status" => "OK", 'schoolID' => $creditmemo->schoolID));
		} catch (Exception $e) {
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Item WHERE Name=" . $creditmemo->credittype, "message" => $e->getMessage(), "status" => "ERROR", 'schoolID' => $creditmemo->schoolID));
		} catch (SdkException $e) {
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Item WHERE Name=" . $creditmemo->credittype, "message" => $e->getMessage(), "status" => "ERROR", 'schoolID' => $creditmemo->schoolID));
		}

		$error = $dataService->getLastError();
		if ($error) {
			logError($error);
			$retArray['error']  = $error;
			$retArray['status'] = FALSE;
			$result = $retArray;
		} else {
			if (is_array($itemArray) && sizeof($itemArray) > 0) {
					$result = current($itemArray);
			}
			else {
				// Fetch IncomeAccount and ExoenseAccount Refs needed to create an Item
				$credittype = $this->credittypes_m->get_credittypes($creditmemo->credittypeID);
				if (empty($credittype->incomeaccountID)) {
					$retArray['error'] = 'Please add an income account for '. $credittype->credittypes;
					$retArray['status'] = FALSE;
					$result = $retArray;
				} else {
					// Create Item
					$dateTime = new \DateTime($creditmemo->date);
					$ItemObj = Item::create([
						"Name" => $creditmemo->credittype,
						"Active" => true,
						"FullyQualifiedName" => $creditmemo->credittype,
						"Taxable" => false,
						"Type" => "Service",
						"IncomeAccountRef"=> [
								"value"=>  $credittype->incomeaccountID
						],
						"InvStartDate"=> $dateTime
					]);

					try {
						$resultingItemObj = $dataService->Add($ItemObj);
						$result = $resultingItemObj;
					}
					catch (Exception $e) {}
					catch (SdkException $e) {}

					$error = $dataService->getLastError();
					if ($error) {
						logError($error);
						$retArray['error']  = $error;
						$retArray['status'] = FALSE;
						$result = $retArray;
					}
				}
			}
		}

		return $result;
	}

	function updateInvoice($invoiceID) {
		$this->invoice_m->update_invoice(['quickbooks_status' => 1], $invoiceID);
	}

	function updateCreditmemo($creditmemoID) {
		$this->creditmemo_m->update_creditmemo(['quickbooks_status' => 1], $creditmemoID);
	}

	function updatePayment($paymentID) {
		$this->payment_m->update_payment(['quickbooks_status' => 1], $paymentID);
	}

        public function download() {
                        if (!class_exists('ZipArchive')) {
                                show_error('PHP ZIP extension is not available on this server.');
                        }

                        $downloadName = 'quickbooks-logs.zip';
                        $path = APPPATH . '../mvc/logs/quickbooks';
                        $rootPath = realpath($path);

                        if ($rootPath === false) {
                                show_error('QuickBooks logs directory not found.');
                        }

                        $tempFile = tempnam(sys_get_temp_dir(), 'qb-logs-');
                        if ($tempFile === false) {
                                show_error('Unable to prepare QuickBooks logs archive.');
                        }

                        $zipPath = $tempFile;
                        $zip = new ZipArchive();
                        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                                @unlink($zipPath);
                                show_error('Unable to prepare QuickBooks logs archive.');
                        }

                        /** @var SplFileInfo[] $files */
                        $files = new RecursiveIteratorIterator(
                                new RecursiveDirectoryIterator($rootPath, RecursiveDirectoryIterator::SKIP_DOTS),
                                RecursiveIteratorIterator::LEAVES_ONLY
                        );

                        foreach ($files as $file) {
                                if ($file->isDir()) {
                                        continue;
                                }

                                $filePath = $file->getRealPath();
                                if ($filePath === false) {
                                        continue;
                                }

                                $relativePath = substr($filePath, strlen($rootPath) + 1);
                                $zip->addFile($filePath, $relativePath);
                        }

                        $zip->close();

                        clearstatcache(true, $zipPath);
                        $fileSize = filesize($zipPath);
                        if ($fileSize === false) {
                                @unlink($zipPath);
                                show_error('Unable to read QuickBooks logs archive.');
                        }

                        while (ob_get_level() > 0) {
                                ob_end_clean();
                        }

                        header('Content-Type: application/zip');
                        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
                        header('Content-Length: ' . $fileSize);
                        header('Pragma: no-cache');
                        header('Expires: 0');

                        readfile($zipPath);
                        @unlink($zipPath);
                        exit;
                }

	private function generateAllInvoiceAmount($invoices) {
		$total = 0;
    if(customCompute($invoices)) {
        foreach ($invoices as $invoice) {
            $total += $invoice->amount;
        }
    }

    return $total;
	}

	private function generateAllCreditmemoAmount($creditmemos) {
		$total = 0;
    if(customCompute($creditmemos)) {
        foreach ($creditmemos as $creditmemo) {
            $total += $creditmemo->amount;
        }
    }

    return $total;
  }

	private function generateAllPaymentAmount($payments) {
		$total = 0;
    if(customCompute($payments)) {
        foreach ($payments as $payment) {
            $total += $payment->paymentamount;
        }
    }

    return $total;
  }
}
