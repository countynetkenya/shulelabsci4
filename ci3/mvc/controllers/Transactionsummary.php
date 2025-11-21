<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Transactionsummary extends Admin_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->model('payment_m');
		$this->load->model('classes_m');
		$this->load->model('schoolyear_m');
		$this->load->model('schoolterm_m');
		$this->load->model('feetypes_m');
		$this->load->model('invoice_m');
		$this->load->model('creditmemo_m');
		$this->load->model('income_m');
		$this->load->model('expense_m');
		$this->load->model('usertype_m');
		$this->load->model('section_m');
		$this->load->model('studentgroup_m');
		$this->load->model('make_payment_m');
		$this->load->model('weaverandfine_m');
		$this->load->model('studentrelation_m');
		$this->load->model('divisions_m');
		$this->load->model('paymenttypes_m');
		$language = $this->session->userdata('lang');
		$this->lang->load('transactionsummary', $language);
	}

	public function rules() {
		$rules = array(
	    array(
				'field'=>'classesID',
				'label'=>$this->lang->line('transactionsummary_class'),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field'=>'studentgroupID',
				'label'=>$this->lang->line('transactionsummary_group'),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field'=>'studentID',
				'label'=>$this->lang->line('transactionsummary_student'),
				'rules' => 'trim|xss_clean'
			)
		);
		return $rules;
	}

	public function send_pdf_to_mail_rules() {
		$rules = array(
	        array(
                'field' => 'fromdate',
                'label' => $this->lang->line('transactionsummary_fromdate'),
                'rules' => 'trim|required|xss_clean|callback_date_valid_new|callback_unique_date_new'
	        ),
	        array(
                'field' => 'todate',
                'label' => $this->lang->line('transactionsummary_todate'),
                'rules' => 'trim|required|xss_clean'
	        ),
	        array(
                'field' => 'to',
                'label' => $this->lang->line('transactionreport_to'),
                'rules' => 'trim|required|xss_clean|valid_email'
	        ),
	        array(
                'field' => 'subject',
                'label' => $this->lang->line('transactionreport_subject'),
                'rules' => 'trim|required|xss_clean'
	        ),
	        array(
                'field' => 'message',
                'label' => $this->lang->line('transactionreport_message'),
                'rules' => 'trim|xss_clean'
	        ),
	        array(
                'field' => 'querydata',
                'label' => $this->lang->line('transactionreport_querydata'),
                'rules' => 'trim|required|xss_clean'
	        )
		);
		return $rules;
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
		$schoolID = $this->session->userdata('schoolID');
		$this->data['classes'] = $this->classes_m->general_get_order_by_classes(array('schoolID' => $schoolID));
		$this->data['groups'] = $this->studentgroup_m->get_order_by_studentgroup(array('schoolID' => $schoolID));
		$this->data['schoolyears'] = $this->schoolyear_m->get_order_by_schoolyear(array('schoolID' => $schoolID));
		$this->data['schoolyearID'] = $this->session->userdata('defaultschoolyearID');
		$this->data['schoolterms'] = $this->schoolterm_m->get_order_by_schoolterm(array('schoolyearID' => $this->data['schoolyearID'], 'schoolID' => $schoolID));
		$this->data['feetypes'] = $this->feetypes_m->get_order_by_feetypes(array('schoolID' => $schoolID));
		$this->data["subview"] = "report/transaction/TransactionSummaryView";
		$this->load->view('_layout_main', $this->data);
	}

	public function getTransactionSummary() {
		$retArray['status'] = FALSE;
		$retArray['render'] = '';
		if(permissionChecker('transactionreport')) {
			if($_POST) {
				$rules = $this->rules();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {
					$retArray = $this->form_validation->error_array();
			    $retArray['status'] = FALSE;
			    echo json_encode($retArray);
			    exit;
				} else {
					$schoolID       = $this->session->userdata('schoolID');
					$schoolyearID 	= $this->input->post('schoolyearID');
					$classesID    	= $this->input->post('classesID');
					$studentgroupID = $this->input->post('studentgroupID');
					$studentID    	= $this->input->post('studentID');
					$schooltermID 	= $this->input->post('schoolTermID');
					$reportType 		= $this->input->post('reportType');
					$reportDetails 	= $this->input->post('reportDetails');
					$items 	        = $this->input->post('items');
					$selectedTotal 	= $this->input->post('selectedTotal');

					$_POST['schoolID'] = $schoolID;
					if ($schooltermID != 0) {
						$schoolterm = $this->schoolterm_m->get_single_schoolterm(array('schooltermID' => $schooltermID, 'schoolID' => $schoolID));
						$_POST['termFrom'] = $schoolterm->startingdate;
						$_POST['termTo'] = $schoolterm->endingdate;
					}

					$studentarray['srschoolID'] = $schoolID;
					$invoicearray['schoolID'] = $schoolID;

					if ($schoolyearID != 0) {
						$studentarray['srschoolyearID'] = $schoolyearID;
						$invoicearray['schoolyearID'] = $schoolyearID;
					}

					if ($classesID != 0) {
						$studentarray['srclassesID'] = $classesID;
						$invoicearray['classesID'] = $classesID;
					}

					if ($studentgroupID != 0) {
						$studentarray['srstudentgroupID'] = $studentgroupID;
						$invoicearray['classesID'] = $classesID;
					}

					$this->data['reportType'] = $reportType;
					$this->data['reportDetails'] = $reportDetails;
					$this->data['selectedTotal'] = $selectedTotal;
					$this->data['students'] = pluck($this->studentrelation_m->get_order_by_studentrelation($studentarray),'obj','srstudentID');
					foreach ($this->data['students'] as $student)
						$student->group = $this->studentgroup_m->get_single_studentgroup(array('studentgroupID' => $student->srstudentgroupID))->group;
					$this->data['invoices'] = $this->invoice_m->get_all_fees_for_report($this->input->post());
					$this->data['creditmemos'] = $this->creditmemo_m->get_all_fees_for_report($this->input->post());
					$this->data['payments'] = $this->payment_m->get_all_payment_for_report($this->input->post());
					$this->data['classes'] = $this->classes_m->general_get_order_by_classes(array('schoolID' => $schoolID));
					$this->data['divisions'] = $this->divisions_m->get_order_by_divisions(array('schoolID' => $schoolID));
					$this->data['dates'] = array_unique(pluck($this->invoice_m->get_order_by_invoice(array('schoolID' => $schoolID)),'date','invoiceID'));
					$this->data['dates2'] = array_unique(pluck($this->creditmemo_m->get_order_by_creditmemo(array('schoolID' => $schoolID)),'date','creditmemoID'));
					$this->data['dates3'] = array_unique(pluck($this->payment_m->get_order_by_payment(array('schoolID' => $schoolID)),'paymentdate','paymentID'));
					$months = $months2 = $months3 = array();

					foreach($this->data['dates'] as $date) {
						$months[] = substr($date, 0, -3);
					}
					foreach($this->data['dates2'] as $date) {
						$months2[] = substr($date, 0, -3);
					}
					foreach($this->data['dates3'] as $date) {
						$months3[] = substr($date, 0, -3);
					}
					$this->data['schoolterms'] = $this->schoolterm_m->get_order_by_schoolterm(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
					$this->data['months'] = array_unique($months);
					$this->data['months2'] = array_unique($months2);
					$this->data['months3'] = array_unique($months3);
					$this->data['years'] = array_unique(pluck($this->invoice_m->get_order_by_invoice(array('schoolID' => $schoolID)),'year','invoiceID'));
					$this->data['years2'] = array_unique(pluck($this->creditmemo_m->get_order_by_creditmemo(array('schoolID' => $schoolID)),'year','creditmemoID'));
					$this->data['years3'] = array_unique(pluck($this->payment_m->get_order_by_payment(array('schoolID' => $schoolID)),'paymentyear','paymentID'));
					$this->data['sections'] = pluck($this->section_m->general_get_order_by_section(array('schoolID' => $schoolID)),'section','sectionID');
					$columns = array();
					if (!empty($items) && in_array("Term Fee", $items)) {
						$columns["term_fees"] = "Term Fee";
					}
					$feetypes = pluck($this->feetypes_m->get_feetypes_wherein($items),'feetypes','feetypesID');
					foreach ($feetypes as $feetype) {
						$columns[$feetype] = $feetype;
					}
					if (!empty($items) && in_array("Total Amount", $items)) {
						$columns["total"] = "Total Amount";
					}
					$this->data['columns'] = $columns;

					if ($reportType == "invoice_report") {
						if ($reportDetails == "student_detail") {
							$this->data['totalStudentInvoiceAmount'] = $this->totalInvoiceAmountcustomCompute($this->invoice_m->get_all_fees_for_report($this->input->post()), $columns, "studentID");
						}
						elseif ($reportDetails == "class_summary") {
							$this->data['totalClassInvoiceAmount'] = $this->totalInvoiceAmountcustomCompute($this->invoice_m->get_all_fees_for_report($this->input->post()), $columns, "classesID");
						}
						elseif ($reportDetails == "division_summary") {
							$this->data['totalDivisionInvoiceAmount'] = $this->totalInvoiceAmountcustomCompute($this->invoice_m->get_all_fees_for_report($this->input->post()), $columns, "division");
						}
						elseif ($reportDetails == "date_detail") {
							$this->data['totalDatedetailInvoiceAmount'] = $this->totalInvoiceAmountcustomCompute($this->invoice_m->get_all_fees_for_report($this->input->post()), $columns, "invoiceID");
						}
						elseif ($reportDetails == "date_summary") {
							$this->data['totalDateInvoiceAmount'] = $this->totalInvoiceAmountcustomCompute($this->invoice_m->get_all_fees_for_report($this->input->post()), $columns, "date");
						}
						elseif ($reportDetails == "month_summary") {
							$this->data['totalMonthInvoiceAmount'] = $this->totalInvoiceAmountcustomCompute($this->invoice_m->get_all_fees_for_report($this->input->post()), $columns, "month");
						}
						elseif ($reportDetails == "term_summary") {
							$this->data['totalTermInvoiceAmount'] = $this->totalInvoiceAmountcustomCompute($this->invoice_m->get_all_fees_for_report($this->input->post()), $columns, "schooltermID");
						}
						elseif ($reportDetails == "year_summary") {
							$this->data['totalYearInvoiceAmount'] = $this->totalInvoiceAmountcustomCompute($this->invoice_m->get_all_fees_for_report($this->input->post()), $columns, "year");
						}
					}
					elseif ($reportType == "creditmemo_report") {
						if ($reportDetails == "student_detail") {
							$this->data['totalStudentCreditmemoAmount'] = $this->totalCreditmemoAmountcustomCompute($this->creditmemo_m->get_all_fees_for_report($this->input->post()), "studentID");
						}
						elseif ($reportDetails == "class_summary") {
							$this->data['totalClassCreditmemoAmount'] = $this->totalCreditmemoAmountcustomCompute($this->creditmemo_m->get_all_fees_for_report($this->input->post()), "classesID");
						}
						elseif ($reportDetails == "division_summary") {
							$this->data['totalDivisionCreditmemoAmount'] = $this->totalCreditmemoAmountcustomCompute($this->creditmemo_m->get_all_fees_for_report($this->input->post()), "division");
						}
						elseif ($reportDetails == "date_detail") {
							$this->data['totalDatedetailCreditmemoAmount'] = $this->totalCreditmemoAmountcustomCompute($this->creditmemo_m->get_all_fees_for_report($this->input->post()), "creditmemoID");
						}
						elseif ($reportDetails == "date_summary") {
							$this->data['totalDateCreditmemoAmount'] = $this->totalCreditmemoAmountcustomCompute($this->creditmemo_m->get_all_fees_for_report($this->input->post()), "date");
						}
						elseif ($reportDetails == "month_summary") {
							$this->data['totalMonthCreditmemoAmount'] = $this->totalCreditmemoAmountcustomCompute($this->creditmemo_m->get_all_fees_for_report($this->input->post()), "month");
						}
						elseif ($reportDetails == "term_summary") {
							$this->data['totalTermCreditmemoAmount'] = $this->totalCreditmemoAmountcustomCompute($this->creditmemo_m->get_all_fees_for_report($this->input->post()), "schooltermID");
						}
						elseif ($reportDetails == "year_summary") {
							$this->data['totalYearCreditmemoAmount'] = $this->totalCreditmemoAmountcustomCompute($this->creditmemo_m->get_all_fees_for_report($this->input->post()), "year");
						}
					}
					elseif ($reportType == "payment_report") {
						$this->data['paymenttypes'] = $this->paymenttypes_m->get_order_by_paymenttypes(array('schoolID' => $schoolID));
						if ($reportDetails == "student_detail") {
							$this->data['totalStudentPaymentAmount'] = $this->totalPaymentAmountcustomCompute($this->payment_m->get_all_payment_for_report($this->input->post()), "studentID");
						}
						elseif ($reportDetails == "class_summary") {
							$this->data['totalClassPaymentAmount'] = $this->totalPaymentAmountcustomCompute($this->payment_m->get_all_payment_for_report($this->input->post()), "classesID");
						}
						elseif ($reportDetails == "division_summary") {
							$this->data['totalDivisionPaymentAmount'] = $this->totalPaymentAmountcustomCompute($this->payment_m->get_all_payment_for_report($this->input->post()), "division");
						}
						elseif ($reportDetails == "date_detail") {
							$this->data['totalDatedetailPaymentAmount'] = $this->totalPaymentAmountcustomCompute($this->payment_m->get_all_payment_for_report($this->input->post()), "paymentID");
						}
						elseif ($reportDetails == "date_summary") {
							$this->data['totalDatePaymentAmount'] = $this->totalPaymentAmountcustomCompute($this->payment_m->get_all_payment_for_report($this->input->post()), "paymentdate");
						}
						elseif ($reportDetails == "month_summary") {
							$this->data['totalMonthPaymentAmount'] = $this->totalPaymentAmountcustomCompute($this->payment_m->get_all_payment_for_report($this->input->post()), "paymentmonth");
						}
						elseif ($reportDetails == "term_summary") {
							$this->data['totalTermPaymentAmount'] = $this->totalPaymentAmountcustomCompute($this->payment_m->get_all_payment_for_report($this->input->post()), "schooltermID");
						}
						elseif ($reportDetails == "year_summary") {
							$this->data['totalYearPaymentAmount'] = $this->totalPaymentAmountcustomCompute($this->payment_m->get_all_payment_for_report($this->input->post()), "paymentyear");
						}
					}

					$retArray['render'] = $this->load->view('report/transaction/TransactionSummary',$this->data,true);
					$retArray['status'] = TRUE;
					echo json_encode($retArray);
					exit;
				}
			} else {
				$retArray['status'] = TRUE;
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

	private function totalInvoiceAmountcustomCompute($arrays, $columns, $summary) {
		$totalAmount = [];
		if(customCompute($arrays)) {
			foreach($arrays as $key => $array) {
				$key = $array->$summary;
				$feetype = $array->feetype;
				$termFees = 0;
				if ($summary == "month")
					$key = $array->year ."-".$array->month;
				if (strpos($array->feetype, 'Term 1 Fees') !== false || strpos($array->feetype, 'Term 2 Fees') !== false || strpos($array->feetype, 'Term 3 Fees') !== false) {
					if (isset($totalAmount[$key]['term_fees'])) {
						$totalAmount[$key]['term_fees'] += $array->amount;
					} else {
						$totalAmount[$key]['term_fees'] = $array->amount;
					}
					$termFees += $array->amount;
				}
				else {
					if (isset($totalAmount[$key][$feetype]))
						$totalAmount[$key][$feetype] += $array->amount;
					else
						$totalAmount[$key][$feetype] = $array->amount;
				}

				if (in_array($array->feetype, $columns))
					$totalAmount[$key]['selected_total'] += $array->amount;
			  if (in_array("Term Fee", $columns))
				 	$totalAmount[$key]['selected_total'] += $termFees;

				if (isset($totalAmount[$key]['total']))
					$totalAmount[$key]['total'] += $array->amount;
				else
					$totalAmount[$key]['total'] = $array->amount;
			}
		}
		return $totalAmount;
	}

	private function totalCreditmemoAmountcustomCompute($arrays, $summary) {
		$totalAmount = [];
		if(customCompute($arrays)) {
			foreach($arrays as $key => $array) {
				$key = $array->$summary;
				if ($summary == "month")
					$key = $array->year ."-".$array->month;
				if (strpos($array->credittype, 'Sibling') !== false) {
					if (isset($totalAmount[$key]['sibling_discount'])) {
						$totalAmount[$key]['sibling_discount'] += $array->amount;
					} else {
						$totalAmount[$key]['sibling_discount'] = $array->amount;
					}
				}
				elseif (strpos($array->credittype, 'Head Teacher') !== false) {
					if (isset($totalAmount[$key]['headteacher_discount']))
						$totalAmount[$key]['headteacher_discount'] += $array->amount;
					else
						$totalAmount[$key]['headteacher_discount'] = $array->amount;
				}
				elseif (strpos($array->credittype, 'Staff') !== false) {
					if (isset($totalAmount[$key]['staff_discount']))
						$totalAmount[$key]['staff_discount'] += $array->amount;
					else
						$totalAmount[$key]['staff_discount'] = $array->amount;
				}
				elseif (strpos($array->credittype, 'Director') !== false) {
					if (isset($totalAmount[$key]['director_discount']))
						$totalAmount[$key]['director_discount'] += $array->amount;
					else
						$totalAmount[$key]['director_discount'] = $array->amount;
				}

				if (isset($totalAmount[$key]['total']))
					$totalAmount[$key]['total'] += $array->amount;
				else
					$totalAmount[$key]['total'] = $array->amount;
			}
		}
		return $totalAmount;
	}

  private function totalPaymentAmountcustomCompute($arrays, $summary) {
		$totalAmount = [];
		if(customCompute($arrays)) {

			foreach($arrays as $key => $array) {
				$key = $array->$summary;
				if ($summary == "paymentmonth")
					$key = $array->paymentyear ."-".$array->paymentmonth;

				if (isset($totalAmount[$key][$array->paymenttypeID])) {
					$totalAmount[$key][$array->paymenttypeID] += $array->paymentamount;
				} else {
					$totalAmount[$key][$array->paymenttypeID] = $array->paymentamount;
				}

				if (isset($totalAmount[$key]['total']))
					$totalAmount[$key]['total'] += $array->paymentamount;
				else
					$totalAmount[$key]['total'] = $array->paymentamount;
			}
		}
		return $totalAmount;
	}

	public function pdf() {
		if(permissionChecker('transactionreport')) {
			$fromdate = $this->uri->segment(3);
			$todate = $this->uri->segment(4);
			$pdfoption = $this->uri->segment(5);
			if((isset($fromdate) && (int)$fromdate) && (isset($todate) && (int)$todate) && (isset($pdfoption) && (int)$pdfoption)) {
				$this->data['fromdate'] = $fromdate;
				$this->data['todate'] = $todate;

				$schoolID = $this->session->userdata('schoolID');
				$schoolyearID = $this->session->userdata('defaultschoolyearID');
				$this->data['students'] = pluck($this->studentrelation_m->get_order_by_studentrelation(array('srschoolyearID'=>$schoolyearID,'srschoolID'=>$schoolID)),'obj','srstudentID');
				$this->data['classes'] = pluck($this->classes_m->general_get_order_by_classes(array('schoolID' => $schoolID)),'classes','classesID');
				$this->data['sections'] = pluck($this->section_m->general_get_order_by_section(array('schoolID' => $schoolID)),'section','sectionID');
				$this->data['feetypes'] = pluck($this->feetypes_m->get_order_by_feetypes(array('schoolID' => $schoolID)),'feetypes','feetypesID');
				$this->data['weaverandfine'] = pluck($this->weaverandfine_m->get_order_by_weaverandfine(array('schoolyearID'=>$schoolyearID,'schoolID'=>$schoolID)),'obj','paymentID');

				$array = [];
				$array['schoolID'] = $schoolID;
				$array['schoolyearID'] = $schoolyearID;
				$array['fromdate'] = date('Y-m-d',$fromdate);
				$array['todate'] = date('Y-m-d',$todate);
				$this->data['incomes'] = $this->income_m->get_income_order_by_date($array);
				$this->data['expenses'] = $this->expense_m->get_expense_order_by_date($array);
				$this->data['get_payments'] = $this->payment_m->get_payments($array);
				$this->data['pdfoption'] = $pdfoption;

				$this->data['allUserName'] = getAllUserObjectWithoutStudent();
				$this->data['usertypes']   = pluck($this->usertype_m->get_order_by_usertype_with_or(array('schoolID' => $schoolID)),'usertype','usertypeID');
				$this->data['salarys'] = $this->make_payment_m->get_payment_salary_by_date(array('fromdate' => date('d-m-Y',$fromdate),'todate'=> date('d-m-Y',$todate), 'schoolID' => $schoolID));

				$this->reportPDF('transactionreport.css', $this->data, 'report/transaction/TransactionReportPDF');
			} else {
				$this->data["subview"] = "error";
				$this->load->view('_layout_main', $this->data);
			}
		} else {
			$this->data["subview"] = "errorpermission";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function send_pdf_to_mail() {
		$retArray['status'] = FALSE;
		$retArray['render'] = '';

		if(permissionChecker('transactionreport')) {
			if($_POST) {
				$rules = $this->send_pdf_to_mail_rules();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {
					$retArray = $this->form_validation->error_array();
			    $retArray['status'] = FALSE;
			    echo json_encode($retArray);
			    exit;
				} else {
					$to = $this->input->post('to');
					$subject = $this->input->post('subject');
					$message = $this->input->post('message');

					$this->data['fromdate'] = $this->input->post('fromdate');
					$this->data['todate'] = $this->input->post('todate');

					$schoolID = $this->session->userdata('schoolID');
					$schoolyearID = $this->session->userdata('defaultschoolyearID');
					$this->data['students'] = pluck($this->studentrelation_m->get_order_by_studentrelation(array('srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID)),'obj','srstudentID');
					$this->data['classes'] = pluck($this->classes_m->general_get_order_by_classes(array('schoolID' => $schoolID)),'classes','classesID');
					$this->data['sections'] = pluck($this->section_m->general_get_order_by_section(array('schoolID' => $schoolID)),'section','sectionID');
					$this->data['feetypes'] = pluck($this->feetypes_m->get_order_by_feetypes(array('schoolID' => $schoolID)),'feetypes','feetypesID');
					$this->data['weaverandfine'] = pluck($this->weaverandfine_m->get_order_by_weaverandfine(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID)),'obj','paymentID');

					$array = [];
					$array['schoolID'] = $schoolID;
					$array['schoolyearID'] = $schoolyearID;
					$array['fromdate'] = date('Y-m-d',$this->data['fromdate']);
					$array['todate'] = date('Y-m-d',$this->data['todate']);
					$this->data['incomes'] = $this->income_m->get_income_order_by_date($array);
					$this->data['expenses'] = $this->expense_m->get_expense_order_by_date($array);
					$this->data['get_payments'] = $this->payment_m->get_payments($array);
					$this->data['pdfoption'] = $this->input->post('querydata');

					$this->data['allUserName'] = getAllUserObjectWithoutStudent();
					$this->data['usertypes']   = pluck($this->usertype_m->get_order_by_usertype_with_or(array('schoolID' => $schoolID)),'usertype','usertypeID');
					$this->data['salarys'] = $this->make_payment_m->get_payment_salary_by_date(array('fromdate' => date('d-m-Y',$this->data['fromdate']),'todate'=> date('d-m-Y',$this->data['todate']), 'schoolID' => $schoolID));

					$this->reportSendToMail('transactionreport.css', $this->data, 'report/transaction/TransactionReportPDF', $to, $subject, $message);
					$retArray['status'] = TRUE;
				  echo json_encode($retArray);
				}
			} else {
				$retArray['message'] = $this->lang->line('transactionreport_permissionmethod');
				echo json_encode($retArray);
				exit;
			}

		} else {
			$retArray['message'] = $this->lang->line('transactionreport_permission');
			echo json_encode($retArray);
			exit;
		}

	}

	public function xlsx() {
		if(permissionChecker('transactionreport')) {
			$this->load->library('phpspreadsheet');

			$sheet = $this->phpspreadsheet->spreadsheet->getActiveSheet();
			$sheet->getDefaultColumnDimension()->setWidth(20);
			$sheet->getDefaultRowDimension()->setRowHeight(20);
			$sheet->getColumnDimension('A')->setWidth(25);
			$sheet->getColumnDimension('C')->setWidth(25);
			$sheet->getRowDimension('1')->setRowHeight(30);
			$sheet->getRowDimension('2')->setRowHeight(25);

			$data = $this->xmlData();

			// Redirect output to a client’s web browser (Xlsx)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="transactionreport.xlsx"');
			header('Cache-Control: max-age=0');
			// If you're serving to IE 9, then the following may be needed
			header('Cache-Control: max-age=1');

			// If you're serving to IE over SSL, then the following may be needed
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
			header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
			header('Pragma: public'); // HTTP/1.0

			$this->phpspreadsheet->output($this->phpspreadsheet->spreadsheet);
		} else {
			$this->data["subview"] = "errorpermission";
			$this->load->view('_layout_main', $this->data);
		}
	}

	private function xmlData() {
		$fromdate = $this->uri->segment(3);
		$todate = $this->uri->segment(4);
		$xmloption = $this->uri->segment(5);
		if((isset($fromdate) && (int)$fromdate) && (isset($todate) && (int)$todate) && (isset($xmloption) && (int)$xmloption)) {

			$this->data['fromdate'] = $fromdate;
			$this->data['todate'] = $todate;
			$schoolID = $this->session->userdata('schoolID');
			$schoolyearID = $this->session->userdata('defaultschoolyearID');

			$this->data['students'] = pluck($this->studentrelation_m->get_order_by_studentrelation(array('srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID)),'obj','srstudentID');
			$this->data['classes'] = pluck($this->classes_m->general_get_order_by_classes(array('schoolID' => $schoolID)),'classes','classesID');
			$this->data['sections'] = pluck($this->section_m->general_get_order_by_section(array('schoolID' => $schoolID)),'section','sectionID');
			$this->data['feetypes'] = pluck($this->feetypes_m->get_order_by_feetypes(array('schoolID' => $schoolID)),'feetypes','feetypesID');
			$this->data['weaverandfine'] = pluck($this->weaverandfine_m->get_order_by_weaverandfine(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID)),'obj','paymentID');

			$array = [];
			$array['schoolID'] = $schoolID;
			$array['schoolyearID'] = $schoolyearID;
			$array['fromdate'] = date('Y-m-d',$fromdate);
			$array['todate'] = date('Y-m-d',$todate);
			$this->data['get_payments'] = $this->payment_m->get_payments($array);
			$this->data['incomes'] = $this->income_m->get_income_order_by_date($array);
			$this->data['expenses'] = $this->expense_m->get_expense_order_by_date($array);
			$this->data['xmloption'] = $xmloption;

			$this->data['allUserName'] = getAllUserObjectWithoutStudent();
			$this->data['usertypes']   = pluck($this->usertype_m->get_order_by_usertype_with_or(array('schoolID' => $schoolID)),'usertype','usertypeID');
			$this->data['salarys'] = $this->make_payment_m->get_payment_salary_by_date(array('fromdate' => date('d-m-Y',$fromdate),'todate'=> date('d-m-Y',$todate), 'schoolID' => $schoolID));

			return $this->generateXML($this->data);
		} else {
			$this->data["subview"] = "error";
			$this->load->view('_layout_main', $this->data);
		}
	}

	private function generateXML($arrays) {
		extract($arrays);
		$sheet = $this->phpspreadsheet->spreadsheet->getActiveSheet();
		if($xmloption == 1 ) {
			if(customCompute($get_payments)) {
				$headers = array();
				$headers['slno'] = $this->lang->line('slno');
				$headers['date'] = $this->lang->line('transactionreport_date');
				$headers['name'] = $this->lang->line('transactionreport_name');
        $headers['registerNO'] = $this->lang->line('transactionreport_registerNO');
				$headers['class'] = $this->lang->line('transactionreport_class');
        $headers['section'] = $this->lang->line('transactionreport_section');
        $headers['roll'] = $this->lang->line('transactionreport_roll');
				$headers['feetype'] = $this->lang->line('transactionreport_feetype');
				$headers['amount'] = $this->lang->line('transactionreport_paid');
				$headers['weaver'] = $this->lang->line('transactionreport_weaver');
				$headers['fine'] = $this->lang->line('transactionreport_fine');

				$bodys = array();
				$i=0;
				$totalamount = 0;
        $totalweaver = 0;
        $totalfine   = 0;

				foreach ($get_payments as $get_payment) {
					if(isset($weaverandfine[$get_payment->paymentID]) && (($weaverandfine[$get_payment->paymentID]->weaver != '') || ($weaverandfine[$get_payment->paymentID]->fine != '')) || $get_payment->paymentamount != '') {

						$bodys[$i][] = $i+1;
						$bodys[$i][] = date('d M Y',strtotime($get_payment->paymentdate));
            $bodys[$i][] = isset($students[$get_payment->studentID]) ? $students[$get_payment->studentID]->srname : '';
            $bodys[$i][] = isset($students[$get_payment->studentID]) ? $students[$get_payment->studentID]->srregisterNO : '';

            if(isset($students[$get_payment->studentID])) {
                if(isset($classes[$students[$get_payment->studentID]->srclassesID])) {
                    $bodys[$i][] = $classes[$students[$get_payment->studentID]->srclassesID];
                }
            }

            if(isset($students[$get_payment->studentID])) {
                if(isset($sections[$students[$get_payment->studentID]->srsectionID])) {
                    $bodys[$i][] = $sections[$students[$get_payment->studentID]->srsectionID];
                }
            }

            $bodys[$i][] = isset($students[$get_payment->studentID]) ? $students[$get_payment->studentID]->srroll : '';
            $bodys[$i][] = isset($feetypes[$get_payment->feetypeID]) ? $feetypes[$get_payment->feetypeID] : '';

            $amount = $get_payment->paymentamount;
            $bodys[$i][] = number_format($amount,2);
            $totalamount +=$amount;

            if(isset($weaverandfine[$get_payment->paymentID])) {
                $weaver = $weaverandfine[$get_payment->paymentID]->weaver;
                $bodys[$i][] = number_format($weaver,2);
                $totalweaver += $weaver;
            } else {
                $bodys[$i][] = number_format(0,2);
            }

            if(isset($weaverandfine[$get_payment->paymentID])) {
                $fine = $weaverandfine[$get_payment->paymentID]->fine;
                $bodys[$i][] = number_format($fine,2);
                $totalfine +=$fine;
            } else{
                $bodys[$i][] = number_format(0,2);
            }
	          $i++;
	        }
				}

				$bodys[$i][] = "";
				$bodys[$i][] = "";
				$bodys[$i][] = "";
				$bodys[$i][] = "";
				$bodys[$i][] = "";
				$bodys[$i][] = "";
				$bodys[$i][] = "";
				$bodys[$i][] = "";
				$bodys[$i][] = number_format($totalamount,2);
				$bodys[$i][] = number_format($totalweaver,2);
				$bodys[$i][] = number_format($totalfine,2);


				$fromdateValue = $this->lang->line('transactionreport_fromdate')." : ".date('d M Y',$fromdate);
				$todateValue   = $this->lang->line('transactionreport_todate')." : ".date('d M Y',$todate);
				$sheet->setCellValue('A1', $fromdateValue);
				$sheet->setCellValue('K1', $todateValue);

				if(customCompute($headers)) {
					$row = 2;
					$column = "A";
					foreach($headers as $header) {
						$sheet->setCellValue($column.$row, $header);
		    		$column++;
					}
				}

				if(customCompute($bodys)) {
					$row = 3;
					foreach($bodys as $single_rows) {
						$column = 'A';
						foreach ($single_rows as $key => $value) {
							$sheet->setCellValue($column.$row, $value);
		    			$column++;
						}
						$row++;
					}
				}

				$grandTotalValue = $this->lang->line('transactionreport_grand_total') . (!empty($siteinfos->currency_code) ? "(".$siteinfos->currency_code.")" : '');
				$sheet->setCellValue('A'.($row-1), $grandTotalValue);


				$styleArray = [
				    'font' => [
				        'bold' => true,
				    ],
				    'alignment' =>[
				    	'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
				    	'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
				    ],
				    'borders' => [
			            'allBorders' => [
			                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
			            ]
			        ]
				];
				$sheet->getStyle('A1:K2')->applyFromArray($styleArray);

				$styleArray = [
				    'font' => [
				        'bold' => FALSE,
				    ],
				    'alignment' =>[
				    	'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
				    	'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
				    ],
				    'borders' => [
			            'allBorders' => [
			                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
			            ]
			        ]
				];
				$styleColumn = ($row-2);
				$sheet->getStyle('A3:K'.$styleColumn)->applyFromArray($styleArray);

				$styleArray = [
				    'font' => [
				        'bold' => TRUE,
				    ],
				    'alignment' =>[
				    	'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
				    	'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
				    ],
				    'borders' => [
			            'allBorders' => [
			                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
			            ]
			        ]
				];
				$styleColumn = $row-1;
				$sheet->getStyle('A'.$styleColumn.':K'.$styleColumn)->applyFromArray($styleArray);

				$startmerge = "A".($row-1);
				$endmerge = "H".($row-1);
				$sheet->mergeCells("$startmerge:$endmerge");

			}
		} elseif($xmloption == 2) {
			if(customCompute($incomes)) {
				$headers = [];
				$headers['name'] = $this->lang->line('transactionreport_name');
				$headers['date'] = $this->lang->line('transactionreport_date');
				$headers['amount'] = $this->lang->line('transactionreport_amount');


				$i = 0;
				$totalincome = 0;
				$bodys = array();

				foreach($incomes as $income) {
					$bodys[$i][] = $income->name;
					$bodys[$i][] = date('d M Y',strtotime($income->date));
					$amount = $income->amount;
					$bodys[$i][] = number_format($amount,2);
          $totalincome += $amount;
          $i++;
				}

				$bodys[$i][] = "";
				$bodys[$i][] = "";
				$bodys[$i][] = number_format($totalincome,2);

				$fromdateValue = $this->lang->line('transactionreport_fromdate')." : ".date('d M Y',$fromdate);
				$todateValue   = $this->lang->line('transactionreport_todate')." : ".date('d M Y',$todate);
				$sheet->setCellValue('A1', $fromdateValue);
				$sheet->setCellValue('C1', $todateValue);

				if(customCompute($headers)) {
					$column = "A";
					$row = 2;
					foreach($headers as $header) {
						$sheet->setCellValue($column.$row, $header);
		    		$column++;
					}
				}

				if(customCompute($bodys)) {
					$row = 3;
					foreach($bodys as $single_rows) {
						$column = 'A';
						foreach($single_rows as $value) {
							$sheet->setCellValue($column.$row, $value);
		    			$column++;
						}
						$row++;
					}
				}

				$styleArray = [
				    'font' => [
				        'bold' => TRUE,
				    ],
				    'alignment' =>[
				    	'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
				    	'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
				    ],
				    'borders' => [
			            'allBorders' => [
			                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
			            ]
			        ]
				];
				$sheet->getStyle('A1:C2')->applyFromArray($styleArray);

				$styleArray = [
				    'font' => [
				        'bold' => FALSE,
				    ],
				    'alignment' =>[
				    	'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
				    	'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
				    ],
				    'borders' => [
			            'allBorders' => [
			                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
			            ]
			        ]
				];
				$styleColumn = "C".($row-2);
				$sheet->getStyle('A3:'.$styleColumn)->applyFromArray($styleArray);

				$styleArray = [
				    'font' => [
				        'bold' => TRUE,
				    ],
				    'alignment' =>[
				    	'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
				    	'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
				    ],
				    'borders' => [
			            'allBorders' => [
			                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
			            ]
			        ]
				];
				$styleColumn = $row-1;
				$sheet->getStyle('A'.$styleColumn.':'.'C'.$styleColumn)->applyFromArray($styleArray);

				$grandValue = $this->lang->line('transactionreport_grand_total') . (!empty($siteinfos->currency_code) ? "(".$siteinfos->currency_code.")" : '');
				$sheet->setCellValue('A'.$styleColumn,$grandValue);

				$startmerge = "A".$styleColumn;
				$endmerge = "B".$styleColumn;
				$sheet->mergeCells("$startmerge:$endmerge");

			}
		} elseif($xmloption == 3) {
			if(customCompute($expenses)) {
				$headers = array();
				$headers[]  = $this->lang->line('transactionreport_name');
				$headers[]  = $this->lang->line('transactionreport_date');
				$headers[]  = $this->lang->line('transactionreport_amount');

				$i= 0;
				$totalexpense = 0;
				$bodys = array();
				foreach($expenses as $expense) {
					$bodys[$i][] = $expense->expense;
					$bodys[$i][] = date('d M Y',strtotime($expense->date));
					$amount = $expense->amount;
					$bodys[$i][] = number_format($amount,2);
          $totalexpense += $amount;
					$i++;
				}
				$bodys[$i][] = "";
				$bodys[$i][] = "";
				$bodys[$i][] = $totalexpense;

				$fromdateValue = $this->lang->line('transactionreport_fromdate')." : ".date('d M Y',$fromdate);
				$todateValue   = $this->lang->line('transactionreport_todate')." : ".date('d M Y',$todate);
				$sheet->setCellValue('A1', $fromdateValue);
				$sheet->setCellValue('C1', $todateValue);

				if(customCompute($headers)) {
					$column = "A";
					$row = 2;
					foreach($headers as $header) {
						$sheet->setCellValue($column.$row, $header);
		    		$column++;
					}
				}

				if(customCompute($bodys)) {
					$row = 3;
					foreach($bodys as $single_rows) {
						$column = 'A';
						foreach($single_rows as $value) {
							$sheet->setCellValue($column.$row, $value);
		    			$column++;
						}
						$row++;
					}
				}

				$grandTotalValue = $this->lang->line('transactionreport_grand_total') . (!empty($siteinfos->currency_code) ? "(".$siteinfos->currency_code.")" : '');

				$sheet->setCellValue('A'.($row-1), $grandTotalValue);

				$styleArray = [
				    'font' => [
				        'bold' => true,
				    ],
				    'alignment' =>[
				    	'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
				    	'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
				    ],
				    'borders' => [
			            'allBorders' => [
			                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
			            ]
			        ]
				];
				$sheet->getStyle('A1:C2')->applyFromArray($styleArray);

				$styleArray = [
				    'font' => [
				        'bold' => FALSE,
				    ],
				    'alignment' =>[
				    	'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
				    	'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
				    ],
				    'borders' => [
			            'allBorders' => [
			                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
			            ]
			        ]
				];
				$styleColumn = "C".($row-2);
				$sheet->getStyle('A3:'.$styleColumn)->applyFromArray($styleArray);

				$styleArray = [
				    'font' => [
				        'bold' => TRUE,
				    ],
				    'alignment' =>[
				    	'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
				    	'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
				    ],
				    'borders' => [
			            'allBorders' => [
			                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
			            ]
			        ]
				];
				$styleColumn = $row-1;
				$sheet->getStyle('A'.$styleColumn.':'.'C'.$styleColumn)->applyFromArray($styleArray);

				$startmerge = "A".($row-1);
				$endmerge = "B".($row-1);
				$sheet->mergeCells("$startmerge:$endmerge");
			}
		} elseif($xmloption == 4) {
			if(customCompute($salarys)) {
				$headers = array();
				$headers[]  = $this->lang->line('slno');
				$headers[]  = $this->lang->line('transactionreport_date');
				$headers[]  = $this->lang->line('transactionreport_name');
				$headers[]  = $this->lang->line('transactionreport_type');
				$headers[]  = $this->lang->line('transactionreport_month');
				$headers[]  = $this->lang->line('transactionreport_amount');

				$i= 0;
				$totalSalary = 0;
				$bodys = array();
				foreach($salarys as $salary) {
					$bodys[$i][] = $i+1;
					$bodys[$i][] = date('d M Y',strtotime($salary->create_date));
					$bodys[$i][] = isset($allUserName[$salary->usertypeID][$salary->userID]) ? $allUserName[$salary->usertypeID][$salary->userID]->name : '';
					$bodys[$i][] = isset($usertypes[$salary->usertypeID]) ? $usertypes[$salary->usertypeID] : '';
					$bodys[$i][] = date('F Y',strtotime('01-'.$salary->month));
					$bodys[$i][] = number_format($salary->payment_amount,2);
          $totalSalary += $salary->payment_amount;
					$i++;
				}
				$bodys[$i][] = "";
				$bodys[$i][] = "";
				$bodys[$i][] = "";
				$bodys[$i][] = "";
				$bodys[$i][] = "";
				$bodys[$i][] = number_format($totalSalary,2);

				$fromdateValue = $this->lang->line('transactionreport_fromdate')." : ".date('d M Y',$fromdate);
				$todateValue   = $this->lang->line('transactionreport_todate')." : ".date('d M Y',$todate);
				$sheet->setCellValue('A1', $fromdateValue);
				$sheet->setCellValue('F1', $todateValue);

				if(customCompute($headers)) {
					$column = "A";
					$row = 2;
					foreach($headers as $header) {
						$sheet->setCellValue($column.$row, $header);
		    		$column++;
					}
				}

				if(customCompute($bodys)) {
					$row = 3;
					foreach($bodys as $single_rows) {
						$column = 'A';
						foreach($single_rows as $value) {
							$sheet->setCellValue($column.$row, $value);
		    			$column++;
						}
						$row++;
					}
				}

				$grandTotalValue = $this->lang->line('transactionreport_grand_total') . (!empty($siteinfos->currency_code) ? "(".$siteinfos->currency_code.")" : '');

				$sheet->setCellValue('A'.($row-1), $grandTotalValue);

				$styleArray = [
				    'font' => [
				        'bold' => true,
				    ],
				    'alignment' =>[
				    	'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
				    	'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
				    ],
				    'borders' => [
			            'allBorders' => [
			                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
			            ]
			        ]
				];
				$sheet->getStyle('A1:F2')->applyFromArray($styleArray);

				$styleArray = [
				    'font' => [
				        'bold' => FALSE,
				    ],
				    'alignment' =>[
				    	'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
				    	'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
				    ],
				    'borders' => [
			            'allBorders' => [
			                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
			            ]
			        ]
				];

				$styleColumn = "F".($row-2);
				$sheet->getStyle('A3:'.$styleColumn)->applyFromArray($styleArray);

				$styleArray = [
				    'font' => [
				        'bold' => TRUE,
				    ],
				    'alignment' =>[
				    	'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
				    	'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
				    ],
				    'borders' => [
			            'allBorders' => [
			                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
			            ]
			        ]
				];
				$styleColumn = $row-1;
				$sheet->getStyle('A'.$styleColumn.':'.'F'.$styleColumn)->applyFromArray($styleArray);

				$startmerge = "A".($row-1);
				$endmerge = "E".($row-1);
				$sheet->mergeCells("$startmerge:$endmerge");
				$sheet->mergeCells("B1:E1");
			}
		} else {
			redirect('transactionreport');
		}
	}

	public function date_valid($date) {
		if($date) {
			if(strlen($date) < 10) {
				$this->form_validation->set_message("date_valid", "The %s is not valid dd-mm-yyyy");
		    return FALSE;
			} else {
	   		$arr = explode("-", $date);
        $dd = $arr[0];
        $mm = $arr[1];
        $yyyy = $arr[2];
      	if(checkdate($mm, $dd, $yyyy)) {
      		return TRUE;
      	} else {
      		$this->form_validation->set_message("date_valid", "The %s is not valid dd-mm-yyyy");
     		  return FALSE;
      	}
	    }
		}
		return TRUE;
	}

	public function unique_date() {
		$fromdate = $this->input->post('fromdate');
		$todate   = $this->input->post('todate');

		$startingdate = $this->data['schoolyearsessionobj']->startingdate;
		$endingdate = $this->data['schoolyearsessionobj']->endingdate;

		if($fromdate != '' && $todate != '') {
			if(strtotime($fromdate) > strtotime($todate)) {
				$this->form_validation->set_message("unique_date", "The from date can not be upper than todate .");
		   	return FALSE;
			}
			if((strtotime($fromdate) < strtotime($startingdate)) || (strtotime($fromdate) > strtotime($endingdate))) {
				$this->form_validation->set_message("unique_date", "The from date are invalid .");
			  return FALSE;
			}
			if((strtotime($todate) < strtotime($startingdate)) || (strtotime($todate) > strtotime($endingdate))) {
				$this->form_validation->set_message("unique_date", "The to date are invalid .");
			  return FALSE;
			}
			return TRUE;
		}
		return TRUE;
	}

	public function date_valid_new($date) {
		$date = date('d-m-Y',$date);
		if($date) {
			if(strlen($date) < 10) {
				$this->form_validation->set_message("date_valid_new", "The %s is not valid dd-mm-yyyy");
		    return FALSE;
			} else {
	   		$arr = explode("-", $date);
        $dd = $arr[0];
        $mm = $arr[1];
        $yyyy = $arr[2];
      	if(checkdate($mm, $dd, $yyyy)) {
      		return TRUE;
      	} else {
      		$this->form_validation->set_message("date_valid_new", "The %s is not valid dd-mm-yyyy");
     		return FALSE;
      	}
	    }
		}
		return TRUE;
	}

	public function unique_date_new() {
		$fromdate = date('d-m-Y',$this->input->post('fromdate'));
		$todate   = date('d-m-Y',$this->input->post('todate'));

		$startingdate = $this->data['schoolyearsessionobj']->startingdate;
		$endingdate = $this->data['schoolyearsessionobj']->endingdate;

		if($fromdate != '' && $todate != '') {
			if(strtotime($fromdate) > strtotime($todate)) {
				$this->form_validation->set_message("unique_date_new", "The from date can not be upper than todate .");
		   	return FALSE;
			}
			if((strtotime($fromdate) < strtotime($startingdate)) || (strtotime($fromdate) > strtotime($endingdate))) {
				$this->form_validation->set_message("unique_date_new", "The from date is invalid .");
			  return FALSE;
			}
			if((strtotime($todate) < strtotime($startingdate)) || (strtotime($todate) > strtotime($endingdate))) {
				$this->form_validation->set_message("unique_date_new", "The to date is invalid .");
			  return FALSE;
			}
			return TRUE;
		}
		return TRUE;
	}

}
