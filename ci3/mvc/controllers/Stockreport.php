<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Stockreport extends Admin_Controller {
	public function __construct() {
		parent::__construct();

		$this->load->model("stock_m");
		$this->load->model("product_m");
		$this->load->model("productwarehouse_m");
    $this->load->model("schoolterm_m");

    $language = $this->session->userdata('lang');
		$this->lang->load('stockreport', $language);
	}

	public function rules() {
		$rules = array(
				array(
							'field' => 'type',
							'label' => $this->lang->line('stockreport_type'),
							'rules' => 'trim|xss_clean'
				),
				array(
							'field' => 'term',
							'label' => $this->lang->line('stockreport_term'),
							'rules' => 'trim|xss_clean'
				),
				array(
							'field' => 'year',
							'label' => $this->lang->line('stockreport_year'),
							'rules' => 'trim|xss_clean'
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
		$this->data['schoolterms'] = $this->schoolterm_m->get_order_by_schoolterm(array('schoolID' => $schoolID));
    $this->data["subview"] = "report/stock/StockReportView";
		$this->load->view('_layout_main', $this->data);
	}

  public function getStockReport() {
		$retArray['status'] = FALSE;
		$retArray['render'] = '';
		if(permissionChecker('stockreport')) {
			if($_POST) {
				$rules = $this->rules();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {
					$retArray = $this->form_validation->error_array();
			    $retArray['status'] = FALSE;
			    echo json_encode($retArray);
			    exit;
				} else {
					$schoolID = $this->session->userdata('schoolID');
					$this->data['schooltermID'] = $this->input->post('schooltermID');
					$this->data['year'] = $this->input->post('year');
					$schoolterm = $this->schoolterm_m->get_single_schoolterm(array('schooltermID' => $this->input->post('schooltermID'), 'schoolID' => $schoolID));
					if(customCompute($schoolterm)) {
						$_POST['fromdate'] = $schoolterm->startingdate;
						$_POST['todate'] = $schoolterm->endingdate;
					}

					$this->data['products']          = pluck($this->product_m->get_order_by_product(array('schoolID' => $schoolID)), 'productname', 'productID');
	        $this->data['productwarehouses'] = pluck($this->productwarehouse_m->get_order_by_productwarehouse(array('schoolID' => $schoolID)), 'productwarehousename', 'productwarehouseID');
					$this->data['stocks'] = $this->stock_m->get_all_stock_for_report($this->input->post());

					$retArray['render'] = $this->load->view('report/stock/StockReport',$this->data,true);
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

  public function date_valid($date) {
      if($date) {
          if(strlen($date) < 10) {
              $this->form_validation->set_message("date_valid", "%s is not valid dd-mm-yyyy.");
              return FALSE;
          } else {
              $arr = explode("-", $date);
              $dd = $arr[0];
              $mm = $arr[1];
              $yyyy = $arr[2];
              if(checkdate($mm, $dd, $yyyy)) {
                  return TRUE;
              } else {
                  $this->form_validation->set_message("date_valid", "%s is not valid dd-mm-yyyy.");
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

      if($fromdate != '' && $todate == '') {
          $this->form_validation->set_message("unique_date", "The to date field not be empty .");
          return FALSE;
      }

      if($fromdate == '' && $todate != '') {
          $this->form_validation->set_message("unique_date", "The from date field not be empty .");
          return FALSE;
      }

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

}
