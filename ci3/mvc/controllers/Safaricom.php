<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH . '../vendor/autoload.php');
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Payment;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;

class Safaricom extends CI_Controller {
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

		$this->load->model('mainmpesa_m');
		$this->load->model('mpesa_m');
		$this->load->model("schoolyear_m");
		$this->load->model("schoolterm_m");
		$this->load->model('studentrelation_m');
		$this->load->model('student_m');
		$this->load->model('parents_m');
		$this->load->model('payment_m');
    $this->load->model('globalpayment_m');
		$this->load->model('invoice_m');
		$this->load->model('creditmemo_m');
		$this->load->model("quickbookssettings_m");
		$this->load->model("quickbookslog_m");
		$this->load->model("mailandsms_m");
		$this->load->model("payment_gateway_option_m");
		$this->load->model("setting_m");
		$this->load->model('productsale_m');
		$this->load->model("productsalepaid_m");
		$this->load->model("paymenttypes_m");
	}

	public function ipn() {
		$postData = file_get_contents('php://input');
		$result = json_decode($postData, true);
		foreach($result as $data) {
			$resultCode = $data['stkCallback']['ResultCode'];
			if($resultCode == 0) {
				$Item = $data['stkCallback']['CallbackMetadata']['Item'];
				foreach($Item as $data2) {
					if($data2['Name'] == "Amount") {
						$amount = (int)$data2['Value'];
					} elseif($data2['Name'] == "MpesaReceiptNumber") {
						$receipt = $data2['Value'];
					} elseif($data2['Name'] == "TransactionDate") {
						$time = time();
					} elseif($data2['Name'] == "PhoneNumber") {
						$phone = $data2['Value'];
					}
				}

				$mainmpesa = $this->mainmpesa_m->get_single_mainmpesa(array('paidstatus' => '0', 'phonenumber' => $phone, 'amount' => $amount));
				if(customCompute($mainmpesa)) {
					if($mainmpesa->module == "productsale") {
						$productsalepaid = $this->productsalepaid_m->get_single_productsalepaid(array('productsaleID' => $mainmpesa->referencenumber));
						if((float)$amount == (float)$productsalepaid->productsalepaidamount) {
								$array['productsalestatus'] = 3;
						} elseif((float)$productsalepaid->productsalepaidamount > 0 && ((float)$amount > (float)$productsalepaid->productsalepaidamount)) {
								$array['productsalestatus'] = 2;
						} elseif((float)$productsalepaid->productsalepaidamount > 0 && ((float)$amount < (float)$productsalepaid->productsalepaidamount)) {
								$array['productsalestatus'] = 3;
						}
						$this->productsale_m->update_productsale($array, $mainmpesa->referencenumber);
						$this->mainmpesa_m->update_mainmpesa(array('paidstatus' => '1'), $mainmpesa->mainmpesaID);
					}
					else {
						$mpesa = $this->mpesa_m->get_order_by_mpesa(array('mainmpesaID' => $mainmpesa->mainmpesaID));

						$schoolID           = $mainmpesa->schoolID;
						$schoolyearID       = $mainmpesa->schoolyearID;
						$schooltermID       = $mainmpesa->schooltermID;
						$paymentdate        = $mainmpesa->paymentdate;
						$memo               = $mainmpesa->memo;
						$referencenumber    = $mainmpesa->referencenumber;
						$sectionID          = 0;
						$paymentLastID      = 0;
						$student            = array();

						$paymenttype = $this->paymenttypes_m->get_single_paymenttypes(array('paymenttypes' => 'mpesa', 'schoolID' => $schoolID));

						foreach($mpesa as $data) {
							if($data->userID) {
								$student = $this->studentrelation_m->get_single_student2(array('srstudentID' => $data->userID, 'srschoolyearID' => $schoolyearID));
								if(customCompute($student)) {
									$sectionID = $student->srsectionID;
								}
							}

							$classesID          = $student->classesID;
							$paymentyear        = date('Y', strtotime($paymentdate));

							$globalpayment['classesID']         = $classesID;
							$globalpayment['studentID']         = $data->userID;
							$globalpayment['paymentyear']       = $paymentyear;
							$globalpayment['schoolyearID']      = $schoolyearID;
							$globalpayment['sectionID']         = $sectionID;
							$globalpayment['schooltermID']      = $schooltermID;
							$globalpayment['schoolID']          = $schoolID;

							$this->globalpayment_m->insert_globalpayment($globalpayment);
							$globalLastID = $this->db->insert_id();

							if($globalLastID) {
								$payment = array(
									'schoolID' => $schoolID,
									'schoolyearID' => $schoolyearID,
									'schooltermID' => $schooltermID,
									'studentID' => $data->userID,
									'paymentamount' => ($data->amount == '') ? NULL : $data->amount,
									'paymenttypeID' => $paymenttype['paymenttypesID'],
									'paymenttype' => $paymenttype['paymenttypes'],
									'paymentdate' =>  $paymentdate,
									'paymentday' => date('d', strtotime($paymentdate)),
									'paymentmonth' => date('m', strtotime($paymentdate)),
									'paymentyear' => date('Y', strtotime($paymentdate)),
									'userID' => $mainmpesa->userID,
									'usertypeID' => '4',
									//'uname' => $this->session->userdata('name'),
									'transactionID' => $receipt,
									'memo' => $memo,
									//'referencenumber' => $referencenumber,
									'globalpaymentID' => $globalLastID,
								);

								if(customCompute($payment)) {
									$this->payment_m->insert_payment($payment);
									$paymentLastID = $this->db->insert_id();
									//$single_student = $this->student_m->get_single_student(array('studentID' => $data->studentID));
									//if ($config['active'] == "on")
										//$this->createPayment($single_student, $payment, $paymentLastID);
								}
							}
						}

						$this->mainmpesa_m->update_mainmpesa(array('paidstatus' => '1'), $mainmpesa->mainmpesaID);

						if(customCompute($student)) {
							$parent = $this->parents_m->get_parents($student->parentID);
							$allPaymentList = $this->payment_m->get_order_by_payment(['studentID' => $student->studentID]);
							$totalPaymentAmount = $this->generateAllPaymentAmount($allPaymentList);
							$invoices = $this->invoice_m->get_order_by_invoice(['studentID' => $student->studentID, 'deleted_at' => 1]);
							$totalInvoiceAmount = $this->generateAllInvoiceAmount($invoices);
							$creditmemos = $this->creditmemo_m->get_order_by_creditmemo(['studentID' => $student->studentID, 'deleted_at' => 1]);
							$totalCreditmemoAmount = $this->generateAllCreditmemoAmount($creditmemos);
							$balance = $totalInvoiceAmount - ($totalPaymentAmount + $totalCreditmemoAmount);
							$message = "Hi ". $parent->name .", payment of Ksh". $amount ." received for ". $student->srname .". Ref. number ". $paymentLastID .". New balance is Ksh ". $balance;
							$result = $this->allgetway_send_message("smsleopard", $parent->phone, $message, $parent->schoolID);
							$array = array(
								'userID' => $parent->parentsID,
								'usertypeID' => 4,
								'users' => $parent->name,
								'recipient' => $parent->phone,
								'sms_gateway' => 'smsleopard',
								'type' => 'Sms',
								'message' => $message,
								'year' => date('Y'),
								'sendername' => 'system',
								'schoolID' => $parent->schoolID,
							);
							$this->mailandsms_m->insert_mailandsms($array);
						}
					}
				}
			} else {
				log_message('error', $postData);
			}
		}
	}

        public function confirmation() {
                $postData = file_get_contents('php://input');

                // Write into the application's logs directory to avoid permission/path issues
                $logDir = APPPATH . 'logs/';
                if (! is_dir($logDir)) {
                        @mkdir($logDir, 0755, true);
                }

                $filePath = $logDir . 'safaricom_messages.log';

                $record = array(
                        'timestamp' => date('c'),
                        'payload' => $postData,
                );

                $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
                $entry = json_encode($record, $options);
                if ($entry === false) {
                        $record['encoding_error'] = json_last_error_msg();
                        $fallbackOptions = $options;
                        if (defined('JSON_PARTIAL_OUTPUT_ON_ERROR')) {
                                $fallbackOptions |= JSON_PARTIAL_OUTPUT_ON_ERROR;
                        }

                        $entry = json_encode($record, $fallbackOptions);
                }

                if ($entry === false) {
                        $entry = date('c') . ' ' . $postData;
                }

                $entry .= PHP_EOL;

                // Use atomic append with file lock
                @file_put_contents($filePath, $entry, FILE_APPEND | LOCK_EX);
        }

	public function validation() {
		try {
			//Set the response content type to application/json
			header("Content-Type:application/json");
			$resp = '{"ResultCode":0, "ResultDesc":"Validation passed successfully"}';

			//read incoming request
			$postData = file_get_contents('php://input');
			$filePath = "safaricom_messages.log";
			//error log
			$errorLog = "safaricom_errors.log";
			//Parse payload to json
			$jdata = json_decode($postData, true);

			//perform business operations here
			$shortcode = $jdata['BusinessShortCode'];
			$studentID = $jdata['BillRefNumber'];
			$amount = $jdata['TransAmount'] + 0;
			$receipt = $jdata['TransID'];
			$name = $jdata['FirstName'] ." ". $jdata['LastName'];
			$msisdn = $jdata['MSISDN'];
			$paymentLastID = 0;

			$payment_gateway_option = $this->payment_gateway_option_m->get_single_payment_gateway_option_values(array('payment_value' => $shortcode));
			$schoolID = $payment_gateway_option->schoolID;
			$setting = $this->setting_m->get_setting_where('school_year', $schoolID);
			$schoolyearID = $setting->value;

			//if ($studentID) {
				$student = $this->studentrelation_m->get_single_student2(array('srstudentID' => $studentID, 'srschoolyearID' => $schoolyearID));
				//if(customCompute($student)) {
					$sectionID = $student->srsectionID;

					$globalpayment['schoolID']     = $schoolID;
					$globalpayment['classesID']    = $student->srclassesID;
					$globalpayment['studentID']    = $studentID;
					$globalpayment['paymentyear']  = date('Y');
					$globalpayment['schoolyearID'] = $schoolyearID;
					$globalpayment['sectionID']    = $sectionID;

					$this->globalpayment_m->insert_globalpayment($globalpayment);
					$globalLastID = $this->db->insert_id();

					if($globalLastID) {
						$payment = array(
							'schoolID' => $schoolID,
							'schoolyearID' => $schoolyearID,
							'studentID' => $studentID,
							'paymentamount' => ($amount == '') ? NULL : $amount,
							'paymenttype' => 'Mpesa',
							'paymentdate' => date('Y-m-d'),
							'paymentday' => date('d'),
							'paymentmonth' => date('m'),
							'paymentyear' => date('Y'),
							'userID' => $student->parentID,
							'usertypeID' => '4',
							'uname' => $name,
							'transactionID' => $receipt,
							'msisdn' => $msisdn,
							'globalpaymentID' => $globalLastID,
						);

						if(customCompute($payment)) {
							$this->payment_m->insert_payment($payment);
							$paymentLastID = $this->db->insert_id();
						}
					}
				//}
			//}

			if(customCompute($student)) {
				$parent = $this->parents_m->get_parents($student->parentID);
				$allPaymentList = $this->payment_m->get_order_by_payment(['studentID' => $student->studentID]);
				$totalPaymentAmount = $this->generateAllPaymentAmount($allPaymentList);
				$invoices = $this->invoice_m->get_order_by_invoice(['studentID' => $student->studentID, 'deleted_at' => 1]);
				$totalInvoiceAmount = $this->generateAllInvoiceAmount($invoices);
				$creditmemos = $this->creditmemo_m->get_order_by_creditmemo(['studentID' => $student->studentID, 'deleted_at' => 1]);
				$totalCreditmemoAmount = $this->generateAllCreditmemoAmount($creditmemos);
				$balance = $totalInvoiceAmount - ($totalPaymentAmount + $totalCreditmemoAmount);
				$message = "Hi ". $parent->name .", payment of Ksh ". $amount ." received for ". $student->srname .". Ref. number ". $paymentLastID .". New balance is Ksh ". $balance;
				$result = $this->allgetway_send_message("smsleopard", $parent->phone, $message, $student->schoolID);
				$array = array(
					'userID' => $parent->parentsID,
					'usertypeID' => 4,
					'users' => $parent->name,
					'recipient' => $parent->phone,
					'sms_gateway' => 'smsleopard',
					'type' => 'Sms',
					'message' => $message,
					'year' => date('Y'),
					'sendername' => 'system',
					'schoolID' => $schoolID,
				);
				$this->mailandsms_m->insert_mailandsms($array);
			}

			//open text file for logging messages by appending
			$file = fopen($filePath, "a");
			//log incoming request
			fwrite($file, $postData);
			fwrite($file, "\r\n");
		} catch(Exception $ex){
			//append exception to file
			$logErr = fopen($errorLog, "a");
			fwrite($logErr, $ex->getMessage());
			fwrite($logErr, "\r\n");
			fclose($logErr);

			//set failure response
			$resp ='{"ResultCode": 1, "ResultDesc":"Validation failure due to internal service error"}';
		}

		//log response and close file
		fwrite($file, $resp);
		fclose($file);

	}

	function createPayment($student, $payment, $id)
	{
		/*  This sample performs the following functions:
		1.   Add a customer
		2    Create payment using the information above
		*/

		// Create SDK instance
		$config = array();
		$get_quickbooks = $this->quickbookssettings_m->get_order_by_quickbooksettings();
		foreach($get_quickbooks as $key => $value) {
			$config[$value->field_names] = $value->field_values;
		}
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
		if(now() > $config['sessionAccessTokenExpiry'])
			$accessToken = $this->refreshToken();
		else
		  $accessToken = unserialize($config['sessionAccessToken']);
		$dataService->throwExceptionOnError(true);

		/*
		* Update the OAuth2Token of the dataService object
		*/
		$dataService->updateOAuth2Token($accessToken);
		$path = APPPATH . '../mvc/logs/quickbooks/'. date('Y-m-d');
		if(!file_exists($path)) {
			mkdir($path, 0777, true);
		}
		$dataService->setLogLocation($path);

		/*
		* 1. Get CustomerRef
		*/
		$customerRef = $this->getCustomerObj($dataService, $student);

		/*
		* 2. Create Payment using the CustomerRef and ItemRef
		*/
		$paymentObj = Payment::create([
			"TotalAmt" => $payment['paymentamount'],
			"CustomerRef" => [
					"value" => $customerRef->Id
			],
			"PaymentRefNum" => $id,
			"TxnDate" => $payment['paymentdate']
		]);
		$resultingPaymentObj = $dataService->Add($paymentObj);
	}

	/*
	Find if a customer with DisplayName if not, create one and return
	*/
	function getCustomerObj($dataService, $student) {
		$customerName = $student->studentID ."-". $student->name;
		try {
			$customerArray = $dataService->Query("select * from Customer where DisplayName='" . addslashes($customerName) . "'");
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Customer where DisplayName=" . $customerName, "status" => "OK"));
		} catch(Exception $e) {
			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Customer where DisplayName=" . $customerName, "message" => $e->getMessage(), "status" => "ERROR"));
		}

		$error = $dataService->getLastError();
		if($error) {
			logError($error);
			$retArray['error']  = $error;
			$retArray['status'] = FALSE;
			echo json_encode($retArray);
			exit;
		} else {
			if(is_array($customerArray) && sizeof($customerArray) > 0) {
					return current($customerArray);
			}
		}

		// Create Customer
		$customerRequestObj = Customer::create([
			"DisplayName" => $customerName,
			"PrimaryEmailAddr" => [
					"Address" => $student->email
			],
			"DisplayName" => $customerName,
			"PrimaryPhone" => [
					"FreeFormNumber" => $student->phone
			],
		]);
		$customerResponseObj = $dataService->Add($customerRequestObj);
		$error = $dataService->getLastError();
		if($error) {
			logError($error);
			$retArray['error']  = $error;
			$retArray['status'] = FALSE;
			echo json_encode($retArray);
			exit;
		} else {
			return $customerResponseObj;
		}
	}

	function refreshToken()
	{
     /*
     * Retrieve the accessToken value from session variable
     */
		 $config = array();
		 $get_quickbooks = $this->quickbookssettings_m->get_order_by_quickbooksettings();
		 foreach($get_quickbooks as $key => $value) {
			 $config[$value->field_names] = $value->field_values;
		 }
     $accessToken = unserialize($config['sessionAccessToken']);
     $oauth2LoginHelper = new OAuth2LoginHelper($accessToken->getclientID(),$accessToken->getClientSecret());
     $newAccessTokenObj = $oauth2LoginHelper->
                    refreshAccessTokenWithRefreshToken($accessToken->getRefreshToken());
     $newAccessTokenObj->setRealmID($accessToken->getRealmID());
     $newAccessTokenObj->setBaseURL($accessToken->getBaseURL());
		 $sessionAccessTokenExpiry = now()+60*60;

		 $array = array(
			 array(
					'field_names' => 'sessionAccessToken',
					'field_values' => serialize($newAccessTokenObj)
			 ),
			 array(
					'field_names' => 'sessionAccessTokenExpiry',
					'field_values' => $sessionAccessTokenExpiry
			 )
		 );

		 $this->quickbookssettings_m->update_quickbookssettings($array);

		 return $newAccessTokenObj;
	}

	private function generateAllInvoiceAmount($invoices) {
		$total = 0;
    if(customCompute($invoices)) {
        foreach($invoices as $invoice) {
            $total += $invoice->amount;
        }
    }

    return $total;
	}

	private function generateAllCreditmemoAmount($creditmemos) {
		$total = 0;
    if(customCompute($creditmemos)) {
        foreach($creditmemos as $creditmemo) {
            $total += $creditmemo->amount;
        }
    }

    return $total;
  }

	private function generateAllPaymentAmount($payments) {
		$total = 0;
    if(customCompute($payments)) {
        foreach($payments as $payment) {
            $total += $payment->paymentamount;
        }
    }

    return $total;
  }

	private function allgetway_send_message($getway, $to, $message, $schoolID) {
		$result = [];

		$config = array (
                  'schoolID' => $schoolID
              );

		$this->load->library("smsleopard", $config);
		$this->load->library("clickatell");
		$this->load->library("twilio");
		$this->load->library("bulk");
		$this->load->library("msg91");

		if($getway == 'smsleopard') {
			if($to) {
				if($this->smsleopard->send($to, $message) == TRUE)  {
					$result['check'] = TRUE;
					return $result;
				} else {
					$result['check'] = FALSE;
					$result['message'] = "Check your SMSLeopard account";
					return $result;
				}
			}
		} elseif($getway == "clickatell") {
			if($to) {
				$this->clickatell->send_message($to, $message);
				$result['check'] = TRUE;
				return $result;
			}
		} elseif($getway == 'twilio') {
			$get = $this->twilio->get_twilio();
			$from = $get['number'];
			if($to) {
				$response = $this->twilio->sms($from, $to, $message);
				if($response->IsError) {
					$result['check'] = FALSE;
					$result['message'] = $response->ErrorMessage;
					return $result;
				} else {
					$result['check'] = TRUE;
					return $result;
				}

			}
		} elseif($getway == 'bulk') {
			if($to) {
				if($this->bulk->send($to, $message) == TRUE)  {
					$result['check'] = TRUE;
					return $result;
				} else {
					$result['check'] = FALSE;
					$result['message'] = "Check your bulk account";
					return $result;
				}
			}
		} elseif($getway == 'msg91') {
			if($to) {
				if($this->msg91->send($to, $message) == TRUE)  {
					$result['check'] = TRUE;
					return $result;
				} else {
					$result['check'] = FALSE;
					$result['message'] = "Check your msg91 account";
					return $result;
				}
			}
		}
	}
}
