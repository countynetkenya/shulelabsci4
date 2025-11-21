<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Paymenthistory extends Admin_Controller {
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
		$this->load->model('feetypes_m');
		$this->load->model('invoice_m');
		$this->load->model('creditmemo_m');
		$this->load->model('payment_m');
		$this->load->model('student_m');
		$this->load->model('parents_m');
		$this->load->model('maininvoice_m');
		$this->load->model('weaverandfine_m');
		$this->load->model('studentrelation_m');
		$this->load->model('studentrelation_m');
		$this->load->model('globalpayment_m');
		$this->load->model("paymenttypes_m");
		$language = $this->session->userdata('lang');
		$this->lang->load('paymenthistory', $language);
	}

	protected function payment_rules() {
		$usertypeID = $this->session->userdata('usertypeID');
		$rules = array(
			array(
				'field' => 'amount',
				'label' => $this->lang->line("paymenthistory_amount"),
				'rules' => 'trim|required|xss_clean|max_length[11]|numeric|callback_valid_number'
			),
			array(
				'field' => 'transactionID',
				'label' => $this->lang->line("paymenthistory_transaction"),
				'rules' => 'trim|required|xss_clean'
			),
			array(
				'field' => 'paymentmethod',
				'label' => $this->lang->line("paymenthistory_paymentmethod"),
				'rules' => 'trim|required|xss_clean|max_length[11]|numeric|callback_unique_paymentmethod'
			),
			array(
				'field' => 'studentID',
				'label' => $this->lang->line("paymenthistory_student"),
				'rules' => 'trim|required|xss_clean|max_length[11]|numeric|callback_unique_studentID'
			),
		);

		return $rules;
	}

	protected function mpesa_rules() {
		$usertypeID = $this->session->userdata('usertypeID');
		$rules = array(
			array(
				'field' => 'paymentmethod',
				'label' => $this->lang->line("paymenthistory_paymentmethod"),
				'rules' => 'trim|required|xss_clean|max_length[11]|numeric|callback_unique_paymentmethod'
			),
			array(
				'field' => 'studentID',
				'label' => $this->lang->line("paymenthistory_student"),
				'rules' => 'trim|required|xss_clean|max_length[11]|numeric|callback_unique_studentID'
			),
		);

		return $rules;
	}

	public function index() {
		$usertypeID = $this->session->userdata('usertypeID');
		$userID = $this->session->userdata('loginuserID');
		$schoolyearID = $this->session->userdata('defaultschoolyearID');
		if($usertypeID == 3) {
			$this->data['payments'] = $this->payment_m->get_payment_with_studentrelation_by_studentID($userID, $schoolyearID);
			$this->data["subview"] = "paymenthistory/index_parents";
			$this->load->view('_layout_main', $this->data);
		} elseif($usertypeID == 4) {
			$this->data['headerassets'] = array(
				'css' => array(
					'assets/select2/css/select2.css',
					'assets/select2/css/select2-bootstrap.css'
				),
				'js' => array(
					'assets/select2/select2.js'
				)
			);

			$students = $this->studentrelation_m->get_order_by_student(array('parentID' => $userID, 'schoolyearID' => $schoolyearID));
			if(customCompute($students)) {
				$studentArray = pluck($students, 'srstudentID');
				$this->data['payments'] = [];
				$this->data['payments'] = $this->payment_m->get_payment_with_studentrelation_by_studentID($studentArray, $schoolyearID);
				$this->data["subview"] = "paymenthistory/index";
				$this->load->view('_layout_main', $this->data);
			} else {
				$this->data['payments'] = [];
				$this->data["subview"] = "paymenthistory/index";
				$this->load->view('_layout_main', $this->data);
			}
		} else {
			$this->data['payments'] = $this->payment_m->get_payment_with_studentrelation(array('schoolyearID' => $schoolyearID, 'schoolID' => $this->session->userdata('schoolID')));
			$this->data["subview"] = "paymenthistory/index";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function edit() {
		if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('usertypeID') == 5)) {
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
			$schoolID = $this->session->userdata('schoolID');
			$schoolyearID = $this->session->userdata('defaultschoolyearID');
			if((int)$id) {
				$this->data['students']  = $this->studentrelation_m->get_order_by_student([
						'srschoolyearID' => $schoolyearID,
						'srschoolID'     => $schoolID,
				]);
				$this->data['paymentmethods'] = $this->paymenttypes_m->get_order_by_paymenttypes(array('schoolID' => $schoolID));
				$this->data['payment'] = $this->payment_m->get_single_payment(array('paymentID' => $id, 'paymentamount !=' => NULL, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
				if(customCompute($this->data['payment'])) {
					if(($this->data['payment']->paymenttype != "Paypal") && ($this->data['payment']->paymenttype != 'Stripe') && ($this->data['payment']->paymenttype != 'Payumoney') && ($this->data['payment']->paymenttype != 'Voguepay')) {
						$this->data['invoice'] = $this->invoice_m->get_invoice($this->data['payment']->invoiceID);
						if(customCompute($this->data['invoice'])) {
							if($_POST) {
								$paymentArray = array();
								$studentID = $this->input->post('studentID');
								$paymenttype = pluck($this->paymenttypes_m->get_order_by_paymenttypes(array('schoolID' => $schoolID)), 'paymenttypes', 'paymenttypesID');
								if(!$this->input->post('amount') && !$this->input->post('transactionID')) {
									$rules = $this->mpesa_rules();
									$paymentArray = array(
										'studentID' => $studentID,
										'paymenttypeID' => $this->input->post('paymentmethod'),
										'paymenttype' => $paymenttype[$this->input->post('paymentmethod')],
									);
								}
								else {
									$rules = $this->payment_rules();
									$paymentArray = array(
										'studentID' => $studentID,
										'paymentamount' => $this->input->post('amount'),
										'paymenttypeID' => $this->input->post('paymentmethod'),
										'paymenttype' => $paymenttype[$this->input->post('paymentmethod')],
										'transactionID' => $this->input->post('transactionID'),
									);
								}
								$this->form_validation->set_rules($rules);
								if ($this->form_validation->run() == FALSE) {
									$this->data["subview"] = "paymenthistory/edit";
									$this->load->view('_layout_main', $this->data);
								} else {
									$maininvoicestatus = 0;
									$invoicepaidstatus = 0;
									$student = $this->studentrelation_m->get_single_student2(array('srstudentID' => $studentID, 'srschoolyearID' => $schoolyearID, 'schoolID' => $schoolID));

									$globalpaymentArray = array(
										'studentID' => $studentID,
										'classesID' => $student->srclassesID,
										'sectionID' => $student->srsectionID,
									);

									$this->globalpayment_m->update_globalpayment($globalpaymentArray, $this->data['payment']->globalpaymentID);

									$this->payment_m->update_payment($paymentArray, $id);
									$this->session->set_flashdata('success', $this->lang->line('menu_success'));
									redirect(base_url("paymenthistory/index"));
								}
							} else {
								$this->data["subview"] = "paymenthistory/edit";
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
			} else {
				$this->data["subview"] = "error";
				$this->load->view('_layout_main', $this->data);
			}
		} else {
			$this->data["subview"] = "error";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function view() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		$schoolID = $this->session->userdata('schoolID');
		$schoolyearID = $this->session->userdata('defaultschoolyearID');
		if((int)$id) {
			$this->data['payment'] = $this->payment_m->get_single_payment_with_studentrelation(array('paymentID' => $id, 'paymentamount !=' => NULL, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
			if(customCompute($this->data['payment'])) {
				$this->data['invoices'] = $this->invoice_m->get_order_by_invoice(array('studentID' => $this->data['payment']->srstudentID, 'deleted_at' => 1, 'date <' => $this->data['payment']->paymentdate, 'schoolID' => $schoolID));
				$this->data['creditmemos'] = $this->creditmemo_m->get_order_by_creditmemo(array('studentID' => $this->data['payment']->srstudentID, 'deleted_at' => 1, 'date <' => $this->data['payment']->paymentdate, 'schoolID' => $schoolID));
				$paymentList = $this->payment_m->get_order_by_payment(array('studentID' => $this->data['payment']->srstudentID, 'paymentdate <' => $this->data['payment']->paymentdate, 'schoolID' => $schoolID));

				if (!empty($this->data['invoices'])) {
					foreach ($this->data['invoices'] as $invoice) {
						$statement[] = ['fee_type' => $invoice->feetype, 'amount' => $invoice->amount, 'date' => $invoice->date, 'column' => 'debit'];
					}
					foreach ($this->data['creditmemos'] as $creditmemo) {
						$statement[] = ['fee_type' => $creditmemo->credittype, 'amount' => $creditmemo->amount, 'date' => $creditmemo->date, 'column' => 'credit'];
					}
					foreach ($paymentList as $payment) {
						$statement[] = ['fee_type' => 'Paid', 'amount' => $payment->paymentamount, 'date' => $payment->paymentdate, 'column' => 'credit'];
					}

					usort($statement, function($a, $b) {
						return $a['date'] <=> $b['date'];
					});

					foreach($statement as $key => $value) {
						if ($statement[$key]['column'] == "debit") {
							$balance += $statement[$key]['amount'];
						} else {
							$balance -= $statement[$key]['amount'];
						}
					}
				}

				$this->data['balance'] = $balance;

				$this->data['createuser'] = getNameByUsertypeIDAndUserID($this->data['payment']->usertypeID, $this->data['payment']->userID);
				$this->data["subview"] = "paymenthistory/view";
				$this->load->view('_layout_main', $this->data);
			}
		}
	}

	public function delete() {
		if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('defaultschoolyearID') == 5)) {
			$id = htmlentities(escapeString($this->uri->segment(3)));
			if((int)$id) {
				$schoolID = $this->session->userdata('schoolID');
				$schoolyearID = $this->session->userdata('defaultschoolyearID');
				$singlePayment = $this->payment_m->get_single_payment(array('paymentID' => $id, 'paymentamount !=' => NULL, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
				if(customCompute($singlePayment)) {
					if(($singlePayment->paymenttype != "Paypal") && ($singlePayment->paymenttype != 'Stripe') && ($singlePayment->paymenttype != 'PayUmoney') && ($singlePayment->paymenttype != 'Voguepay')) {
						$globalID = $singlePayment->globalpaymentID;
						if($globalID) {
							$payments = $this->payment_m->get_order_by_payment(array('globalpaymentID' => $globalID, 'schoolID' => $schoolID));
							$paymentArray = pluck($payments, 'paymentID');
							$this->payment_m->delete_batch_payment($paymentArray);
							$this->globalpayment_m->delete_globalpayment($globalID);
						}

						$this->session->set_flashdata('success', $this->lang->line('menu_success'));
						redirect(base_url("paymenthistory/index"));
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

	public function valid_number() {
		if($this->input->post('amount') != 0) {
			if($this->input->post('amount') && $this->input->post('amount') < 0) {
				$this->form_validation->set_message("valid_number", "%s is invalid number");
				return FALSE;
			}
			return TRUE;
		} else {
			$this->form_validation->set_message("valid_number", "Give me valid amount not zero");
			return FALSE;
		}
		return TRUE;
	}

	public function unique_amount() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		$this->data['payment'] = $this->payment_m->get_single_payment(array('paymentID' => $id, 'schoolID' => $this->session->userdata('schoolID')));
		if(customCompute($this->data['payment'])) {
			$this->data['invoice'] = $this->invoice_m->get_single_invoice(array('invoiceID' => $this->data['payment']->invoiceID));
			if(customCompute($this->data['invoice'])) {
				$this->data['getDbPayment'] = $this->payment_m->get_payment_by_sum_for_edit($this->data['payment']->invoiceID, $id);
				$this->data['weaverandfine'] = $this->weaverandfine_m->get_sum_weaverandfine('weaver', array('invoiceID' => $this->data['invoice']->invoiceID));
				$this->data['dueamount'] = ($this->data['invoice']->amount - ((($this->data['invoice']->amount/100) * $this->data['invoice']->discount) + $this->data['getDbPayment']->paymentamount + $this->data['weaverandfine']->weaver));
				if($this->input->post('amount') > $this->data['dueamount']) {
					$this->form_validation->set_message("unique_amount", "The %s is greater than of due amount");
					return FALSE;
				}
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}

	public function unique_studentID()
	{
			if($this->input->post('studentID') == 0) {
					$this->form_validation->set_message("unique_studentID", "%s field is required.");
					return FALSE;
			}
			return TRUE;
	}

	public function unique_paymentmethod() {
		if($this->input->post('paymentmethod') === '0') {
			$this->form_validation->set_message("unique_paymentmethod", "The %s field is required");
	     	return FALSE;
		}
		return TRUE;
	}

	private function grandtotalandpaidsingle($maininvoice, $schoolyearID, $schoolID, $studentID = NULL) {
    	$retArray = ['grandtotal' => 0, 'totalamount' => 0, 'totaldiscount' => 0, 'totalpayment' => 0, 'totalfine' => 0, 'totalweaver' => 0];
        if(customCompute($maininvoice)) {
		    	if((int)$studentID && $studentID != NULL) {
			        $invoiceitems = pluck_multi_array_key($this->invoice_m->get_order_by_invoice(array('studentID' => $studentID, 'maininvoiceID' => $maininvoice->maininvoiceID,  'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID)), 'obj', 'maininvoiceID', 'invoiceID');
			        $paymentitems = pluck_multi_array($this->payment_m->get_order_by_payment(array('schoolyearID' => $schoolyearID, 'paymentamount !=' => NULL, 'schoolID' => $schoolID)), 'obj', 'invoiceID');
			        $weaverandfineitems = pluck_multi_array($this->weaverandfine_m->get_order_by_weaverandfine(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID)), 'obj', 'invoiceID');
		    	} else {
		    		$invoiceitem = [];
		    		$paymentitems = [];
		    		$weaverandfineitems = [];
		    	}

	    		if(isset($invoiceitems[$maininvoice->maininvoiceID])) {
	    			if(customCompute($invoiceitems[$maininvoice->maininvoiceID])) {
	    				foreach ($invoiceitems[$maininvoice->maininvoiceID] as $invoiceitem) {
	    					$amount = $invoiceitem->amount;
	    					if($invoiceitem->discount > 0) {
	    						$amount = ($invoiceitem->amount - (($invoiceitem->amount/100) *$invoiceitem->discount));
	    					}

	    					if(isset($retArray['grandtotal'])) {
	    						$retArray['grandtotal'] = ($retArray['grandtotal'] + $amount);
	    					} else {
	    						$retArray['grandtotal'] = $amount;
	    					}

	    					if(isset($retArray['totalamount'])) {
	    						$retArray['totalamount'] = ($retArray['totalamount'] + $invoiceitem->amount);
	    					} else {
	    						$retArray['totalamount'] = $invoiceitem->amount;
	    					}

	    					if(isset($retArray['totaldiscount'])) {
	    						$retArray['totaldiscount'] = ($retArray['totaldiscount'] + (($invoiceitem->amount/100) *$invoiceitem->discount));
	    					} else {
	    						$retArray['totaldiscount'] = (($invoiceitem->amount/100) *$invoiceitem->discount);
	    					}

	    					if(isset($paymentitems[$invoiceitem->invoiceID])) {
	    						if(customCompute($paymentitems[$invoiceitem->invoiceID])) {
	    							foreach ($paymentitems[$invoiceitem->invoiceID] as $paymentitem) {
	    								if(isset($retArray['totalpayment'])) {
	    									$retArray['totalpayment'] = ($retArray['totalpayment'] + $paymentitem->paymentamount);
	    								} else {
	    									$retArray['totalpayment'] = $paymentitem->paymentamount;
	    								}
	    							}
	    						}
	    					}

	    					if(isset($weaverandfineitems[$invoiceitem->invoiceID])) {
	    						if(customCompute($weaverandfineitems[$invoiceitem->invoiceID])) {
	    							foreach ($weaverandfineitems[$invoiceitem->invoiceID] as $weaverandfineitem) {
	    								if(isset($retArray['totalweaver'])) {
	    									$retArray['totalweaver'] = ($retArray['totalweaver'] + $weaverandfineitem->weaver);
	    								} else {
	    									$retArray['totalweaver'] = $weaverandfineitem->weaver;
	    								}

	    								if(isset($retArray['totalfine'])) {
	    									$retArray['totalfine'] = ($retArray['totalfine'] + $weaverandfineitem->fine);
	    								} else {
	    									$retArray['totalfine'] = $weaverandfineitem->fine;
	    								}
	    							}
	    						}
	    					}
	    				}
	    			}
	    		}
        }

        return $retArray;
    }
}
