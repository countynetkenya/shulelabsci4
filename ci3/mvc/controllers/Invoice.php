<?php if(!defined('BASEPATH'))
    exit('No direct script access allowed');
require_once(APPPATH . 'libraries/PaymentGateway/PaymentGateway.php');
require_once(APPPATH . 'libraries/PaymentGateway/Service/PaymentService.php');
require_once(APPPATH . '../vendor/autoload.php');
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Item;
use QuickBooksOnline\API\Facades\Invoice as QBInvoice;
use QuickBooksOnline\API\Facades\Account;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;

class Invoice extends Admin_Controller
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
    protected $_amountgivenstatus = '';
    protected $_amountgivenstatuserror = [];
    public $payment_gateway;
    public $payment_gateway_array;

    function __construct()
    {
        parent::__construct();
        $this->load->model("invoice_m");
        $this->load->model("feetypes_m");
        $this->load->model("bundlefeetypes_m");
        $this->load->model("bundlefeetype_feetypes_m");
        $this->load->model("invoice_feetypes_m");
        $this->load->model('payment_m');
        $this->load->model("classes_m");
        $this->load->model("student_m");
        $this->load->model("parents_m");
        $this->load->model("section_m");
        $this->load->model('user_m');
        $this->load->model('weaverandfine_m');
        $this->load->model("payment_settings_m");
        $this->load->model("globalpayment_m");
        $this->load->model("maininvoice_m");
        $this->load->model("studentrelation_m");
    		$this->load->model('studentgroup_m');
    		$this->load->model('schoolterm_m');
        $this->load->model('payment_gateway_m');
        $this->load->model('payment_gateway_option_m');
        $this->load->model("quickbookssettings_m");
        $this->load->model("quickbookslog_m");
        $language = $this->session->userdata('lang');
        $this->lang->load('student', $language);
        $this->lang->load('invoice', $language);
        $this->payment_gateway       = new PaymentGateway();
        $this->payment_gateway_array = pluck($this->payment_gateway_m->get_order_by_gateway_with_values(['payment_option' => '%status', 'payment_value' => '1']), 'payment_option', 'slug');
        if(!empty($this->payment_gateway_array)) {
            foreach($this->payment_gateway_array as $gateway_key => $gateway) {
                $this->lang->load($gateway_key .'_rules_lang.php', $language);
            }
        }
    }

    protected function rules()
    {
        $rules = [
            [
                'field' => 'classesID',
                'label' => $this->lang->line("invoice_classesID"),
                'rules' => 'trim|required|xss_clean|max_length[11]|numeric|callback_unique_classID'
            ],
            [
                'field' => 'studentID',
                'label' => $this->lang->line("invoice_studentID"),
                'rules' => 'trim|required|xss_clean|max_length[11]|numeric|callback_unique_studentID'
            ],
            [
                'field' => 'invoice_active',
                'label' => $this->lang->line("invoice_status"),
                'rules' => 'trim|xss_clean|callback_unique_invoiceactive'
            ],
			      [
                'field' => 'schooltermID',
                'label' => $this->lang->line("invoice_schooltermID"),
                'rules' => 'trim|required|xss_clean|numeric'
            ],
            [
                'field' => 'feetypeitems',
                'label' => $this->lang->line("invoice_feetypeitem"),
                'rules' => 'trim|xss_clean|required|callback_unique_feetypeitems'
            ],
            [
                'field' => 'date',
                'label' => $this->lang->line("invoice_date"),
                'rules' => 'trim|required|xss_clean|max_length[10]|callback_date_valid'
            ],
        ];

        return $rules;
    }

    protected function send_mail_rules()
    {
        $rules = [
            [
                'field' => 'id',
                'label' => $this->lang->line('invoice_id'),
                'rules' => 'trim|required|xss_clean|numeric|callback_valid_data'
            ],
            [
                'field' => 'to',
                'label' => $this->lang->line('to'),
                'rules' => 'trim|required|xss_clean|valid_email'
            ],
            [
                'field' => 'subject',
                'label' => $this->lang->line('subject'),
                'rules' => 'trim|required|xss_clean'
            ],
            [
                'field' => 'message',
                'label' => $this->lang->line('message'),
                'rules' => 'trim|xss_clean'
            ]
        ];
        return $rules;
    }

    public function index()
    {
        $usertypeID   = $this->session->userdata("usertypeID");
        $schoolID     = $this->session->userdata('schoolID');
        $schoolyearID = $this->session->userdata("defaultschoolyearID");

        if($usertypeID == 3) {
            $username = $this->session->userdata("username");
            $student  = $this->student_m->get_single_student(["username" => $username]);
            if(customCompute($student)) {
                $this->data['maininvoices']         = $this->maininvoice_m->get_maininvoice_with_studentrelation_by_studentID($student->studentID, $schoolyearID);
                $this->data['grandtotalandpayment'] = $this->grandtotalandpaid($this->data['maininvoices'], $schoolyearID, $schoolID);

                $this->data["subview"] = "invoice/index";
                $this->load->view('_layout_main', $this->data);
            } else {
                $this->data["subview"] = "error";
                $this->load->view('_layout_main', $this->data);
            }
        } elseif($usertypeID == 4) {
            $this->data['headerassets'] = [
                'css' => [
                    'assets/select2/css/select2.css',
                    'assets/select2/css/select2-bootstrap.css'
                ],
                'js'  => [
                    'assets/select2/select2.js'
                ]
            ];

            $parentID = $this->session->userdata("loginuserID");
            $students = $this->studentrelation_m->get_order_by_student([
                'parentID'       => $parentID,
                'srschoolyearID' => $schoolyearID
            ]);
            if(customCompute($students)) {
                $studentArray                       = pluck($students, 'srstudentID');
                $this->data['maininvoices']         = $this->maininvoice_m->get_maininvoice_with_studentrelation_by_multi_studentID($studentArray, $schoolyearID);
                $this->data['grandtotalandpayment'] = $this->grandtotalandpaid($this->data['maininvoices'], $schoolyearID, $schoolID);
                $this->data["subview"]              = "invoice/index";
                $this->load->view('_layout_main', $this->data);
            } else {
                $this->data['maininvoices']         = [];
                $this->data['grandtotalandpayment'] = [];
                $this->data["subview"]              = "invoice/index";
                $this->load->view('_layout_main', $this->data);
            }
        } else {
            $this->data['maininvoices']         = $this->maininvoice_m->get_maininvoice_with_studentrelation(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
      			$this->data['invoices']             = $this->invoice_m->get_invoice_with_studentrelation(array('schoolID' => $schoolID));
      			$this->data['grandtotalandpayment'] = $this->grandtotalandpaid($this->data['invoices'], $schoolyearID, $schoolID);
            $this->data["subview"]              = "invoice/index";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function add()
    {
        if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('defaultschoolyearID') == 5)) {
            $this->data['headerassets'] = [
                'css' => [
                    'assets/datepicker/datepicker.css',
                    'assets/select2/css/select2.css',
                    'assets/select2/css/select2-bootstrap.css'
                ],
                'js'  => [
                    'assets/datepicker/datepicker.js',
                    'assets/select2/select2.js'
                ]
            ];

            $schoolID = $this->session->userdata('schoolID');
            $this->data['classes']  = $this->classes_m->general_get_order_by_classes(array('schoolID' => $schoolID));
			      $this->data['terms']  = $this->schoolterm_m->get_order_by_schoolterm(array('schoolyearID' => $this->data['siteinfos']->school_year, 'schoolID' => $schoolID));
            $this->data['feetypes'] = $this->feetypes_m->get_order_by_feetypes(array('schoolID' => $schoolID));
            $this->data['bundlefeetypes'] = $this->bundlefeetypes_m->get_bundlefeetypes_with_feetypes_total();
			      $this->data['studentgroups'] = $this->studentgroup_m->get_order_by_studentgroup(array('schoolID' => $schoolID));
            $this->data['students'] = [];

            $config = $this->quickbooksConfig();

            if ($config['active'] == "1") {
              $dataService = DataService::Configure(array(
                  'auth_mode' => 'oauth2',
                  'ClientID' => $config['client_id'],
                  'ClientSecret' =>  $config['client_secret'],
                  'RedirectURI' => base_url() . "quickbooks/callback",
                  'scope' => 'com.intuit.quickbooks.accounting openid profile email phone address',
                  'baseUrl' => $config['stage']
              ));

              $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
              $authUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();

              // Store the url in PHP Session Object;
              $_SESSION['authUrl'] = $authUrl;
              $this->data["authUrl"] = $authUrl;
              $this->data["config"] = $config;
            }

            $this->data["subview"] = "invoice/add";
            $this->load->view('_layout_main', $this->data);
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function edit()
    {
        if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('defaultschoolyearID') == 5)) {
            $this->data['headerassets'] = [
                'css' => [
                    'assets/datepicker/datepicker.css',
                    'assets/select2/css/select2.css',
                    'assets/select2/css/select2-bootstrap.css'
                ],
                'js'  => [
                    'assets/datepicker/datepicker.js',
                    'assets/select2/select2.js'
                ]
            ];

            $maininvoiceID = htmlentities(escapeString($this->uri->segment(3)));
            if((int)$maininvoiceID) {
                $schoolID                    = $this->session->userdata('schoolID');
                $schoolyearID                = $this->session->userdata('defaultschoolyearID');
                $this->data['maininvoiceID'] = $maininvoiceID;
                $this->data['maininvoice']   = $this->maininvoice_m->get_single_maininvoice(['maininvoiceID' => $maininvoiceID, 'schoolID' => $schoolID]);
                if(customCompute($this->data['maininvoice'])) {
          					$this->data['classes']  = $this->classes_m->general_get_order_by_classes(array('schoolID' => $schoolID));
          					$this->data['feetypes'] = pluck($this->feetypes_m->get_order_by_feetypes(array('schoolID' => $schoolID)), 'obj', 'feetypesID');
                    $this->data['bundlefeetypes'] = pluck($this->bundlefeetypes_m->get_bundlefeetypes_with_feetypes_total(), 'obj', 'bundlefeetypesID');
                    $this->data['terms']  = $this->schoolterm_m->get_order_by_schoolterm(array('schoolyearID' => $this->data['siteinfos']->school_year, 'schoolID' => $schoolID));
          					$this->data['students'] = $this->studentrelation_m->get_order_by_studentrelation([
          						'srclassesID'    => $this->data['maininvoice']->maininvoiceclassesID,
          						'srschoolyearID' => $schoolyearID,
                      'srschoolID'     => $schoolID,
          					]);

					          $this->data['invoices'] = $this->invoice_m->get_order_by_invoice(['maininvoiceID' => $maininvoiceID, 'schoolID' => $schoolID]);

          					$this->data["subview"] = "invoice/edit";
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
    }

    public function delete()
    {
        if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('defaultschoolyearID') == 5)) {
            $maininvoiceID = htmlentities(escapeString($this->uri->segment(3)));
            if((int)$maininvoiceID) {
                $maininvoice = $this->maininvoice_m->get_single_maininvoice([
                    'maininvoiceID'         => $maininvoiceID,
                    'maininvoicedeleted_at' => 1,
                    'schoolID'              => $this->session->userdata('schoolID'),
                ]);
                if(customCompute($maininvoice)) {
          					$this->maininvoice_m->update_maininvoice(['maininvoicedeleted_at' => 0], $maininvoiceID);
          					$this->invoice_m->update_invoice_by_maininvoiceID(['deleted_at' => 0], $maininvoiceID);
          					$this->session->set_flashdata('success', $this->lang->line('menu_success'));
          					redirect(base_url('invoice/index'));
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

    public function view()
    {
        $usertypeID = $this->session->userdata("usertypeID");
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $schoolID = $this->session->userdata('schoolID');
        $this->data['feetypes'] = pluck($this->feetypes_m->get_order_by_feetypes(array('schoolID' => $schoolID)), 'feetypes', 'feetypesID');
        $this->data['bundlefeetypes'] = pluck($this->bundlefeetypes_m->get_order_by_bundlefeetypes(array('schoolID' => $schoolID)), 'bundlefeetypes', 'bundlefeetypesID');

        if($usertypeID == 3) {
            $id = htmlentities(escapeString($this->uri->segment(3)));
            if((int)$id) {
                $studentID  = $this->session->userdata("loginuserID");
                $getstudent = $this->studentrelation_m->get_single_student([
                    "srstudentID"    => $studentID,
                    'srschoolyearID' => $schoolyearID
                ]);
                if(customCompute($getstudent)) {
                    $this->data['maininvoice'] = $this->maininvoice_m->get_maininvoice_with_studentrelation_by_maininvoiceID(array('invoiceID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
                    if(customCompute($this->data['maininvoice']) && ($this->data['maininvoice']->maininvoicestudentID == $getstudent->studentID)) {
                        $this->data['invoices'] = $this->invoice_m->get_order_by_invoice(['maininvoiceID' => $id, 'schoolID' => $schoolID]);

                        $this->data['grandtotalandpayment'] = $this->grandtotalandpaidsingle($this->data['maininvoice'], $schoolyearID, $schoolID, $this->data["maininvoice"]->maininvoicestudentID);

                        $this->data["student"] = $this->student_m->get_single_student(['studentID' => $this->data["maininvoice"]->maininvoicestudentID]);

                        $this->data['createuser'] = getNameByUsertypeIDAndUserID($this->data['maininvoice']->maininvoiceusertypeID, $this->data['maininvoice']->maininvoiceuserID);

                        $this->data["subview"] = "invoice/view";
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
        } elseif($usertypeID == 4) {
            $id = htmlentities(escapeString($this->uri->segment(3)));
            if((int)$id) {
                $parentID     = $this->session->userdata("loginuserID");
                $getStudents  = $this->studentrelation_m->get_order_by_student([
                    'parentID'       => $parentID,
                    'srschoolyearID' => $schoolyearID
                ]);
                $fetchStudent = pluck($getStudents, 'srstudentID', 'srstudentID');
                if(customCompute($fetchStudent)) {
                    $this->data['maininvoice'] = $this->maininvoice_m->get_maininvoice_with_studentrelation_by_maininvoiceID(array('invoiceID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
                    if($this->data['maininvoice']) {
                        if(in_array($this->data['maininvoice']->maininvoicestudentID, $fetchStudent)) {
                            $this->data['invoices'] = $this->invoice_m->get_order_by_invoice(['maininvoiceID' => $id, 'schoolID' => $schoolID]);

                            $this->data['grandtotalandpayment'] = $this->grandtotalandpaidsingle($this->data['maininvoice'], $schoolyearID, $schoolID, $this->data["maininvoice"]->maininvoicestudentID);

                            $this->data["student"] = $this->student_m->get_single_student(['studentID' => $this->data["maininvoice"]->maininvoicestudentID]);

                            $this->data['createuser'] = getNameByUsertypeIDAndUserID($this->data['maininvoice']->maininvoiceusertypeID, $this->data['maininvoice']->maininvoiceuserID);

                            $this->data["subview"] = "invoice/view";
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
        } else {
            $id = htmlentities(escapeString($this->uri->segment(3)));
            if((int)$id) {
                $this->data['maininvoice'] = $this->maininvoice_m->get_maininvoice_with_studentrelation_by_maininvoiceID(array('invoiceID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
                $this->data['invoices']    = $this->invoice_m->get_order_by_invoice(['maininvoiceID' => $id, 'schoolID' => $schoolID]);

                if(customCompute($this->data["maininvoice"])) {
                    $this->data['grandtotalandpayment'] = $this->grandtotalandpaidsingle($this->data['maininvoice'], $schoolyearID, $schoolID, $this->data["maininvoice"]->maininvoicestudentID);

                    $this->data["student"] = $this->student_m->get_single_student(['studentID' => $this->data["maininvoice"]->maininvoicestudentID]);

                    $this->data['createuser'] = getNameByUsertypeIDAndUserID($this->data['maininvoice']->maininvoiceusertypeID, $this->data['maininvoice']->maininvoiceuserID);

                    $this->data["subview"] = "invoice/view";
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
    }

    public function print_preview()
    {
        if(permissionChecker('invoice_view')) {
            $schoolID               = $this->session->userdata('schoolID');
            $usertypeID             = $this->session->userdata("usertypeID");
            $schoolyearID           = $this->session->userdata('defaultschoolyearID');
            $this->data['feetypes'] = pluck($this->feetypes_m->get_order_by_feetypes(array('schoolID' => $schoolID)), 'feetypes', 'feetypesID');
            if($usertypeID == 3) {
                $id = htmlentities(escapeString($this->uri->segment(3)));
                if((int)$id) {
                    $studentID  = $this->session->userdata("loginuserID");
                    $getstudent = $this->studentrelation_m->get_single_student([
                        "srstudentID"    => $studentID,
                        'srschoolyearID' => $schoolyearID
                    ]);
                    if(customCompute($getstudent)) {
                        $this->data['maininvoice'] = $this->maininvoice_m->get_maininvoice_with_studentrelation_by_maininvoiceID(array('invoiceID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
                        if(customCompute($this->data['maininvoice']) && ($this->data['maininvoice']->maininvoicestudentID == $getstudent->studentID)) {
                            $this->data['invoices'] = $this->invoice_m->get_order_by_invoice(['maininvoiceID' => $id, 'schoolID' => $schoolID]);

                            $this->data['grandtotalandpayment'] = $this->grandtotalandpaidsingle($this->data['maininvoice'], $schoolyearID, $schoolID, $this->data["maininvoice"]->maininvoicestudentID);

                            $this->data["student"] = $this->student_m->get_single_student(['studentID' => $this->data["maininvoice"]->maininvoicestudentID]);

                            $this->data['createuser'] = getNameByUsertypeIDAndUserID($this->data['maininvoice']->maininvoiceusertypeID, $this->data['maininvoice']->maininvoiceuserID);
                            $this->reportPDF('invoicemodule.css', $this->data, 'invoice/print_preview');
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
            } elseif($usertypeID == 4) {
                $id = htmlentities(escapeString($this->uri->segment(3)));
                if((int)$id) {
                    $parentID     = $this->session->userdata("loginuserID");
                    $getstudents  = $this->studentrelation_m->get_order_by_student([
                        'parentID'       => $parentID,
                        'srschoolyearID' => $schoolyearID
                    ]);
                    $fetchStudent = pluck($getstudents, 'srstudentID', 'srstudentID');
                    if(customCompute($fetchStudent)) {
                        $this->data['maininvoice'] = $this->maininvoice_m->get_maininvoice_with_studentrelation_by_maininvoiceID(array('invoiceID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
                        if($this->data['maininvoice']) {
                            if(in_array($this->data['maininvoice']->maininvoicestudentID, $fetchStudent)) {
                                $this->data['invoices'] = $this->invoice_m->get_order_by_invoice(['maininvoiceID' => $id, 'schoolID' => $schoolID]);

                                $this->data['grandtotalandpayment'] = $this->grandtotalandpaidsingle($this->data['maininvoice'], $schoolyearID, $schoolID, $this->data["maininvoice"]->maininvoicestudentID);

                                $this->data["student"] = $this->student_m->get_single_student(['studentID' => $this->data["maininvoice"]->maininvoicestudentID]);

                                $this->data['createuser'] = getNameByUsertypeIDAndUserID($this->data['maininvoice']->maininvoiceusertypeID, $this->data['maininvoice']->maininvoiceuserID);

                                $this->reportPDF('invoicemodule.css', $this->data, 'invoice/print_preview');
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
                $id = htmlentities(escapeString($this->uri->segment(3)));
                if((int)$id) {
                    $this->data['maininvoice'] = $this->maininvoice_m->get_maininvoice_with_studentrelation_by_maininvoiceID(array('invoiceID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
                    $this->data['invoices']    = $this->invoice_m->get_order_by_invoice(['maininvoiceID' => $id, 'schoolID' => $schoolID]);

                    if($this->data["maininvoice"]) {
                        $this->data['grandtotalandpayment'] = $this->grandtotalandpaidsingle($this->data['maininvoice'], $schoolyearID, $schoolID, $this->data["maininvoice"]->maininvoicestudentID);

                        $this->data["student"] = $this->student_m->get_single_student(['studentID' => $this->data["maininvoice"]->maininvoicestudentID]);

                        $this->data['createuser'] = getNameByUsertypeIDAndUserID($this->data['maininvoice']->maininvoiceusertypeID, $this->data['maininvoice']->maininvoiceuserID);
                        $this->reportPDF('invoicemodule.css', $this->data, 'invoice/print_preview');
                    } else {
                        $this->data["subview"] = "error";
                        $this->load->view('_layout_main', $this->data);
                    }
                } else {
                    $this->data["subview"] = "error";
                    $this->load->view('_layout_main', $this->data);
                }
            }
        } else {
            $this->data["subview"] = "errorpermission";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function send_mail()
    {
        $schoolID               = $this->session->userdata('schoolID');
        $usertypeID             = $this->session->userdata("usertypeID");
        $schoolyearID           = $this->session->userdata('defaultschoolyearID');
        $this->data['feetypes'] = pluck($this->feetypes_m->get_order_by_feetypes(array('schoolID' => $schoolID)), 'feetypes', 'feetypesID');

        $retArray['status']  = FALSE;
        $retArray['message'] = '';
        if(permissionChecker('invoice_view')) {
            if($_POST) {
                $rules = $this->send_mail_rules();
                $this->form_validation->set_rules($rules);
                if($this->form_validation->run() == FALSE) {
                    $retArray           = $this->form_validation->error_array();
                    $retArray['status'] = FALSE;
                    echo json_encode($retArray);
                    exit;
                } else {
                    $to      = $this->input->post('to');
                    $subject = $this->input->post('subject');
                    $message = $this->input->post('message');
                    $id      = $this->input->post('id');
                    $f       = FALSE;

                    if($usertypeID == 3) {
                        if((int)$id) {
                            $studentID  = $this->session->userdata("loginuserID");
                            $getstudent = $this->studentrelation_m->get_single_student([
                                'srstudentID'    => $studentID,
                                'srschoolyearID' => $schoolyearID
                            ]);

                            $this->data['maininvoice'] = $this->maininvoice_m->get_maininvoice_with_studentrelation_by_maininvoiceID(array('invoiceID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
                            if(customCompute($this->data['maininvoice']) && ($this->data['maininvoice']->maininvoicestudentID == $getstudent->studentID)) {
                                $f = TRUE;
                            }
                        }
                    } elseif($usertypeID == 4) {
                        if((int)$id) {
                            $parentID     = $this->session->userdata("loginuserID");
                            $getStudents  = $this->studentrelation_m->get_order_by_student([
                                'parentID'       => $parentID,
                                'srschoolyearID' => $schoolyearID
                            ]);
                            $fetchStudent = pluck($getStudents, 'srstudentID', 'srstudentID');
                            if(customCompute($fetchStudent)) {
                                $this->data['maininvoice'] = $this->maininvoice_m->get_maininvoice_with_studentrelation_by_maininvoiceID(array('invoiceID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
                                if(customCompute($this->data['maininvoice'])) {
                                    if(in_array($this->data['maininvoice']->maininvoicestudentID, $fetchStudent)) {
                                        $f = TRUE;
                                    }
                                }
                            }
                        }
                    } else {
                        $f = TRUE;
                    }

                    if($f) {
                        $id = $this->input->post('id');
                        if((int)$id) {
                            $this->data['maininvoice'] = $this->maininvoice_m->get_maininvoice_with_studentrelation_by_maininvoiceID(array('invoiceID' => $id, 'schoolID' => $schoolID));
                            $this->data['invoices']    = $this->invoice_m->get_order_by_invoice(['maininvoiceID' => $id, 'schoolID' => $schoolID]);
                            if(customCompute($this->data["maininvoice"])) {
                                $this->data['grandtotalandpayment'] = $this->grandtotalandpaidsingle($this->data['maininvoice'], $schoolyearID, $schoolID, $this->data["maininvoice"]->maininvoicestudentID);

                                $this->data["student"] = $this->student_m->get_single_student(['studentID' => $this->data["maininvoice"]->maininvoicestudentID]);

                                $this->data['createuser'] = getNameByUsertypeIDAndUserID($this->data['maininvoice']->maininvoiceusertypeID, $this->data['maininvoice']->maininvoiceuserID);

                                $this->reportSendToMail('invoicemodule.css', $this->data, 'invoice/print_preview', $to, $subject, $message);
                                $retArray['message'] = "Success";
                                $retArray['status']  = TRUE;
                                echo json_encode($retArray);
                                exit;
                            } else {
                                $retArray['message'] = $this->lang->line('invoice_data_not_found');
                                echo json_encode($retArray);
                                exit;
                            }
                        } else {
                            $retArray['message'] = $this->lang->line('invoice_id_not_found');
                            echo json_encode($retArray);
                            exit;
                        }
                    } else {
                        $retArray['message'] = $this->lang->line('invoice_authorize');
                        echo json_encode($retArray);
                        exit;
                    }
                }
            } else {
                $retArray['message'] = $this->lang->line('invoice_postmethod');
                echo json_encode($retArray);
                exit;
            }
        } else {
            $retArray['message'] = $this->lang->line('invoice_permission');
            echo json_encode($retArray);
            exit;
        }
    }

    protected function payment_rules( $invoices )
    {
        $rules = [
            [
                'field' => 'payment_method',
                'label' => $this->lang->line("invoice_paymentmethod"),
                'rules' => 'trim|required|xss_clean|max_length[11]|callback_unique_payment_method'
            ]
        ];

        if($invoices) {
            if(customCompute($invoices)) {
                foreach($invoices as $invoice) {
                    if($invoice->paidstatus != 2) {
                        $rules[] = [
                            'field' => 'paidamount_' . $invoice->invoiceID,
                            'label' => $this->lang->line("invoice_amount"),
                            'rules' => 'trim|xss_clean|max_length[15]|callback_unique_givenamount'
                        ];

                        $rules[] = [
                            'field' => 'weaver_' . $invoice->invoiceID,
                            'label' => $this->lang->line("invoice_weaver"),
                            'rules' => 'trim|xss_clean|max_length[15]|callback_unique_givenamount'
                        ];

                        $rules[] = [
                            'field' => 'fine_' . $invoice->invoiceID,
                            'label' => $this->lang->line("invoice_fine"),
                            'rules' => 'trim|xss_clean|max_length[15]|callback_unique_givenamount'
                        ];
                    }
                }
            }
        }

        return $rules;
    }

    public function unique_givenamount( $postValue )
    {
        if($this->_amountgivenstatus == '') {
            $paidstatus   = FALSE;
            $weaverstatus = FALSE;
            $finestatus   = FALSE;
            $id           = htmlentities(escapeString($this->uri->segment(3)));
            $schoolyearID = $this->session->userdata('defaultschoolyearID');
            if((int)$id) {
                $maininvoice = $this->maininvoice_m->get_single_maininvoice(['maininvoiceID' => $id, 'schoolID' => $this->session->userdata('schoolID')]);
                if(customCompute($maininvoice)) {
                    $invoices                = $this->invoice_m->get_order_by_invoice([
                        'maininvoiceID' => $id,
                        'deleted_at'    => 1
                    ]);
                    $invoicepaymentandweaver = $this->paymentdue($maininvoice, $schoolyearID, $maininvoice->maininvoicestudentID);
                    if(customCompute($invoices)) {
                        foreach($invoices as $invoice) {
                            if($invoice->paidstatus != 2) {
                                if($this->input->post('paidamount_' . $invoice->invoiceID) != '') {
                                    $paidstatus = TRUE;
                                }

                                if($this->input->post('weaver_' . $invoice->invoiceID) != '') {
                                    $weaverstatus = TRUE;
                                }

                                if($this->input->post('fine_' . $invoice->invoiceID) != '') {
                                    $finestatus = TRUE;
                                }
                            }

                            $amount = 0;
                            if(isset($invoicepaymentandweaver['totalamount'][$invoice->invoiceID])) {
                                $amount += (float)$invoicepaymentandweaver['totalamount'][$invoice->invoiceID];
                            }

                            if(isset($invoicepaymentandweaver['totaldiscount'][$invoice->invoiceID])) {
                                $amount -= (float)$invoicepaymentandweaver['totaldiscount'][$invoice->invoiceID];
                            }

                            if((float)$amount < (float)((float)$this->input->post('paidamount_' . $invoice->invoiceID) + (float)$this->input->post('weaver_' . $invoice->invoiceID))) {
                                if($this->input->post('paidamount_' . $invoice->invoiceID) != '') {
                                    $this->_amountgivenstatuserror[] = (float)$this->input->post('paidamount_' . $invoice->invoiceID);
                                }

                                if($this->input->post('weaver_' . $invoice->invoiceID) != '') {
                                    $this->_amountgivenstatuserror[] = (float)$this->input->post('weaver_' . $invoice->invoiceID);
                                }
                            }
                        }
                    }
                }
            }

            if($this->session->userdata('usertypeID') == 1 || $this->session->userdata('usertypeID') == 5) {
                if($paidstatus || $weaverstatus || $finestatus) {
                    $this->_amountgivenstatus = TRUE;
                    return TRUE;
                } else {
                    $this->_amountgivenstatus = FALSE;
                    $this->form_validation->set_message("unique_givenamount", "The amount is required.");
                    return FALSE;
                }
            } else {
                if($paidstatus) {
                    $this->_amountgivenstatus = TRUE;
                    return TRUE;
                } else {
                    $this->_amountgivenstatus = FALSE;
                    $this->form_validation->set_message("unique_givenamount", "The amount is required.");
                    return FALSE;
                }
            }
        } else {
            if($this->_amountgivenstatus) {
                if($postValue != '') {
                    if(in_array((float)$postValue, $this->_amountgivenstatuserror)) {
                        $this->form_validation->set_message("unique_givenamount", "The amount is required.");
                        return FALSE;
                    } else {
                        return TRUE;
                    }
                } else {
                    return TRUE;
                }
            } else {
                $this->form_validation->set_message("unique_givenamount", "The amount is required.");
                return FALSE;
            }
        }
    }

    public function unique_payment_method()
    {
        $payment_methods = $this->payment_gateway_m->get_order_by_payment_gateway_with_values(['payment_option =%' => 'status', 'payment_value' => '1', 'schoolID' => $this->session->userdata('schoolID')]);
        if(in_array(ucfirst($this->input->post('payment_method')), $this->payment_methods($payment_methods))) {
            if(ucfirst($this->input->post('payment_method')) === 'Select') {
                $this->form_validation->set_message("unique_payment_method", "Payment method is required.");
                return false;
            } else {
                if(!$this->payment_gateway->gateway($this->input->post('payment_method'))->status()) {
                    $this->form_validation->set_message("unique_payment_method", "The Payment method is disable now, try other payment method system");
                    return false;
                }
                return true;
            }
        }
    }

    public function payment()
    {
        if(permissionChecker('invoice_view')) {
            $this->data['headerassets'] = [
                'css' => [
                    'assets/select2/css/select2.css',
                    'assets/select2/css/select2-bootstrap.css',
                    'assets/datepicker/datepicker.css',
                ],
                'js'  => [
                    'assets/datepicker/datepicker.js',
                    'assets/select2/select2.js'
                ]
            ];

            $id           = htmlentities(escapeString($this->uri->segment(3)));
            $schoolID     = $this->session->userdata('schoolID');
            $schoolyearID = $this->session->userdata('defaultschoolyearID');
            if((int)$id) {
                $maininvoice = $this->maininvoice_m->get_single_maininvoice(['maininvoiceID' => $id, 'schoolID' => $schoolID]);
                if(customCompute($maininvoice)) {
                    if($maininvoice->maininvoicestatus != 2) {
                        $this->data['student']        = $this->studentrelation_m->get_single_studentrelation([
                            'srstudentID'    => $maininvoice->maininvoicestudentID,
                            'srschoolyearID' => $schoolyearID
                        ]);
                        $this->data['studentprofile'] = $this->studentrelation_m->get_single_student([
                            'srstudentID'    => $maininvoice->maininvoicestudentID,
                            'srschoolyearID' => $schoolyearID
                        ]);
                        if(customCompute($this->data['student'])) {
                            $usertypeID = $this->session->userdata('usertypeID');
                            $userID     = $this->session->userdata('loginuserID');

                            $f = FALSE;
                            if($usertypeID == 3) {
                                if($this->data['student']->srstudentID == $userID) {
                                    $f = TRUE;
                                }
                            } elseif($usertypeID == 4) {
                                $parentID     = $this->session->userdata("loginuserID");
                                $getStudents  = $this->studentrelation_m->get_order_by_student([
                                    'parentID'       => $parentID,
                                    'srschoolyearID' => $schoolyearID
                                ]);
                                $fetchStudent = pluck($getStudents, 'srstudentID', 'srstudentID');
                                if(customCompute($fetchStudent)) {
                                    if(in_array($this->data['student']->srstudentID, $fetchStudent)) {
                                        $f = TRUE;
                                    }
                                }
                            } else {
                                $f = TRUE;
                            }

                            if($f) {
                                $this->data['usertype']                = $this->usertype_m->get_single_usertype(['usertypeID' => 3]);
                                $this->data['class']                   = $this->classes_m->general_get_single_classes(['classesID' => $this->data['student']->srclassesID]);
                                $this->data['section']                 = $this->section_m->general_get_single_section(['sectionID' => $this->data['student']->srsectionID]);
                                $this->data['invoices']                = $this->invoice_m->get_order_by_invoice(['maininvoiceID' => $id, 'deleted_at' => 1, 'schoolID' => $schoolID]);
                                $this->data['feetypes']                = pluck($this->feetypes_m->get_order_by_feetypes(array('schoolID' => $schoolID)), 'feetypes', 'feetypesID');
                                $this->data['invoicepaymentandweaver'] = $this->paymentdue($maininvoice, $schoolyearID, $this->data['student']->srstudentID);
                                $this->data['payment_settings']        = $this->payment_gateway_m->get_order_by_gateway_with_values(['payment_option =%' => 'status', 'payment_value' => 1, 'schoolID' => $schoolID]);
                                $this->data['payment_options']         = pluck($this->payment_gateway_option_m->get_payment_gateway_option_values(array('schoolID' => $schoolID)), 'payment_value', 'payment_option');
                                $this->data['payment_gateway']         = $this->payment_methods($this->data['payment_settings']);
                                $this->data['maininvoice']             = $maininvoice;
                                if($_POST) {
                                    $rules = $this->payment_rules($this->data['invoices']);
                                    $this->form_validation->set_rules($rules);
                                    if($this->form_validation->run() == FALSE) {
                                        $this->data["subview"] = "invoice/payment";
                                        $this->load->view('_layout_main', $this->data);
                                    } else {
                                        if($this->input->post('payment_method')) {
                                            $this->payment_gateway->gateway($this->input->post('payment_method'))->payment($this->input->post(), $maininvoice);
                                        } else {
                                            $this->session->set_flashdata('error', 'You are not authorized');
                                            redirect(base_url("invoice/payment/$id"));
                                        }
                                    }
                                } else {
                                    $this->data["subview"] = "invoice/payment";
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
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function viewpayment()
    {
        if(permissionChecker('invoice_view')) {
            $globalpaymentID = htmlentities(escapeString($this->uri->segment(3)));
            $maininvoiceID   = htmlentities(escapeString($this->uri->segment(4)));
            $schoolID        = $this->session->userdata('schoolID');
            $schoolyearID    = $this->session->userdata('defaultschoolyearID');
            if((int)$globalpaymentID && (int)$maininvoiceID) {
                $globalpayment = $this->globalpayment_m->get_single_globalpayment([
                    'globalpaymentID' => $globalpaymentID,
                    'schoolyearID'    => $schoolyearID,
                    'schoolID'        => $schoolID,
                ]);
                $maininvoice   = $this->maininvoice_m->get_single_maininvoice([
                    'maininvoiceID'           => $maininvoiceID,
                    'maininvoiceschoolyearID' => $schoolyearID,
                    'schoolID'                => $schoolID,
                ]);
                if(customCompute($maininvoice) && customCompute($globalpayment)) {
                    $usertypeID = $this->session->userdata('usertypeID');
                    $userID     = $this->session->userdata('loginuserID');

                    $f = FALSE;
                    if($usertypeID == 3) {
                        $getstudent = $this->studentrelation_m->get_single_studentrelation([
                            'srstudentID'    => $globalpayment->studentID,
                            'srschoolyearID' => $globalpayment->schoolyearID
                        ]);
                        if(customCompute($getstudent)) {
                            if($getstudent->srstudentID == $userID) {
                                $f = TRUE;
                            }
                        }
                    } elseif($usertypeID == 4) {
                        $parentID     = $this->session->userdata("loginuserID");
                        $schoolyearID = $this->session->userdata('defaultschoolyearID');
                        $getStudents  = $this->studentrelation_m->get_order_by_student([
                            'parentID'       => $parentID,
                            'srschoolyearID' => $schoolyearID
                        ]);
                        $fetchStudent = pluck($getStudents, 'srstudentID', 'srstudentID');
                        if(customCompute($fetchStudent)) {
                            if(in_array($globalpayment->studentID, $fetchStudent)) {
                                $f = TRUE;
                            }
                        }
                    } else {
                        $f = TRUE;
                    }

                    if($f) {
                        $studentrelation = $this->studentrelation_m->get_single_studentrelation([
                            'srstudentID'    => $globalpayment->studentID,
                            'srschoolyearID' => $globalpayment->schoolyearID
                        ]);
                        if(customCompute($studentrelation)) {
                            $this->data['feetypes'] = pluck($this->feetypes_m->get_order_by_feetypes(array('schoolID' => $schoolID)), 'feetypes', 'feetypesID');
                            $this->data['student']  = $this->student_m->get_single_student(['studentID' => $globalpayment->studentID]);
                            $this->data['invoices'] = pluck($this->invoice_m->get_order_by_invoice(['maininvoiceID' => $maininvoiceID, 'schoolID' => $schoolID]), 'obj', 'invoiceID');

                            $this->payment_m->order_payment('paymentID', 'asc');
                            $this->data['payments']       = $this->payment_m->get_order_by_payment(['globalpaymentID' => $globalpaymentID, 'schoolID' => $schoolID]);
                            $this->data['weaverandfines'] = pluck($this->weaverandfine_m->get_order_by_weaverandfine(['globalpaymentID' => $globalpaymentID, 'schoolID' => $schoolID]), 'obj', 'paymentID');

                            $this->data['paymenttype'] = '';
                            if(customCompute($this->data['payments'])) {
                                foreach($this->data['payments'] as $payment) {
                                    $this->data['paymenttype'] = $payment->paymenttype;
                                    break;
                                }
                            }

                            $this->data['studentrelation'] = $studentrelation;
                            $this->data['globalpayment']   = $globalpayment;
                            $this->data['maininvoice']     = $maininvoice;

                            $this->data["subview"] = "invoice/viewpayment";
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
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function print_previewviewpayment()
    {
        if(permissionChecker('invoice_view')) {
            $globalpaymentID = htmlentities(escapeString($this->uri->segment(3)));
            $maininvoiceID   = htmlentities(escapeString($this->uri->segment(4)));
            $schoolID        = $this->session->userdata('schoolID');
            $schoolyearID    = $this->session->userdata('defaultschoolyearID');

            if((int)$globalpaymentID && (int)$maininvoiceID) {
                $globalpayment = $this->globalpayment_m->get_single_globalpayment([
                    'globalpaymentID' => $globalpaymentID,
                    'schoolyearID'    => $schoolyearID,
                    'schoolID'        => $schoolID,
                ]);
                $maininvoice   = $this->maininvoice_m->get_single_maininvoice([
                    'maininvoiceID'           => $maininvoiceID,
                    'maininvoiceschoolyearID' => $schoolyearID,
                    'schoolID'                => $schoolID,
                ]);
                if(customCompute($maininvoice) && customCompute($globalpayment)) {
                    $usertypeID = $this->session->userdata('usertypeID');
                    $userID     = $this->session->userdata('loginuserID');

                    $f = FALSE;
                    if($usertypeID == 3) {
                        $getstudent = $this->studentrelation_m->get_single_studentrelation([
                            'srstudentID'    => $globalpayment->studentID,
                            'srschoolyearID' => $globalpayment->schoolyearID
                        ]);
                        if(customCompute($getstudent)) {
                            if($getstudent->srstudentID == $userID) {
                                $f = TRUE;
                            }
                        }
                    } elseif($usertypeID == 4) {
                        $parentID     = $this->session->userdata("loginuserID");
                        $schoolyearID = $this->session->userdata('defaultschoolyearID');
                        $getStudents  = $this->studentrelation_m->get_order_by_student([
                            'parentID'       => $parentID,
                            'srschoolyearID' => $schoolyearID
                        ]);
                        $fetchStudent = pluck($getStudents, 'srstudentID', 'srstudentID');
                        if(customCompute($fetchStudent)) {
                            if(in_array($globalpayment->studentID, $fetchStudent)) {
                                $f = TRUE;
                            }
                        }
                    } else {
                        $f = TRUE;
                    }

                    if($f) {
                        $studentrelation = $this->studentrelation_m->get_single_studentrelation([
                            'srstudentID'    => $globalpayment->studentID,
                            'srschoolyearID' => $globalpayment->schoolyearID
                        ]);
                        if(customCompute($studentrelation)) {
                            $this->data['feetypes'] = pluck($this->feetypes_m->get_order_by_feetypes(array('schoolID' => $schoolID)), 'feetypes', 'feetypesID');
                            $this->data['student']  = $this->student_m->get_single_student(['studentID' => $globalpayment->studentID]);
                            $this->data['invoices'] = pluck($this->invoice_m->get_order_by_invoice(['maininvoiceID' => $maininvoiceID, 'schoolID' => $schoolID]), 'obj', 'invoiceID');
                            $this->payment_m->order_payment('paymentID', 'asc');
                            $this->data['payments']       = $this->payment_m->get_order_by_payment(['globalpaymentID' => $globalpaymentID, 'schoolID' => $schoolID]);
                            $this->data['weaverandfines'] = pluck($this->weaverandfine_m->get_order_by_weaverandfine(['globalpaymentID' => $globalpaymentID, 'schoolID' => $schoolID]), 'obj', 'paymentID');

                            $this->data['paymenttype'] = '';
                            if(customCompute($this->data['payments'])) {
                                foreach($this->data['payments'] as $payment) {
                                    $this->data['paymenttype'] = $payment->paymenttype;
                                    break;
                                }
                            }

                            $this->data['studentrelation'] = $studentrelation;
                            $this->data['globalpayment']   = $globalpayment;
                            $this->data['maininvoice']     = $maininvoice;

                            $this->reportPDF('invoicemodulepayment.css', $this->data, 'invoice/print_previewviewpayment');
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

    protected function viewpayment_send_mail_rules()
    {
        $rules = [
            [
                'field' => 'globalpaymentID',
                'label' => $this->lang->line('invoice_globalpaymentID'),
                'rules' => 'trim|required|xss_clean|numeric|callback_valid_data'
            ],
            [
                'field' => 'maininvoiceID',
                'label' => $this->lang->line('invoice_maininvoiceID'),
                'rules' => 'trim|required|xss_clean|numeric|callback_valid_data'
            ],
            [
                'field' => 'to',
                'label' => $this->lang->line('to'),
                'rules' => 'trim|required|xss_clean|valid_email'
            ],
            [
                'field' => 'subject',
                'label' => $this->lang->line('subject'),
                'rules' => 'trim|required|xss_clean'
            ],
            [
                'field' => 'message',
                'label' => $this->lang->line('message'),
                'rules' => 'trim|xss_clean'
            ]
        ];
        return $rules;
    }

    public function viewpayment_send_mail()
    {
        $retArray['status']  = FALSE;
        $retArray['message'] = '';
        if(permissionChecker('invoice_view')) {
            if($_POST) {
                $rules = $this->viewpayment_send_mail_rules();
                $this->form_validation->set_rules($rules);
                if($this->form_validation->run() == FALSE) {
                    $retArray           = $this->form_validation->error_array();
                    $retArray['status'] = FALSE;
                    echo json_encode($retArray);
                    exit;
                } else {
                    $schoolID        = $this->session->userdata('schoolID');
                    $schoolyearID    = $this->session->userdata('defaultschoolyearID');
                    $globalpaymentID = $this->input->post('globalpaymentID');
                    $maininvoiceID   = $this->input->post('maininvoiceID');
                    $to              = $this->input->post('to');
                    $subject         = $this->input->post('subject');
                    $message         = $this->input->post('message');

                    if((int)$globalpaymentID && (int)$maininvoiceID) {
                        $globalpayment = $this->globalpayment_m->get_single_globalpayment([
                            'globalpaymentID' => $globalpaymentID,
                            'schoolyearID'    => $schoolyearID,
                            'schoolID'        => $schoolID,
                        ]);
                        $maininvoice   = $this->maininvoice_m->get_single_maininvoice([
                            'maininvoiceID'           => $maininvoiceID,
                            'maininvoiceschoolyearID' => $schoolyearID,
                            'schoolID'                => $schoolID,
                        ]);

                        if(customCompute($maininvoice) && customCompute($globalpayment)) {
                            $usertypeID = $this->session->userdata('usertypeID');
                            $userID     = $this->session->userdata('loginuserID');

                            $f = FALSE;
                            if($usertypeID == 3) {
                                $getstudent = $this->studentrelation_m->get_single_studentrelation([
                                    'srstudentID'    => $globalpayment->studentID,
                                    'srschoolyearID' => $globalpayment->schoolyearID
                                ]);
                                if(customCompute($getstudent)) {
                                    if($getstudent->srstudentID == $userID) {
                                        $f = TRUE;
                                    }
                                }
                            } elseif($usertypeID == 4) {
                                $parentID     = $this->session->userdata("loginuserID");
                                $schoolyearID = $this->session->userdata('defaultschoolyearID');
                                $getStudents  = $this->studentrelation_m->get_order_by_student([
                                    'parentID'       => $parentID,
                                    'srschoolyearID' => $schoolyearID
                                ]);
                                $fetchStudent = pluck($getStudents, 'srstudentID', 'srstudentID');
                                if(customCompute($fetchStudent)) {
                                    if(in_array($globalpayment->studentID, $fetchStudent)) {
                                        $f = TRUE;
                                    }
                                }
                            } else {
                                $f = TRUE;
                            }

                            if($f) {
                                $studentrelation = $this->studentrelation_m->get_single_studentrelation([
                                    'srstudentID'    => $globalpayment->studentID,
                                    'srschoolyearID' => $globalpayment->schoolyearID
                                ]);
                                if(customCompute($studentrelation)) {
                                    $this->data['feetypes']       = pluck($this->feetypes_m->get_order_by_feetypes(array('schoolID' => $schoolID)), 'feetypes', 'feetypesID');
                                    $this->data['student']        = $this->student_m->get_single_student(['studentID' => $globalpayment->studentID]);
                                    $this->data['invoices']       = pluck($this->invoice_m->get_order_by_invoice(['maininvoiceID' => $maininvoiceID, 'schoolID' => $schoolID]), 'obj', 'invoiceID');
                                    $this->payment_m->order_payment('paymentID', 'asc');
                                    $this->data['payments']       = $this->payment_m->get_order_by_payment(['globalpaymentID' => $globalpaymentID, 'schoolID' => $schoolID]);
                                    $this->data['weaverandfines'] = pluck($this->weaverandfine_m->get_order_by_weaverandfine(['globalpaymentID' => $globalpaymentID, 'schoolID' => $schoolID]), 'obj', 'paymentID');

                                    $this->data['paymenttype'] = '';
                                    if(customCompute($this->data['payments'])) {
                                        foreach($this->data['payments'] as $payment) {
                                            $this->data['paymenttype'] = $payment->paymenttype;
                                            break;
                                        }
                                    }

                                    $this->data['studentrelation'] = $studentrelation;
                                    $this->data['globalpayment']   = $globalpayment;
                                    $this->data['maininvoice']     = $maininvoice;

                                    $this->reportSendToMail('invoicemodulepayment.css', $this->data, 'invoice/print_previewviewpayment', $to, $subject, $message);
                                    $retArray['message'] = "Success";
                                    $retArray['status']  = TRUE;
                                    echo json_encode($retArray);
                                } else {
                                    $retArray['message'] = $this->lang->line('invoice_data_not_found');
                                    echo json_encode($retArray);
                                    exit;
                                }
                            } else {
                                $retArray['message'] = $this->lang->line('invoice_data_not_found');
                                echo json_encode($retArray);
                                exit;
                            }
                        } else {
                            $retArray['message'] = $this->lang->line('invoice_data_not_found');
                            echo json_encode($retArray);
                            exit;
                        }
                    } else {
                        $retArray['message'] = $this->lang->line('invoice_data_not_found');
                        echo json_encode($retArray);
                        exit;
                    }
                }
            } else {
                $retArray['message'] = $this->lang->line('invoice_postmethod');
                echo json_encode($retArray);
                exit;
            }
        } else {
            $retArray['message'] = $this->lang->line('invoice_permission');
            echo json_encode($retArray);
            exit;
        }
    }

    public function valid_data( $data )
    {
        if($data == 0) {
            $this->form_validation->set_message('valid_data', 'The %s field is required.');
            return FALSE;
        }
        return TRUE;
    }

    public function unique_classID()
    {
        if($this->input->post('classesID') == 0) {
            $this->form_validation->set_message("unique_classID", "The %s field is required");
            return FALSE;
        }
        return TRUE;
    }

    public function unique_studentID()
    {
        $id = $this->input->post('editID');
        if((int)$id && $id > 0) {
            if($this->input->post('studentID') == 0) {
                $this->form_validation->set_message("unique_studentID", "%s field is required.");
                return FALSE;
            }
        }
        return TRUE;
    }

    public function date_valid( $date )
    {
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
    }

    public function unique_invoiceactive()
  	{
  			$array = ['', 1, 2];

  			if(!in_array($this->input->post('invoice_active'), $array)) {
  					$this->form_validation->set_message("unique_invoiceactive", "The %s field is required.");
  					return FALSE;
  			}
  			return TRUE;
  	}

    public function unique_feetypeitems()
    {
        $feetypeitems = json_decode($this->input->post('feetypeitems'));
        $status       = [];
        if(customCompute($feetypeitems)) {
            foreach($feetypeitems as $feetypeitem) {
                if($feetypeitem->amount == '') {
                    $status[] = FALSE;
                }
            }
        } else {
            $this->form_validation->set_message("unique_feetypeitems", "The fee type item is required.");
            return FALSE;
        }

        if(in_array(FALSE, $status)) {
            $this->form_validation->set_message("unique_feetypeitems", "The fee type amount is required.");
            return FALSE;
        }
        return TRUE;
    }

    public function getstudent()
    {
        $classesID = $this->input->post('classesID');
		    $studentGroupID = $this->input->post('studentGroupID');
        $schoolID = $this->session->userdata('schoolID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');

        if($this->input->post('edittype')) {
            echo '<option value="0">' . $this->lang->line('invoice_select_student') . '</option>';
        } else {
            echo '<option value="0">' . $this->lang->line('invoice_all_student') . '</option>';
        }

        $students = $this->studentrelation_m->get_order_by_student([
            'srschoolyearID'   => $schoolyearID,
            'srclassesID'      => $classesID,
			      'srstudentGroupID' => $studentGroupID,
            'srschoolID'       => $schoolID,
        ]);
        if(customCompute($students)) {
            foreach($students as $student) {
                echo "<option value=\"$student->srstudentID\">" . $student->srname . " - " . $this->lang->line('invoice_registerno') . " - " . $student->srstudentID . "</option>";
            }
        }
    }

    public function saveinvoice()
    {
        $maininvoiceID      = 0;
        $retArray['status'] = FALSE;
        if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('defaultschoolyearID') == 5)) {
            if(permissionChecker('invoice_add') || permissionChecker('invoice_edit')) {
                if($_POST) {
					          $rules = $this->rules();
                    $this->form_validation->set_rules($rules);
                    if($this->form_validation->run() == FALSE) {
                        $retArray['error']  = $this->form_validation->error_array();
                        $retArray['status'] = FALSE;
                        echo json_encode($retArray);
                        exit;
                    } else {
                        $invoiceMainArray     = [];
                        $invoiceArray         = [];
                        $invoiceFeetypesArray = [];
                        $studentArray         = [];
                        $schoolID             = $this->session->userdata('schoolID');
                        $feetype              = pluck($this->feetypes_m->get_order_by_feetypes(array('schoolID' => $schoolID)), 'feetypes', 'feetypesID');
                        $bundlefeetype        = pluck($this->bundlefeetypes_m->get_order_by_bundlefeetypes(array('schoolID' => $schoolID)), 'bundlefeetypes', 'bundlefeetypesID');
                        $feetypeitems         = json_decode($this->input->post('feetypeitems'));
                        $schoolyearID         = $this->session->userdata('defaultschoolyearID');

                        $studentID = $this->input->post('studentID');
                        $classesID = $this->input->post('classesID');
						            $invoicedate = date("Y-m-d", strtotime($this->input->post("date")));
						            $studentGroupID = $this->input->post('studentGroupID');
                        $feetypeID = $this->input->post('feetypeID');
                        $bundlefeetypeID = $this->input->post('bundlefeetypeID');
                        $active = $this->input->post('invoice_active');
                        $query = [];
                        $query['srclassesID'] = $classesID;
                        $query['srstudentGroupID'] = $studentGroupID;
                        $query['srschoolID'] = $schoolID;
                        $query['srschoolyearID'] = $schoolyearID;
          							if((int)$active) {
          								$query['active'] = $active;
          							}
                        if(((int)$studentID || $studentID == 0) && (int)($classesID)) {
                            if($studentID == 0) {
                                $getstudents = $this->studentrelation_m->get_order_by_student($query);
                            } else {
                                $getstudents = $this->studentrelation_m->get_order_by_student([
                                    "srclassesID"    => $classesID,
                                    'srstudentID'    => $studentID,
                                    'srschoolyearID' => $schoolyearID,
                                    'srschoolID'     => $schoolID,
                                ]);
                            }

                            if(customCompute($getstudents)) {
                                foreach($getstudents as $key => $getstudent) {
                                    $invoiceMainArray[] = [
                                        'schoolID'                => $schoolID,
                                        'maininvoiceschoolyearID' => $schoolyearID,
                                        'maininvoiceclassesID'    => $this->input->post('classesID'),
										                    'maininvoiceschooltermID' => $this->input->post('schooltermID'),
                                        'maininvoicestudentID'    => $getstudent->srstudentID,
                                        'maininvoiceuserID'       => $this->session->userdata('loginuserID'),
                                        'maininvoiceusertypeID'   => $this->session->userdata('usertypeID'),
                                        'maininvoiceuname'        => $this->session->userdata('name'),
                                        'maininvoicedate'         => $invoicedate,
                                        'maininvoicecreate_date'  => date('Y-m-d'),
                                        'maininvoiceday'          => date('d'),
                                        'maininvoicemonth'        => date('m'),
                                        'maininvoiceyear'         => date('Y'),
										                    'maininvoicememo'         => $this->input->post('memo'),
                                        'maininvoicedeleted_at'   => 1
                                    ];

                                    $studentArray[] = $getstudent->srstudentID;
                                }

                                if(customCompute($invoiceMainArray)) {
                                    $count   = customCompute($invoiceMainArray);
                                    $firstID = $this->maininvoice_m->insert_batch_maininvoice($invoiceMainArray);

                                    $lastID = $firstID + ($count - 1);

                                    if($lastID >= $firstID) {
                                        $j = 0;
                                        for($i = $firstID; $i <= $lastID; $i++) {
                                            if(customCompute($feetypeitems)) {
                                              foreach($feetypeitems as $feetypeitem) {
                                                  $invoiceArray[] = [
                                                      'schoolID'      => $invoiceMainArray[$j]['schoolID'],
                                                      'schoolyearID'  => $invoiceMainArray[$j]['maininvoiceschoolyearID'],
                                                      'classesID'     => $invoiceMainArray[$j]['maininvoiceclassesID'],
                                                      'schooltermID'  => $invoiceMainArray[$j]['maininvoiceschooltermID'],
                                                      'studentID'     => $invoiceMainArray[$j]['maininvoicestudentID'],
                                                      'feetypeID'     => isset($feetypeitem->feetypeID) ? $feetypeitem->feetypeID : NULL,
                                                      'feetype'       => isset($feetype[$feetypeitem->feetypeID]) ? $feetype[$feetypeitem->feetypeID] : '',
                                                      'bundlefeetypeID'     => isset($feetypeitem->bundlefeetypeID) ? $feetypeitem->bundlefeetypeID : NULL,
                                                      'bundlefeetype'       => isset($bundlefeetype[$feetypeitem->bundlefeetypeID]) ? $bundlefeetype[$feetypeitem->bundlefeetypeID] : NULL,
                                                      'amount'        => isset($feetypeitem->amount) ? $feetypeitem->amount : 0,
                                                      'discount'      => (isset($feetypeitem->discount) ? (($feetypeitem->discount == '') ? 0 : $feetypeitem->discount) : 0),
                                                      'userID'        => $invoiceMainArray[$j]['maininvoiceuserID'],
                                                      'usertypeID'    => $invoiceMainArray[$j]['maininvoiceusertypeID'],
                                                      'uname'         => $invoiceMainArray[$j]['maininvoiceuname'],
                                                      'date'          => $invoiceMainArray[$j]['maininvoicedate'],
                                                      'create_date'   => $invoiceMainArray[$j]['maininvoicecreate_date'],
                                                      'day'           => $invoiceMainArray[$j]['maininvoiceday'],
                                                      'month'         => $invoiceMainArray[$j]['maininvoicemonth'],
                                                      'year'          => $invoiceMainArray[$j]['maininvoiceyear'],
                                                      'deleted_at'    => $invoiceMainArray[$j]['maininvoicedeleted_at'],
                                                      'maininvoiceID' => $i
                                                  ];
                                                }
                                            }
                                            $j++;
                                        }
                                    }
                                }

                                if (!empty($invoiceArray)) {
                                    $config = $this->quickbooksConfig();
                                    $invoicefirstID = $this->invoice_m->insert_batch_invoice($invoiceArray);
                                    $student = $this->student_m->get_single_student(['studentID' => $studentID, 'schoolID' => $schoolID]);
                                    $invoicecount   = customCompute($invoiceArray);
                                    $invoicelastID  = $invoicefirstID + ($invoicecount - 1);
                                    if($invoicelastID >= $invoicefirstID) {
                                        $k = 0;
                                        for($i = $invoicefirstID; $i <= $invoicelastID; $i++) {
                                            if (isset($invoiceArray[$k]['bundlefeetypeID'])) {
                                                $bundlefeetype_feetypes = $this->bundlefeetype_feetypes_m->get_order_by_bundlefeetype_feetypes(array("bundlefeetypesID" => $invoiceArray[$k]['bundlefeetypeID']));
                                                foreach ($bundlefeetype_feetypes as $bundlefeetype_feetype) {
                                                    $invoiceFeetypesArray[] = [
                                                        'invoiceID' => $i,
                                                        'bundlefeetypesID' => $invoiceArray[$k]['bundlefeetypeID'],
                                                        'feetypesID' => $bundlefeetype_feetype->feetypesID,
                                                        'amount' => $bundlefeetype_feetype->amount
                                                    ];
                                                    $array[] = [
                                                      'feetypeID' => $bundlefeetype_feetype->feetypesID,
                                                      'feetype' => $bundlefeetype_feetype->feetypes,
                                                      'amount' => $bundlefeetype_feetype->amount,
                                                      'date' => $invoiceArray[$k]->date
                                                    ];
                                                    if ($config['active'] == "1" && now() < $config['sessionAccessTokenExpiry'])
                                                      $this->invoiceAndBilling($config, $student, $array, $i);
                                                }
                                            }
                                            else {
                                                if ($config['active'] == "1" && now() < $config['sessionAccessTokenExpiry'])
                                                  $this->invoiceAndBilling($config, $student, $invoiceArray[$k], $i);
                                            }
                                            $k++;
                                        }
                                    }

                                    if (!empty($invoiceFeetypesArray))
                                        $this->invoice_feetypes_m->insert_batch_invoice_feetypes($invoiceFeetypesArray);
                                }

                                $invoiceSubtotalStatus = 1;
                                if((float)$this->input->post('totalsubtotal') == (float)0) {
                                    $invoiceSubtotalStatus = 0;
                                }

                                $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                                $retArray['status']  = TRUE;
                                $retArray['message'] = 'Success';
                                echo json_encode($retArray);
                                exit;
                            } else {
                                $retArray['error'] = ['student' => 'Student not found.'];
                                echo json_encode($retArray);
                                exit;
                            }
                        } else {
                            $retArray['error'] = ['classstudent' => 'Class and Student not found.'];
                            echo json_encode($retArray);
                            exit;
                        }
                    }
                } else {
                    $retArray['error'] = ['posttype' => 'Post type is required.'];
                    echo json_encode($retArray);
                    exit;
                }
            } else {
                $retArray['error'] = ['permission' => 'Invoice permission is required.'];
                echo json_encode($retArray);
                exit;
            }
        } else {
            $retArray['error'] = ['permission' => 'Permission Denied.'];
            echo json_encode($retArray);
            exit;
        }
    }

    public function saveinvoicefforedit()
    {
        if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('defaultschoolyearID') == 5)) {
            $maininvoiceID      = 0;
            $retArray['status'] = FALSE;
            if(permissionChecker('invoice_edit')) {
                if($_POST) {
                    $rules = $this->rules();
                    $this->form_validation->set_rules($rules);
                    if($this->form_validation->run() == FALSE) {
                        $retArray['error']  = $this->form_validation->error_array();
                        $retArray['status'] = FALSE;
                        echo json_encode($retArray);
                        exit;
                    } else {
                        $mainInvoiceArray    = [];
                        $invoiceArray        = [];

                        $editID = $this->input->post('editID');
                        if((int)$editID) {
                            $schoolID = $this->session->userdata('schoolID');
                            $feetype = pluck($this->feetypes_m->get_order_by_feetypes(array('schoolID' => $schoolID)), 'feetypes', 'feetypesID');
                            $bundlefeetype = pluck($this->bundlefeetypes_m->get_order_by_bundlefeetypes(array('schoolID' => $schoolID)), 'bundlefeetypes', 'bundlefeetypesID');
                            $feetypeitems = json_decode($this->input->post('feetypeitems'));
                            $schoolyearID = $this->session->userdata('defaultschoolyearID');
                            $invoicedate = date("Y-m-d", strtotime($this->input->post("date")));
                            $studentID = $this->input->post('studentID');
                            $classesID = $this->input->post('classesID');

                            if((int)$studentID && (int)$classesID) {
                                $getstudent = $this->studentrelation_m->get_single_student([
                                    "srclassesID"    => $classesID,
                                    'srstudentID'    => $studentID,
                                    'srschoolyearID' => $schoolyearID,
                                    'srschoolID'     => $schoolID,
                                ]);
                                if(customCompute($getstudent)) {
                                    if(customCompute($feetypeitems)) {
                                        foreach($feetypeitems as $feetypeitem) {
                                            $invoiceArray[] = [
                                                'schoolID'      => $schoolID,
                                                'schoolyearID'  => $schoolyearID,
												                        'schooltermID'  => $this->input->post('schooltermID'),
                                                'classesID'     => $this->input->post('classesID'),
                                                'studentID'     => $getstudent->srstudentID,
                                                'feetypeID'     => isset($feetypeitem->feetypeID) ? $feetypeitem->feetypeID : 0,
                                                'feetype'       => isset($feetype[$feetypeitem->feetypeID]) ? $feetype[$feetypeitem->feetypeID] : '',
                                                'bundlefeetypeID'     => isset($feetypeitem->bundlefeetypeID) ? $feetypeitem->bundlefeetypeID : NULL,
                                                'bundlefeetype'       => isset($bundlefeetype[$feetypeitem->bundlefeetypeID]) ? $bundlefeetype[$feetypeitem->bundlefeetypeID] : NULL,
                                                'amount'        => isset($feetypeitem->amount) ? $feetypeitem->amount : 0,
                                                'discount'      => (isset($feetypeitem->discount) ? (($feetypeitem->discount == '') ? 0 : $feetypeitem->discount) : 0),
                                                'userID'        => $this->session->userdata('loginuserID'),
                                                'usertypeID'    => $this->session->userdata('usertypeID'),
                                                'uname'         => $this->session->userdata('name'),
                                                'date'          => $invoicedate,
                                                'create_date'   => date('Y-m-d'),
                                                'day'           => date('d'),
                                                'month'         => date('m'),
                                                'year'          => date('Y'),
                                                'deleted_at'    => 1,
                                                'maininvoiceID' => $editID
                                            ];
                                        }
                                    }

                                    $this->invoice_m->delete_invoice_by_maininvoiceID($editID);

                                    $invoicefirstID = $this->invoice_m->insert_batch_invoice($invoiceArray);

                  									if(customCompute($invoiceArray)) {
                  										$mainInvoiceArray = [
                  										  'maininvoicedate' => date("Y-m-d", strtotime($this->input->post("date"))),
                  											'maininvoicememo' => $this->input->post('memo'),
                  										];

                  										$this->maininvoice_m->update_maininvoice($mainInvoiceArray, $editID);
                  									}

                                    $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                                    $retArray['status']  = TRUE;
                                    $retArray['message'] = 'Success';
                                    echo json_encode($retArray);
                                    exit;
                                } else {
                                    $retArray['error'] = ['student' => 'Student not found.'];
                                    echo json_encode($retArray);
                                    exit;
                                }
                            } else {
                                $retArray['error'] = ['classstudent' => 'Class and Student not found.'];
                                echo json_encode($retArray);
                                exit;
                            }
                        } else {
                            $retArray['error'] = ['editid' => 'Edit id is required.'];
                            echo json_encode($retArray);
                            exit;
                        }
                    }
                } else {
                    $retArray['error'] = ['posttype' => 'Post type is required.'];
                    echo json_encode($retArray);
                    exit;
                }
            } else {
                $retArray['error'] = ['permission' => 'Invoice permission is required.'];
                echo json_encode($retArray);
                exit;
            }
        } else {
            $retArray['error'] = ['permission' => 'Permission Denied.'];
            echo json_encode($retArray);
            exit;
        }
    }

    private function grandtotalandpaid( $maininvoices, $schoolyearID, $schoolID )
    {
        $retArray           = [];
        $invoiceitems       = pluck_multi_array_key($this->invoice_m->get_order_by_invoice(['schoolyearID' => $schoolyearID, 'schoolID' => $schoolID]), 'obj', 'maininvoiceID', 'invoiceID');
        $paymentitems       = pluck_multi_array($this->payment_m->get_order_by_payment([
            'schoolyearID'     => $schoolyearID,
            'schoolID'         => $schoolID,
            'paymentamount !=' => NULL
        ]), 'obj', 'invoiceID');
        $weaverandfineitems = pluck_multi_array($this->weaverandfine_m->get_order_by_weaverandfine(['schoolyearID' => $schoolyearID, 'schoolID' => $schoolID]), 'obj', 'invoiceID');
        if(customCompute($maininvoices)) {
            foreach($maininvoices as $maininvoice) {
                if(isset($invoiceitems[$maininvoice->maininvoiceID])) {
                    if(customCompute($invoiceitems[$maininvoice->maininvoiceID])) {
                        foreach($invoiceitems[$maininvoice->maininvoiceID] as $invoiceitem) {
                            $amount = $invoiceitem->amount;
                            if($invoiceitem->discount > 0) {
                                $amount = ($invoiceitem->amount - (($invoiceitem->amount / 100) * $invoiceitem->discount));
                            }

                            if(isset($retArray['grandtotal'][$maininvoice->maininvoiceID])) {
                                $retArray['grandtotal'][$maininvoice->maininvoiceID] = (($retArray['grandtotal'][$maininvoice->maininvoiceID]) + $amount);
                            } else {
                                $retArray['grandtotal'][$maininvoice->maininvoiceID] = $amount;
                            }

                            if(isset($retArray['totalamount'][$maininvoice->maininvoiceID])) {
                                $retArray['totalamount'][$maininvoice->maininvoiceID] = (($retArray['totalamount'][$maininvoice->maininvoiceID]) + $invoiceitem->amount);
                            } else {
                                $retArray['totalamount'][$maininvoice->maininvoiceID] = $invoiceitem->amount;
                            }

                            if(isset($retArray['totaldiscount'][$maininvoice->maininvoiceID])) {
                                $retArray['totaldiscount'][$maininvoice->maininvoiceID] = (($retArray['totaldiscount'][$maininvoice->maininvoiceID]) + (($invoiceitem->amount / 100) * $invoiceitem->discount));
                            } else {
                                $retArray['totaldiscount'][$maininvoice->maininvoiceID] = (($invoiceitem->amount / 100) * $invoiceitem->discount);
                            }

                            if(isset($paymentitems[$invoiceitem->invoiceID])) {
                                if(customCompute($paymentitems[$invoiceitem->invoiceID])) {
                                    foreach($paymentitems[$invoiceitem->invoiceID] as $paymentitem) {
                                        if(isset($retArray['totalpayment'][$maininvoice->maininvoiceID])) {
                                            $retArray['totalpayment'][$maininvoice->maininvoiceID] = (($retArray['totalpayment'][$maininvoice->maininvoiceID]) + $paymentitem->paymentamount);
                                        } else {
                                            $retArray['totalpayment'][$maininvoice->maininvoiceID] = $paymentitem->paymentamount;
                                        }
                                    }
                                }
                            }

                            if(isset($weaverandfineitems[$invoiceitem->invoiceID])) {
                                if(customCompute($weaverandfineitems[$invoiceitem->invoiceID])) {
                                    foreach($weaverandfineitems[$invoiceitem->invoiceID] as $weaverandfineitem) {
                                        if(isset($retArray['totalweaver'][$maininvoice->maininvoiceID])) {
                                            $retArray['totalweaver'][$maininvoice->maininvoiceID] = (($retArray['totalweaver'][$maininvoice->maininvoiceID]) + $weaverandfineitem->weaver);
                                        } else {
                                            $retArray['totalweaver'][$maininvoice->maininvoiceID] = $weaverandfineitem->weaver;
                                        }

                                        if(isset($retArray['totalfine'][$maininvoice->maininvoiceID])) {
                                            $retArray['totalfine'][$maininvoice->maininvoiceID] = (($retArray['totalfine'][$maininvoice->maininvoiceID]) + $weaverandfineitem->fine);
                                        } else {
                                            $retArray['totalfine'][$maininvoice->maininvoiceID] = $weaverandfineitem->fine;
                                        }
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

    private function grandtotalandpaidsingle( $maininvoice, $schoolyearID, $schoolID, $studentID = NULL )
    {
        $retArray = [
            'grandtotal'    => 0,
            'totalamount'   => 0,
            'totaldiscount' => 0,
            'totalpayment'  => 0,
            'totalfine'     => 0,
            'totalweaver'   => 0
        ];
        if(customCompute($maininvoice)) {
            if((int)$studentID && $studentID != NULL) {
                $invoiceitems       = pluck_multi_array_key($this->invoice_m->get_order_by_invoice([
                    'studentID'     => $studentID,
                    'maininvoiceID' => $maininvoice->maininvoiceID,
                    'schoolyearID'  => $schoolyearID,
                    'schoolID'      => $schoolID,
                ]), 'obj', 'maininvoiceID', 'invoiceID');
                $paymentitems       = pluck_multi_array($this->payment_m->get_order_by_payment([
                    'schoolyearID'     => $schoolyearID,
                    'schoolID'         => $schoolID,
                    'paymentamount !=' => NULL
                ]), 'obj', 'invoiceID');
                $weaverandfineitems = pluck_multi_array($this->weaverandfine_m->get_order_by_weaverandfine(['schoolyearID' => $schoolyearID, 'schoolID' => $schoolID]), 'obj', 'invoiceID');
            } else {
                $invoiceitem        = [];
                $paymentitems       = [];
                $weaverandfineitems = [];
            }

            if(isset($invoiceitems[$maininvoice->maininvoiceID])) {
                if(customCompute($invoiceitems[$maininvoice->maininvoiceID])) {
                    foreach($invoiceitems[$maininvoice->maininvoiceID] as $invoiceitem) {
                        $amount = $invoiceitem->amount;
                        if($invoiceitem->discount > 0) {
                            $amount = ($invoiceitem->amount - (($invoiceitem->amount / 100) * $invoiceitem->discount));
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
                            $retArray['totaldiscount'] = ($retArray['totaldiscount'] + (($invoiceitem->amount / 100) * $invoiceitem->discount));
                        } else {
                            $retArray['totaldiscount'] = (($invoiceitem->amount / 100) * $invoiceitem->discount);
                        }

                        if(isset($paymentitems[$invoiceitem->invoiceID])) {
                            if(customCompute($paymentitems[$invoiceitem->invoiceID])) {
                                foreach($paymentitems[$invoiceitem->invoiceID] as $paymentitem) {
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
                                foreach($weaverandfineitems[$invoiceitem->invoiceID] as $weaverandfineitem) {
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

    private function paymentdue( $maininvoice, $schoolyearID, $schoolID, $studentID = NULL )
    {
        $retArray = [];
        if(customCompute($maininvoice)) {
            if((int)$studentID && $studentID != NULL) {
                $invoiceitems       = pluck_multi_array_key($this->invoice_m->get_order_by_invoice([
                    'studentID'     => $studentID,
                    'maininvoiceID' => $maininvoice->maininvoiceID,
                    'schoolyearID'  => $schoolyearID,
                    'schoolID'      => $schoolID,
                ]), 'obj', 'maininvoiceID', 'invoiceID');
                $paymentitems       = pluck_multi_array($this->payment_m->get_order_by_payment([
                    'schoolyearID'     => $schoolyearID,
                    'schoolID'         => $schoolID,
                    'paymentamount !=' => NULL
                ]), 'obj', 'invoiceID');
                $weaverandfineitems = pluck_multi_array($this->weaverandfine_m->get_order_by_weaverandfine(['schoolyearID' => $schoolyearID, 'schoolID' => $schoolID]), 'obj', 'invoiceID');
            } else {
                $invoiceitem        = [];
                $paymentitems       = [];
                $weaverandfineitems = [];
            }

            if(isset($invoiceitems[$maininvoice->maininvoiceID])) {
                if(customCompute($invoiceitems[$maininvoice->maininvoiceID])) {
                    foreach($invoiceitems[$maininvoice->maininvoiceID] as $invoiceitem) {
                        $amount = $invoiceitem->amount;
                        if($invoiceitem->discount > 0) {
                            $amount = ($invoiceitem->amount - (($invoiceitem->amount / 100) * $invoiceitem->discount));
                        }

                        if(isset($retArray['totalamount'][$invoiceitem->invoiceID])) {
                            $retArray['totalamount'][$invoiceitem->invoiceID] = ($retArray['totalamount'][$invoiceitem->invoiceID] + $invoiceitem->amount);
                        } else {
                            $retArray['totalamount'][$invoiceitem->invoiceID] = $invoiceitem->amount;
                        }

                        if(isset($retArray['totaldiscount'][$invoiceitem->invoiceID])) {
                            $retArray['totaldiscount'][$invoiceitem->invoiceID] = ($retArray['totaldiscount'][$invoiceitem->invoiceID] + (($invoiceitem->amount / 100) * $invoiceitem->discount));
                        } else {
                            $retArray['totaldiscount'][$invoiceitem->invoiceID] = (($invoiceitem->amount / 100) * $invoiceitem->discount);
                        }

                        if(isset($paymentitems[$invoiceitem->invoiceID])) {
                            if(customCompute($paymentitems[$invoiceitem->invoiceID])) {
                                foreach($paymentitems[$invoiceitem->invoiceID] as $paymentitem) {
                                    if(isset($retArray['totalpayment'][$paymentitem->invoiceID])) {
                                        $retArray['totalpayment'][$paymentitem->invoiceID] = ($retArray['totalpayment'][$paymentitem->invoiceID] + $paymentitem->paymentamount);
                                    } else {
                                        $retArray['totalpayment'][$paymentitem->invoiceID] = $paymentitem->paymentamount;
                                    }
                                }
                            }
                        }

                        if(isset($weaverandfineitems[$invoiceitem->invoiceID])) {
                            if(customCompute($weaverandfineitems[$invoiceitem->invoiceID])) {
                                foreach($weaverandfineitems[$invoiceitem->invoiceID] as $weaverandfineitem) {
                                    if(isset($retArray['totalweaver'][$weaverandfineitem->invoiceID])) {
                                        $retArray['totalweaver'][$weaverandfineitem->invoiceID] = ($retArray['totalweaver'][$weaverandfineitem->invoiceID] + $weaverandfineitem->weaver);
                                    } else {
                                        $retArray['totalweaver'][$weaverandfineitem->invoiceID] = $weaverandfineitem->weaver;
                                    }

                                    if(isset($retArray['totalfine'][$weaverandfineitem->invoiceID])) {
                                        $retArray['totalfine'][$weaverandfineitem->invoiceID] = ($retArray['totalfine'][$weaverandfineitem->invoiceID] + $weaverandfineitem->fine);
                                    } else {
                                        $retArray['totalfine'][$weaverandfineitem->invoiceID] = $weaverandfineitem->fine;
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

    private function globalpayment( $maininvoice, $schoolyearID, $schoolID, $studentID = NULL )
    {
        if(customCompute($maininvoice)) {
            if((int)$studentID && $studentID != NULL) {
                $invoiceitems       = pluck_multi_array_key($this->invoice_m->get_order_by_invoice([
                    'studentID'     => $studentID,
                    'maininvoiceID' => $maininvoice->maininvoiceID,
                    'schoolyearID'  => $schoolyearID,
                    'schoolID'      => $schoolID,
                ]), 'obj', 'maininvoiceID', 'invoiceID');
                $paymentitems       = pluck_multi_array($this->payment_m->get_order_by_payment(['schoolyearID' => $schoolyearID, 'schoolID' => $schoolID]), 'obj', 'invoiceID');
                $weaverandfineitems = pluck_multi_array($this->weaverandfine_m->get_order_by_weaverandfine(['schoolyearID' => $schoolyearID, 'schoolID' => $schoolID]), 'obj', 'invoiceID');
            } else {
                $invoiceitem        = [];
                $paymentitems       = [];
                $weaverandfineitems = [];
            }

            if(isset($invoiceitems[$maininvoice->maininvoiceID])) {
                if(customCompute($invoiceitems[$maininvoice->maininvoiceID])) {
                    foreach($invoiceitems[$maininvoice->maininvoiceID] as $invoiceitem) {
                        if(isset($paymentitems[$invoiceitem->invoiceID])) {
                            if(customCompute($paymentitems[$invoiceitem->invoiceID])) {
                                foreach($paymentitems[$invoiceitem->invoiceID] as $paymentitem) {
                                    $retArray['globalpayments'][$paymentitem->globalpaymentID][$paymentitem->paymentID] = [
                                        'paymentID'     => $paymentitem->paymentID,
                                        'invoiceID'     => $paymentitem->invoiceID,
                                        'paymentamount' => $paymentitem->paymentamount,
                                        'paymentdate'   => $paymentitem->paymentdate,
                                        'weaver'        => '',
                                        'fine'          => '',
                                    ];
                                }
                            }
                        }

                        if(isset($weaverandfineitems[$invoiceitem->invoiceID])) {
                            if(customCompute($weaverandfineitems[$invoiceitem->invoiceID])) {
                                foreach($weaverandfineitems[$invoiceitem->invoiceID] as $weaverandfineitem) {
                                    $retArray['globalpayments'][$weaverandfineitem->globalpaymentID][$weaverandfineitem->paymentID]['weaver'] = $weaverandfineitem->weaver;

                                    $retArray['globalpayments'][$weaverandfineitem->globalpaymentID][$weaverandfineitem->paymentID]['fine'] = $weaverandfineitem->fine;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $retArray;
    }

    public function paymentlist()
    {
        if(permissionChecker('invoice_view')) {
            $schoolID      = $this->session->userdata('schoolID');
            $schoolyearID  = $this->session->userdata('defaultschoolyearID');
            $maininvoiceID = $this->input->post('maininvoiceID');

            $globalPaymentArray   = [];
            $globalpaymentobjects = [];
            $allpayments          = [];
            $allweaverandfines    = [];
            $paymentlists         = [];

            if(!empty($maininvoiceID) && (int)$maininvoiceID && $maininvoiceID > 0) {
                $maininvoice = $this->maininvoice_m->get_single_maininvoice([
                    'maininvoiceID'           => $maininvoiceID,
                    'maininvoiceschoolyearID' => $schoolyearID,
                    'schoolID'                => $schoolID,
                ]);
                if(customCompute($maininvoice)) {
                    $invoices       = $this->invoice_m->get_order_by_invoice([
                        'maininvoiceID' => $maininvoiceID,
                        'schoolyearID'  => $schoolyearID,
                    ]);
                    $globalpayments = pluck($this->globalpayment_m->get_order_by_globalpayment(['studentID' => $maininvoice->maininvoicestudentID]), 'obj', 'globalpaymentID');

                    if(customCompute($invoices)) {
                        foreach($invoices as $invoice) {
                            $payments = $this->payment_m->get_order_by_payment([
                                'invoiceID' => $invoice->invoiceID,
                                'studentID' => $maininvoice->maininvoicestudentID
                            ]);

                            $weaverandfines = $this->weaverandfine_m->get_order_by_weaverandfine([
                                'invoiceID' => $invoice->invoiceID,
                                'studentID' => $maininvoice->maininvoicestudentID
                            ]);
                            if(customCompute($payments)) {
                                foreach($payments as $payment) {
                                    if(isset($globalpayments[$payment->globalpaymentID])) {
                                        $allpayments[$payment->globalpaymentID][] = $payment;
                                        if(!in_array($payment->globalpaymentID, $globalPaymentArray)) {
                                            $globalPaymentArray[]   = $payment->globalpaymentID;
                                            $globalpaymentobjects[] = $globalpayments[$payment->globalpaymentID];
                                        }
                                    }
                                }
                            }

                            if(customCompute($weaverandfines)) {
                                foreach($weaverandfines as $weaverandfine) {
                                    $allweaverandfines[$weaverandfine->globalpaymentID][] = $weaverandfine;
                                }
                            }
                        }
                    }

                    if(customCompute($globalpaymentobjects)) {
                        foreach($globalpaymentobjects as $globalpaymentobject) {
                            if(isset($allpayments[$globalpaymentobject->globalpaymentID])) {
                                if(customCompute($allpayments[$globalpaymentobject->globalpaymentID])) {
                                    foreach($allpayments[$globalpaymentobject->globalpaymentID] as $payment) {
                                        if(isset($paymentlists[$globalpaymentobject->globalpaymentID])) {
                                            $paymentlists[$globalpaymentobject->globalpaymentID]['paymentamount'] += $payment->paymentamount;
                                        } else {
                                            $paymentlists[$globalpaymentobject->globalpaymentID] = [
                                                'globalpaymentID' => $globalpaymentobject->globalpaymentID,
                                                'paymentamount'   => $payment->paymentamount,
                                                'date'            => $payment->paymentdate,
                                                'paymenttype'     => $payment->paymenttype,
                                            ];
                                        }
                                    }


                                    if(isset($allweaverandfines[$globalpaymentobject->globalpaymentID])) {
                                        foreach($allweaverandfines[$globalpaymentobject->globalpaymentID] as $allweaverandfine) {
                                            if(isset($paymentlists[$globalpaymentobject->globalpaymentID]['weaveramount']) && isset($paymentlists[$globalpaymentobject->globalpaymentID]['fineamount'])) {
                                                $paymentlists[$globalpaymentobject->globalpaymentID]['weaveramount'] += $allweaverandfine->weaver;
                                                $paymentlists[$globalpaymentobject->globalpaymentID]['fineamount']   += $allweaverandfine->fine;
                                            } else {
                                                if(isset($paymentlists[$globalpaymentobject->globalpaymentID])) {
                                                    $paymentlists[$globalpaymentobject->globalpaymentID]['weaveramount'] = $allweaverandfine->weaver;
                                                    $paymentlists[$globalpaymentobject->globalpaymentID]['fineamount']   = $allweaverandfine->fine;
                                                } else {
                                                    $paymentlists[$globalpaymentobject->globalpaymentID] = [
                                                        'weaveramount' => $allweaverandfine->weaver,
                                                        'fineamount'   => $allweaverandfine->fine,
                                                    ];
                                                }
                                            }
                                        }
                                    } else {
                                        $paymentlists[$globalpaymentobject->globalpaymentID]['weaveramount'] = 0;
                                        $paymentlists[$globalpaymentobject->globalpaymentID]['fineamount']   = 0;
                                    }
                                }
                            }
                        }
                    }
                }

                if(customCompute($paymentlists)) {
                    $i = 1;
                    foreach($paymentlists as $key => $paymentlist) {
                        echo '<tr>';
                        echo '<td data-title="' . $this->lang->line('slno') . '">';
                        echo $i;
                        echo '</td>';

                        echo '<td data-title="' . $this->lang->line('invoice_date') . '">';
                        echo date('d M Y', strtotime($paymentlist['date']));
                        echo '</td>';

                        echo '<td data-title="' . $this->lang->line('invoice_paymentmethod') . '">';
                        echo $paymentlist['paymenttype'];
                        echo '</td>';

                        echo '<td data-title="' . $this->lang->line('invoice_paymentamount') . '">';
                        echo number_format($paymentlist['paymentamount'], 2);
                        echo '</td>';

                        echo '<td data-title="' . $this->lang->line('invoice_weaver') . '">';
                        echo number_format($paymentlist['weaveramount'], 2);
                        echo '</td>';

                        echo '<td data-title="' . $this->lang->line('invoice_fine') . '">';
                        echo number_format($paymentlist['fineamount'], 2);
                        echo '</td>';
                        echo '<td data-title="' . $this->lang->line('action') . '">';
                        if(permissionChecker('invoice_view')) {
                            echo '<a href="' . base_url('invoice/viewpayment/' . $paymentlist['globalpaymentID'] . '/' . $maininvoiceID) . '" class="btn btn-success btn-xs mrg" data-placement="top" data-toggle="tooltip" data-original-title="' . $this->lang->line('view') . '"><i class="fa fa-check-square-o"></i></a>';
                        }

                        if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('usertypeID') == 5)) {
                            if(($this->lang->line('Cash') == $paymentlist['paymenttype']) || ($this->lang->line('Cheque') == $paymentlist['paymenttype']) || ('Cash' == $paymentlist['paymenttype']) || ('Cheque' == $paymentlist['paymenttype'])) {
                                if(permissionChecker('invoice_delete')) {
                                    echo '<a href="' . base_url('invoice/deleteinvoicepaid/' . $paymentlist['globalpaymentID'] . '/' . $maininvoiceID) . '" onclick="return confirm(' . "'" . 'you are about to delete a record. This cannot be undone. are you sure?' . "'" . ')" class="btn btn-danger btn-xs mrg" data-placement="top" data-toggle="tooltip" data-original-title="' . $this->lang->line('delete') . '"><i class="fa fa-trash-o"></i></a>';
                                }
                            }
                        }
                        echo '</td>';
                        echo '</tr>';
                        $i++;
                    }
                }
            }

        }
    }

    public function deleteinvoicepaid()
    {
        if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('usertypeID') == 5)) {
            $globalpaymentID = htmlentities(escapeString($this->uri->segment(3)));
            $maininvoiceID   = htmlentities(escapeString($this->uri->segment(4)));
            $schoolID        = $this->session->userdata('schoolID');
            $schoolyearID    = $this->session->userdata('defaultschoolyearID');

            $paymentArray       = [];
            $weaverandfineArray = [];
            if(permissionChecker('invoice_delete')) {
                if((int)$globalpaymentID && (int)$maininvoiceID) {
                    $globalpayment = $this->globalpayment_m->get_single_globalpayment(['globalpaymentID' => $globalpaymentID, 'schoolID' => $schoolID]);
                    if(customCompute($globalpayment)) {
                        $payments       = $this->payment_m->get_order_by_payment(['globalpaymentID' => $globalpaymentID, 'schoolID' => $schoolID]);
                        $weaverandfines = pluck($this->weaverandfine_m->get_order_by_weaverandfine(['globalpaymentID' => $globalpaymentID, 'schoolID' => $schoolID]), 'obj', 'paymentID');

                        $excType = TRUE;
                        foreach($payments as $payment) {
                            if(($this->lang->line('Cash') == $payment->paymenttype) || ($this->lang->line('Cheque') == $payment->paymenttype) || ('Cash' == $payment->paymenttype) || ('Cheque' == $payment->paymenttype)) {
                                $paymentArray[] = $payment->paymentID;
                                if(isset($weaverandfines[$payment->paymentID])) {
                                    $weaverandfineArray[] = $weaverandfines[$payment->paymentID]->weaverandfineID;
                                }
                            } else {
                                $excType               = FALSE;
                                $this->data["subview"] = "error";
                                $this->load->view('_layout_main', $this->data);
                                break;
                            }
                        }

                        if($excType) {
                            $this->payment_m->delete_batch_payment($paymentArray);
                            $this->weaverandfine_m->delete_batch_weaverandfine($weaverandfineArray);
                            $this->globalpayment_m->delete_globalpayment($globalpaymentID);


                            $invoices     = $this->invoice_m->get_order_by_invoice(['maininvoiceID' => $maininvoiceID, 'schoolID' => $schoolID]);
                            $invoicepluck = pluck($invoices, 'invoiceID');

                            $invoicesum       = $this->invoice_m->get_invoice_sum(['maininvoiceID' => $maininvoiceID, 'schoolID' => $schoolID]);
                            $paymentsum       = $this->payment_m->get_where_payment_sum('paymentamount', 'invoiceID', $invoicepluck);
                            $weaverandfinesum = $this->weaverandfine_m->get_where_weaverandfine_sum([
                                'weaver',
                                'fine'
                            ], 'invoiceID', $invoicepluck);

                            $maininvoiceArray = [];
                            if(($paymentsum->paymentamount + $weaverandfinesum->weaver) == NULL) {
                                $maininvoiceArray['maininvoicestatus'] = 0;
                            } elseif((float)($paymentsum->paymentamount + $weaverandfinesum->weaver) == (float)0) {
                                $maininvoiceArray['maininvoicestatus'] = 0;
                            } elseif((float)$invoicesum->invoiceamount == (float)($paymentsum->paymentamount + $weaverandfinesum->weaver)) {
                                $maininvoiceArray['maininvoicestatus'] = 2;
                            } elseif((float)($paymentsum->paymentamount + $weaverandfinesum->weaver) > 0 && ((float)$invoicesum->invoiceamount > (float)($paymentsum->paymentamount + $weaverandfinesum->weaver))) {
                                $maininvoiceArray['maininvoicestatus'] = 1;
                            } elseif((float)($paymentsum->paymentamount + $weaverandfinesum->weaver) > 0 && ((float)$invoicesum->invoiceamount < (float)($paymentsum->paymentamount + $weaverandfinesum->weaver))) {
                                $maininvoiceArray['maininvoicestatus'] = 2;
                            }

                            $payments       = pluck($this->payment_m->get_where_payment_sum('paymentamount', 'invoiceID', $invoicepluck, 'invoiceID'), 'obj', 'invoiceID');
                            $weaverandfines = pluck($this->weaverandfine_m->get_where_weaverandfine_sum([
                                'weaver',
                                'fine'
                            ], 'invoiceID', $invoicepluck, 'invoiceID'), 'obj', 'invoiceID');

                            $invoiceArray = [];
                            if(customCompute($invoices)) {
                                foreach($invoices as $invoice) {
                                    $paymentandweaver = 0;
                                    $paidstatus       = 0;
                                    if(isset($payments[$invoice->invoiceID])) {
                                        $paymentandweaver += $payments[$invoice->invoiceID]->paymentamount;
                                    }

                                    if(isset($weaverandfines[$invoice->invoiceID])) {
                                        $paymentandweaver += $weaverandfines[$invoice->invoiceID]->weaver;
                                    }

                                    if($paymentandweaver == NULL) {
                                        $paidstatus = 0;
                                    } elseif((float)$paymentandweaver == (float)0) {
                                        $paidstatus = 0;
                                    } elseif((float)$invoice->amount == (float)$paymentandweaver) {
                                        $paidstatus = 2;
                                    } elseif((float)$paymentandweaver > 0 && ((float)$invoice->amount > (float)$paymentandweaver)) {
                                        $paidstatus = 1;
                                    } elseif((float)$paymentandweaver > 0 && ((float)$invoice->amount < (float)$paymentandweaver)) {
                                        $paidstatus = 2;
                                    }

                                    $invoiceArray[] = [
                                        'paidstatus' => $paidstatus,
                                        'invoiceID'  => $invoice->invoiceID
                                    ];
                                }
                            }

                            if(customCompute($invoiceArray)) {
                                $this->invoice_m->update_batch_invoice($invoiceArray, 'invoiceID');
                            }
                            $this->maininvoice_m->update_maininvoice($maininvoiceArray, $maininvoiceID);

                            redirect(base_url('invoice/index'));
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

    public function payment_methods( $payment_gateways )
    {
        $payment_methods['select'] = $this->lang->line("invoice_select_paymentmethod");
        if($this->session->userdata('usertypeID') == 1 || $this->session->userdata('usertypeID') == 5) {
            $payment_methods['Cash']   = $this->lang->line('Cash');
            $payment_methods['Cheque'] = $this->lang->line('Cheque');
        }

        if(customCompute($payment_gateways)) {
            $online_gateway  = pluck($payment_gateways, 'name', 'slug');
            $payment_methods = array_merge($payment_methods, $online_gateway);
        }

        return $payment_methods;
    }

    public function success()
    {
        if(isset($this->payment_gateway_array[htmlentities(escapeString($this->uri->segment(3)))])) {
            $this->payment_gateway->gateway(htmlentities(escapeString($this->uri->segment(3))))->success();
        }
    }

    public function cancel()
    {
        if(isset($this->payment_gateway_array[htmlentities(escapeString($this->uri->segment(3)))])) {
            $this->payment_gateway->gateway(htmlentities(escapeString($this->uri->segment(3))))->cancel();
        }
    }

    public function fail()
    {
        if(isset($this->payment_gateway_array[htmlentities(escapeString($this->uri->segment(3)))])) {
            $this->payment_gateway->gateway(htmlentities(escapeString($this->uri->segment(3))))->fail();
        }
    }

    public function weaver()
    {
        if(isset($this->payment_gateway_array[htmlentities(escapeString($this->uri->segment(3)))])) {
            $this->payment_gateway->gateway(htmlentities(escapeString($this->uri->segment(3))))->weaver();
        }
    }

    function invoiceAndBilling($config, $student, $invoice, $id)
    {
      /*  This sample performs the folowing functions:
      1.   Add a customer
      2.   Add an item
      3    Create invoice using the information above
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
      if (!file_exists($path)) {
        mkdir($path, 0777, true);
      }
      $dataService->setLogLocation($path);

      /*
      * 1. Get CustomerRef and ItemRef
      */
      $customerRef = $this->getCustomerObj($dataService, $student);
      $itemRef = $this->getItemObj($dataService, $invoice);

      /*
      * 2. Create Invoice using the CustomerRef and ItemRef
      */
      $invoiceObj = QBInvoice::create([
        "DocNumber" => $id,
        "Line" => [
            "Amount" => $invoice['amount'],
            "DetailType" => "SalesItemLineDetail",
            "SalesItemLineDetail" => [
                "ItemRef" => [
                    "value" => $itemRef->Id
                ]
            ]
        ],
        "CustomerRef"=> [
            "value"=> $customerRef->Id
        ],
        "BillEmail" => [
            "Address" => $student->email
        ],
        "CustomerMemo" => $id
      ]);
      $resultingInvoiceObj = $dataService->Add($invoiceObj);
    }

    /*
    Generate GUID to associate with the sample account names
    */
    function getGUID()
    {
      if (function_exists('com_create_guid')) {
        return com_create_guid();
      }else{
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = // "{"
            $hyphen.substr($charid, 0, 8);
        return $uuid;
      }
    }

    /*
    Find if a customer with DisplayName if not, create one and return
    */
    function getCustomerObj($dataService, $student) {
      $customerName = $student->studentID ."-". $student->name;
      $schoolID = $this->session->userdata('schoolID');
      try {
  			$customerArray = $dataService->Query("select * from Customer where DisplayName='" . addslashes($customerName) . "'");
  			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Customer where DisplayName=" . $customerName, "status" => "OK", 'schoolID' => $schoolID));
  		} catch (Exception $e) {
  			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Customer where DisplayName=" . $customerName, "message" => $e->getMessage(), "status" => "ERROR", 'schoolID' => $schoolID));
  		}

      $error = $dataService->getLastError();
      if ($error) {
        logError($error);
        $retArray['error']  = $error;
        $retArray['status'] = FALSE;
        echo json_encode($retArray);
        exit;
      } else {
        if (is_array($customerArray) && sizeof($customerArray) > 0) {
            return current($customerArray);
        }
      }

      // Create Customer
      $customerRequestObj = Customer::create([
        "FullyQualifiedName" => $student->name,
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
      if ($error) {
        logError($error);
        $retArray['error']  = $error;
        $retArray['status'] = FALSE;
        echo json_encode($retArray);
        exit;
      } else {
        return $customerResponseObj;
      }
    }

    /*
    Find if an Item is present , if not create new Item
    */
    function getItemObj($dataService, $invoice) {
      $schoolID = $this->session->userdata('schoolID');
      try {
  			$itemArray = $dataService->Query("select * from Item WHERE Name='" . $invoice['feetype'] . "'");
  			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Item WHERE Name=" . $invoice['feetype'], "status" => "OK", 'schoolID' => $schoolID));
  		} catch (Exception $e) {
  			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Item WHERE Name=" . $invoice['feetype'], "message" => $e->getMessage(), "status" => "ERROR", 'schoolID' => $schoolID));
  		}

      $error = $dataService->getLastError();
      if ($error) {
        logError($error);
        $retArray['error']  = $error;
        $retArray['status'] = FALSE;
        echo json_encode($retArray);
        exit;
      } else {
        if (is_array($itemArray) && sizeof($itemArray) > 0) {
            return current($itemArray);
        }
      }

      // Fetch IncomeAccount and ExoenseAccount Refs needed to create an Item
      $feetype = $this->feetypes_m->get_feetypes($invoice['feetypeID']);
      if (empty($feetype->incomeaccountID)) {
			$retArray['error'] = ['feetype' => 'Please add an income account for '. $feetype->feetypes];
			$retArray['status'] = FALSE;
			echo json_encode($retArray);
			exit;
  		}

      // Create Item
      $dateTime = new \DateTime($invoice['date']);
      $ItemObj = Item::create([
        "Name" => $invoice['feetype'],
        "Active" => true,
        "FullyQualifiedName" => $invoice['feetype'],
        "Taxable" => false,
        "Type" => "Service",
        "IncomeAccountRef"=> [
            "value"=>  $feetype->incomeaccountID
        ],
        "InvStartDate"=> $dateTime
      ]);
      $resultingItemObj = $dataService->Add($ItemObj);
      $itemId = $resultingItemObj->Id;  // This needs to be passed in the Invoice creation later
      return $resultingItemObj;
    }
}
