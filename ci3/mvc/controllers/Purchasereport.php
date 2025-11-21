<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Purchasereport extends Admin_Controller {
	public function __construct() {
		parent::__construct();

    $this->load->model("productwarehouse_m");
    $this->load->model("productsupplier_m");
    $this->load->model("productpurchase_m");
    $this->load->model("productpurchaseitem_m");
    $this->load->model("productpurchasepaid_m");
		$this->load->model("product_m");
		$this->load->model("productcategory_m");
		$this->load->model("schoolterm_m");
    $language = $this->session->userdata('lang');
		$this->lang->load('purchasereport', $language);
	}

	public function rules() {
		$rules = array(
					array(
								'field' => 'productID',
								'label' => $this->lang->line('purchasereport_productID'),
								'rules' => 'trim|xss_clean'
					),
					array(
								'field' => 'productdesc',
								'label' => $this->lang->line('purchasereport_productdesc'),
								'rules' => 'trim|xss_clean'
					),
					array(
								'field' => 'productcategoryID',
								'label' => $this->lang->line('productpurchasereport_category'),
								'rules' => 'trim|xss_clean'
					),
	        array(
	                'field' => 'productsupplierID',
	                'label' => $this->lang->line('productpurchasereport_supplier'),
	                'rules' => 'trim|xss_clean'
	        ),
          array(
	                'field' => 'productwarehouseID',
	                'label' => $this->lang->line('productpurchasereport_warehouse'),
	                'rules' => 'trim|xss_clean'
	        ),
          array(
	                'field' => 'reference_no',
	                'label' => $this->lang->line('productpurchasereport_referenceNo'),
	                'rules' => 'trim|xss_clean|callback_unique_data'
	        ),
          array(
	                'field' => 'fromdate',
	                'label' => $this->lang->line('productpurchasereport_fromdate'),
	                'rules' => 'trim|xss_clean|callback_date_valid|callback_unique_date'
	        ),
	        array(
	                'field' => 'todate',
	                'label' => $this->lang->line('productpurchasereport_todate'),
	                'rules' => 'trim|xss_clean|callback_date_valid'
	        ),
					array(
              'field' => 'reportdetails',
              'label' => $this->lang->line('purchasereport_reportdetails'),
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
    $this->data['productwarehouses'] = $this->productwarehouse_m->get_order_by_productwarehouse(array('schoolID' => $schoolID));
    $this->data['productsuppliers'] = $this->productsupplier_m->get_order_by_productsupplier(array('schoolID' => $schoolID));
		$this->data['products'] = $this->product_m->get_order_by_product(array('schoolID' => $schoolID));
		$this->data['productcategories'] = $this->productcategory_m->get_order_by_productcategory(array('schoolID' => $schoolID));
		$this->data["subview"] = "report/purchase/PurchaseReportView";
		$this->load->view('_layout_main', $this->data);
	}

  public function unique_data($data) {
      if($data != "") {
          if($data == "0") {
              $this->form_validation->set_message('unique_data', 'The %s field value invalid.');
              return FALSE;
          }
          return TRUE;
      }
      return TRUE;
  }

	public function getPurchaseReport() {
		$retArray['status'] = FALSE;
		$retArray['render'] = '';
		if(permissionChecker('purchasereport')) {
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
          $schoolyearID = $this->session->userdata('defaultschoolyearID');
					$this->data['productID'] = $this->input->post('productID');
					$this->data['productname'] = $this->input->post('productname');
					$this->data['productcategoryID'] = $this->input->post('productcategoryID');
          $this->data['productsupplierID'] = $this->input->post('productsupplierID');
          $this->data['productwarehouseID'] = $this->input->post('productwarehouseID');
          $this->data['reference_no'] = !empty($this->input->post('reference_no')) ? $this->input->post('reference_no') : '0';
          $this->data['fromdate'] = $this->input->post('fromdate');
					$this->data['todate'] = $this->input->post('todate');
					$this->data['reportdetails'] = $this->input->post('reportdetails');
					$this->data['columns'] = $this->input->post('columns');

          $productsuppliers = pluck($this->productsupplier_m->get_order_by_productsupplier(array('schoolID' => $schoolID)), 'productsuppliercompanyname', 'productsupplierID');
          $productwarehouses = pluck($this->productwarehouse_m->get_order_by_productwarehouse(array('schoolID' => $schoolID)), 'productwarehousename', 'productwarehouseID');
					$productcategories = pluck($this->productcategory_m->get_order_by_productcategory(array('schoolID' => $schoolID)), 'productcategoryname', 'productcategoryID');

          $this->data['productsuppliers'] = $productsuppliers;
          $this->data['productwarehouses'] = $productwarehouses;

					$productpurchases = $this->productpurchaseitem_m->get_all_productpurchase_for_report($this->input->post());

          $productpurchaseArray = [];
          /*$totalproductpurchaseprice = 0;
          $totalproductpurchasepaidamount = 0;
          $totalproductpurchasebalanceamount = 0;*/

          if(customCompute($productpurchases)) {
              foreach ($productpurchases as $productpurchase) {
									if($this->input->post('reportdetails') == "transaction") {
											$productpurchaseArray[$productpurchase->productpurchaseID]['reference_no'] = $productpurchase->productpurchasereferenceno;
											$productpurchaseArray[$productpurchase->productpurchaseID]['supplier'] = isset($productsuppliers[$productpurchase->productsupplierID]) ? $productsuppliers[$productpurchase->productsupplierID] : '';
											$productpurchaseArray[$productpurchase->productpurchaseID]['warehouse'] = isset($productwarehouses[$productpurchase->productwarehouseID]) ? $productwarehouses[$productpurchase->productwarehouseID] : '';
											$productpurchaseArray[$productpurchase->productpurchaseID]['month'] = date('F', strtotime($productpurchase->productpurchasedate));
											$productpurchaseArray[$productpurchase->productpurchaseID]['year'] = date('Y', strtotime($productpurchase->productpurchasedate));
											$productpurchaseArray[$productpurchase->productpurchaseID]['dayofweek'] = date('l', strtotime($productpurchase->productpurchasedate));
											$result = $this->schoolterm_m->get_single_schoolterm(array('startingdate <=' => $productpurchase->productpurchasedate, 'endingdate >=' => $productpurchase->productpurchasedate, 'schoolID' => $schoolID));
											$productpurchaseArray[$productpurchase->productpurchaseID]['term'] = $result->schooltermtitle;

											if(isset($productpurchaseArray[$productpurchase->productpurchaseID]['productpurchasequantity']))
													$productpurchaseArray[$productpurchase->productpurchaseID]['productpurchasequantity'] += $productpurchase->productpurchasequantity;
											else
													$productpurchaseArray[$productpurchase->productpurchaseID]['productpurchasequantity'] = $productpurchase->productpurchasequantity;
											$result = $this->productpurchaseitem_m->get_average_unit_cost(array('productID' => $productpurchase->productID, 'schoolID' => $schoolID));
											if(isset($productpurchaseArray[$productpurchase->productpurchaseID]['averageunitprice']))
													$productpurchaseArray[$productpurchase->productpurchaseID]['averageunitprice'] += $result->averageunitprice;
											else
													$productpurchaseArray[$productpurchase->productpurchaseID]['averageunitprice'] = $result->averageunitprice;
											if(isset($productpurchaseArray[$productpurchase->productpurchaseID]['totalcost']))
													$productpurchaseArray[$productpurchase->productpurchaseID]['totalcost'] += $result->averageunitprice * $productpurchase->productpurchasequantity;
											else
													$productpurchaseArray[$productpurchase->productpurchaseID]['totalcost'] = $result->averageunitprice * $productpurchase->productpurchasequantity;
									} elseif($this->input->post('reportdetails') == "summary") {
											$productpurchaseArray[$productpurchase->productpurchaseitemID]['productpurchaseID'] = $productpurchase->productpurchaseitemID;
											$productpurchaseArray[$productpurchase->productpurchaseitemID]['productID'] = $productpurchase->productID;
											$productpurchaseArray[$productpurchase->productpurchaseitemID]['productdesc'] = $productpurchase->productdesc;
											$productpurchaseArray[$productpurchase->productpurchaseitemID]['supplier'] = isset($productsuppliers[$productpurchase->productsupplierID]) ? $productsuppliers[$productpurchase->productsupplierID] : '';
											$productpurchaseArray[$productpurchase->productpurchaseitemID]['warehouse'] = isset($productwarehouses[$productpurchase->productwarehouseID]) ? $productwarehouses[$productpurchase->productwarehouseID] : '';
											$productpurchaseArray[$productpurchase->productpurchaseitemID]['category'] = $productcategories[$productpurchase->productcategoryID];
											$productpurchaseArray[$productpurchase->productpurchaseitemID]['month'] = date('F', strtotime($productpurchase->productpurchasedate));
											$productpurchaseArray[$productpurchase->productpurchaseitemID]['year'] = date('Y', strtotime($productpurchase->productpurchasedate));
											$productpurchaseArray[$productpurchase->productpurchaseitemID]['dayofweek'] = date('l', strtotime($productpurchase->productpurchasedate));
											$result = $this->schoolterm_m->get_single_schoolterm(array('startingdate <=' => $productpurchase->productpurchasedate, 'endingdate >=' => $productpurchase->productpurchasedate, 'schoolID' => $schoolID));
											$productpurchaseArray[$productpurchase->productpurchaseitemID]['term'] = $result->schooltermtitle;
											$productpurchaseArray[$productpurchase->productpurchaseitemID]['productpurchasequantity'] = $productpurchase->productpurchasequantity;
											$result = $this->productpurchaseitem_m->get_average_unit_cost(array('productID' => $productpurchase->productID, 'schoolID' => $schoolID));
											$productpurchaseArray[$productpurchase->productpurchaseitemID]['averageunitprice'] = $result->averageunitprice;
											$productpurchaseArray[$productpurchase->productpurchaseitemID]['totalcost'] = $result->averageunitprice * $productpurchase->productpurchasequantity;
									}

              }
          }

          /*$this->data['totalproductpurchaseprice'] = $totalproductpurchaseprice;
          $this->data['totalproductpurchasepaidamount'] = $totalproductpurchasepaidamount;
          $this->data['totalproductpurchasebalanceamount'] = $totalproductpurchasebalanceamount;*/
          $this->data['productpurchaseitems'] = $productpurchaseArray;
					$retArray['render'] = $this->load->view('report/purchase/PurchaseReport',$this->data,true);
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
