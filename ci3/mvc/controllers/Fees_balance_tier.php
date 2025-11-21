<?php

class Fees_balance_tier extends Admin_Controller{

	public function __construct() {
		parent::__construct();

		$this->load->model('classes_m');
		$this->load->model('studentgroup_m');
		$this->load->model('studentrelation_m');
		$this->load->model('schoolterm_m');
    $this->load->model("fees_balance_tier_m");
		$this->load->model('invoice_m');
		$this->load->model('creditmemo_m');
		$this->load->model('payment_m');
    $language = $this->session->userdata('lang');
    $this->lang->load('fees_balance_tier', $language);
  }

  protected function rules() {
		$fees_balance_tiers = $this->fees_balance_tier_m->get_fees_balance_tiers();
		foreach($fees_balance_tiers as $fees_balance_tier) {
			$rules[] = array(
				'field' => $fees_balance_tier->fees_balance_tier_id,
				'label' => $this->lang->line("fees_balance_tier_days"),
				'rules' => 'trim|xss_clean|max_length[3]|numeric',
				"errors" => [
            'max_length' => 'Please enter a valid percentage value (0-100)',
        ]
			);
		}

		return $rules;
	}

	public function reportRules() {
		$rules = array(
			array(
				'field'=>'classesID',
				'label'=>$this->lang->line('fees_balance_tier_class'),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field'=>'studentgroupID',
				'label'=>$this->lang->line('fees_balance_tier_group'),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field'=>'studentID',
				'label'=>$this->lang->line('fees_balance_tier_student'),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field'=>'fees_balance_tier',
				'label'=>$this->lang->line('panel_title'),
				'rules' => 'trim|xss_clean'
			)
		);
		return $rules;
	}

  public function index() {
		$this->data['fees_balance_tiers']       = $this->fees_balance_tier_m->get_fees_balance_tiers_grouped();
		//$fees_balance_tiers                     = $this->fees_balance_tier_m->get_fees_balance_tiers();
    $this->data['fees_balance_tier_values'] = pluck_multi_array($this->fees_balance_tier_m->get_fees_balance_tier_values(array('schoolID' => $this->session->userdata('schoolID'))), 'obj', 'name');;

		if(customCompute($this->data['fees_balance_tiers'])) {
				if($_POST) {
						$rules = $this->rules();
						$this->form_validation->set_rules($rules);
						if($this->form_validation->run() == false) {
								$this->data["subview"] = "fees_balance_tier/index";
								$this->load->view('_layout_main', $this->data);
						} else {
								$schoolID           = $this->session->userdata('schoolID');
								$array              = [];
								foreach($_POST as $key => $value) {
									$item['fees_balance_tier_id'] = $key;
									$item['tier_value']           = $value;
									$item['schoolID']             = $schoolID;
									$array[] = $item;
								}

								$this->fees_balance_tier_m->update_batch_fees_balance_tier_values($array, 'fees_balance_tier_id');
								$this->session->set_flashdata('success', "Success");
								redirect(site_url("fees_balance_tier/index"));
						}
				} else {
						$this->data["subview"] = "fees_balance_tier/index";
						$this->load->view('_layout_main', $this->data);
				}
		} else {
				$this->data["subview"] = "_not_found";
				$this->load->view('_layout_main', $this->data);
		}
	}

	public function report() {
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

		$schoolID = $this->session->userdata('schoolID');
		$this->data['classes'] = $this->classes_m->general_get_order_by_classes(array('schoolID' => $schoolID));
		$this->data['groups'] = $this->studentgroup_m->get_order_by_studentgroup(array('schoolID' => $schoolID));
		$this->data['fees_balance_tiers'] = $this->fees_balance_tier_m->get_fees_balance_tier_values(array('schoolID' => $schoolID));
		$usertypeID = $this->session->userdata('usertypeID');
		$this->data['students'] = $this->studentrelation_m->general_get_order_by_student(array('srschoolID' => $schoolID));
		$this->data["subview"] = "fees_balance_tier/report_view";
		$this->load->view('_layout_main', $this->data);
	}

	public function getFeesBalanceTierReport() {
		$retArray['status'] = FALSE;
		$retArray['render'] = '';

		if(permissionChecker('fees_balance_tier_report')) {
			if($_POST) {
				$rules = $this->reportRules();
				$this->form_validation->set_rules($rules);
			  if ($this->form_validation->run() == FALSE) {
					$retArray = $this->form_validation->error_array();
					$retArray['status'] = FALSE;
				  echo json_encode($retArray);
				  exit;
				} else {
					$schoolID        = $this->session->userdata('schoolID');
					$classesID       = $this->input->post('classesID');
					$studentgroupID  = $this->input->post('studentgroupID');
					$studentID       = $this->input->post('studentID');
					$feesBalanceTier = $this->input->post('feesBalanceTier');

					$this->data['classesID']             = $classesID;
					$this->data['studentgroupID']        = $studentgroupID;
					$this->data['studentID']             = $studentID;

					$studentArray 								       = [];
					$studentArray['srschoolID'] 	       = $schoolID;
					// invoice and creditmemo
					$invoiceArray 								       = [];
					$invoiceArray['schoolID'] 		       = $schoolID;
					$invoiceBbfArray 							       = [];
					$invoiceBbfArray['schoolID'] 	       = $schoolID;
					$paymentsArray 								       = [];
					$paymentsArray['schoolID'] 		       = $schoolID;
				  $paymentsBbfArray 						       = [];
					$paymentsBbfArray['schoolID'] 			 = $schoolID;

					if((int)$classesID) {
						$studentArray['srclassesID']  		 = $classesID;
						$invoiceArray['classesID']    		 = $classesID;
						$invoiceBbfArray['classesID'] 		 = $classesID;
					}
					if((int)$studentgroupID) {
						$studentArray['srstudentgroupID']  = $studentgroupID;
					}
					if((int)$studentID) {
						$studentArray['srstudentID'] 			 = $studentID;
					}

					$formattedDate = date('Y-m-d');
					$schoolterm = $this->schoolterm_m->get_single_schoolterm(array("startingdate <=" => $formattedDate, "endingdate >=" => $formattedDate, 'schoolID' => $schoolID));
					if (customCompute($schoolterm)) {
						$invoiceArray['date >=']           = $schoolterm->startingdate;
						$invoiceBbfArray['date <']         = $schoolterm->startingdate;
						$paymentsArray['paymentdate >=']   = $schoolterm->startingdate;
						$paymentsBbfArray['paymentdate <'] = $schoolterm->startingdate;
					}

					$this->db->order_by('srclassesID','ASC');
					$this->data['students'] = pluck($this->studentrelation_m->get_order_by_studentrelation($studentArray),'obj','srstudentID');
					$this->data['classes'] = pluck($this->classes_m->general_get_order_by_classes(array('schoolID' => $schoolID)),'classes','classesID');
					$this->data['totalAmountAndDiscount'] = $this->totalAmountAndDiscustomCompute($this->invoice_m->get_all_balancefees_for_report($invoiceArray));
				  $this->data['totalAmountAndDiscountBf'] = $this->totalAmountAndDiscustomCompute($this->invoice_m->get_all_balancefees_bf_for_report($invoiceBbfArray));
					$this->data['totalCredit'] = $this->totalAmountAndDiscustomCompute($this->creditmemo_m->get_all_balancefees_for_report($invoiceArray));
					$this->data['totalCreditBf'] = $this->totalAmountAndDiscustomCompute($this->creditmemo_m->get_all_balancefees_bf_for_report($invoiceBbfArray));
					$this->data['totalPayment'] = $this->totalPaymentAndWeaver($this->payment_m->get_order_by_payment($paymentsArray));
					$this->data['totalPaymentBf'] = $this->totalPaymentAndWeaver($this->payment_m->get_order_by_payment($paymentsBbfArray));
					$tiers = [];

					foreach ($this->data['students'] as $student) {
						$AmountBf = $this->data['totalAmountAndDiscountBf'][$student->srstudentID]['amount'];
						$CreditBf = $this->data['totalCreditBf'][$student->srstudentID]['amount'];
						$PaymentBf = $this->data['totalPaymentBf'][$student->srstudentID]['payment'];
						$BalanceBf = ($AmountBf - $CreditBf) - ($PaymentBf);
						$Amount = $this->data['totalAmountAndDiscount'][$student->srstudentID]['amount'];
						$Credit = $this->data['totalCredit'][$student->srstudentID]['amount'];
						$Payment = $this->data['totalPayment'][$student->srstudentID]['payment'];
						$Payable = $BalanceBf + $Amount - $Credit;
						$dateFrom = new DateTime($schoolterm->startingdate);
						$dateTo = new DateTime();
						$diff = $dateFrom->diff($dateTo)->days;

						if ($Payable > 0) {
							$balancePercentage = $Payment/$Payable*100;
							if ($diff >= 15) {
								$fees_balance_tier = $this->fees_balance_tier_m->get_single_fees_balance_tier_values(array("tier_value >=" => $balancePercentage, 'days' => 15, 'schoolID' => $schoolID));
								if (customCompute($fees_balance_tier)) {
									$tiers[$student->srstudentID] = $fees_balance_tier->name;
								}
							} elseif ($diff >= 30) {
								$fees_balance_tier = $this->fees_balance_tier_m->get_single_fees_balance_tier_values(array("tier_value >=" => $balancePercentage, "days" => 30, 'schoolID' => $schoolID));
								if (customCompute($fees_balance_tier)) {
									$tiers[$student->srstudentID] = $fees_balance_tier->name;
								}
							} elseif ($diff >= 45) {
								$fees_balance_tier = $this->fees_balance_tier_m->get_single_fees_balance_tier_values(array("tier_value >=" => $balancePercentage, "days" => 45, 'schoolID' => $schoolID));
								if (customCompute($fees_balance_tier)) {
									$tiers[$student->srstudentID] = $fees_balance_tier->name;
								}
							}
						}
						if($this->studentgroup_m->get_single_studentgroup(array('studentgroupID' => $student->srstudentgroupID, 'schoolID' => $schoolID)) != null)
							$student->group = $this->studentgroup_m->get_single_studentgroup(array('studentgroupID' => $student->srstudentgroupID, 'schoolID' => $schoolID))->group;
					}

					$this->data['tiers'] = $tiers;

					if (!empty($feesBalanceTier)) {
						$studentIDs = [];
						foreach ($tiers as $key => $value) {
							if ($feesBalanceTier == $value) {
								$studentIDs[] = $key;
							}
						}
						$this->data['students'] = $this->studentrelation_m->get_studentrelation_studentArray($studentIDs);
					}

					$retArray['render'] = $this->load->view('fees_balance_tier/report', $this->data, true);
					$retArray['status'] = TRUE;
					echo json_encode($retArray);
					exit;
				}
			} else {
				echo json_encode($retArray);
				exit;
			}
		} else {
			$retArray['render'] =  $this->load->view('report/reporterror', $this->data, true);
			$retArray['status'] = TRUE;
			echo json_encode($retArray);
			exit;
		}
	}

	private function totalAmountAndDiscustomCompute($arrays) {
		$totalAmountAndDiscount = [];
		if(customCompute($arrays)) {
			foreach($arrays as $key => $array) {
				if(isset($totalAmountAndDiscount[$array->studentID]['amount'])) {
					$totalAmountAndDiscount[$array->studentID]['amount'] += $array->amount;
				} else {
					$totalAmountAndDiscount[$array->studentID]['amount'] = $array->amount;
				}

				if(isset($totalAmountAndDiscount[$array->studentID]['discount'])) {
					$discount = (($array->amount / 100) * $array->discount);
					$totalAmountAndDiscount[$array->studentID]['discount'] += $discount;
				} elseif(isset($array->discount)) {
					$discount = (($array->amount / 100) * $array->discount);
					$totalAmountAndDiscount[$array->studentID]['discount'] = $discount;
				}
			}
		}
		return $totalAmountAndDiscount;
	}

	private function totalPaymentAndWeaver($arrays) {
		$totalPayment = [];
		if(customCompute($arrays)) {
			foreach($arrays as $key => $array) {
				if(isset($totalPayment[$array->studentID]['payment'])) {
					$totalPayment[$array->studentID]['payment'] += $array->paymentamount;
				} else {
					$totalPayment[$array->studentID]['payment'] = $array->paymentamount;
				}
			}
		}
		return $totalPayment;
	}

	private function totalWeaver($arrays) {
		$totalWeaver = [];
		if(customCompute($arrays)) {
			foreach ($arrays as $array) {
				if(isset($totalWeaver[$array->studentID]['weaver'])) {
					$totalWeaver[$array->studentID]['weaver'] += $array->weaver;
				} else {
					$totalWeaver[$array->studentID]['weaver'] = $array->weaver;
				}
			}
		}
		return $totalWeaver;
	}

	private function totalPayment($arrays, $schoolyearID) {
		$weaverandfine = pluck($this->weaverandfine_m->get_order_by_weaverandfine(array('schoolyearID'=>$schoolyearID, 'schoolID' => $this->session->userdata('schoolID'))),'obj','paymentID');
		$retArray = [];
		if(customCompute($arrays)) {
			foreach ($arrays as $array) {
				if(isset($retArray[$array->invoiceID])) {
					$oldAmount = $retArray[$array->invoiceID];
					$oldAmount += $array->paymentamount;
					$retArray[$array->invoiceID] = (int) $oldAmount;
					if(isset($weaverandfine[$array->paymentID])) {
						$oldAmount = $retArray[$array->invoiceID];
						$oldAmount += $weaverandfine[$array->paymentID]->weaver;
						$retArray[$array->invoiceID] = (int) $oldAmount;
					}
				} else {
					$retArray[$array->invoiceID] = (int) $array->paymentamount;
					if(isset($weaverandfine[$array->paymentID])) {
						$oldAmount = $retArray[$array->invoiceID];
						$oldAmount += $weaverandfine[$array->paymentID]->weaver;
						$retArray[$array->invoiceID] = (int) $oldAmount;
					}
				}
			}
		}

		return $retArray;
	}
}
