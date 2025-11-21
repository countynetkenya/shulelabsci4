<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Salereport extends Admin_Controller {
	public function __construct() {
		parent::__construct();

    $this->load->model('usertype_m');
    $this->load->model('systemadmin_m');
    $this->load->model('teacher_m');
    $this->load->model("studentrelation_m");
    $this->load->model('parents_m');
    $this->load->model('user_m');
    $this->load->model("productcategory_m");
    $this->load->model("product_m");
    $this->load->model('productsale_m');
    $this->load->model("productsaleitem_m");
    $this->load->model("productsalepaid_m");
    $this->load->model("productpurchaseitem_m");
		$this->load->model("productwarehouse_m");
		$this->load->model("schoolterm_m");

    $language = $this->session->userdata('lang');
		$this->lang->load('salereport', $language);
	}

	public function rules() {
		$rules = array(
					array(
								'field' => 'productID',
								'label' => $this->lang->line('productsaleitemreport_productID'),
								'rules' => 'trim|xss_clean'
					),
					array(
								'field' => 'productname',
								'label' => $this->lang->line('productsaleitemreport_product'),
								'rules' => 'trim|xss_clean'
					),
					array(
								'field' => 'productwarehouseID',
								'label' => $this->lang->line('productsaleitemreport_productwarehousename'),
								'rules' => 'trim|xss_clean'
					),
					array(
								'field' => 'productcategoryID',
								'label' => $this->lang->line('productsaleitemreport_productcategoryname'),
								'rules' => 'trim|xss_clean'
					),
	        array(
                'field' => 'productsalecustomertypeID',
                'label' => $this->lang->line('productsaleitemreport_productsalecustomertype'),
                'rules' => 'trim|xss_clean'
	        ),
          array(
                'field' => 'productsalecustomerID',
                'label' => $this->lang->line('productsaleitemreport_productsalecustomerName'),
                'rules' => 'trim|xss_clean'
	        ),
          array(
              'field' => 'fromdate',
              'label' => $this->lang->line('productsaleitemreport_fromdate'),
              'rules' => 'trim|xss_clean|callback_date_valid|callback_unique_date'
          ),
          array(
              'field' => 'todate',
              'label' => $this->lang->line('productsaleitemreport_todate'),
              'rules' => 'trim|xss_clean|callback_date_valid'
          ),
					array(
              'field' => 'reportdetails',
              'label' => $this->lang->line('salereport_reportdetails'),
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
    $this->data['usertypes'] = $this->usertype_m->get_order_by_usertype_with_or(array('schoolID' => $schoolID));
		$this->data['products'] = $this->product_m->get_order_by_product(array('schoolID' => $schoolID));
		$this->data['productcategories'] = $this->productcategory_m->get_order_by_productcategory(array('schoolID' => $schoolID));
		$this->data['warehouses'] = $this->productwarehouse_m->get_order_by_productwarehouse(array('schoolID' => $schoolID));
    $this->data["subview"] = "report/sale/SaleReportView";
		$this->load->view('_layout_main', $this->data);
	}

    public function getSaleReport() {
		$retArray['status'] = FALSE;
		$retArray['render'] = '';
		if(permissionChecker('salereport')) {
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
					$this->data['productwarehouseID'] = $this->input->post('productwarehouseID');
					$this->data['productcategoryID'] = $this->input->post('productcategoryID');
          $this->data['productsalecustomertypeID'] = $this->input->post('productsalecustomertypeID');
          $this->data['productsalecustomerID'] = $this->input->post('productsalecustomerID');
          $this->data['fromdate'] = $this->input->post('fromdate');
					$this->data['todate'] = $this->input->post('todate');
					$this->data['reportdetails'] = $this->input->post('reportdetails');
					$this->data['columns'] = $this->input->post('columns');

          $usertypes = pluck($this->usertype_m->get_order_by_usertype_with_or(array('schoolID' => $schoolID)), 'usertype', 'usertypeID');
					$schools = pluck($this->school_m->get_order_by_school(array('schoolID !=' => 0)), 'name', 'schoolID');
					$warehouses = pluck($this->productwarehouse_m->get_order_by_productwarehouse(array('schoolID' => $schoolID)), 'productwarehousename', 'productwarehouseID');
					$productcategories = pluck($this->productcategory_m->get_order_by_productcategory(array('schoolID' => $schoolID)), 'productcategoryname', 'productcategoryID');

					$this->data['usertypes'] = $usertypes;
          $users = $this->getuserlist($_POST);
          $this->data['users'] = $users;
					$productsaleitems = $this->productsaleitem_m->get_all_productsaleitem_for_report($this->input->post());

          /*$productsalepaidsArray = [];
          $productsalepaids = $this->productsalepaid_m->get_order_by_productsalepaid(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
          if(customCompute($productsalepaids)) {
              foreach($productsalepaids as $productsalepaid) {
                  if(isset($productsalepaidsArray[$productsalepaid->productsaleID])) {
                      $productsalepaidsArray[$productsalepaid->productsaleID] += $productsalepaid->productsalepaidamount;
                  } else {
                      $productsalepaidsArray[$productsalepaid->productsaleID] = $productsalepaid->productsalepaidamount;
                  }
              }
          }*/

          $productArray = [];
          //$totalproductsaleprice = 0;
          //$totalproductsalepaidamount = 0;
          //$totalproductsalebalanceamount = 0;

          /*$lCheck = FALSE;
          if($this->data['productsalecustomertypeID'] == 3) {
              $lCheck = TRUE;
          }*/

          if(customCompute($productsaleitems)) {
              foreach($productsaleitems as $product) {
                  /*if($lCheck) {
                      $classesID = (int)$this->data['productsaleclassesID'];
                      if($classesID) {
                          if(!(isset($users[3][$product->productsalecustomerID]) && ($users[3][$product->productsalecustomerID]->srclassesID == $classesID))) {
                              continue;
                          }
                      }
                  }*/

									if($this->input->post('reportdetails') == "transaction") {
											$productArray[$product->productsaleID]['productsalereferenceno'] = $product->productsalereferenceno;
											$productArray[$product->productsaleID]['month'] = date('F', strtotime($product->productsaledate));
											$productArray[$product->productsaleID]['year'] = date('Y', strtotime($product->productsaledate));
											$productArray[$product->productsaleID]['dayofweek'] = date('l', strtotime($product->productsaledate));
											$result = $this->schoolterm_m->get_single_schoolterm(array('startingdate <=' => $product->productsaledate, 'endingdate >=' => $product->productsaledate, 'schoolID' => $schoolID));
											$productArray[$product->productsaleID]['term'] = $result->schooltermtitle;

											if(isset($productArray[$product->productsaleID]['productsalequantity']))
													$productArray[$product->productsaleID]['productsalequantity'] += $product->productsalequantity;
											else
													$productArray[$product->productsaleID]['productsalequantity'] = $product->productsalequantity;
											if(isset($productArray[$product->productsaleID]['productsaleunitprice']))
													$productArray[$product->productsaleID]['productsaleunitprice'] += $product->productsaleunitprice;
											else
													$productArray[$product->productsaleID]['productsaleunitprice'] = $product->productsaleunitprice;
											$result = $this->productpurchaseitem_m->get_average_unit_cost(array('productID' => $product->productID, 'schoolID' => $schoolID));
											if(isset($productArray[$product->productsaleID]['averageunitprice']))
													$productArray[$product->productsaleID]['averageunitprice'] += $result->averageunitprice;
											else
													$productArray[$product->productsaleID]['averageunitprice'] = $result->averageunitprice;
											$productArray[$product->productsaleID]['averageunitprice'] = $result->averageunitprice;
											if(isset($productArray[$product->productsaleID]['margin']))
													$productArray[$product->productsaleID]['margin'] += $product->productsaleunitprice - $result->averageunitprice;
											else
													$productArray[$product->productsaleID]['margin'] = $product->productsaleunitprice - $result->averageunitprice;
											if(isset($productArray[$product->productsaleID]['totalprice']))
													$productArray[$product->productsaleID]['totalprice'] += $product->productsaleunitprice * $product->productsalequantity;
											else
													$productArray[$product->productsaleID]['totalprice'] = $product->productsaleunitprice * $product->productsalequantity;
											if(isset($productArray[$product->productsaleID]['totalcost']))
													$productArray[$product->productsaleID]['totalcost'] += $result->averageunitprice * $product->productsalequantity;
											else
													$productArray[$product->productsaleID]['totalcost'] = $result->averageunitprice * $product->productsalequantity;
											if(isset($productArray[$product->productsaleID]['totalcost']))
													$productArray[$product->productsaleID]['totalmargin'] += ($product->productsaleunitprice - $result->averageunitprice) * $product->productsalequantity;
											else
													$productArray[$product->productsaleID]['totalmargin'] = ($product->productsaleunitprice - $result->averageunitprice) * $product->productsalequantity;
											try {
													if(isset($productArray[$product->productsaleID]['marginpercentage']))
															$productArray[$product->productsaleID]['marginpercentage'] += ($product->productsaleunitprice - $result->averageunitprice) / $product->productsaleunitprice * 100;
													else
															$productArray[$product->productsaleID]['marginpercentage'] = ($product->productsaleunitprice - $result->averageunitprice) / $product->productsaleunitprice * 100;
											} catch (DivisionByZeroError $e) {}
									} elseif($this->input->post('reportdetails') == "summary") {
		                  $productArray[$product->productsaleitemID]['productsaleID'] = $product->productsaleitemID;
		                  $productArray[$product->productsaleitemID]['productsalecustomertype'] = isset($usertypes[$product->productsalecustomertypeID]) ? $usertypes[$product->productsalecustomertypeID] : ($product->productsalecustomertypeID == "school" ? 'School' : '');

		                  $name = '';
		                  if(isset($users[$product->productsalecustomertypeID][$product->productsalecustomerID])) {
		                      $name = isset($users[$product->productsalecustomertypeID][$product->productsalecustomerID]->name) ? $users[$product->productsalecustomertypeID][$product->productsalecustomerID]->name : $users[$product->productsalecustomertypeID][$product->productsalecustomerID]->srname;
		                  } elseif($product->productsalecustomertypeID == "school") {
													$name = $schools[$product->productsalecustomerID];
											}
		                  $productArray[$product->productsaleitemID]['productsalecustomerName'] = $name;
											$productArray[$product->productsaleitemID]['productID'] = $product->productID;
											$productArray[$product->productsaleitemID]['productdesc'] = $product->productdesc;
											$productArray[$product->productsaleitemID]['productsalequantity'] = $product->productsalequantity;
											$productArray[$product->productsaleitemID]['productsaleunitprice'] = $product->productsaleunitprice;
											$result = $this->productpurchaseitem_m->get_average_unit_cost(array('productID' => $product->productID, 'schoolID' => $schoolID));
											$productArray[$product->productsaleitemID]['averageunitprice'] = $result->averageunitprice;
											$productArray[$product->productsaleitemID]['margin'] = $product->productsaleunitprice - $result->averageunitprice;
											$productArray[$product->productsaleitemID]['totalprice'] = $product->productsaleunitprice * $product->productsalequantity;
											$productArray[$product->productsaleitemID]['totalcost'] = $result->averageunitprice * $product->productsalequantity;
											$productArray[$product->productsaleitemID]['totalmargin'] = ($product->productsaleunitprice - $result->averageunitprice) * $product->productsalequantity;
											try {
													$productArray[$product->productsaleitemID]['marginpercentage'] = ($product->productsaleunitprice - $result->averageunitprice) / $product->productsaleunitprice * 100;
											} catch (DivisionByZeroError $e) {}
											$productArray[$product->productsaleitemID]['productwarehousename'] = $warehouses[$product->productwarehouseID];
											$productArray[$product->productsaleitemID]['productcategoryname'] = $productcategories[$product->productcategoryID];
		                	$productArray[$product->productsaleitemID]['productsaledate'] = date('d M Y', strtotime($product->productsaledate));
											$productArray[$product->productsaleitemID]['month'] = date('F', strtotime($product->productsaledate));
											$productArray[$product->productsaleitemID]['year'] = date('Y', strtotime($product->productsaledate));
											$productArray[$product->productsaleitemID]['dayofweek'] = date('l', strtotime($product->productsaledate));
											$result = $this->schoolterm_m->get_single_schoolterm(array('startingdate <=' => $product->productsaledate, 'endingdate >=' => $product->productsaledate, 'schoolID' => $schoolID));
											$productArray[$product->productsaleitemID]['term'] = $result->schooltermtitle;
											$productArray[$product->productsaleitemID]['productsaleprice'] = ($product->productsaleunitprice * $product->productsalequantity);
									}
              }
          }

          $this->data['productsaleitems'] = $productArray;

					$retArray['render'] = $this->load->view('report/sale/SaleReport',$this->data,true);
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


    private function getuserlist($queryArray) {
				$schoolID = $this->session->userdata('schoolID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $retArray = [];

        $systemadmins = $this->systemadmin_m->get_order_by_systemadmin(array('schoolID' => $schoolID));
        if(customCompute($systemadmins)) {
            foreach ($systemadmins as $systemadmin) {
                $retArray[1][$systemadmin->systemadminID] = $systemadmin;
            }
        }

        $teachers = $this->teacher_m->general_get_order_by_teacher(array('schoolID' => $schoolID));
        if(customCompute($teachers)) {
            foreach ($teachers as $teacher) {
                $retArray[2][$teacher->teacherID] = $teacher;
            }
        }

        $sArray = [];
				$sArray['srschoolID'] = $schoolID;
        $sArray['srschoolyearID'] = $schoolyearID;
        if(isset($queryArray['productsalecustomertypeID']) && $queryArray['productsalecustomertypeID'] == 3) {
            if(isset($queryArray['productsaleclassesID']) && (int)$queryArray['productsaleclassesID']) {
                $sArray['srclassesID'] = $queryArray['productsaleclassesID'];
            }

            if(isset($queryArray['productsalecustomerID']) && (int)$queryArray['productsalecustomerID']) {
                $sArray['srstudentID'] = $queryArray['productsalecustomerID'];
            }
        }

        $students = $this->studentrelation_m->get_order_by_studentrelation($sArray);
        if(customCompute($students)) {
            foreach ($students as $student) {
                $retArray[3][$student->srstudentID] = $student;
            }
        }

        $parentss = $this->parents_m->get_order_by_parents(array('schoolID' => $schoolID));
        if(customCompute($parentss)) {
            foreach ($parentss as $parents) {
                $retArray[4][$parents->parentsID] = $parents;
            }
        }

        $users = $this->user_m->get_order_by_user(array('schoolID' => $schoolID));
        if(customCompute($users)) {
            foreach ($users as $user) {
                $retArray[$user->usertypeID][$user->userID] = $user;
            }
        }

        return $retArray;
    }

    public function getuser() {
        $productsalecustomertypeID = $this->input->post('productsalecustomertypeID');
				$schoolID = $this->session->userdata('schoolID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');

        echo "<option value=\"0\">",$this->lang->line('productsalereport_please_select'),"</option>";
        if((int)$productsalecustomertypeID) {
            if($productsalecustomertypeID == 1) {
                $systemadmins = $this->systemadmin_m->get_order_by_systemadmin(array('schoolID' => $schoolID));
                if(customCompute($systemadmins)) {
                    foreach ($systemadmins as $systemadmin) {
                        echo "<option value=\"$systemadmin->systemadminID\">",$systemadmin->name,"</option>";
                    }
                }
            } elseif($productsalecustomertypeID == 2) {
                $teachers = $this->teacher_m->general_get_order_by_teacher(array('schoolID' => $schoolID));
                if(customCompute($teachers)) {
                    foreach ($teachers as $teacher) {
                        echo "<option value=\"$teacher->teacherID\">",$teacher->name,"</option>";
                    }
                }
            } elseif($productsalecustomertypeID == 3) {
                $classesID = $this->input->post('productsaleclassesID');
                if((int)$classesID) {
                    $students = $this->studentrelation_m->get_order_by_studentrelation(array('srschoolyearID' => $schoolyearID, 'srclassesID' => $classesID, 'srschoolID' => $schoolID));
                    if(customCompute($students)) {
                        foreach ($students as $student) {
                            echo "<option value=\"$student->srstudentID\">".$student->srname." - ".$this->lang->line('productsalereport_roll')." - ".$student->srroll."</option>";
                        }
                    }
                }
            } elseif($productsalecustomertypeID == 4) {
                $parentss = $this->parents_m->get_order_by_parents(array('schoolID' => $schoolID));
                if(customCompute($parentss)) {
                    foreach ($parentss as $parents) {
                        echo "<option value=\"$parents->parentsID\">",$parents->name,"</option>";
                    }
                }
            } else {
                $users = $this->user_m->get_order_by_user(array('usertypeID' => $productsalecustomertypeID, 'schoolID' => $schoolID));
                if(customCompute($users)) {
                    foreach ($users as $user) {
                        echo "<option value=\"$user->userID\">",$user->name,"</option>";
                    }
                }
            }
        }
    }



}
