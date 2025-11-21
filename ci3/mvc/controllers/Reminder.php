<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reminder extends CI_Controller {
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
    $this->load->model('schoolterm_m');
    $this->load->model('parents_m');
    $this->load->model('studentrelation_m');
    $this->load->model('payment_m');
    $this->load->model('invoice_m');
		$this->load->model('creditmemo_m');
    $this->load->model('payment_gateway_option_m');
    $this->load->model('fees_balance_tier_m');
  }

  public function red() {
    $this->search("red");
  }

  public function orange() {
    $this->search("orange");
  }

  public function purple() {
    $this->search("purple");
  }

  private function search($status) {
		$formattedDate = date('Y-m-d');
		$schoolterm = $this->schoolterm_m->get_order_by_schoolterm(array("startingdate <=" => $formattedDate, "endingdate >=" => $formattedDate));
		$schoolterm = $schoolterm[0];
		$dateFrom = new DateTime($schoolterm->startingdate);
		$dateTo = new DateTime();
		$diff = $dateFrom->diff($dateTo)->days;

		$multiparents = $this->parents_m->get_parents();
		if(customCompute($multiparents)) {

			foreach ($multiparents as $key => $multiparent) {
			  $message = "Hi ". $multiparent->name .", ";
				$students = $this->studentrelation_m->general_get_order_by_student(['parentID' => $multiparent->parentsID]);
				foreach ($students as $student) {
					$allPaymentList = $this->payment_m->get_order_by_payment(['studentID' => $student->studentID]);
					$totalPaymentAmount = $this->generateAllPaymentAmount($allPaymentList);
					$invoices = $this->invoice_m->get_order_by_invoice(['studentID' => $student->studentID]);
					$totalInvoiceAmount = $this->generateAllInvoiceAmount($invoices);
					$creditmemos = $this->creditmemo_m->get_order_by_creditmemo(['studentID' => $student->studentID]);
					$totalCreditmemoAmount = $this->generateAllCreditmemoAmount($creditmemos);
					$balance = $totalInvoiceAmount - ($totalPaymentAmount + $totalCreditmemoAmount);
					if ($totalInvoiceAmount > 0) {
						$balancePercentage = $balance/$totalInvoiceAmount*100;
						$gatewayOptions = $this->payment_gateway_option_m->get_single_payment_gateway_option(array('payment_option' => 'mpesa_shortcode'));
						if ($diff >= 15) {
							$fees_balance_tier = $this->fees_balance_tier_m->get_tiers(array("fifteen_days >=" => $balancePercentage));
							if (customCompute($fees_balance_tier)) {
								if (strtolower($fees_balance_tier->name) == $status) {
									$message .= $student->srname ." has a balance of Ksh ". $balance .". To pay via M-PESA please enter Paybill number ". $gatewayOptions->payment_value ." and Account number ". $student->srstudentID;
									echo $message ."<br>";
									//$ = $this->userConfigSMS($message, $multiparent, 4, "smsleopard");
								}
							}
						} elseif ($diff >= 30) {
							$fees_balance_tier = $this->fees_balance_tier_m->get_tiers(array("thirty_days >=" => $balancePercentage));
							if (customCompute($fees_balance_tier)) {
								if (strtolower($fees_balance_tier->name) == $status) {
									$message .= $student->srname ." has a balance of Ksh ". $balance .". To pay via M-PESA please enter Paybill number ". $gatewayOptions->payment_value ." and Account number ". $student->srstudentID;
									echo $message ."<br>";
									//$ = $this->userConfigSMS($message, $multiparent, 4, "smsleopard");
								}
							}
						} elseif ($diff >= 45) {
							$fees_balance_tier = $this->fees_balance_tier_m->get_tiers(array("fortyfive_days >=" => $balancePercentage));
							if (customCompute($fees_balance_tier)) {
								if (strtolower($fees_balance_tier->name) == $status) {
									$message .= $student->srname ." has a balance of Ksh ". $balance .". To pay via M-PESA please enter Paybill number ". $gatewayOptions->payment_value ." and Account number ". $student->srstudentID;
									//$ = $this->userConfigSMS($message, $multiparent, 4, "smsleopard");
									echo $message ."<br>";
								}
							}
						}
					}
				}
			}
		}
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
