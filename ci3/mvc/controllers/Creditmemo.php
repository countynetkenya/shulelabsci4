<?php if(!defined('BASEPATH'))
    exit('No direct script access allowed');
require_once(APPPATH . '../vendor/autoload.php');
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Item;
use QuickBooksOnline\API\Facades\CreditMemo as QBCreditMemo;
use QuickBooksOnline\API\Facades\Account;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;

class Creditmemo extends Admin_Controller
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

    function __construct()
    {
        parent::__construct();
        $this->load->model("creditmemo_m");
        $this->load->model("credittypes_m");
        $this->load->model('payment_m');
        $this->load->model("classes_m");
        $this->load->model("student_m");
        $this->load->model("parents_m");
        $this->load->model("section_m");
        $this->load->model('user_m');
		    $this->load->model('weaverandfine_m');
        $this->load->model("payment_settings_m");
        $this->load->model("globalpayment_m");
        $this->load->model("maincreditmemo_m");
        $this->load->model("studentrelation_m");
    		$this->load->model('studentgroup_m');
    		$this->load->model('schoolterm_m');
        $this->load->model("quickbookssettings_m");
        $this->load->model("quickbookslog_m");
        $language = $this->session->userdata('lang');
        $this->lang->load('student', $language);
        $this->lang->load('creditmemo', $language);
    }

    protected function rules( $statusID = 0 )
    {
        $rules = [
            [
                'field' => 'classesID',
                'label' => $this->lang->line("creditmemo_classesID"),
                'rules' => 'trim|required|xss_clean|max_length[11]|numeric|callback_unique_classID'
            ],
            [
                'field' => 'studentID',
                'label' => $this->lang->line("creditmemo_studentID"),
                'rules' => 'trim|required|xss_clean|max_length[11]|numeric|callback_unique_studentID'
            ],
            [
                'field' => 'creditmemo_active',
                'label' => $this->lang->line("creditmemo_status"),
                'rules' => 'trim|xss_clean|callback_unique_creditmemoactive'
            ],
			      [
                'field' => 'schooltermID',
                'label' => $this->lang->line("creditmemo_schooltermID"),
                'rules' => 'trim|required|xss_clean|numeric'
            ],
            [
                'field' => 'credittypeitems',
                'label' => $this->lang->line("creditmemo_credittypeitem"),
                'rules' => 'trim|xss_clean|required|callback_unique_credittypeitems'
            ],
            [
                'field' => 'date',
                'label' => $this->lang->line("creditmemo_date"),
                'rules' => 'trim|required|xss_clean|max_length[10]|callback_date_valid'
            ],
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
                $this->data['maincreditmemos']      = $this->maincreditmemo_m->get_maincreditmemo_with_studentrelation_by_studentID($student->studentID, $schoolyearID);
                $this->data['grandtotalandpayment'] = $this->grandtotalandpaid($this->data['maincreditmemos'], $schoolyearID, $schoolID);

                $this->data["subview"] = "creditmemo/index";
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
                $this->data['maincreditmemos']      = $this->maincreditmemo_m->get_maincreditmemo_with_studentrelation_by_multi_studentID($studentArray, $schoolyearID);
                $this->data['grandtotalandpayment'] = $this->grandtotalandpaid($this->data['maincreditmemos'], $schoolyearID, $schoolID);
                $this->data["subview"]              = "creditmemo/index";
                $this->load->view('_layout_main', $this->data);
            } else {
                $this->data['maincreditmemos']         = [];
                $this->data['grandtotalandpayment'] = [];
                $this->data["subview"]              = "creditmemo/index";
                $this->load->view('_layout_main', $this->data);
            }
        } else {
            $this->data['maincreditmemos']      = $this->maincreditmemo_m->get_maincreditmemo_with_studentrelation(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
            $this->data['grandtotalandpayment'] = $this->grandtotalandpaid($this->data['maincreditmemos'], $schoolyearID, $schoolID);
			      $this->data["subview"]              = "creditmemo/index";
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
            $this->data['classes'] = $this->classes_m->general_get_order_by_classes(array('schoolID' => $schoolID));
            $this->data['terms']  = $this->schoolterm_m->get_order_by_schoolterm(array('schoolyearID' => $this->data['siteinfos']->school_year, 'schoolID' => $schoolID));
            $this->data['credittypes'] = $this->credittypes_m->get_order_by_credittypes(array('schoolID' => $schoolID));
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

            $this->data["subview"] = "creditmemo/add";
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

            $maincreditmemoID = htmlentities(escapeString($this->uri->segment(3)));
            if((int)$maincreditmemoID) {
                $schoolID                       = $this->session->userdata('schoolID');
                $schoolyearID                   = $this->session->userdata('defaultschoolyearID');
                $this->data['maincreditmemoID'] = $maincreditmemoID;
                $this->data['maincreditmemo']   = $this->maincreditmemo_m->get_single_maincreditmemo(['maincreditmemoID' => $maincreditmemoID, 'schoolID' => $schoolID]);
                if(customCompute($this->data['maincreditmemo'])) {
                    if($this->data['maincreditmemo']->maincreditmemostatus == 0) {
                        $this->data['classes']  = $this->classes_m->general_get_order_by_classes(array('schoolID' => $schoolID));
                        $this->data['credittypes'] = pluck($this->credittypes_m->get_order_by_credittypes(array('schoolID' => $schoolID)), 'obj', 'credittypesID');
                        $this->data['terms']  = $this->schoolterm_m->get_order_by_schoolterm(array('schoolyearID' => $this->data['siteinfos']->school_year, 'schoolID' => $schoolID));
                        $this->data['students'] = $this->studentrelation_m->get_order_by_studentrelation([
                            'srclassesID'    => $this->data['maincreditmemo']->maincreditmemoclassesID,
                            'srschoolyearID' => $schoolyearID,
                            'srschoolID'     => $schoolID,
                        ]);

                        $this->data['creditmemos'] = $this->creditmemo_m->get_order_by_creditmemo(['maincreditmemoID' => $maincreditmemoID, 'schoolID' => $schoolID]);

                        $this->data["subview"] = "creditmemo/edit";
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

    public function delete()
    {
        if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('defaultschoolyearID') == 5)) {
            $maincreditmemoID = htmlentities(escapeString($this->uri->segment(3)));
            if((int)$maincreditmemoID) {
                $maincreditmemo = $this->maincreditmemo_m->get_single_maincreditmemo([
                    'maincreditmemoID'         => $maincreditmemoID,
                    'maincreditmemodeleted_at' => 1,
                    'schoolID'                 => $this->session->userdata('schoolID'),
                ]);
                if(customCompute($maincreditmemo)) {
          					$this->maincreditmemo_m->update_maincreditmemo(['maincreditmemodeleted_at' => 0], $maincreditmemoID);
          					$this->creditmemo_m->update_creditmemo_by_maincreditmemoID(['deleted_at' => 0], $maincreditmemoID);
          					$this->session->set_flashdata('success', $this->lang->line('menu_success'));
          					redirect(base_url('creditmemo/index'));
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
        $usertypeID             = $this->session->userdata("usertypeID");
        $schoolID               = $this->session->userdata('schoolID');
        $schoolyearID           = $this->session->userdata('defaultschoolyearID');
        $this->data['credittypes'] = pluck($this->credittypes_m->get_order_by_credittypes(array('schoolID' => $schoolID)), 'credittypes', 'credittypesID');

        if($usertypeID == 3) {
            $id = htmlentities(escapeString($this->uri->segment(3)));
            if((int)$id) {
                $studentID  = $this->session->userdata("loginuserID");
                $getstudent = $this->studentrelation_m->get_single_student([
                    "srstudentID"    => $studentID,
                    'srschoolyearID' => $schoolyearID
                ]);
                if(customCompute($getstudent)) {
                    $this->data['maincreditmemo'] = $this->maincreditmemo_m->get_maincreditmemo_with_studentrelation_by_maincreditmemoID(array('creditmemoID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
                    if(customCompute($this->data['maincreditmemo']) && ($this->data['maincreditmemo']->maincreditmemostudentID == $getstudent->studentID)) {
                        $this->data['creditmemos'] = $this->creditmemo_m->get_order_by_creditmemo(['maincreditmemoID' => $id, 'schoolID' => $schoolID]);

                        $this->data['grandtotalandpayment'] = $this->grandtotalandpaidsingle($this->data['maincreditmemo'], $schoolyearID, $schoolID, $this->data["maincreditmemo"]->maincreditmemostudentID);

                        $this->data["student"] = $this->student_m->get_single_student(['studentID' => $this->data["maincreditmemo"]->maincreditmemostudentID]);

                        $this->data['createuser'] = getNameByUsertypeIDAndUserID($this->data['maincreditmemo']->maincreditmemousertypeID, $this->data['maincreditmemo']->maincreditmemouserID);

                        $this->data["subview"] = "creditmemo/view";
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
                    $this->data['maincreditmemo'] = $this->maincreditmemo_m->get_maincreditmemo_with_studentrelation_by_maincreditmemoID(array('creditmemoID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
                    if($this->data['maincreditmemo']) {
                        if(in_array($this->data['maincreditmemo']->maincreditmemostudentID, $fetchStudent)) {
                            $this->data['creditmemos'] = $this->creditmemo_m->get_order_by_creditmemo(['maincreditmemoID' => $id, 'schoolID' => $schoolID]);

                            $this->data['grandtotalandpayment'] = $this->grandtotalandpaidsingle($this->data['maincreditmemo'], $schoolyearID, $schoolID, $this->data["maincreditmemo"]->maincreditmemostudentID);

                            $this->data["student"] = $this->student_m->get_single_student(['studentID' => $this->data["maincreditmemo"]->maincreditmemostudentID]);

                            $this->data['createuser'] = getNameByUsertypeIDAndUserID($this->data['maincreditmemo']->maincreditmemousertypeID, $this->data['maincreditmemo']->maincreditmemouserID);

                            $this->data["subview"] = "creditmemo/view";
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
                $this->data['maincreditmemo'] = $this->maincreditmemo_m->get_maincreditmemo_with_studentrelation_by_maincreditmemoID(array('creditmemoID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
                $this->data['creditmemos']    = $this->creditmemo_m->get_order_by_creditmemo(['maincreditmemoID' => $id, 'schoolID' => $schoolID]);

                if(customCompute($this->data["maincreditmemo"])) {
                    $this->data['grandtotalandpayment'] = $this->grandtotalandpaidsingle($this->data['maincreditmemo'], $schoolyearID, $schoolID, $this->data["maincreditmemo"]->maincreditmemostudentID);

                    $this->data["student"] = $this->student_m->get_single_student(['studentID' => $this->data["maincreditmemo"]->maincreditmemostudentID]);

                    $this->data['createuser'] = getNameByUsertypeIDAndUserID($this->data['maincreditmemo']->maincreditmemousertypeID, $this->data['maincreditmemo']->maincreditmemouserID);

                    $this->data["subview"] = "creditmemo/view";
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
        if(permissionChecker('creditmemo_view')) {
            $usertypeID             = $this->session->userdata("usertypeID");
            $schoolID               = $this->session->userdata('schoolID');
            $schoolyearID           = $this->session->userdata('defaultschoolyearID');
            $this->data['credittypes'] = pluck($this->credittypes_m->get_order_by_credittypes(array('schoolID' => $schoolID)), 'credittypes', 'credittypesID');
            if($usertypeID == 3) {
                $id = htmlentities(escapeString($this->uri->segment(3)));
                if((int)$id) {
                    $studentID  = $this->session->userdata("loginuserID");
                    $getstudent = $this->studentrelation_m->get_single_student([
                        "srstudentID"    => $studentID,
                        'srschoolyearID' => $schoolyearID
                    ]);
                    if(customCompute($getstudent)) {
                        $this->data['maincreditmemo'] = $this->maincreditmemo_m->get_maincreditmemo_with_studentrelation_by_maincreditmemoID(array('creditmemoID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
                        if(customCompute($this->data['maincreditmemo']) && ($this->data['maincreditmemo']->maincreditmemostudentID == $getstudent->studentID)) {
                            $this->data['creditmemos'] = $this->creditmemo_m->get_order_by_creditmemo(['maincreditmemoID' => $id, 'schoolID' => $schoolID]);

                            $this->data['grandtotalandpayment'] = $this->grandtotalandpaidsingle($this->data['maincreditmemo'], $schoolyearID, $schoolID, $this->data["maincreditmemo"]->maincreditmemostudentID);

                            $this->data["student"] = $this->student_m->get_single_student(['studentID' => $this->data["maincreditmemo"]->maincreditmemostudentID]);

                            $this->data['createuser'] = getNameByUsertypeIDAndUserID($this->data['maincreditmemo']->maincreditmemousertypeID, $this->data['maincreditmemo']->maincreditmemouserID);
                            $this->reportPDF('invoicemodule.css', $this->data, 'creditmemo/print_preview');
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
                        $this->data['maincreditmemo'] = $this->maincreditmemo_m->get_maincreditmemo_with_studentrelation_by_maincreditmemoID(array('creditmemoID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
                        if($this->data['maincreditmemo']) {
                            if(in_array($this->data['maincreditmemo']->maincreditmemostudentID, $fetchStudent)) {
                                $this->data['creditmemos'] = $this->creditmemo_m->get_order_by_creditmemo(['maincreditmemoID' => $id, 'schoolID' => $schoolID]);

                                $this->data['grandtotalandpayment'] = $this->grandtotalandpaidsingle($this->data['maincreditmemo'], $schoolyearID, $schoolID, $this->data["maincreditmemo"]->maincreditmemostudentID);

                                $this->data["student"] = $this->student_m->get_single_student(['studentID' => $this->data["maincreditmemo"]->maincreditmemostudentID]);

                                $this->data['createuser'] = getNameByUsertypeIDAndUserID($this->data['maincreditmemo']->maincreditmemousertypeID, $this->data['maincreditmemo']->maincreditmemouserID);

                                $this->reportPDF('invoicemodule.css', $this->data, 'creditmemo/print_preview');
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
                    $this->data['maincreditmemo'] = $this->maincreditmemo_m->get_maincreditmemo_with_studentrelation_by_maincreditmemoID(array('creditmemoID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
                    $this->data['creditmemos']    = $this->creditmemo_m->get_order_by_creditmemo(['maincreditmemoID' => $id, 'schoolID' => $schoolID]);

                    if($this->data["maincreditmemo"]) {
                        $this->data['grandtotalandpayment'] = $this->grandtotalandpaidsingle($this->data['maincreditmemo'], $schoolyearID, $schoolID, $this->data["maincreditmemo"]->maincreditmemostudentID);

                        $this->data["student"] = $this->student_m->get_single_student(['studentID' => $this->data["maincreditmemo"]->maincreditmemostudentID]);

                        $this->data['createuser'] = getNameByUsertypeIDAndUserID($this->data['maincreditmemo']->maincreditmemousertypeID, $this->data['maincreditmemo']->maincreditmemouserID);
                        $this->reportPDF('invoicemodule.css', $this->data, 'creditmemo/print_preview');
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

	public function getstudent()
    {
        $classesID    = $this->input->post('classesID');
		    $studentGroupID = $this->input->post('studentGroupID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');

        if($this->input->post('edittype')) {
            echo '<option value="0">' . $this->lang->line('creditmemo_select_student') . '</option>';
        } else {
            echo '<option value="0">' . $this->lang->line('creditmemo_all_student') . '</option>';
        }

        $students = $this->studentrelation_m->get_order_by_student([
            'srschoolyearID'   => $schoolyearID,
            'srclassesID'      => $classesID,
			      'srstudentGroupID' => $studentGroupID,
            'srschoolID'       => $schoolID,
        ]);
        if(customCompute($students)) {
            foreach($students as $student) {
                echo "<option value=\"$student->srstudentID\">" . $student->srname . " - " . $this->lang->line('creditmemo_roll') . " - " . $student->srstudentID . "</option>";
            }
        }
    }

	private function grandtotalandpaid( $maincreditmemos, $schoolyearID, $schoolID )
    {
        $retArray           = [];
        $creditmemoitems       = pluck_multi_array_key($this->creditmemo_m->get_order_by_creditmemo(['schoolyearID' => $schoolyearID, 'schoolID' => $schoolID]), 'obj', 'maincreditmemoID', 'creditmemoID');
        if(customCompute($maincreditmemos)) {
            foreach($maincreditmemos as $maincreditmemo) {
                if(isset($creditmemoitems[$maincreditmemo->maincreditmemoID])) {
                    if(customCompute($creditmemoitems[$maincreditmemo->maincreditmemoID])) {
                        foreach($creditmemoitems[$maincreditmemo->maincreditmemoID] as $creditmemoitem) {
                            $amount = $creditmemoitem->amount;

                            if(isset($retArray['grandtotal'][$maincreditmemo->maincreditmemoID])) {
                                $retArray['grandtotal'][$maincreditmemo->maincreditmemoID] = (($retArray['grandtotal'][$maincreditmemo->maincreditmemoID]) + $amount);
                            } else {
                                $retArray['grandtotal'][$maincreditmemo->maincreditmemoID] = $amount;
                            }

                            if(isset($retArray['totalamount'][$maincreditmemo->maincreditmemoID])) {
                                $retArray['totalamount'][$maincreditmemo->maincreditmemoID] = (($retArray['totalamount'][$maincreditmemo->maincreditmemoID]) + $creditmemoitem->amount);
                            } else {
                                $retArray['totalamount'][$maincreditmemo->maincreditmemoID] = $creditmemoitem->amount;
                            }
                        }
                    }
                }
            }
        }

        return $retArray;
    }

	private function grandtotalandpaidsingle( $maincreditmemo, $schoolyearID, $schoolID, $studentID = NULL )
    {
        $retArray = [
            'grandtotal'    => 0,
            'totalamount'   => 0,
            'totaldiscount' => 0,
            'totalpayment'  => 0,
            'totalfine'     => 0,
            'totalweaver'   => 0
        ];
        if(customCompute($maincreditmemo)) {
            if((int)$studentID && $studentID != NULL) {
                $creditmemoitems       = pluck_multi_array_key($this->creditmemo_m->get_order_by_creditmemo([
                    'studentID'     => $studentID,
                    'maincreditmemoID' => $maincreditmemo->maincreditmemoID,
                    'schoolyearID'  => $schoolyearID,
                    'schoolID' => $schoolID,
                ]), 'obj', 'maincreditmemoID', 'creditmemoID');
                $paymentitems       = pluck_multi_array($this->payment_m->get_order_by_payment([
                    'schoolyearID'     => $schoolyearID,
                    'schoolID' => $schoolID,
                    'paymentamount !=' => NULL
                ]), 'obj', 'creditmemoID');
                $weaverandfineitems = pluck_multi_array($this->weaverandfine_m->get_order_by_weaverandfine(['schoolyearID' => $schoolyearID, 'schoolID' => $schoolID]), 'obj', 'creditmemoID');
            } else {
                $creditmemoitem        = [];
                $paymentitems       = [];
                $weaverandfineitems = [];
            }

            if(isset($creditmemoitems[$maincreditmemo->maincreditmemoID])) {
                if(customCompute($creditmemoitems[$maincreditmemo->maincreditmemoID])) {
                    foreach($creditmemoitems[$maincreditmemo->maincreditmemoID] as $creditmemoitem) {
                        $amount = $creditmemoitem->amount;

                        if(isset($retArray['grandtotal'])) {
                            $retArray['grandtotal'] = ($retArray['grandtotal'] + $amount);
                        } else {
                            $retArray['grandtotal'] = $amount;
                        }

                        if(isset($retArray['totalamount'])) {
                            $retArray['totalamount'] = ($retArray['totalamount'] + $creditmemoitem->amount);
                        } else {
                            $retArray['totalamount'] = $creditmemoitem->amount;
                        }
                    }
                }
            }
        }

        return $retArray;
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

    public function unique_creditmemoactive()
  	{
  			$array = ['', 1, 2];

  			if(!in_array($this->input->post('creditmemo_active'), $array)) {
  					$this->form_validation->set_message("unique_creditmemoactive", "The %s field is required.");
  					return FALSE;
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

	public function unique_credittypeitems()
    {
        $credittypeitems = json_decode($this->input->post('credittypeitems'));
        $status       = [];
        if(customCompute($credittypeitems)) {
            foreach($credittypeitems as $credittypeitem) {
                if($credittypeitem->amount == '') {
                    $status[] = FALSE;
                }
            }
        } else {
            $this->form_validation->set_message("unique_credittypeitems", "The credit type item is required.");
            return FALSE;
        }

        if(in_array(FALSE, $status)) {
            $this->form_validation->set_message("unique_credittypeitems", "The credit type amount is required.");
            return FALSE;
        }
        return TRUE;
    }

	public function savecreditmemo()
    {
        $maincreditmemoID      = 0;
        $retArray['status'] = FALSE;
        if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('defaultschoolyearID') == 5)) {
            if(permissionChecker('creditmemo_add') || permissionChecker('creditmemo_edit')) {
                if($_POST) {
                    $rules = $this->rules($this->input->post('statusID'));
                    $this->form_validation->set_rules($rules);
                    if($this->form_validation->run() == FALSE) {
                        $retArray['error']  = $this->form_validation->error_array();
                        $retArray['status'] = FALSE;
                        echo json_encode($retArray);
                        exit;
                    } else {
                        $schoolID            = $this->session->userdata('schoolID');
                        $creditmemoMainArray = [];
                        $creditmemoArray     = [];
                        $studentArray        = [];
                        $credittype          = pluck($this->credittypes_m->get_order_by_credittypes(array('schoolID' => $schoolID)), 'credittypes', 'credittypesID');
                        $credittypeitems     = json_decode($this->input->post('credittypeitems'));
                        $schoolyearID        = $this->session->userdata('defaultschoolyearID');

                        $studentID = $this->input->post('studentID');
                        $classesID = $this->input->post('classesID');
						            $studentGroupID = $this->input->post('studentGroupID');
                        if(((int)$studentID || $studentID == 0) && (int)($classesID)) {
                            if($studentID == 0) {
                                $getstudents = $this->studentrelation_m->get_order_by_student([
                                    "srclassesID"      => $classesID,
									                  'srstudentGroupID' => $studentGroupID,
                                    'srschoolyearID'   => $schoolyearID,
                                    'srschoolID'       => $schoolID,
                                ]);
                            } else {
                                $getstudents = $this->studentrelation_m->get_order_by_student([
                                    "srclassesID"    => $classesID,
                                    'srstudentID'    => $studentID,
                                    'srschoolyearID' => $schoolyearID,
                                    'srschoolID'     => $schoolID,
                                ]);
                            }

                            if(customCompute($getstudents)) {

								                $creditmemodate = date("Y-m-d", strtotime($this->input->post("date")));

                                foreach($getstudents as $key => $getstudent) {
                                    $creditmemoMainArray[] = [
                                        'schoolID'                   => $schoolID,
                                        'maincreditmemoschoolyearID' => $schoolyearID,
										                    'maincreditmemoschooltermID' => $this->input->post('schooltermID'),
                                        'maincreditmemoclassesID'    => $this->input->post('classesID'),
                                        'maincreditmemostudentID'    => $getstudent->srstudentID,
                                        'maincreditmemouserID'       => $this->session->userdata('loginuserID'),
                                        'maincreditmemousertypeID'   => $this->session->userdata('usertypeID'),
                                        'maincreditmemouname'        => $this->session->userdata('name'),
                                        'maincreditmemodate'         => $creditmemodate,
                                        'maincreditmemocreate_date'  => date('Y-m-d'),
                                        'maincreditmemoday'          => date('d'),
                                        'maincreditmemomonth'        => date('m'),
                                        'maincreditmemoyear'         => date('Y'),
										                    'maincreditmemomemo'         => $this->input->post('memo'),
                                        'maincreditmemodeleted_at'   => 1
                                    ];

                                    $studentArray[] = $getstudent->srstudentID;
                                }

                                if(customCompute($creditmemoMainArray)) {
                                    $student = $this->student_m->get_single_student(['studentID' => $studentID, 'schoolID' => $schoolID]);
                                    $count   = customCompute($creditmemoMainArray);
                                    $firstID = $this->maincreditmemo_m->insert_batch_maincreditmemo($creditmemoMainArray);

                                    $lastID = $firstID + ($count - 1);

                                    if($lastID >= $firstID) {
                                        $j = 0;
                                        for($i = $firstID; $i <= $lastID; $i++) {
                                            if(customCompute($credittypeitems)) {
                                                foreach($credittypeitems as $credittypeitem) {
                                                    $creditmemoArray[] = [
                                                        'schoolID'      => $creditmemoMainArray[$j]['schoolID'],
                                                        'schoolyearID'  => $creditmemoMainArray[$j]['maincreditmemoschoolyearID'],
														                            'schooltermID'  => $creditmemoMainArray[$j]['maincreditmemoschooltermID'],
                                                        'classesID'     => $creditmemoMainArray[$j]['maincreditmemoclassesID'],
                                                        'studentID'     => $creditmemoMainArray[$j]['maincreditmemostudentID'],
                                                        'credittypeID'  => isset($credittypeitem->credittypeID) ? $credittypeitem->credittypeID : 0,
                                                        'credittype'    => isset($credittype[$credittypeitem->credittypeID]) ? $credittype[$credittypeitem->credittypeID] : '',
                                                        'amount'        => isset($credittypeitem->amount) ? $credittypeitem->amount : 0,
                                                        'userID'        => $creditmemoMainArray[$j]['maincreditmemouserID'],
                                                        'usertypeID'    => $creditmemoMainArray[$j]['maincreditmemousertypeID'],
                                                        'uname'         => $creditmemoMainArray[$j]['maincreditmemouname'],
                                                        'date'          => $creditmemoMainArray[$j]['maincreditmemodate'],
                                                        'create_date'   => $creditmemoMainArray[$j]['maincreditmemocreate_date'],
                                                        'day'           => $creditmemoMainArray[$j]['maincreditmemoday'],
                                                        'month'         => $creditmemoMainArray[$j]['maincreditmemomonth'],
                                                        'year'          => $creditmemoMainArray[$j]['maincreditmemoyear'],
                                                        'deleted_at'    => $creditmemoMainArray[$j]['maincreditmemodeleted_at'],
                                                        'maincreditmemoID' => $i
                                                    ];
                                                }
                                            }
                                            $j++;
                                        }
                                    }
                                }

                                $config = $this->quickbooksConfig();
                                if (!empty($creditmemoArray)) {
                                    $creditmemofirstID = $this->creditmemo_m->insert_batch_creditmemo($creditmemoArray);
                                    $creditmemocount   = customCompute($creditmemoArray);
                                    $creditmemolastID  = $creditmemofirstID + ($creditmemocount - 1);

                                    if($creditmemolastID >= $creditmemofirstID) {
                                        $k = 0;
                                        for($i = $creditmemofirstID; $i <= $creditmemolastID; $i++) {
                                            if ($config['active'] == "1" && now() < $config['sessionAccessTokenExpiry'])
                                              $this->creditmemoAndBilling($config, $student, $creditmemoArray[$k], $i);
                                            $k++;
                                        }
                                    }
                                }

                                $creditmemoSubtotalStatus = 1;
                                if((float)$this->input->post('totalsubtotal') == (float)0) {
                                    $creditmemoSubtotalStatus = 0;
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
                $retArray['error'] = ['permission' => 'Credit Memo permission is required.'];
                echo json_encode($retArray);
                exit;
            }
        } else {
            $retArray['error'] = ['permission' => 'Permission Denied.'];
            echo json_encode($retArray);
            exit;
        }
    }

	public function savecreditmemoforedit()
    {
        if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('defaultschoolyearID') == 5)) {
            $maincreditmemoID      = 0;
            $retArray['status'] = FALSE;
            if(permissionChecker('creditmemo_edit')) {
                if($_POST) {
                    $rules = $this->rules($this->input->post('statusID'));
                    $this->form_validation->set_rules($rules);
                    if($this->form_validation->run() == FALSE) {
                        $retArray['error']  = $this->form_validation->error_array();
                        $retArray['status'] = FALSE;
                        echo json_encode($retArray);
                        exit;
                    } else {
                        $maincreditmemoArray    = [];
                        $creditmemoArray        = [];

                        $editID = $this->input->post('editID');
                        if((int)$editID) {
                            $schoolID = $this->session->userdata('schoolID');
                            $credittype = pluck($this->credittypes_m->get_order_by_credittypes(array('schoolID' => $schoolID)), 'credittypes', 'credittypesID');
                            $credittypeitems = json_decode($this->input->post('credittypeitems'));
                            $schoolyearID = $this->session->userdata('defaultschoolyearID');

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

									                  $invoicedate = date("Y-m-d", strtotime($this->input->post("date")));

                                    if(customCompute($credittypeitems)) {
                                        foreach($credittypeitems as $credittypeitem) {
                                            $creditmemoArray[] = [
                                                'schoolID'      => $schoolID,
                                                'schoolyearID'  => $schoolyearID,
												                        'schooltermID'  => $this->input->post('schooltermID'),
                                                'classesID'     => $this->input->post('classesID'),
                                                'studentID'     => $getstudent->srstudentID,
                                                'credittypeID'  => isset($credittypeitem->credittypeID) ? $credittypeitem->credittypeID : 0,
                                                'credittype'    => isset($credittype[$credittypeitem->credittypeID]) ? $credittype[$credittypeitem->credittypeID] : '',
                                                'amount'        => isset($credittypeitem->amount) ? $credittypeitem->amount : 0,
                                                'userID'        => $this->session->userdata('loginuserID'),
                                                'usertypeID'    => $this->session->userdata('usertypeID'),
                                                'uname'         => $this->session->userdata('name'),
                                                'date'          => date("Y-m-d", strtotime($this->input->post("date"))),
                                                'create_date'   => date('Y-m-d'),
                                                'day'           => date('d'),
                                                'month'         => date('m'),
                                                'year'          => date('Y'),
                                                'deleted_at'    => 1,
                                                'maincreditmemoID' => $editID
                                            ];
                                        }
                                    }

                                    $this->creditmemo_m->delete_creditmemo_by_maincreditmemoID($editID);

                                    $creditmemofirstID = $this->creditmemo_m->insert_batch_creditmemo($creditmemoArray);

                  									$mainCreditMemoArray = [
                  										  'maincreditmemodate' => date("Y-m-d", strtotime($this->input->post("date"))),
                  											'maincreditmemomemo' => $this->input->post('memo'),
                  									];

                  									$this->maincreditmemo_m->update_maincreditmemo($mainCreditMemoArray, $editID);

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
                $retArray['error'] = ['permission' => 'Credit Memo permission is required.'];
                echo json_encode($retArray);
                exit;
            }
        } else {
            $retArray['error'] = ['permission' => 'Permission Denied.'];
            echo json_encode($retArray);
            exit;
        }
    }

    function creditmemoAndBilling($config, $student, $creditmemo, $id)
    {
      /*  This sample performs the following functions:
      1.   Add a customer
      2.   Add an item
      3    Create creditmemo using the information above
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
      $itemRef = $this->getItemObj($dataService, $creditmemo);

      /*
      * 2. Create CreditMemo using the CustomerRef and ItemRef
      */
      $creditmemoObj = QBCreditMemo::create([
        "DocNumber" => $id,
        "Line" => [
            "Amount" => $creditmemo['amount'],
            "DetailType" => "SalesItemLineDetail",
            "SalesItemLineDetail" => [
                "ItemRef" => [
                    "value" => $itemRef->Id
                ]
            ]
        ],
        "CustomerRef" => [
            "value"=> $customerRef->Id
        ],
        "BillEmail" => [
            "Address" => $student->email
        ],
        "CustomerMemo" => $id
      ]);
      $resultingCreditmemoObj = $dataService->Add($creditmemoObj);
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
    function getItemObj($dataService, $creditmemo) {
      $schoolID = $this->session->userdata('schoolID');
      try {
  			$itemArray = $dataService->Query("select * from Item WHERE Name='" . $creditmemo['credittype'] . "'");
  			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Item WHERE Name=" . $creditmemo['credittype'], "status" => "OK", 'schoolID' => $schoolID));
  		} catch (Exception $e) {
  			$this->quickbookslog_m->insert_quickbookslog(array("ip" => $_SERVER['REMOTE_ADDR'], "request" => "select * from Item WHERE Name=" . $creditmemo['credittype'], "message" => $e->getMessage(), "status" => "ERROR", 'schoolID' => $schoolID));
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

      // Fetch IncomeAccount Refs needed to create an Item
      $credittype = $this->credittypes_m->get_credittypes($creditmemo['credittypeID']);
      if (empty($credittype->incomeaccountID)) {
			$retArray['error'] = ['credittype' => 'Please add an income account for '. $credittype->credittypes];
			$retArray['status'] = FALSE;
			echo json_encode($retArray);
			exit;
  		}

      // Create Item
      $dateTime = new \DateTime($creditmemo['date']);
      $ItemObj = Item::create([
        "Name" => $credittype,
        "Active" => true,
        "FullyQualifiedName" => $credittype,
        "Taxable" => false,
        "Type" => "Service",
        "IncomeAccountRef"=> [
            "value"=>  $credittype->incomeaccountID
        ],
        "InvStartDate"=> $dateTime
      ]);
      $resultingItemObj = $dataService->Add($ItemObj);
      $itemId = $resultingItemObj->Id;  // This needs to be passed in the Invoice creation later
      return $resultingItemObj;
    }
}
?>
