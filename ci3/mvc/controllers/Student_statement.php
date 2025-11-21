<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH . 'libraries/PaymentGateway/PaymentGateway.php');
require_once(APPPATH . '../vendor/autoload.php');
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Payment;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;

class Student_statement extends Admin_Controller
{
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

    public $payment_gateway;

    function __construct() {
        parent::__construct();
        $this->load->model("student_m");
		    $this->load->model("parents_m");
        $this->load->model("classes_m");
        $this->load->model("section_m");
		    $this->load->model("schoolterm_m");
        $this->load->model('studentgroup_m');
        $this->load->model('feetypes_m');
        $this->load->model('invoice_m');
		    $this->load->model('creditmemo_m');
        $this->load->model('payment_m');
        $this->load->model('globalpayment_m');
        $this->load->model('weaverandfine_m');
        $this->load->model('maininvoice_m');
        $this->load->model('studentrelation_m');
		    $this->load->model('mainmpesa_m');
		    $this->load->model('mpesa_m');
        $this->load->model("quickbookssettings_m");
        $this->load->model("paymenttypes_m");
        $this->load->model("quickbookslog_m");
        $this->load->model('payment_gateway_option_m');
        $this->load->library('studentstatementservice');
        $language = $this->session->userdata('lang');
        $this->lang->load('global_payment', $language);
        $this->payment_gateway       = new PaymentGateway();
    }

    protected function rules()
    {
        $rules = [
            [
                'field' => 'classesID',
                'label' => $this->lang->line("global_classes"),
                'rules' => 'trim|xss_clean|max_length[11]',
            ],
            [
                'field' => 'sectionID',
                'label' => $this->lang->line("global_section"),
                'rules' => 'trim|xss_clean|max_length[11]',
            ],
            [
                'field' => 'studentID',
                'label' => $this->lang->line("global_student"),
                'rules' => 'trim|xss_clean|max_length[11]',
            ],
			      [
                'field' => 'parentID',
                'label' => $this->lang->line("global_parent"),
                'rules' => 'trim|xss_clean|max_length[11]',
            ],
        ];

        return $rules;
    }

    function validate_either(){
    	if($this->input->post('studentID') || $this->input->post('parentID')){
    		return TRUE;
    	} else {
    		$this->form_validation->set_message('validate_either', 'Please enter atleast one of student or parent');
    		return FALSE;
    	}
    }

    public function index() {
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

        $schoolID                       = $this->session->userdata('schoolID');
        $schoolyearID                   = $this->session->userdata('defaultschoolyearID');
        $this->data['classes']          = $this->classes_m->get_order_by_classes(array('schoolID' => $schoolID));
		    $this->data['schoolYears']      = $this->schoolyear_m->get_order_by_schoolyear(array('schoolID' => $schoolID));
        $this->data['sections']         = 0;
        $this->data['globalpayments']   = [];

        $this->data['set_classesID']    = 0;
        $this->data['set_sectionID']    = 0;
        $this->data['set_studentID']    = 0;
                    $this->data['set_parentID']     = 0;
        $this->data['set_groupID']      = 0;
                    $this->data['set_schoolYearID'] = $schoolyearID;
        $this->data['set_schooltermID'] = (int) $this->input->get_post('schooltermID');
        $this->data['set_dateFrom']     = $this->input->get_post('dateFrom');
        $this->data['set_dateTo']       = $this->input->get_post('dateTo');
        $this->data['set_month']        = $this->input->get_post('month');
        $this->data['set_specificDate'] = $this->input->get_post('specificDate');
        $this->data['terms']            = $this->schoolterm_m->get_order_by_schoolterm(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));

        $this->data['studentStatementApiUrl'] = site_url('student_statement/api');
        $this->data['studentStatementCsvUrl'] = site_url('student_statement/export_csv');
        $this->data['studentStatementPdfUrl'] = site_url('student_statement/export_pdf');

		    $usertypeID = $this->session->userdata('usertypeID');
        $this->data['usertypeID'] = $usertypeID;
        if($usertypeID == 3) {
			    $userID = $this->session->userdata('loginuserID');
			    $this->data['students']         = $this->studentrelation_m->general_get_order_by_student(['studentID' => $userID]);
		    } elseif($usertypeID == 4) {
			    $userID = $this->session->userdata('loginuserID');
			    $this->data['students']         = $this->studentrelation_m->general_get_order_by_student(['parentID' => $userID]);
          $this->data['parents']          = $this->parents_m->get_parents_wherein(['parentsID' => $userID]);
		    } elseif($usertypeID == 1) {
			    $this->data['students']         = $this->studentrelation_m->general_get_order_by_student(array('srschoolID' => $schoolID));
          $this->data['parents']          = $this->parents_m->get_order_by_parents(array('schoolID' => $schoolID));
          if($this->input->post('classesID') > 0) {
              $this->data['sections'] = $this->section_m->get_order_by_section(array('classesID' => $this->input->post('classesID'), 'schoolID' => $schoolID));
              if($this->input->post('sectionID') > 0) {
                  $this->data['students'] = $this->studentrelation_m->get_order_by_student(array('srclassesID' => $this->input->post('classesID'), 'srsectionID' => $this->input->post('sectionID'), 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID));
  				        foreach ($this->data['students'] as $each_student) {
  					        $parentIDs[] = $each_student->parentID;
  				        }
  				        $this->data['parents'] = $this->parents_m->get_parents_wherein($parentIDs);
  			      } else {
                $this->data['students'] = $this->studentrelation_m->get_order_by_student(array('srclassesID' => $this->input->post('classesID'), 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID));
  				      foreach($this->data['students'] as $each_student) {
  					      $studentIDs[] = $each_student->studentID;
  				      }
  				      $this->data['parents'] = $this->parents_m->get_parents_wherein($parentIDs);
  			      }
          }
        }

        if($_POST) {
            $rules = $this->rules();
            $this->form_validation->set_rules($rules);
            if($this->form_validation->run() == FALSE) {
                $this->data["subview"]  = "student_statement/index";
                $this->load->view('_layout_main', $this->data);
            } else {
                $classesID                      = $this->input->post('classesID');
                $sectionID                      = $this->input->post('sectionID');
                $studentID                      = $this->input->post('studentID');
				        $parentID                       = $this->input->post('parentID');
				        $schoolyearID                   = $this->input->post('schoolYearID');
                $schooltermID                   = $this->input->post('schooltermID');
				        $dateTo                         = $this->input->post('dateTo');
                $dateFrom                       = $this->input->post('dateFrom');

                $this->data['set_classesID']    = $classesID;
                $this->data['set_sectionID']    = $sectionID;
                $this->data['set_studentID']    = $studentID;
				        $this->data['set_parentID']     = $parentID;
				        $this->data['set_schoolYearID'] = $schoolyearID;
                $this->data['set_schooltermID'] = $schooltermID;
				        $this->data['set_dateFrom']     = $dateFrom;
				        $this->data['set_dateTo']       = $dateTo;

				        $this->data['class'] = $this->classes_m->get_single_classes(array('classesID' => $classesID, 'schoolID' => $schoolID));
                $this->data['feetypes'] = pluck($this->feetypes_m->get_order_by_feetypes(array('schoolID' => $schoolID)), 'feetypes', 'feetypesID');
                $this->data['single_parent'] = $this->parents_m->get_single_parents(array('parentsID' => $parentID, 'schoolID' => $schoolID));
                $this->data['guardians'] = pluck_multi_values($this->parents_m->get_order_by_parents(array('schoolID' => $schoolID)), ['name', 'phone', 'email'], 'parentsID');

                $studentArray = array('srstudentID' => $studentID, 'srschoolID' => $schoolID);
                $parentArray = array('parentID' => $parentID, 'schoolID' => $schoolID);
                $studentsArray['srschoolID'] = $schoolID;
                if($schoolyearID > 0) {
                  $schoolyear = $this->schoolyear_m->get_single_schoolyear(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
                  $this->data['schoolyear'] = $schoolyear;
                  $studentArray['srschoolyearID'] = $schoolyearID;
                  $parentArray['srschoolyearID'] = $schoolyearID;
                  $studentsArray['srschoolyearID'] = $schoolyearID;
                }
                if($classesID > 0)
                  $studentsArray['srclassesID'] = $classesID;
				        if((int)$studentID > 0)
					        $students = $this->studentrelation_m->general_get_order_by_student($studentArray);
				        elseif((int)$parentID > 0)
					        $students = $this->studentrelation_m->general_get_order_by_student($parentArray);
                elseif($usertypeID == 4)
                  $students = $this->student_m->get_order_by_student(array("parentID" => $this->session->userdata('loginuserID')));
				        elseif($usertypeID == 1 || $usertypeID == 5)
					        $students = $this->studentrelation_m->general_get_order_by_student($studentsArray);

                if(customCompute($students)) {
                  $classes = pluck($this->classes_m->get_order_by_classes(array('schoolID' => $schoolID)), 'classes', 'classesID');
				          foreach($students as $student) {
                    $student->classes = $classes[$student->classesID];
  						      // payment
  						      $paymentArray = array('studentID' => $student->studentID, 'schoolID' => $schoolID);
                    $paymentBbfArray['schoolID'] = $schoolID;
                    if($schoolyearID > 0) {
                      $paymentArray['schoolyearID'] = $schoolyearID;
                      $paymentBbfArray['studentID'] = $student->studentID;
                      $paymentBbfArray['schoolyearID <'] = $schoolyearID;
                    }
                    else {
                      $paymentBbfArray['schoolyearID'] = 0;
                    }
  						      if(strtotime($dateFrom)) {
							        $formattedDate = date('Y-m-d', strtotime($dateFrom));
							        $paymentArray['schoolyearID'] = $schoolyearID;
                      $paymentArray['paymentdate >='] = $formattedDate;
							        $paymentBbfArray['studentID'] = $student->studentID;
                      $paymentBbfArray['schoolyearID'] = $schoolyearID;
                      $paymentBbfArray['paymentdate <'] = $formattedDate;
  						      }
  						      if(strtotime($dateTo)) {
							        $formattedDate = date('Y-m-d', strtotime($dateTo));
							        $paymentArray['paymentdate <='] = $formattedDate;
  						      }
  						      $allPaymentList = $this->payment_m->get_order_by_payment($paymentArray);
  						      $bbfPaymentList = $this->payment_m->get_order_by_payment($paymentBbfArray);
  						      // invoice and creditmemo
  						      $invoiceAndCreditmemoArray = array('studentID' => $student->studentID, 'deleted_at' => 1, 'schoolID' => $schoolID);
        						$invoiceAndCreditmemoBbfArray = array('deleted_at' => 1, 'schoolID' => $schoolID);
        						$globalpaymentArray = array('studentID' => $student->studentID, 'schoolID' => $schoolID);

                    if($schoolyearID > 0) {
                      $invoiceAndCreditmemoArray['schoolyearID'] = $schoolyearID;
                      $invoiceAndCreditmemoBbfArray['studentID'] = $student->studentID;
                      $invoiceAndCreditmemoBbfArray['schoolyearID <'] = $schoolyearID;
                      $invoiceAndCreditmemoBbfArray['deleted_at'] = 1;
                    }
                    else {
                      $invoiceAndCreditmemoBbfArray['schoolyearID'] = 0;
                    }
  						      if(strtotime($dateFrom)) {
        							$formattedDate = date('Y-m-d', strtotime($dateFrom));
        							$invoiceAndCreditmemoArray['date >='] = $formattedDate;
        							$invoiceAndCreditmemoBbfArray['studentID'] = $student->studentID;
                      $invoiceAndCreditmemoBbfArray['deleted_at'] = 1;
                      $invoiceAndCreditmemoBbfArray['schoolyearID'] = $schoolyearID;
                      $invoiceAndCreditmemoBbfArray['date <'] = $formattedDate;
  						      }
				            if(strtotime($dateTo)) {
				              $formattedDate = date('Y-m-d', strtotime($dateTo));
				              $invoiceAndCreditmemoArray['date <='] = $formattedDate;
				            }

        						$this->data['single_classes'] = $this->classes_m->get_single_classes(array('classesID' => $student->classesID, 'schoolID' => $schoolID));
        						$this->data['single_section'] = $this->section_m->get_single_section(array('sectionID' => $student->sectionID, 'schoolID' => $schoolID));
        						$this->data['single_group'] = $this->studentgroup_m->get_single_studentgroup(array('studentgroupID' => $student->studentgroupID, 'schoolID' => $schoolID));

        						$this->data['invoices'] = $this->invoice_m->get_order_by_invoice($invoiceAndCreditmemoArray);
        						$this->data['creditmemos'] = $this->creditmemo_m->get_order_by_creditmemo($invoiceAndCreditmemoArray);
        						$bbf_invoices = $this->invoice_m->get_order_by_invoice($invoiceAndCreditmemoBbfArray);
        						$bbf_creditmemos = $this->creditmemo_m->get_order_by_creditmemo($invoiceAndCreditmemoBbfArray);

        						$this->data['invoicefeetype'] = pluck($this->data['invoices'], 'feetypeID', 'invoiceID');

        						$this->data['globalpayments'] = $this->globalpayment_m->get_order_by_globalpayment($globalpaymentArray);

        						$this->data['paidpayments'] = $this->generateAllPaymentAmountWithGlobalID($allPaymentList);

        						$this->data['weaverandfines'] = pluck($this->weaverandfine_m->get_order_by_weaverandfine(array('studentID' => $single_student->studentID, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID)), 'obj', 'paymentID');

        						// balance brought forward
        						$balance = 0;
  						      $statement = array();
  						      if(!empty($invoiceAndCreditmemoBbfArray)) {
        							foreach($bbf_invoices as $invoice) {
                        $feetype = ($invoice->feetype != "") ? $invoice->feetype : (($invoice->bundlefeetype != "") ? $invoice->bundlefeetype : $invoice->productsaleitem);
        								$statement[] = ['fee_type' => $feetype, 'amount' => $invoice->amount, 'date' => $invoice->date, 'column' => 'debit'];
        							}
        							foreach($bbf_creditmemos as $creditmemo) {
        								$statement[] = ['fee_type' => $creditmemo->credittype, 'amount' => $creditmemo->amount, 'date' => $creditmemo->date, 'column' => 'credit'];
        							}
        							foreach($bbfPaymentList as $payment) {
        								$statement[] = ['fee_type' => 'Paid', 'amount' => $payment->paymentamount, 'date' => $payment->paymentdate, 'column' => 'credit'];
        							}

        							usort($statement, function($a, $b) {
      								  return $a['date'] <=> $b['date'];
        							});

        							foreach($statement as $key => $value) {
        								if($statement[$key]['column'] == "debit") {
        									$balance += $statement[$key]['amount'];
        								} else {
        									$balance -= $statement[$key]['amount'];
        								}
        							}
  						      }

        						// statement
        						$statement = [];
        						$statement[] = ['fee_type' => 'Balance brought forward', 'amount' => '', 'date' => '', 'column' => '', 'balance' => $balance];
        						foreach($this->data['invoices'] as $invoice) {
                      $feetype = ($invoice->feetype != "") ? $invoice->feetype : (($invoice->bundlefeetype != "") ? $invoice->bundlefeetype : $invoice->productsaleitem);
        							$statement[] = ['fee_type' => "Invoice #". $invoice->invoiceID ." - ". $feetype, 'amount' => $invoice->amount, 'date' => $invoice->date, 'column' => 'debit'];
        						}
        						foreach($this->data['creditmemos'] as $creditmemo) {
        							$statement[] = ['fee_type' => "Credit Memo #". $creditmemo->creditmemoID ." - ". $creditmemo->credittype, 'amount' => $creditmemo->amount, 'date' => $creditmemo->date, 'column' => 'credit'];
        						}
        						foreach($allPaymentList as $payment) {
                      $description = 'Payment Ref No. '. $payment->globalpaymentID .'; '. $payment->paymenttype .'; '. $payment->transactionID;
        							$statement[] = ['fee_type' => $description, 'amount' => $payment->paymentamount, 'date' => $payment->paymentdate, 'column' => 'credit'];
        						}

        						usort($statement, function($a, $b) {
        							return $a['date'] <=> $b['date'];
        						});

                    $arrDisplay = [];

        						foreach($statement as $key => $value) {
        							if($statement[$key]['column'] == "debit") {
        								$balance += $statement[$key]['amount'];
        							} else {
                        if ((int)$statement[$key]['amount'])
        								  $balance -= $statement[$key]['amount'];
        							}
        							$statement[$key]['balance'] = $balance;
                      $schoolterm = $this->schoolterm_m->get_single_schoolterm(array('startingdate <=' => $statement[$key]['date'], 'endingdate >=' => $statement[$key]['date'], 'schoolID' => $schoolID));
                      $subheading = "";
                      if(customCompute($schoolterm)) {
                        $studentrelation = $this->studentrelation_m->get_single_studentrelation(array('srstudentID' => $student->studentID, 'srschoolyearID' => $schoolterm->schoolyearID, 'srschoolID' => $schoolID));
                        $subheading = $schoolterm->schooltermtitle ." - ". $studentrelation->srclasses;
                      }
                      $arrDisplay[$subheading][] = $statement[$key];
        						}
				            $student->statement = $arrDisplay;
				          }
				          $this->data['statements'] = $students;
				        } else {
                  $this->data['single_classes'] = [];
                  $this->data['single_section'] = [];
                  $this->data['single_group'] = [];
                  $this->data['invoices'] = [];
                  $this->data['globalpayments'] = [];
                }

                $this->data["subview"] = "student_statement/index";
                $this->load->view('_layout_main', $this->data);
            }
        } else {
            $this->data["subview"] = "student_statement/index";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function print_preview()
    {
        $id = htmlentities(escapeString($this->uri->segment(3)));
        if((int)$id) {
          $schoolID = $this->session->userdata('schoolID');
          $student = $this->studentrelation_m->general_get_single_student(array('srstudentID' => $id, 'schoolID' => $schoolID));
          // payment
          $allPaymentList = $this->payment_m->get_order_by_payment(array('studentID' => $student->srstudentID, 'schoolID' => $schoolID));
          $bbfPaymentList = $this->payment_m->get_order_by_payment(array('schoolyearID' => 0, 'schoolID' => $schoolID));
          // invoice and creditmemo
          $this->data['invoices'] = $this->invoice_m->get_order_by_invoice(array('studentID' => $student->srstudentID, 'deleted_at' => 1, 'schoolID' => $schoolID));
          $this->data['creditmemos'] = $this->creditmemo_m->get_order_by_creditmemo(array('studentID' => $student->srstudentID, 'deleted_at' => 1, 'schoolID' => $schoolID));
          $bbf_invoices = $this->invoice_m->get_order_by_invoice(array('schoolyearID' => 0, 'schoolID' => $schoolID));
          $bbf_creditmemos = $this->creditmemo_m->get_order_by_creditmemo(array('schoolyearID' => 0, 'schoolID' => $schoolID));

          // balance brought forward
          $balance = 0;
          $statement = array();

          foreach($bbf_invoices as $invoice) {
            $feetype = ($invoice->feetype!="") ? $invoice->feetype : $invoice->bundlefeetype;
          	$statement[] = ['fee_type' => $feetype, 'amount' => $invoice->amount, 'date' => $invoice->date, 'column' => 'debit'];
          }
          foreach($bbf_creditmemos as $creditmemo) {
          	$statement[] = ['fee_type' => $creditmemo->credittype, 'amount' => $creditmemo->amount, 'date' => $creditmemo->date, 'column' => 'credit'];
          }
          foreach($bbfPaymentList as $payment) {
          	$statement[] = ['fee_type' => 'Paid', 'amount' => $payment->paymentamount, 'date' => $payment->paymentdate, 'column' => 'credit'];
          }

          usort($statement, function($a, $b) {
          	return $a['date'] <=> $b['date'];
          });

          foreach($statement as $key => $value) {
          	if($statement[$key]['column'] == "debit") {
          		$balance += $statement[$key]['amount'];
          	} else {
          		$balance -= $statement[$key]['amount'];
          	}
          }

          // statement
        	$statement = array();
        	$statement[] = ['fee_type' => 'Balance brought forward', 'amount' => '', 'date' => '', 'column' => '', 'balance' => $balance];
        	foreach($this->data['invoices'] as $invoice) {
            $feetype = ($invoice->feetype!="") ? $invoice->feetype : $invoice->bundlefeetype;
        		$statement[] = ['fee_type' => "Invoice #". $invoice->invoiceID ." - ". $feetype, 'amount' => $invoice->amount, 'date' => $invoice->date, 'column' => 'debit'];
        	}
        	foreach($this->data['creditmemos'] as $creditmemo) {
        		$statement[] = ['fee_type' => "Credit Memo #". $creditmemo->creditmemoID ." - ". $creditmemo->credittype, 'amount' => $creditmemo->amount, 'date' => $creditmemo->date, 'column' => 'credit'];
        	}
        	foreach($allPaymentList as $payment) {
            $description = 'Payment Ref No. '. $payment->globalpaymentID .'; '. $payment->paymenttype .'; '. $payment->transactionID;
        		$statement[] = ['fee_type' => $description, 'amount' => $payment->paymentamount, 'date' => $payment->paymentdate, 'column' => 'credit'];
        	}

        	usort($statement, function($a, $b) {
        		return $a['date'] <=> $b['date'];
        	});

        	foreach($statement as $key => $value) {
        		if($statement[$key]['column'] == "debit") {
        			$balance += $statement[$key]['amount'];
        		} else {
            if((int)$statement[$key]['amount'])
        			  $balance -= $statement[$key]['amount'];
        		}
        		$statement[$key]['balance'] = $balance;
        	}

          $gatewayOptions = $this->payment_gateway_option_m->get_single_payment_gateway_option_values(array('payment_option' => 'mpesa_shortcode', 'schoolID' => $schoolID));
          $this->data['paybill'] = $gatewayOptions->payment_value;

          $this->data['student'] = $student;
          $this->data['statement'] = $statement;

          $this->reportPDF('invoicemodule.css', $this->data, 'student_statement/print_preview');
        }
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

	  private function generateAllPaymentAmount2($payments) {
		    $total = 0;
        if(customCompute($payments)) {
            foreach($payments as $payment) {
                $total += $payment->paymentamount;
            }
        }

        return $total;
    }

    private function generateAllPaymentAmount($payments) {
        $returnArray = [];
        if(customCompute($payments)) {
            foreach($payments as $payment) {
                $returnArray[$payment->invoiceID] = isset($returnArray[$payment->invoiceID]) ?  $returnArray[$payment->invoiceID] + $payment->paymentamount :  $payment->paymentamount;
            }
        }

        return $returnArray;
    }

    private function generateAllPaymentAmountWithGlobalID($payments) {
        $returnArray = [];
        $weaverandfine = pluck($this->weaverandfine_m->get_order_by_weaverandfine(array('schoolID' => $this->session->userdata('schoolID'))), 'obj', 'paymentID');

        if(customCompute($payments)) {
            foreach($payments as $payment) {
                $returnArray['paid'][$payment->globalpaymentID] = isset($returnArray['paid'][$payment->globalpaymentID]) ?  $returnArray['paid'][$payment->globalpaymentID] + $payment->paymentamount :  $payment->paymentamount;

                if(isset($returnArray['paid'][$payment->globalpaymentID])) {
                    if(isset($weaverandfine[$payment->paymentID])) {
                        if(isset($returnArray['weaver'][$payment->globalpaymentID])) {
                            $returnArray['weaver'][$payment->globalpaymentID] += $weaverandfine[$payment->paymentID]->weaver;
                        } else {
                            $returnArray['weaver'][$payment->globalpaymentID] = $weaverandfine[$payment->paymentID]->weaver;
                        }

                        if(isset($returnArray['fine'][$payment->globalpaymentID])) {
                            $returnArray['fine'][$payment->globalpaymentID] += $weaverandfine[$payment->paymentID]->fine;
                        } else {
                            $returnArray['fine'][$payment->globalpaymentID] = $weaverandfine[$payment->paymentID]->fine;
                        }
                    }

                    if(!isset($returnArray['paiddate'][$payment->globalpaymentID])) {
                        $returnArray['paiddate'][$payment->globalpaymentID] = $payment->paymentdate;
                    }
                }
            }
        }

        return $returnArray;
    }

    private function generateAllWeaverAmount($weaverandfines) {
        $returnArray = [];
        if(customCompute($weaverandfines)) {
            foreach($weaverandfines as $weaverandfine) {
                $returnArray[$weaverandfine->invoiceID] = isset($returnArray[$weaverandfine->invoiceID]) ?  $returnArray[$weaverandfine->invoiceID] + $weaverandfine->weaver :  $weaverandfine->weaver;
            }
        }
        return $returnArray;
    }

    /*public function unique_classes() {
        if ($this->input->post('classesID') == 0) {
            $this->form_validation->set_message("unique_classes", "The %s field is required");
            return FALSE;
        }
        return TRUE;
    }*/

    public function unique_student() {
        if($this->input->post('studentID') == 0) {
            $this->form_validation->set_message("unique_student", "The %s field is required");
            return false;
        }
        return true;
    }

	  public function unique_parent() {
        if($this->input->post('parentID') == 0) {
            $this->form_validation->set_message("unique_parent", "The %s field is required");
            return false;
        }
        return true;
    }

	  public function unique_studentitems() {
        $schoolID     = $this->session->userdata('schoolID');
        $usertypeID   = $this->session->userdata('usertypeID');
        $studentitems = json_decode($this->input->post('studentitems'));
        $status       = [];

        if(customCompute($studentitems)) {
          foreach ($studentitems as $studentitem) {
            if(!(int)$studentitem->amount) {
              $this->form_validation->set_message("unique_studentitems", "The payment amount is required.");
              return FALSE;
            }
            if(!(int)$studentitem->paymenttypeID) {
              $this->form_validation->set_message("unique_studentitems", "The payment method is required.");
              return FALSE;
            }
            if(empty($studentitem->transactionID) && $usertypeID==1) {
              $this->form_validation->set_message("unique_studentitems", "The payment transaction ID is required.");
              return FALSE;
            }
            else {
              $payment = $this->payment_m->get_order_by_payment(array("transactionID" => $studentitem->transactionID, 'schoolID' => $schoolID));

        			if(customCompute($payment)) {
        				$this->form_validation->set_message("unique_studentitems", "The payment transaction ID already exists.");
        				return FALSE;
        			}
            }
          }
        }
        if(!customCompute($studentitems)) {
            $this->form_validation->set_message("unique_studentitems", "The payment amount, payment method and transaction ID is required.");
            return FALSE;
        }

        if(in_array(FALSE, $status)) {
            $this->form_validation->set_message("unique_studentitems", "The payment amount and payment method is required.");
            return FALSE;
        }
        return TRUE;
    }

    public function sectioncall() {
        $id = $this->input->post('id');
        if((int) $id) {
            $allsection = $this->section_m->get_order_by_section([ "classesID" => $id, 'schoolID' => $this->session->userdata('schoolID')]);
            echo "<option value='0'>", $this->lang->line("global_select_section"), "</option>";
            foreach($allsection as $value) {
                echo "<option value=\"$value->sectionID\">", $value->section, "</option>";
            }
        } else {
            echo "<option value='0'>", $this->lang->line("global_select_section"), "</option>";
        }
    }

    public function studentcall() {
        $classesID = $this->input->post('classesID');
        $sectionID = $this->input->post('sectionID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $schoolID = $this->session->userdata('schoolID');
        $usertypeID = $this->session->userdata('usertypeID');
        $userID = $this->session->userdata('loginuserID');
        $studentArray = [ "srclassesID" => $classesID, 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID ];
        if($usertypeID == 3) {
          $studentArray['srstudentID'] = $userID;
        } elseif($usertypeID == 4) {
          $studentArray['parentID'] = $userID;
        }

        if((int) $classesID && (int) $sectionID) {
            if($sectionID == 0) {
                $allstudent = $this->studentrelation_m->get_order_by_student($studentArray);
            } else {
                $studentArray['srsectionID'] = $sectionID;
                $allstudent = $this->studentrelation_m->get_order_by_student($studentArray);
            }

            echo "<option value='0'>", $this->lang->line("global_select_student"), "</option>";
            foreach($allstudent as $value) {
                echo "<option value=\"$value->srstudentID\">", $value->srname.' - '.$this->lang->line('global_register_no').' - '.$value->srstudentID, "</option>";
            }
        } elseif((int) $classesID) {
            $allstudent = $this->studentrelation_m->get_order_by_student($studentArray);
            echo "<option value='0'>", $this->lang->line("global_select_student"), "</option>";
            foreach($allstudent as $value) {
                echo "<option value=\"$value->srstudentID\">", $value->srname.' - '.$this->lang->line('global_register_no').' - '.$value->srstudentID, "</option>";
            }
        } else {
            echo "<option value='0'>", $this->lang->line("global_select_section"), "</option>";
        }
    }

	  public function parentcall() {
        $classesID = $this->input->post('classesID');
        $sectionID = $this->input->post('sectionID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $schoolID = $this->session->userdata('schoolID');

        if((int) $classesID && (int) $sectionID) {
            if($sectionID == 0) {
                $allstudent = $this->studentrelation_m->get_order_by_student([ "srclassesID" => $classesID, 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID ]);
				        foreach($allstudent as $row) {
					          $allparent[] = $this->parents_m->get_select_parents(NULL, array("parentsID" => $row->parentID));
				        }
            } else {
                $allstudent = $this->studentrelation_m->get_order_by_student([ "srclassesID" => $classesID, 'srsectionID' => $sectionID, 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID ]);
        				foreach($allstudent as $row) {
        					  $allparent[] = $this->parents_m->get_select_parents(NULL, array("parentsID" => $row->parentID));
        				}
      			}

            echo "<option value='0'>", $this->lang->line("global_select_parent"), "</option>";
            foreach($allparent as $value) {
                echo "<option value=\"$value->parentsID\">", $value->name, "</option>";
            }
        } elseif((int) $classesID) {
            $allstudent = $this->studentrelation_m->get_order_by_student([ "srclassesID" => $classesID, 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID ]);
			      $allparent = array();
			      $add = true;
      			foreach($allstudent as $row) {
      				foreach($this->parents_m->get_select_parents(NULL, array("parentsID" => $row->parentID)) as $parent) {
      					foreach($allparent as $item) {
      						if($item->parentsID == $parent->parentsID)
      							$add = false;
      					}
      					if($add)
      						$allparent[] = $parent;
      				}
      			}

            echo "<option value='0'>", $this->lang->line("global_select_parent"), "</option>";
            foreach($allparent as $value) {
                echo "<option value=\"$value->parentsID\">", $value->name, "</option>";
            }
        } else {
            echo "<option value='0'>", $this->lang->line("global_select_section"), "</option>";
        }
    }

    public function termcall() {
        $id = $this->input->post('schoolYearID');
        if((int) $id) {
            $terms = $this->schoolterm_m->get_order_by_schoolterm(array('schoolyearID' => $id, 'schoolID' => $this->session->userdata('schoolID')));
            echo "<option value='0'>", $this->lang->line("global_select_schoolterm"), "</option>";
            foreach($terms as $value) {
                echo "<option value=\"$value->schooltermID\">", $value->schooltermtitle, "</option>";
            }
        } else {
            echo "<option value='0'>", $this->lang->line("global_select_schoolterm"), "</option>";
        }
    }

    public function datescall() {
        $id = $this->input->post('schooltermID');
        if((int) $id) {
            $term = $this->schoolterm_m->get_single_schoolterm(array('schooltermID' => $id, 'schoolID' => $this->session->userdata('schoolID')));
            echo json_encode($term);
        }
    }

    protected function paymentRules() {
        $rules = array(
            /*array(
                'field' => 'studentID',
                'label' => $this->lang->line("global_student"),
                'rules' => 'trim|required|xss_clean|numeric|max_length[11]'
            ),
            array(
                'field' => 'classesID',
                'label' => $this->lang->line("global_classes"),
                'rules' => 'trim|required|xss_clean|numeric|max_length[11]'
            ),
            array(
                'field' => 'invoicename',
                'label' => $this->lang->line("global_invoice_name"),
                'rules' => 'trim|required|xss_clean|max_length[127]'
            ),
            array(
                'field' => 'invoicedescription',
                'label' => $this->lang->line("global_description"),
                'rules' => 'trim|xss_clean|max_length[127]'
            ),
            array(
                'field' => 'invoicenumber',
                'label' => $this->lang->line("global_invoice_number"),
                'rules' => 'trim|required|xss_clean|min_length[6]'
            ),
			      array(
                'field' => 'schooltermID',
                'label' => $this->lang->line("payment_schooltermID"),
                'rules' => 'trim|required|xss_clean|numeric'
            ),*/
            array(
                'field' => 'date',
                'label' => $this->lang->line("global_payment_date"),
                'rules' => 'trim|xss_clean|callback_date_valid'
            ),
            /*array(
                'field' => 'payment_status',
                'label' => $this->lang->line("global_payment_status"),
                'rules' => 'trim|required|xss_clean|max_length[7]'
            ),*/
			      array(
                'field' => 'phonenumber',
                'label' => $this->lang->line("global_phone_number"),
                'rules' => 'trim|xss_clean|numeric|regex_match[/^254/]|exact_length[12]|callback_phonenumber_valid'
            ),
			      array(
                'field' => 'studentitems',
                'label' => $this->lang->line("payment_studentitem"),
                'rules' => 'trim|xss_clean|required|callback_unique_studentitems'
            ),
            /*array(
                'field' => 'paid',
                'label' => $this->lang->line("global_paid"),
                'rules' => 'trim|xss_clean|max_length[10]|callback_unique_paidweaverfine'
            )*/
        );
        return $rules;
    }

	  public function date_valid( $date ) {
    		$usertypeID = $this->session->userdata('usertypeID');
    		if($usertypeID == 1) {
    			if(strlen($date) < 10) {
    				$this->form_validation->set_message("date_valid", "%s is not valid dd-mm-yyyy");
    				return FALSE;
    			} else {
    				$arr  = explode("-", $date);
    				$dd   = $arr[0];
    				$mm   = $arr[1];
    				$yyyy = $arr[2];
    				if(checkdate($mm, $dd, $yyyy)) {
    					return TRUE;
    				} else {
    					$this->form_validation->set_message("date_valid", "%s is not valid dd-mm-yyyy");
    					return FALSE;
    				}
    			}
    		} else {
    			return TRUE;
    		}
    }

	public function phonenumber_valid( $phonenumber ) {
  		$usertypeID = $this->session->userdata('usertypeID');
      if($usertypeID == 4 && $phonenumber == '') {
          $this->form_validation->set_message("phonenumber_valid", "Please enter a phone number");
          return FALSE;
      } else {
          return TRUE;
      }
  }

  public function unique_paidweaverfine() {
      $paids = $this->input->post('paid');
      $weavers = $this->input->post('weaver');
      $fines = $this->input->post('fine');

      $max_value = 10;
      $paidRequiredStatus = FALSE;
      $weaverRequiredStatus = FALSE;
      $fineRequiredStatus = FALSE;

      if(customCompute($paids)) {
          foreach($paids as $paid) {
              if($paid['value'] != '') {
                  if((float) $paid['value']) {
                      if(strlen($paid['value']) <= $max_value && strlen($paid['value']) >= 0) {
                          $paidRequiredStatus = TRUE;
                      } else {
                          $this->form_validation->set_message("unique_paidweaverfine", "%s cannot exceed ".$max_value." characters in length.");
                          return FALSE;
                      }
                  } else {
                      $this->form_validation->set_message("unique_paidweaverfine", "%s must contain only numbers.");
                      return FALSE;
                  }
              }
          }
      }

      if(customCompute($weavers)) {
          foreach($weavers as $weaver) {
              if($weaver['value'] != '') {
                  if((float) $weaver['value']) {
                      if(strlen($weaver['value']) <= $max_value && strlen($weaver['value']) >= 0) {
                          $weaverRequiredStatus = TRUE;
                      } else {
                          $this->form_validation->set_message("unique_paidweaverfine", "%s cannot exceed ".$max_value." characters in length.");
                          return FALSE;
                      }
                  } else {
                      $this->form_validation->set_message("unique_paidweaverfine", "%s must contain only numbers.");
                      return FALSE;
                  }
              }
          }
      }

      if(customCompute($fines)) {
          foreach($fines as $fine) {
              if($fine['value'] != '') {
                  if((float) $fine['value']) {
                      if(strlen($fine['value']) <= $max_value && strlen($fine['value']) >= 0) {
                          $fineRequiredStatus = TRUE;
                      } else {
                          $this->form_validation->set_message("unique_paidweaverfine", "%s cannot exceed ".$max_value." characters in length.");
                          return FALSE;
                      }
                  } else {
                      $this->form_validation->set_message("unique_paidweaverfine", "%s must contain only numbers.");
                      return FALSE;
                  }
              }
          }
      }

      if($paidRequiredStatus || $weaverRequiredStatus || $fineRequiredStatus) {
          if($this->session->flashdata('paymentGenerateStatus')) {
              return FALSE;
              $this->form_validation->set_message("unique_paidweaverfine", "%s is required.");
          }

          return TRUE;
      } else {
          $this->form_validation->set_message("unique_paidweaverfine", "%s is required.");
          return FALSE;
      }
  }

	public function paymentSend() {
        $retArray['status'] = FALSE;
        $retArray['message'] = '';

        if($_POST) {
            $rules = $this->paymentRules();
            $this->form_validation->set_rules($rules);
			      $usertypeID = $this->session->userdata("usertypeID");

            if($this->form_validation->run() == FALSE) {
                $retArray['error'] = $this->form_validation->error_array();
                $retArray['status'] = FALSE;
                echo json_encode($retArray);
                exit;
            } elseif(permissionChecker('payment_add') && $usertypeID != 4) {
                $schoolID     = $this->session->userdata('schoolID');
                $schoolyearID = $this->session->userdata('defaultschoolyearID');
				        $studentitems = json_decode($this->input->post('studentitems'));
				        $paymentdate  = date("Y-m-d", strtotime($this->input->post("date")));
				        $schooltermID = $this->input->post('schooltermID');
				        $memo         = $this->input->post('memo');
				        $sectionID    = 0;
                $paymenttype  = pluck($this->paymenttypes_m->get_order_by_paymenttypes(array('schoolID' => $schoolID)), 'paymenttypes', 'paymenttypesID');

                $config = $this->quickbooksConfig();

                foreach($studentitems as $studentitem) {
			             if($studentitem->studentID) {
		                   $student = $this->studentrelation_m->get_single_student(array('srstudentID' => $studentitem->studentID, 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID));
		                   if(customCompute($student)) {
			                       $sectionID = $student->srsectionID;
		                   }
			             }

			             $classesID          = $student->classesID;
			             $paymentyear        = date('Y', strtotime($paymentdate));

			             $globalpayment['classesID']         = $classesID;
			             $globalpayment['studentID']         = $studentitem->studentID;
			             $globalpayment['paymentyear']       = $paymentyear;
			             $globalpayment['schoolyearID']      = $schoolyearID;
			             $globalpayment['sectionID']         = $sectionID;
			             $globalpayment['schooltermID']      = $schooltermID;
                   $globalpayment['schoolID']          = $schoolID;

			             $this->globalpayment_m->insert_globalpayment($globalpayment);
			             $globalLastID = $this->db->insert_id();

			             if($globalLastID) {
		                   if($studentitem->transactionID != '')
			                     $transactionID = $studentitem->transactionID;
		                   else
			                     $transactionID = 'CASHANDCHEQUE'.random19();

		                   $payment = array(
                          'schoolID' => $schoolID,
            							'schoolyearID' => $schoolyearID,
                          'schooltermID' => $schooltermID,
            							'studentID' => $studentitem->studentID,
            							'paymentamount' => ($studentitem->amount == '') ? NULL : $studentitem->amount,
                          'paymenttypeID' => ($studentitem->paymenttypeID == '') ? NULL : $studentitem->paymenttypeID,
                          'paymenttype' => isset($paymenttype[$studentitem->paymenttypeID]) ? $paymenttype[$studentitem->paymenttypeID] : NULL,
            							'paymentdate' =>  $paymentdate,
            							'paymentday' => date('d', strtotime($paymentdate)),
            							'paymentmonth' => date('m', strtotime($paymentdate)),
            							'paymentyear' => date('Y', strtotime($paymentdate)),
            							'userID' => $this->session->userdata('loginuserID'),
            							'usertypeID' => $this->session->userdata('usertypeID'),
            							'uname' => $this->session->userdata('name'),
            							'transactionID' => $transactionID,
            							'memo' => $memo,
            							'globalpaymentID' => $globalLastID,
		                   );

            					 if(customCompute($payment)) {
            							$this->payment_m->insert_payment($payment);
            							$paymentLastID = $this->db->insert_id();
                          $single_student = $this->student_m->get_single_student(array('studentID' => $studentitem->studentID, 'schoolID' => $schoolID));
                          if ($config['active'] == "1" && now() < $config['sessionAccessTokenExpiry'])
                              $this->createPayment($config, $single_student, $payment, $paymentLastID);
                          if(permissionChecker('paymenthistory_view'))
            							   $retArray['id'] = $paymentLastID;
            							$retArray['status'] = TRUE;
            					 }
			              }
				        }

                $this->session->set_flashdata('paymentGenerateStatus', TRUE);
                $this->session->set_flashdata('paymentGenerateGlobalLastID', $globalLastID);
                $this->session->set_flashdata('paymentGenerateLastStudentID', $studentID);
                $this->session->set_flashdata('success', $this->lang->line('menu_success'));

                echo json_encode($retArray);
                exit;
            } elseif($usertypeID == 4) {
        				$parentID           = $this->session->userdata('loginuserID');
        				$studentitems       = json_decode($this->input->post('studentitems'));
        				$schoolyearID       = $this->session->userdata('defaultschoolyearID');
                $schoolID           = $this->session->userdata('schoolID');
        				$schooltermID       = $this->input->post('schooltermID');
        				$paymentdate        = date("Y-m-d");
        				$memo               = $this->input->post('memo');
        				$phonenumber        = $this->input->post('phonenumber');
        				$totalamount        = 0;

        				foreach($studentitems as $studentitem) {
        					$totalamount += $studentitem->amount;
        				}

                $mainmpesa['usertypeID']      = 4;
        				$mainmpesa['userID']          = $parentID;
                $mainmpesa['schoolID']        = $schoolID;
        				$mainmpesa['schoolyearID']    = $schoolyearID;
        				$mainmpesa['schooltermID']    = $schooltermID;
        				$mainmpesa['amount']          = $totalamount;
        				$mainmpesa['paymentdate']     = $paymentdate;
        				$mainmpesa['phonenumber']     = $phonenumber;
        				$mainmpesa['memo']            = $memo;

        				$this->mainmpesa_m->insert_mainmpesa($mainmpesa);
        				$mainmpesaLastID = $this->db->insert_id();

        				if($mainmpesaLastID) {
        					foreach($studentitems as $studentitem) {
        						$mpesa = array(
                      'usertypeID' => 5,
        							'userID' => $studentitem->studentID,
        							'amount' => ($studentitem->amount == '') ? NULL : $studentitem->amount,
        							'mainmpesaID' => $mainmpesaLastID,
        						);

        						if(customCompute($mpesa)) {
        							$this->mpesa_m->insert_mpesa($mpesa);
        							$mpesaLastID = $this->db->insert_id();
        						}
        					}
        				}

                $array = array('phonenumber' => $phonenumber, 'amount' => $totalamount);

        				$response = $this->payment_gateway->gateway("mpesa")->payment($array, null);

                // Handle the response
                if($response['ResponseCode'] === '0') {
                    // Payment request was successful
                    $checkoutRequestID = $response['CheckoutRequestID'];
                    $responseDescription = $response['ResponseDescription'];
                    // You can now redirect the customer to the payment page or display a success message.
                    $retArray['status'] = TRUE;
                    $this->session->set_flashdata('success', $responseDescription);
                } else {
                    // Payment request failed
                    $errorMessage = $response['errorMessage'];
                    $errorCode = $response['errorCode'];
                    // Handle the error as needed.
                    $retArray['error'][0] = $errorMessage;
                    $retArray['status'] = FALSE;
                }

        				echo json_encode($retArray);
                exit;
			      }
        } else {
            $retArray['message'] = 'Something wrong';
            echo json_encode($retArray);
            exit;
        }
    }

    function createPayment($config, $student, $payment, $id)
    {
      /*  This sample performs the following functions:
      1.   Add a customer
      2    Create payment using the information above
      */

      // Create SDK instance
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

      // Fetch DepositAccount Ref
      $paymenttype = $this->paymenttypes_m->get_paymenttypes($payment['paymenttypeID']);
      if(empty($paymenttype->deposittoaccountrefID)) {
  			$retArray['error'] = ['paymenttype' => 'Please add a deposit account for '. $paymenttype->paymenttypes];
  			$retArray['status'] = FALSE;
  			echo json_encode($retArray);
  			exit;
  		}

      $paymentObj = Payment::create([
        "TotalAmt"=> $payment['paymentamount'],
        "CustomerRef"=> [
            "value"=> $customerRef->Id
        ],
        "DepositToAccountRef"=> [
            "value"=> $paymenttype->deposittoaccountrefID
        ],
        "PaymentRefNum"=> $id,
        "TxnDate"=> $payment['paymentdate']
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
                        $this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Customer where DisplayName=" . $customerName, "status" => "OK", 'schoolID' => $student->schoolID));
                } catch (Exception $e) {
                        $this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Customer where DisplayName=" . $customerName, "message" => $e->getMessage(), "status" => "ERROR", 'schoolID' => $student->schoolID));
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
        ]
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

    public function api()
    {
        $filters = $this->gatherStatementFilters();
        $payload = $this->studentstatementservice->build($filters);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'ok',
                'data' => $payload,
            ]));
    }

    public function export_csv()
    {
        $filters = $this->gatherStatementFilters();
        $payload = $this->studentstatementservice->build($filters);

        $filename = 'student_statement_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store, no-cache');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Student ID', 'Student Name', 'Class', 'Section', 'Group', 'Date', 'Description', 'Debit', 'Credit', 'Balance', 'Term', 'Month', 'Day', 'Type']);

        foreach ($payload['students'] as $student) {
            foreach ($student['rows'] as $row) {
                fputcsv($output, [
                    $student['student']['studentID'],
                    $student['student']['student_name'],
                    $student['student']['class'],
                    $student['student']['section'],
                    $student['student']['group'],
                    $row['day'] ?? '',
                    $row['description'],
                    $row['debit'],
                    $row['credit'],
                    $row['balance'],
                    $row['term'],
                    $row['month'],
                    $row['day'],
                    $row['type'],
                ]);
            }
        }

        fflush($output);
        fclose($output);
        exit;
    }

    public function export_pdf()
    {
        $filters = $this->gatherStatementFilters();
        $payload = $this->studentstatementservice->build($filters);

        $this->data['statementPayload'] = $payload;
        $this->data['filters'] = $payload['filters'];

        $this->reportPDF('invoicemodule.css', $this->data, 'student_statement/export_pdf');
    }

    protected function gatherStatementFilters(): array
    {
        $filters = [
            'classesID' => (int) $this->input->get_post('classesID'),
            'sectionID' => (int) $this->input->get_post('sectionID'),
            'studentID' => (int) $this->input->get_post('studentID'),
            'parentID' => (int) $this->input->get_post('parentID'),
            'schoolyearID' => (int) $this->input->get_post('schoolYearID'),
            'schooltermID' => (int) $this->input->get_post('schooltermID'),
            'dateFrom' => $this->input->get_post('dateFrom'),
            'dateTo' => $this->input->get_post('dateTo'),
            'month' => $this->input->get_post('month'),
            'specificDate' => $this->input->get_post('specificDate'),
            'schoolID' => (int) $this->session->userdata('schoolID'),
            'usertypeID' => (int) $this->session->userdata('usertypeID'),
            'loginuserID' => (int) $this->session->userdata('loginuserID'),
        ];

        if ($filters['schoolyearID'] === 0) {
            $filters['schoolyearID'] = (int) $this->session->userdata('defaultschoolyearID');
        }

        if (empty($filters['dateFrom'])) {
            $filters['dateFrom'] = $this->input->get_post('set_dateFrom');
        }
        if (empty($filters['dateTo'])) {
            $filters['dateTo'] = $this->input->get_post('set_dateTo');
        }

        return $filters;
    }
}
