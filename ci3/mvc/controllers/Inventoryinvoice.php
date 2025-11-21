<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH . 'libraries/PaymentGateway/PaymentGateway.php');
require_once(APPPATH . '../vendor/autoload.php');
use Mike42\Escpos\Printer;
use Mike42\Escpos\ImagickEscposImage;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;

class Inventoryinvoice extends Admin_Controller {
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
        $this->load->model('usertype_m');
        $this->load->model('paymenttypes_m');
        $this->load->model('classes_m');
        $this->load->model('systemadmin_m');
        $this->load->model('teacher_m');
        $this->load->model('student_m');
        $this->load->model("studentrelation_m");
        $this->load->model('parents_m');
        $this->load->model('user_m');
        $this->load->model("productcategory_m");
        $this->load->model("product_m");
        $this->load->model('productsale_m');
        $this->load->model("productsaleitem_m");
        $this->load->model("productsalepaid_m");
        $this->load->model("productpurchaseitem_m");
        $this->load->model("mainmpesa_m");
        $this->load->model("mpesa_m");
        $this->load->model("productwarehouse_m");
        $this->load->model("schoolterm_m");
        $this->load->model("maininvoice_m");
        $this->load->model("invoice_m");
        $this->load->helper('inventory');
        $language = $this->session->userdata('lang');
        $this->lang->load('inventoryinvoice', $language);
        $this->payment_gateway       = new PaymentGateway();
    }

    public function index() {
        $this->data['headerassets'] = array(
            'css' => array(
                'assets/datepicker/datepicker.css',
            ),
            'js' => array(
                'assets/datepicker/datepicker.js',
            )
        );

        $schoolID = $this->session->userdata('schoolID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $this->data['usertypes'] = pluck($this->usertype_m->get_order_by_usertype_with_or(array('schoolID' => $schoolID)), 'usertype', 'usertypeID');
        $this->data['users'] = $this->getuserlist();
        $this->data['schools'] = pluck($this->school_m->get_order_by_school(), 'name', 'schoolID');
        $this->data['paymentmethods'] = $this->paymenttypes_m->get_order_by_paymenttypes(array('schoolID' => $schoolID));
        $this->data['productsales'] = $this->productsale_m->get_order_by_productsale(array('productsalecustomertypeID' => 3, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
        $this->data['grandtotalandpaid'] = $this->grandtotalandpaid($this->data['productsales'], $schoolyearID, $schoolID);
        $this->data["subview"] = "inventoryinvoice/index";
        $this->load->view('_layout_main', $this->data);
    }

    private function getuserlist() {
        $schoolID = $this->session->userdata('schoolID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $retArray = [];

        $systemadmins = $this->systemadmin_m->get_order_by_systemadmin(array('schoolID' => $schoolID));
        if(customCompute($systemadmins)) {
            foreach ($systemadmins as $systemadmin) {
                $retArray[1][$systemadmin->systemadminID] = $systemadmin;
            }
        }

        $teachers = $this->teacher_m->get_order_by_teacher(array('schoolID' => $schoolID));
        if(customCompute($teachers)) {
            foreach ($teachers as $teacher) {
                $retArray[2][$teacher->teacherID] = $teacher;
            }
        }

        $students = $this->studentrelation_m->get_order_by_studentrelation(array('srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID));
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

    private function grandtotalandpaid($productsales, $schoolyearID, $schoolID) {
        $retArray = [];

        $productsaleKey = [];
        if(customCompute($productsales)) {
            foreach ($productsales as $productsale) {
                $productsaleKey[] = $productsale->productsaleID;
            }
        }

        if(customCompute($productsaleKey)) {
            $productsaleitems = pluck_multi_array($this->productsaleitem_m->get_order_by_productsaleitem(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID)), 'obj', 'productsaleID');

            $productsalepaids = pluck_multi_array($this->productsalepaid_m->get_where_in_productsalepaid($productsaleKey, 'productsaleID'), 'obj', 'productsaleID');

            if(customCompute($productsales)) {
                foreach ($productsales as $productsale) {
                    if(isset($productsaleitems[$productsale->productsaleID])) {
                        if(customCompute($productsaleitems[$productsale->productsaleID])) {
                            foreach ($productsaleitems[$productsale->productsaleID] as $productpurchaseitem) {
                                if(isset($retArray['grandtotal'][$productpurchaseitem->productsaleID])) {
                                    $retArray['grandtotal'][$productpurchaseitem->productsaleID] = (($retArray['grandtotal'][$productpurchaseitem->productsaleID]) + ($productpurchaseitem->productsaleunitprice*$productpurchaseitem->productsalequantity));
                                } else {
                                    $retArray['grandtotal'][$productpurchaseitem->productsaleID] = ($productpurchaseitem->productsaleunitprice*$productpurchaseitem->productsalequantity);
                                }
                            }
                        }
                    }

                    if(isset($productsalepaids[$productsale->productsaleID])) {
                        if(customCompute($productsalepaids[$productsale->productsaleID])) {
                            foreach ($productsalepaids[$productsale->productsaleID] as $productsalepaid) {
                                if(isset($retArray['totalpaid'][$productsalepaid->productsaleID])) {
                                    $retArray['totalpaid'][$productsalepaid->productsaleID] = (($retArray['totalpaid'][$productsalepaid->productsaleID]) + ($productsalepaid->productsalepaidamount));
                                } else {
                                    $retArray['totalpaid'][$productsalepaid->productsaleID] = ($productsalepaid->productsalepaidamount);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $retArray;
    }

    public function download() {
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        if(permissionChecker('inventoryinvoice')) {
            $id = htmlentities(escapeString($this->uri->segment(3)));
            if((int)$id) {
                $productsale = $this->productsale_m->get_single_productsale(array('productsaleID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $this->session->userdata('schoolID')));
                $file = realpath('uploads/images/'.$productsale->productsalefile);
                $originalname = $productsale->productsalefileorginalname;
                if (file_exists($file)) {
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="'.basename($originalname).'"');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($file));
                    readfile($file);
                    exit;
                } else {
                    redirect(base_url('inventoryinvoice/index'));
                }
            } else {
                redirect(base_url('inventoryinvoice/index'));
            }
        } else {
            redirect(base_url('inventoryinvoice/index'));
        }
    }

    public function view() {
        $this->data['headerassets'] = array(
            'css' => array(
                'assets/datepicker/datepicker.css',
            ),
            'js' => array(
                'assets/datepicker/datepicker.js',
            )
        );
        $id = htmlentities(escapeString($this->uri->segment(3)));
        $schoolID = $this->session->userdata('schoolID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        if((int)$id) {
            $this->data['productsale'] = $this->productsale_m->get_single_productsale(array('productsaleID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));

            $this->data['products'] = pluck($this->product_m->get_order_by_product(array('schoolID' => $schoolID)), 'productname', 'productID');

            $this->data['productsaleitems'] = $this->productsaleitem_m->get_order_by_productsaleitem(array('productsaleID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));

            $this->data['productsalepaid'] = $this->productsalepaid_m->get_productsalepaid_sum('productsalepaidamount', array('productsaleID' => $id, 'schoolID' => $schoolID));

            if($this->data['productsale']) {
                $this->data['usertypes'] = pluck($this->usertype_m->get_order_by_usertype_with_or(array('schoolID' => $schoolID)), 'usertype', 'usertypeID');
                if((int)$this->data['productsale']->productsalecustomertypeID) {
                    $this->data['user'] = $this->getuserlistobj($this->data['productsale']->productsalecustomertypeID, $this->data['productsale']->productsalecustomerID, $schoolyearID);
                } else {
                    $this->data['user'] = $this->school_m->get_school($this->data['productsale']->productsalecustomerID);
                    $settingValues = $this->setting_m->get_setting($this->data['productsale']->productsalecustomerID);
                    $this->data['user']->address = $settingValues->address;
                    $this->data['user']->phone = $settingValues->phone;
                    $this->data['user']->email = $settingValues->email;
                }
                $this->data['createuser'] = getNameByUsertypeIDAndUserID($this->data['productsale']->create_usertypeID, $this->data['productsale']->create_userID);

                $this->data["subview"] = "inventoryinvoice/view";
                $this->load->view('_layout_main', $this->data);
            } else {
                $this->data["subview"] = "error";
                $this->load->view('_layout_main', $this->data);
            }
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function getuserlistobj($usertypeID, $userID, $schoolyearID) {
        $user = [];
        if($usertypeID == 1) {
            $user = $this->systemadmin_m->get_single_systemadmin(array('systemadminID' => $userID));
        } elseif($usertypeID == 2) {
            $user = $this->teacher_m->get_single_teacher(array('teacherID' => $userID));
        } elseif($usertypeID == 3) {
            $user = $this->studentrelation_m->get_studentrelation_join_student(array('srstudentID' => $userID, 'srschoolyearID' => $schoolyearID), TRUE);
        } elseif($usertypeID == 4) {
            $user = $this->parents_m->get_single_parents(array('parentsID' => $userID));
        } else {
            $user = $this->user_m->get_single_user(array('usertypeID' => $usertypeID, 'userID' => $userID));
        }

        return $user;
    }

    public function print_preview() {
        if(permissionChecker('inventoryinvoice_view')) {
            $id = htmlentities(escapeString($this->uri->segment(3)));
            $schoolID = $this->session->userdata('schoolID');
            $schoolyearID = $this->session->userdata('defaultschoolyearID');
            if((int)$id) {
                $this->data['productsale'] = $this->productsale_m->get_single_productsale(array('productsaleID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));

                $this->data['products'] = pluck($this->product_m->get_order_by_product(array('schoolID' => $schoolID)), 'productname', 'productID');

                $this->data['productsaleitems'] = $this->productsaleitem_m->get_order_by_productsaleitem(array('productsaleID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));

                $this->data['productsalepaid'] = $this->productsalepaid_m->get_productsalepaid_sum('productsalepaidamount', array('productsaleID' => $id, 'schoolID' => $schoolID));

                if($this->data['productsale']) {
                    $this->data['usertypes'] = pluck($this->usertype_m->get_order_by_usertype_with_or(array('schoolID' => $schoolID)), 'usertype', 'usertypeID');
                    $this->data['user'] = $this->getuserlistobj($this->data['productsale']->productsalecustomertypeID, $this->data['productsale']->productsalecustomerID, $schoolyearID);

                    $this->data['createuser'] = getNameByUsertypeIDAndUserID($this->data['productsale']->create_usertypeID, $this->data['productsale']->create_userID);

                    $this->reportPDF('productsalemodule.css', $this->data, 'productsale/print_preview');
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

    public function print() {
      if(permissionChecker('inventoryinvoice_view')) {
          $id = htmlentities(escapeString($this->uri->segment(3)));
          $schoolID = $this->session->userdata('schoolID');
          $schoolyearID = $this->session->userdata('defaultschoolyearID');
          if((int)$id) {
              $this->data['productsale'] = $this->productsale_m->get_single_productsale(array('productsaleID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));

              $this->data['products'] = pluck($this->product_m->get_order_by_product(array('schoolID' => $schoolID)), 'productname', 'productID');

              $this->data['productsaleitems'] = $this->productsaleitem_m->get_order_by_productsaleitem(array('productsaleID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));

              $this->data['productsalepaid'] = $this->productsalepaid_m->get_productsalepaid_sum('productsalepaidamount', array('productsaleID' => $id, 'schoolID' => $schoolID));

              if($this->data['productsale']) {
                  $this->data['usertypes'] = pluck($this->usertype_m->get_order_by_usertype_with_or(array('schoolID' => $schoolID)), 'usertype', 'usertypeID');
                  $this->data['user'] = $this->getuserlistobj($this->data['productsale']->productsalecustomertypeID, $this->data['productsale']->productsalecustomerID, $schoolyearID);

                  $this->data['createuser'] = getNameByUsertypeIDAndUserID($this->data['productsale']->create_usertypeID, $this->data['productsale']->create_userID);

                  $pdf = $_SERVER['DOCUMENT_ROOT'] ."/". $this->pathPDF('productsalemodule.css', $this->data, 'inventoryinvoice/print_preview');
                  $connector = new FilePrintConnector("php://stdout");
                  $printer = new Printer($connector);
                  try {
                    $pages = ImagickEscposImage::loadPdf($pdf);
                    foreach ($pages as $page) {
                        $printer -> graphics($page);
                    }
                    $printer -> cut();
                    echo "Printing...";
                  } catch (Exception $e) {
                    /*
                   * loadPdf() throws exceptions if files or not found, or you don't have the
                   * imagick extension to read PDF's
                   */
                    echo $e -> getMessage() . "\n";
                  } finally {
                    $printer -> close();
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

    protected function send_mail_rules() {
        $rules = array(
            array(
                'field' => 'productsaleID',
                'label' => $this->lang->line('inventoryinvoice_id'),
                'rules' => 'trim|required|xss_clean|numeric|callback_valid_data'
            ), array(
                'field' => 'to',
                'label' => $this->lang->line('to'),
                'rules' => 'trim|required|xss_clean|valid_email'
            ), array(
                'field' => 'subject',
                'label' => $this->lang->line('subject'),
                'rules' => 'trim|required|xss_clean'
            ), array(
                'field' => 'message',
                'label' => $this->lang->line('message'),
                'rules' => 'trim|xss_clean'
            )
        );
        return $rules;
    }

    public function send_mail() {
        $schoolID = $this->session->userdata('schoolID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $retArray['status'] = FALSE;
        $retArray['message'] = '';
        if(permissionChecker('inventoryinvoice_view')) {
            if($_POST) {
                $rules = $this->send_mail_rules();
                $this->form_validation->set_rules($rules);
                if ($this->form_validation->run() == FALSE) {
                    $retArray = $this->form_validation->error_array();
                    $retArray['status'] = FALSE;
                    echo json_encode($retArray);
                    exit;
                } else {
                    $to         = $this->input->post('to');
                    $subject    = $this->input->post('subject');
                    $message    = $this->input->post('message');
                    $id         = $this->input->post('productsaleID');

                    $this->data['productsale'] = $this->productsale_m->get_single_productsale(array('productsaleID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));

                    $this->data['products'] = pluck($this->product_m->get_order_by_product(array('schoolID' => $schoolID)), 'productname', 'productID');

                    $this->data['productsaleitems'] = $this->productsaleitem_m->get_order_by_productsaleitem(array('productsaleID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));

                    $this->data['productsalepaid'] = $this->productsalepaid_m->get_productsalepaid_sum('productsalepaidamount', array('productsaleID' => $id, 'schoolID' => $schoolID));

                    if($this->data['productsale']) {
                        $this->data['usertypes'] = pluck($this->usertype_m->get_order_by_usertype_with_or(array('schoolID' => $schoolID)), 'usertype', 'usertypeID');
                        $this->data['user'] = $this->getuserlistobj($this->data['productsale']->productsalecustomertypeID, $this->data['productsale']->productsalecustomerID, $schoolyearID);
                        $this->data['createuser'] = getNameByUsertypeIDAndUserID($this->data['productsale']->create_usertypeID, $this->data['productsale']->create_userID);

                        $this->reportSendToMail('productsalemodule.css', $this->data, 'productsale/print_preview', $to, $subject, $message);
                        $retArray['message'] = "Success";
                        $retArray['status'] = TRUE;
                        echo json_encode($retArray);
                        exit;
                    } else {
                        $retArray['message'] = $this->lang->line('inventoryinvoice_data_not_found');
                        echo json_encode($retArray);
                        exit;
                    }
                }
            } else {
                $retArray['message'] = $this->lang->line('inventoryinvoice_permissionmethod');
                echo json_encode($retArray);
                exit;
            }
        } else {
            $retArray['message'] = $this->lang->line('inventoryinvoice_permission');
            echo json_encode($retArray);
            exit;
        }
    }

    public function paymentlist() {
        $schoolID = $this->session->userdata('schoolID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $productsaleID = $this->input->post('productsaleID');

        $paymentmethodarray = array(
            1 => $this->lang->line('inventoryinvoice_cash'),
            2 => $this->lang->line('inventoryinvoice_cheque'),
            3 => $this->lang->line('inventoryinvoice_credit_card'),
            4 => $this->lang->line('inventoryinvoice_other'),
        );

        $productsale = $this->productsale_m->get_single_productsale(array('productsaleID' => $productsaleID, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
        if(customCompute($productsale)) {
            if(!empty($productsaleID) && (int)$productsaleID && $productsaleID > 0) {
                $productsalepaids = $this->productsalepaid_m->get_order_by_productsalepaid(array('productsaleID' => $productsaleID, 'schoolID' => $schoolID));
                if(customCompute($productsalepaids)) {
                    $i = 1;
                    foreach ($productsalepaids as $productsalepaid) {
                        echo '<tr>';
                            echo '<td data-title="'.$this->lang->line('slno').'">';
                                echo $i;
                            echo '</td>';

                            echo '<td data-title="'.$this->lang->line('inventoryinvoice_date').'">';
                                echo date('d M Y', strtotime($productsalepaid->productsalepaiddate));
                            echo '</td>';

                            echo '<td data-title="'.$this->lang->line('inventoryinvoice_referenceno').'">';
                                echo $productsalepaid->productsalepaidreferenceno;
                            echo '</td>';

                            echo '<td data-title="'.$this->lang->line('inventoryinvoice_amount').'">';
                                echo number_format($productsalepaid->productsalepaidamount, 2);
                                if($productsalepaid->productsalepaidfile != "") {
                                    echo ' <a href="'.base_url("productsale/paymentfiledownload/".$productsalepaid->productsalepaidID).'" style="color:#428bca"><i class="fa fa-chain"></i></a>';

                                }
                            echo '</td>';

                            echo '<td data-title="'.$this->lang->line('inventoryinvoice_paid_by').'">';
                                if(isset($paymentmethodarray[$productsalepaid->productsalepaidpaymentmethod])) {
                                    echo $paymentmethodarray[$productsalepaid->productsalepaidpaymentmethod];
                                }
                            echo '</td>';

                            if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('defaultschoolyearID') == 5)) {
                                echo '<td data-title="'.$this->lang->line('action').'">';
                                    if($productsale->productsalerefund == 0) {
                                        if(permissionChecker('inventoryinvoice_delete')) {
                                            echo '<a href="'.base_url('productsale/deletesalepaid/'.$productsalepaid->productsalepaidID).'" onclick="return confirm('."'".'you are about to delete a record. This cannot be undone. are you sure?'."'".')" class="btn btn-danger btn-xs mrg" data-placement="top" data-toggle="tooltip" data-original-title="'.$this->lang->line('delete').'"><i class="fa fa-trash-o"></i></a>';
                                        }
                                    }
                                echo '</td>';
                            }
                        echo '</tr>';

                        $i++;
                    }
                }
            }
        }
    }

    public function deletesalepaid() {
        if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('defaultschoolyearID') == 5)) {
            $productsalepaidID = htmlentities(escapeString($this->uri->segment(3)));
            $schoolyearID = $this->session->userdata('defaultschoolyearID');

            if(permissionChecker('inventoryinvoice_delete')) {
                if((int)$productsalepaidID) {
                    $productsalepaid = $this->productsalepaid_m->get_single_productsalepaid(array('productsalepaidID' => $productsalepaidID, 'schoolID' => $this->session->userdata('schoolID')));

                    if(customCompute($productsalepaid)) {
                        $productsale = $this->productsale_m->get_single_productsale(array('productsaleID' => $productsalepaid->productsaleID, 'schoolyearID' => $schoolyearID));
                        if(customCompute($productsale) && $productsale->productsalerefund == 0) {

                            $this->productsalepaid_m->delete_productsalepaid($productsalepaidID);

                            $productsaleitemsum = $this->productsaleitem_m->get_productsaleitem_sum(array('productsaleID' => $productsale->productsaleID, 'schoolyearID' => $schoolyearID));

                            $productsalepaidsum = $this->productsalepaid_m->get_productsalepaid_sum('productsalepaidamount', array('productsaleID' => $productsale->productsaleID));

                            $array = [];
                            if($productsalepaidsum->productsalepaidamount == NULL) {
                                $array['productsalestatus'] = 1;
                            } elseif((float)$productsaleitemsum->result == (float)$productsalepaidsum->productsalepaidamount) {
                                $array['productsalestatus'] = 3;
                            } elseif((float)$productsalepaidsum->productsalepaidamount > 0 && ((float)$productsaleitemsum->result > (float)$productsalepaidsum->productsalepaidamount)) {
                                $array['productsalestatus'] = 2;
                            } elseif((float)$productsalepaidsum->productsalepaidamount > 0 && ((float)$productsaleitemsum->result < (float)$productsalepaidsum->productsalepaidamount)) {
                                $array['productsalestatus'] = 3;
                            }

                            $this->productsale_m->update_productsale($array, $productsale->productsaleID);
                            $this->session->set_flashdata('success', $this->lang->line('menu_success'));

                            redirect(base_url('inventoryinvoice/index'));
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

    public function getsaleinfo() {
        $schoolID = $this->session->userdata('schoolID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $productsaleID = $this->input->post('productsaleID');

        $retArray['status'] = FALSE;
        $retArray['dueamount'] = 0.00;
        if(permissionChecker('inventoryinvoice_add')) {
            if(!empty($productsaleID) && (int)$productsaleID && $productsaleID > 0) {
                $productsale = $this->productsale_m->get_single_productsale(array('productsaleID' => $productsaleID, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
                if(customCompute($productsale)) {
                    if($productsale->productsalerefund == 0 && $productsale->productsalestatus != 3) {
                        $productsaleitemsum = $this->productsaleitem_m->get_productsaleitem_sum(array('productsaleID' => $productsaleID, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
                        $productsalepaidsum = $this->productsalepaid_m->get_productsalepaid_sum('productsalepaidamount', array('productsaleID' => $productsaleID, 'schoolID' => $schoolID));

                        $retArray['dueamount'] = number_format((($productsaleitemsum->result) - ($productsalepaidsum->productsalepaidamount)), 2, '.', '');
                        $retArray['status'] = TRUE;
                    }
                }
            }
        }

        echo json_encode($retArray);
        exit;
    }

    protected function rules_payment() {
        $rules = array(
            array(
                'field' => 'productsalepaiddate',
                'label' => $this->lang->line("inventoryinvoice_date"),
                'rules' => 'trim|required|xss_clean|callback_date_valid'
            ),
            array(
                'field' => 'productsalepaidreferenceno',
                'label' => $this->lang->line("inventoryinvoice_referenceno"),
                'rules' => 'trim|required|xss_clean|max_length[99]'
            ),
            array(
                'field' => 'productsalepaidamount',
                'label' => $this->lang->line("inventoryinvoice_amount"),
                'rules' => 'trim|required|xss_clean|numeric|max_length[15]'
            ),
            array(
                'field' => 'productsalepaidpaymentmethod',
                'label' => $this->lang->line("inventoryinvoice_paymentmethod"),
                'rules' => 'trim|required|xss_clean|numeric|max_length[1]|callback_valid_data'
            ),
            array(
                'field' => 'productsaleID',
                'label' => $this->lang->line("inventoryinvoice_description"),
                'rules' => 'trim|required|xss_clean|numeric|max_length[11]'
            ),
            array(
                'field' => 'productsalepaidfile',
                'label' => $this->lang->line("inventoryinvoice_file"),
                'rules' => 'trim|xss_clean|max_length[200]|callback_paidfileupload'
            )
        );
        return $rules;
    }

    public function paidfileupload() {
        $new_file = "";
        $original_file_name = '';
        if($_FILES["productsalepaidfile"]['name'] !="") {
            $file_name = $_FILES["productsalepaidfile"]['name'];
            $original_file_name = $file_name;
            $random = random19();
            $makeRandom = hash('sha512', $random.'productsalepaidfile'.config_item("encryption_key"));
            $file_name_rename = $makeRandom;
            $explode = explode('.', $file_name);
            if(customCompute($explode) >= 2) {
                $new_file = $file_name_rename.'.'.end($explode);
                $config['upload_path'] = "./uploads/images";
                $config['allowed_types'] = "gif|jpg|png|jpeg|pdf|doc|xml|docx|GIF|JPG|PNG|JPEG|PDF|DOC|XML|DOCX|xls|xlsx|txt|ppt|csv";
                $config['file_name'] = $new_file;
                $config['max_size'] = '2048';
                $config['max_width'] = '30000';
                $config['max_height'] = '30000';
                $this->load->library('upload', $config);
                if(!$this->upload->do_upload("productsalepaidfile")) {
                    $this->form_validation->set_message("fileupload", $this->upload->display_errors());
                    return FALSE;
                } else {
                    $this->upload_data['file'] =  $this->upload->data();
                    $this->upload_data['file']['original_file_name'] = $original_file_name;
                    return TRUE;
                }
            } else {
                $this->form_validation->set_message("fileupload", "Invalid file");
                return FALSE;
            }
        } else {
            $this->upload_data['file']['file_name'] = '';
            $this->upload_data['file']['original_file_name'] = '';
            return TRUE;
        }
    }

    public function saveproductsalepayment() {
        $schoolID = $this->session->userdata('schoolID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $productsaleID = 0;
        $retArray['status'] = FALSE;
        if(permissionChecker('inventoryinvoice_add')) {
            $productsale = $this->productsale_m->get_single_productsale(array('productsaleID' => $this->input->post('productsaleID'), 'schoolID' => $schoolID));

            if(customCompute($productsale)) {
                if($productsale->productsalerefund == 0 && $productsale->productsalestatus != 3) {
                    if($_POST) {
                        $rules = $this->rules_payment();
                        $this->form_validation->set_rules($rules);
                        if ($this->form_validation->run() == FALSE) {
                            $retArray['error'] = $this->form_validation->error_array();
                            $retArray['status'] = FALSE;
                            echo json_encode($retArray);
                            exit;
                        } else {
                            $array = array(
                                'schoolID' => $schoolID,
                                'schoolyearID' => $schoolyearID,
                                'productsalepaidschoolyearID' => $this->data['siteinfos']->school_year,
                                'productsaleID' => $this->input->post('productsaleID'),
                                'productsalepaiddate' => date('Y-m-d', strtotime($this->input->post("productsalepaiddate"))),
                                'productsalepaidreferenceno' => $this->input->post('productsalepaidreferenceno'),
                                'productsalepaidamount' => $this->input->post('productsalepaidamount'),
                                'productsalepaidpaymentmethod' => $this->input->post('productsalepaidpaymentmethod'),
                                'productsalepaiddescription' => '',
                                "productsalepaidfile" => $this ->upload_data['file']['file_name'],
                                "productsalepaidorginalname" => $this ->upload_data['file']['original_file_name'],
                                'create_date' => date('Y-m-d H:i:s'),
                                'modify_date' => date('Y-m-d H:i:s'),
                                'create_userID' => $this->session->userdata('loginuserID'),
                                'create_usertypeID' => $this->session->userdata('usertypeID')
                            );

                            $this->productsalepaid_m->insert_productsalepaid($array);

                            $productsaleitemsum = $this->productsaleitem_m->get_productsaleitem_sum(array('productsaleID' => $this->input->post('productsaleID'), 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));

                            $productsalepaidsum = $this->productsalepaid_m->get_productsalepaid_sum('productsalepaidamount', array('productsaleID' => $this->input->post('productsaleID'), 'schoolID' => $schoolID));

                            $productsalearray['productsalestatus'] = 1;
                            if((float)$productsaleitemsum->result == (float)$productsalepaidsum->productsalepaidamount) {
                                $productsalearray['productsalestatus'] = 3;
                            } elseif((float)$productsalepaidsum->productsalepaidamount > 0 && ((float)$productsaleitemsum->result > (float)$productsalepaidsum->productsalepaidamount)) {
                                $productsalearray['productsalestatus'] = 2;
                            } elseif((float)$productsalepaidsum->productsalepaidamount > 0 && ((float)$productsaleitemsum->result < (float)$productsalepaidsum->productsalepaidamount)) {
                                $productsalearray['productsalestatus'] = 3;
                            }

                            $this->productsale_m->update_productsale($productsalearray, $this->input->post('productsaleID'));

                            $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                            $retArray['status'] = TRUE;
                            $retArray['message'] = 'Success';
                            echo json_encode($retArray);
                            exit;
                        }
                    } else {
                        $retArray['error'] = array('posttype' => 'Post type is required.');
                        echo json_encode($retArray);
                        exit;
                    }
                } else {
                    $retArray['error'] = array('permission' => 'This invoice already fully paid.');
                    echo json_encode($retArray);
                    exit;
                }
            } else {
                $retArray['error'] = array('permission' => 'Sale ID does not found.');
                echo json_encode($retArray);
                exit;
            }
        } else {
            $retArray['error'] = array('permission' => 'Add payment permission is required.');
            echo json_encode($retArray);
            exit;
        }
    }

    public function paymentfiledownload() {
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        if(permissionChecker('inventoryinvoice')) {
            $id = htmlentities(escapeString($this->uri->segment(3)));
            if((int)$id) {
                $productsalepaid = $this->productsalepaid_m->get_single_productsalepaid(array('productsalepaidID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $this->session->userdata('schoolID')));
                $file = realpath('uploads/images/'.$productsalepaid->productsalepaidfile);
                $originalname = $productsalepaid->productsalepaidorginalname;
                if (file_exists($file)) {
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="'.basename($originalname).'"');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($file));
                    readfile($file);
                    exit;
                } else {
                    redirect(base_url('inventoryinvoice/index'));
                }
            } else {
                redirect(base_url('inventoryinvoice/index'));
            }
        } else {
            redirect(base_url('inventoryinvoice/index'));
        }
    }

    public function add() {
        if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('defaultschoolyearID') == 5)) {
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
            $this->data['paymentmethods'] = $this->paymenttypes_m->get_order_by_paymenttypes(array('schoolID' => $schoolID));
            $this->data['classes'] = $this->classes_m->general_get_order_by_classes(array('schoolID' => $schoolID));
            $this->data['productcategorys'] = $this->productcategory_m->get_order_by_productcategory(array('schoolID' => $schoolID));
            $this->data['products'] = $this->product_m->get_order_by_product(array('schoolID' => $schoolID));
            $this->data['productobj'] = json_encode(pluck($this->product_m->get_order_by_product(array('schoolID' => $schoolID)), 'obj', 'productID'));
            $this->data['productwarehouses'] = $this->productwarehouse_m->get_order_by_productwarehouse(array('schoolID' => $schoolID));
            $this->data['productpurchasequintity'] = json_encode(pluck_multi_array_key($this->productpurchaseitem_m->get_productpurchaseitem_quantity(array('schoolID' => $schoolID)), 'obj', 'productwarehouseID', 'productID'));
            $this->data['productsalequintity'] = json_encode(pluck_multi_array_key($this->productsaleitem_m->get_productsaleitem_quantity(array('schoolID' => $schoolID)), 'obj', 'productwarehouseID', 'productID'));

            $this->data["subview"] = "inventoryinvoice/add";
            $this->load->view('_layout_main', $this->data);
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function edit() {
        if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('defaultschoolyearID') == 5)) {
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
            $id = htmlentities(escapeString($this->uri->segment(3)));
            $schoolID = $this->session->userdata('schoolID');
            $schoolyearID = $this->session->userdata('defaultschoolyearID');
            if((int)$id) {
                $this->data['productsaleID'] = $id;
                $this->data['productsale'] = $this->productsale_m->get_single_productsale(array('productsaleID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));

                $this->data['usertypes'] = $this->usertype_m->get_order_by_usertype_with_or(array('schoolID' => $schoolID));
                $this->data['classes'] = $this->classes_m->general_get_order_by_classes(array('schoolID' => $schoolID));
                $this->data['productcategorys'] = $this->productcategory_m->get_order_by_productcategory(array('schoolID' => $schoolID));

                $this->data['products'] = pluck($this->product_m->get_order_by_product(array('schoolID' => $schoolID)), 'productname', 'productID');
                $this->data['productobj'] = json_encode(pluck($this->product_m->get_order_by_product(array('schoolID' => $schoolID)), 'obj', 'productID'));
                $this->data['productwarehouses'] = $this->productwarehouse_m->get_order_by_productwarehouse(array('schoolID' => $schoolID));
                $this->data['productpurchasequintity'] = json_encode(pluck($this->productpurchaseitem_m->get_productpurchaseitem_quantity(array('schoolID' => $schoolID)), 'obj', 'productID'));

                $this->data['productsalequintity'] = json_encode(pluck($this->productsaleitem_m->get_productsaleitem_quantity(array('schoolID' => $schoolID)), 'obj', 'productID'));

                $this->data['productsalepaid'] = $this->productsalepaid_m->get_productsalepaid_sum('productsalepaidamount', array('productsaleID' => $id, 'schoolID' => $schoolID));


                if(customCompute($this->data['productsale'])) {
                    $this->data['productsalequintityforedit'] = json_encode(pluck($this->productsaleitem_m->get_productsaleitem_quantity(array('productsaleID' => $this->data['productsale']->productsaleID, 'schoolID' => $schoolID)), 'obj', 'productID'));


                    if($this->data['productsale']->productsalecustomertypeID == 3) {
                        $srstudent = $this->studentrelation_m->get_single_studentrelation(array('srstudentID' => $this->data['productsale']->productsalecustomerID, 'srschoolyearID' => $schoolyearID));
                        if(customCompute($srstudent)) {
                            $this->data['classesID'] = $srstudent->srclassesID;
                        } else {
                            $this->data['classesID'] = 0;
                        }
                    } else {
                        $this->data['classesID'] = 0;
                    }

                    $this->data['productsalecustomers'] = $this->getuserlistbyrole($this->data['productsale']->productsalecustomertypeID, $this->data['classesID']);

                    if(($this->data['productsale']->productsalerefund == 0) && ($this->data['productsalepaid']->productsalepaidamount == NULL)) {
                        $this->data['productsaleitems'] = $this->productsaleitem_m->get_order_by_productsaleitem(array('schoolyearID' => $schoolyearID, 'productsaleID' => $id));
                        $this->data["subview"] = "inventoryinvoice/edit";
                        $this->load->view('_layout_main', $this->data);
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

    public function getuserlistbyrole($usertypeID, $classesID = 0, $obj = FALSE)  {
        $userArray = [];
        $schoolID = $this->session->userdata('schoolID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');

        if($usertypeID == 1) {
            $systemadmins = $this->systemadmin_m->get_order_by_systemadmin(array('schoolID' => $schoolID));
            if($obj == FALSE) {
                $userArray = array(0 => $this->lang->line("inventoryinvoice_select_user"));
                if(customCompute($systemadmins)) {
                    foreach ($systemadmins as $systemadmin) {
                        $userArray[$systemadmin->systemadminID] = $systemadmin->name;
                    }
                }
            } else {
                $userArray = $systemadmins;
            }
        } elseif($usertypeID == 2) {
            $teachers = $this->teacher_m->get_order_by_teacher(array('schoolID' => $schoolID));
            if($obj == FALSE) {
                $userArray = array(0 => $this->lang->line("inventoryinvoice_select_user"));
                if(customCompute($teachers)) {
                    foreach ($teachers as $teacher) {
                        $userArray[$teacher->teacherID] = $teacher->name;
                    }
                }
            } else {
                $userArray = $teachers;
            }
        } elseif($usertypeID == 3) {
            if($classesID == 0) {
                $students = $this->studentrelation_m->get_order_by_studentrelation(array('srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID));
            } else {
                $students = $this->studentrelation_m->get_order_by_studentrelation(array('srschoolyearID' => $schoolyearID, 'srclassesID' => $classesID, 'srschoolID' => $schoolID));
            }

            if($obj == FALSE) {
                $userArray = array(0 => $this->lang->line("inventoryinvoice_select_user"));
                if(customCompute($students)) {
                    foreach ($students as $student) {
                        $userArray[$student->srstudentID] = $student->srname.' - '.$this->lang->line('inventoryinvoice_roll').' - '.$student->srroll;
                    }
                }
            } else {
                $userArray = $students;
            }
        } elseif($usertypeID == 4) {
            $parents = $this->parents_m->get_order_by_parents(array('schoolID' => $schoolID));
            if($obj == FALSE) {
                $userArray = array(0 => $this->lang->line("inventoryinvoice_select_user"));
                if(customCompute($parents)) {
                    foreach ($parents as $parent) {
                        $userArray[$parent->parentsID] = $parent->name;
                    }
                }
            } else {
                $userArray = $parents;
            }
        } else {
            $users = $this->user_m->get_order_by_user(array('usertypeID' => $usertypeID, 'schoolID' => $schoolID));
            if($obj == FALSE) {
                $userArray = array(0 => $this->lang->line("inventoryinvoice_select_user"));
                if(customCompute($users)) {
                    foreach ($users as $user) {
                        $userArray[$user->userID] = $user->name;
                    }
                }
            } else {
                $userArray = $users;
            }
        }

        return $userArray;
    }

    public function getproductsale() {
        $productcategoryID = $this->input->post('productcategoryID');
        if((int)$productcategoryID) {
            $products = $this->product_m->get_order_by_product(array('productcategoryID' => $productcategoryID, 'schoolID' => $this->session->userdata('schoolID')));
            echo "<option value='0'>", $this->lang->line("inventoryinvoice_select_product"),"</option>";
            foreach ($products as $product) {
                echo "<option value=\"$product->productID\">",$product->productname,"</option>";
            }
        }
    }

    public function getuser() {
        $productsalecustomertypeID = $this->input->post('productsalecustomertypeID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $schoolID = $this->session->userdata('schoolID');

        echo "<option value=\"0\">",$this->lang->line('inventoryinvoice_select_user'),"</option>";
        if((int)$productsalecustomertypeID) {
            if($productsalecustomertypeID == 1) {
                $systemadmins = $this->systemadmin_m->get_order_by_systemadmin(array('schoolID' => $schoolID));
                if(customCompute($systemadmins)) {
                    foreach ($systemadmins as $systemadmin) {
                        echo "<option value=\"$systemadmin->systemadminID\">",$systemadmin->name,"</option>";
                    }
                }
            } elseif($productsalecustomertypeID == 2) {
                $teachers = $this->teacher_m->get_order_by_teacher(array('schoolID' => $schoolID));
                if(customCompute($teachers)) {
                    foreach ($teachers as $teacher) {
                        echo "<option value=\"$teacher->teacherID\">",$teacher->name,"</option>";
                    }
                }
            } elseif($productsalecustomertypeID == 3) {
                $classesID = $this->input->post('productsaleclassesID');
                if($this->input->post('productsaleusercalltype') == 'edit') {
                    $this->db->order_by('srroll', 'asc');
                    $students = $this->studentrelation_m->get_order_by_student(array('srschoolyearID' => $schoolyearID, 'srclassesID' => $classesID, 'srschoolID' => $schoolID));
                    if(customCompute($students)) {
                        foreach ($students as $student) {
                            if(!empty($student->studentID)) {
                                echo "<option value=\"$student->srstudentID\">".$student->srname." - ".$this->lang->line('inventoryinvoice_roll')." - ".$student->srroll."</option>";
                            }
                        }
                    }
                } else {
                    $students = $this->studentrelation_m->get_order_by_student(array('srschoolyearID' => $schoolyearID, 'srclassesID' => $classesID, 'srschoolID' => $schoolID));
                    if(customCompute($students)) {
                        foreach ($students as $student) {
                            echo "<option value=\"$student->srstudentID\">".$student->srname." - ".$this->lang->line('inventoryinvoice_roll')." - ".$student->srroll."</option>";
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
        } elseif($productsalecustomertypeID == "school") {
            $schools = explode(",", $this->session->userdata('schools'));
            $schoolArray = pluck($this->school_m->get_order_by_school(), 'name', 'schoolID');
            if(customCompute($schools)) {
                foreach ($schools as $school) {
                    echo "<option value=\"$school\">",$schoolArray[$school],"</option>";
                }
            }
        }
    }

    protected function rules($paymentStatus = 0) {
        $rules = array(
            array(
                'field' => 'productsalecustomerID',
                'label' => $this->lang->line("inventoryinvoice_user"),
                'rules' => 'trim|required|xss_clean|max_length[11]|numeric|callback_valid_data'
            ),
            array(
                'field' => 'productsalereferenceno',
                'label' => $this->lang->line("inventoryinvoice_referenceno"),
                'rules' => 'trim|required|xss_clean|max_length[99]'
            ),
            array(
                'field' => 'productsaledate',
                'label' => $this->lang->line("inventoryinvoice_date"),
                'rules' => 'trim|required|xss_clean|max_length[11]|callback_date_valid'
            ),

            array(
                'field' => 'productsalefile',
                'label' => $this->lang->line("inventoryinvoice_file"),
                'rules' => 'trim|xss_clean|max_length[200]|callback_fileupload'
            ),
            array(
                'field' => 'productsaledescription',
                'label' => $this->lang->line("inventoryinvoice_description"),
                'rules' => 'trim|xss_clean|max_length[520]'
            ),
            array(
                'field' => 'productitem',
                'label' => $this->lang->line("inventoryinvoice_productitem"),
                'rules' => 'trim|xss_clean|callback_unique_productitem|callback_unique_productitemadjust'
            ),
            array(
                'field' => 'editID',
                'label' => $this->lang->line("inventoryinvoice_editid"),
                'rules' => 'trim|required|xss_clean|numeric'
            )
        );

        return $rules;
    }

    public function valid_data($data) {
        if($data == 0) {
            $this->form_validation->set_message('valid_data','The %s field is required.');
            return FALSE;
        }
        return TRUE;
    }

    public function date_valid($date) {
        if($date) {
            if(strlen($date) <10) {
                $this->form_validation->set_message("date_valid", "%s is not valid dd-mm-yyyy");
                return FALSE;
            } else {
                $arr = explode("-", $date);
                $dd = $arr[0];
                $mm = $arr[1];
                $yyyy = $arr[2];
                if(checkdate($mm, $dd, $yyyy)) {
                    return TRUE;
                } else {
                    $this->form_validation->set_message("date_valid", "%s is not valid dd-mm-yyyy");
                    return FALSE;
                }
            }
        }
        return TRUE;
    }

    public function unique_productitem() {
        $productitems = json_decode($this->input->post('productitem'));
        $status = FALSE;
        if(customCompute($productitems)) {
            foreach ($productitems as $productitem) {
                if($productitem->unitprice != '' && $productitem->quantity != '') {
                    $status = TRUE;
                }
            }
        }

        if($status) {
            return TRUE;
        } else {
            $this->form_validation->set_message("unique_productitem", "The product item is required.");
            return FALSE;
        }
    }

    public function unique_productitemadjust() {
        $productwarehouseID = $this->input->post('productwarehouseID');
        if($productwarehouseID > 0) {
            $productitems = json_decode($this->input->post('productitem'));
            if(customCompute($productitems)) {
                $productQuantities = [];
                foreach ($productitems as $productitem) {
                    if(isset($productQuantities[$productitem->productID])) {
                        $productQuantities[$productitem->productID] += $productitem->quantity;
                    } else {
                        $productQuantities[$productitem->productID] = $productitem->quantity;
                    }
                }

                if(customCompute($productQuantities)) {
                    foreach($productQuantities as $productID => $quantity) {
                        if(!has_sufficient_stock($productID, $productwarehouseID, $quantity)) {
                            $product = $this->product_m->get_single_product(['productID' => $productID]);
                            $this->form_validation->set_message("unique_productitemadjust", "The ". $product->productname." is stock out.");
                            return FALSE;
                        }
                    }
                }
            }
        } else {
            $this->form_validation->set_message("unique_productitemadjust", "The warehouse is required.");
            return FALSE;
        }
        return TRUE;
    }

    public function fileupload() {
        $id = $this->input->post('editID');
        $productsale = [];
        if((int)$id && $id > 0) {
            $productsale = $this->productsale_m->get_single_productsale(array('productsaleID' => $id, 'schoolID' => $this->session->userdata('schoolID')));
        }

        $new_file = "";
        $original_file_name = '';
        if($_FILES["productsalefile"]['name'] !="") {
            $file_name = $_FILES["productsalefile"]['name'];
            $original_file_name = $file_name;
            $random = random19();
            $makeRandom = hash('sha512', $random.'productsale'.config_item("encryption_key"));
            $file_name_rename = $makeRandom;
            $explode = explode('.', $file_name);
            if(customCompute($explode) >= 2) {
                $new_file = $file_name_rename.'.'.end($explode);
                $config['upload_path'] = "./uploads/images";
                $config['allowed_types'] = "gif|jpg|png|jpeg|pdf|doc|xml|docx|GIF|JPG|PNG|JPEG|PDF|DOC|XML|DOCX|xls|xlsx|txt|ppt|csv";
                $config['file_name'] = $new_file;
                $config['max_size'] = '2048';
                $config['max_width'] = '30000';
                $config['max_height'] = '30000';
                $this->load->library('upload', $config);
                if(!$this->upload->do_upload("productsalefile")) {
                    $this->form_validation->set_message("fileupload", $this->upload->display_errors());
                    return FALSE;
                } else {
                    $this->upload_data['file'] =  $this->upload->data();
                    $this->upload_data['file']['original_file_name'] = $original_file_name;
                    return TRUE;
                }
            } else {
                $this->form_validation->set_message("fileupload", "Invalid file");
                return FALSE;
            }
        } else {
            if(customCompute($productsale)) {
                $this->upload_data['file'] = array('file_name' => $productsale->productsalefile);
                $this->upload_data['file']['original_file_name'] = $productsale->productsalefileorginalname;
                return TRUE;
            } else {
                $this->upload_data['file'] = array('file_name' => $new_file);
                $this->upload_data['file']['original_file_name'] = $original_file_name;
                return TRUE;
            }
        }
    }

    public function saveproductsale() {
        $productpurchaseID = 0;
        $retArray['status'] = FALSE;
        if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('defaultschoolyearID') == 5)) {
            if(permissionChecker('inventoryinvoice_add') || permissionChecker('inventoryinvoice_edit')) {
                if($_POST) {
                    $rules = $this->rules($this->input->post('productsalepaymentstatusID'));
                    $this->form_validation->set_rules($rules);
                    if ($this->form_validation->run() == FALSE) {
                        $retArray['error'] = $this->form_validation->error_array();
                        $retArray['status'] = FALSE;
                        echo json_encode($retArray);
                        exit;
                    } else {
                        $schoolID = $this->session->userdata('schoolID');
                        $schoolyearID = $this->session->userdata('defaultschoolyearID');
                        $student = $this->student_m->get_single_student(array('studentID' => $this->input->post("productsalecustomerID"), 'schoolID' => $schoolID));
                        $date = date('Y-m-d', strtotime($this->input->post("productsaledate")));
                        $schoolterm = $this->schoolterm_m->get_single_schoolterm(array('startingdate <=' => $date, 'endingdate >=' => $date, 'schoolID' => $schoolID));
                        if(!customCompute($schoolterm)) {
                          $retArray['error'] = array('schoolterm' => 'No school term found for selected date.');
                          echo json_encode($retArray);
                          exit;
                        }
                        $products = pluck($this->product_m->get_order_by_product(array('schoolID' => $schoolID)), 'productname', 'productID');

                        $array = array(
                            'schoolID' => $schoolID,
                            'schoolyearID' => $schoolyearID,
                            "productsalecustomertypeID" => 3,
                            "productsalecustomerID" => $this->input->post("productsalecustomerID"),
                            "productwarehouseID" => $this->input->post("productwarehouseID"),
                            "productsalereferenceno" => $this->input->post("productsalereferenceno"),
                            "productsaledate" => $date,
                            "productsaledescription" => $this->input->post("productsaledescription"),
                            "productsalestatus" => 1, // due
                            "productsalerefund" => 0,
                            "productsalefile" => $this ->upload_data['file']['file_name'],
                            "productsalefileorginalname" => $this ->upload_data['file']['original_file_name'],
                            'create_date' => date('Y-m-d H:i:s'),
                            'modify_date' => date('Y-m-d H:i:s'),
                            'create_userID' => $this->session->userdata('loginuserID'),
                            'create_usertypeID' => $this->session->userdata('usertypeID')
                        );

                        $success = $this->lang->line('menu_success');
                        $productsalepaidpaymentmethodID = $this->input->post("productsalepaidpaymentmethod");
                        $updateID = $this->input->post('editID');
                        if(permissionChecker('inventoryinvoice_edit')) {
                            if($updateID > 0) {
                                $productsaleID = $updateID;
                                $productsale = $this->productsale_m->get_single_productsale([
                                    'productsaleID'         => $productsaleID,
                                    'schoolID'              => $schoolID,
                                ]);
                                if(customCompute($productsale)) {
                                  $this->productsaleitem_m->delete_productsaleitem_by_productsaleID($productsaleID);
                                }
                            } else {
                                $this->productsale_m->insert_productsale($array);
                                $productsaleID = $this->db->insert_id();
                            }
                        } else {
                            $this->productsale_m->insert_productsale($array);
                            $productsaleID = $this->db->insert_id();
                        }

                        $invoiceMain = [
                            'schoolID'                => $schoolID,
                            'maininvoiceschoolyearID' => $schoolyearID,
                            'maininvoiceclassesID'    => $student->classesID,
                            'maininvoiceschooltermID' => $schoolterm->schooltermID,
                            'maininvoicestudentID'    => $student->studentID,
                            'maininvoiceuserID'       => $this->session->userdata('loginuserID'),
                            'maininvoiceusertypeID'   => $this->session->userdata('usertypeID'),
                            'maininvoiceuname'        => $this->session->userdata('name'),
                            'maininvoicedate'         => $date,
                            'maininvoicecreate_date'  => date('Y-m-d'),
                            'maininvoiceday'          => date('d'),
                            'maininvoicemonth'        => date('m'),
                            'maininvoiceyear'         => date('Y'),
                            'maininvoicedeleted_at'   => 1
                        ];

                        $this->maininvoice_m->insert_maininvoice($invoiceMain);
                        $maininvoiceID = $this->db->insert_id();

                        $totalAmount = 0;
                        $productsaleitem = [];
                        $invoice = [];
                        $lineTotals = [];
                        $productitems = json_decode($this->input->post('productitem'));
                        if(customCompute($productitems)) {
                            if($updateID == 0) {
                                $productitemschoolyearID = $schoolyearID;
                            } else {
                                $updatedata = $this->productsale_m->get_single_productsale(array('productsaleID' => $updateID, 'schoolID' => $schoolID));
                                if(customCompute($updatedata)) {
                                    $productitemschoolyearID = $updatedata->schoolyearID;
                                } else {
                                    $productitemschoolyearID = $schoolyearID;
                                }
                            }
                            foreach ($productitems as $productitem) {
                                $unitPrice = isset($productitem->unitprice) && $productitem->unitprice !== '' ? (float)$productitem->unitprice : 0;
                                $quantity = isset($productitem->quantity) && $productitem->quantity !== '' ? (float)$productitem->quantity : 0;
                                if($quantity <= 0) {
                                    continue;
                                }

                                $billingType = isset($productitem->billingType) && strtoupper($productitem->billingType) === 'NON_BILLABLE' ? 'NON_BILLABLE' : 'BILLABLE';
                                $nonbillableReason = isset($productitem->nonbillableReason) ? trim($productitem->nonbillableReason) : '';
                                $taxCode = isset($productitem->taxCode) ? trim($productitem->taxCode) : '';
                                $defaultPrice = (isset($productitem->defaultPrice) && $productitem->defaultPrice !== '') ? (float)$productitem->defaultPrice : null;

                                if($billingType === 'NON_BILLABLE' && $nonbillableReason === '') {
                                    $message = $this->lang->line('inventoryinvoice_nonbillable_reason_required');
                                    if(!$message) {
                                        $message = 'Non-billable items require a reason.';
                                    }
                                    $retArray['error'] = array('nonbillable' => $message);
                                    echo json_encode($retArray);
                                    exit;
                                }

                                $lineAmount = $unitPrice * $quantity;
                                if($billingType === 'BILLABLE') {
                                    $totalAmount += $lineAmount;
                                }

                                $unitPriceOverride = null;
                                if($defaultPrice !== null && round($unitPrice - $defaultPrice, 2) != 0.00) {
                                    $unitPriceOverride = $unitPrice;
                                }

                                $productsaleitem[] = array(
                                    'schoolyearID' => $productitemschoolyearID,
                                    'productsaleID' => $productsaleID,
                                    'productID' => $productitem->productID,
                                    'productsaleunitprice' => $unitPrice,
                                    'productsalequantity' => $quantity,
                                    'schoolID' => $schoolID,
                                    'billing_type' => $billingType,
                                    'nonbillable_reason' => $billingType === 'NON_BILLABLE' ? $nonbillableReason : null,
                                    'tax_code_override' => $taxCode !== '' ? $taxCode : null,
                                    'unit_price_override' => $unitPriceOverride,
                                );

                                $lineTotals[] = ($billingType === 'BILLABLE') ? $lineAmount : 0;
                            }
                        }

                        if(customCompute($productsaleitem)) {
                            $count   = customCompute($productsaleitem);
                            $firstID = $this->productsaleitem_m->insert_batch_productsaleitem($productsaleitem);

                            $lastID = $firstID + ($count - 1);

                            if($lastID >= $firstID) {
                                $j = 0;
                                for($i = $firstID; $i <= $lastID; $i++) {
                                    $invoice[] = [
                                        'schoolID'      => $invoiceMain['schoolID'],
                                        'schoolyearID'  => $invoiceMain['maininvoiceschoolyearID'],
                                        'classesID'     => $invoiceMain['maininvoiceclassesID'],
                                        'schooltermID'  => $invoiceMain['maininvoiceschooltermID'],
                                        'studentID'     => $invoiceMain['maininvoicestudentID'],
                                        'productsaleitemID' => $i,
                                        'productsaleitem' => $productsaleitem[$j]['productsalequantity'] ."*". $products[$productsaleitem[$j]['productID']],
                                        'amount'        => isset($lineTotals[$j]) ? $lineTotals[$j] : 0,
                                        'discount'      => 0,
                                        'userID'        => $invoiceMain['maininvoiceuserID'],
                                        'usertypeID'    => $invoiceMain['maininvoiceusertypeID'],
                                        'uname'         => $invoiceMain['maininvoiceuname'],
                                        'date'          => $invoiceMain['maininvoicedate'],
                                        'create_date'   => $invoiceMain['maininvoicecreate_date'],
                                        'day'           => $invoiceMain['maininvoiceday'],
                                        'month'         => $invoiceMain['maininvoicemonth'],
                                        'year'          => $invoiceMain['maininvoiceyear'],
                                        'deleted_at'    => $invoiceMain['maininvoicedeleted_at'],
                                        'maininvoiceID' => $maininvoiceID
                                    ];

                                    $j++;
                                }
                            }
                        }

                        $this->invoice_m->insert_batch_invoice($invoice);
                        $this->session->set_flashdata('success', $success);
                        $retArray['status'] = TRUE;
                        $retArray['message'] = 'Success';
                        echo json_encode($retArray);
                        exit;
                    }
                } else {
                    $retArray['error'] = array('posttype' => 'Post type is required.');
                    echo json_encode($retArray);
                    exit;
                }
            } else {
                $retArray['error'] = array('permission' => 'Sale permission is required.');
                echo json_encode($retArray);
                exit;
            }
        } else {
            $retArray['error'] = array('permission' => 'Sale permission is required.');
            echo json_encode($retArray);
            exit;
        }
    }

    public function delete() {
        if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('defaultschoolyearID') == 5)) {
            $id = htmlentities(escapeString($this->uri->segment(3)));
            if((int)$id) {
                $schoolID = $this->session->userdata('schoolID');
                $schoolyearID = $this->session->userdata('defaultschoolyearID');
                $this->data['productsale'] = $this->productsale_m->get_single_productsale(array('productsaleID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
                $this->data['productsalepaid'] = $this->productsalepaid_m->get_productsalepaid_sum('productsalepaidamount', array('productsaleID' => $id, 'schoolID' => $schoolID));

                if(customCompute($this->data['productsale'])) {
                    if(($this->data['productsale']->productsalerefund == 0) && ($this->data['productsalepaid']->productsalepaidamount == NULL)) {
                        $this->productsale_m->delete_productsale($id);
                        $this->productsaleitem_m->delete_productsaleitem_by_productsaleID($id);
                        $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                        redirect(base_url("inventoryinvoice/index"));
                    } else {
                        redirect(base_url("inventoryinvoice/index"));
                    }
                } else {
                    redirect(base_url("inventoryinvoice/index"));
                }
            } else {
                redirect(base_url("inventoryinvoice/index"));
            }
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function cancel() {
        if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('defaultschoolyearID') == 5)) {
            if(permissionChecker('inventoryinvoice_edit')) {
                $id = htmlentities(escapeString($this->uri->segment(3)));
                if((int)$id) {
                    $schoolyearID = $this->session->userdata('defaultschoolyearID');
                    $this->data['productsale'] = $this->productsale_m->get_single_productsale(array('productsaleID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $this->session->userdata('schoolID')));
                    if(customCompute($this->data['productsale'])) {
                        $this->productsale_m->update_productsale(array('productsalerefund' => 1), $id);
                        $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                        redirect(base_url("inventoryinvoice/index"));
                    } else {
                        redirect(base_url("inventoryinvoice/index"));
                    }
                } else {
                    redirect(base_url("inventoryinvoice/index"));
                }
            } else {
                redirect(base_url("inventoryinvoice/index"));
            }
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }
}
