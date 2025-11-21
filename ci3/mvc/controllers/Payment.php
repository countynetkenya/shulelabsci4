<?php if(!defined('BASEPATH'))
    exit('No direct script access allowed');
require_once(APPPATH . '../vendor/autoload.php');
use QuickBooksOnline\API\DataService\DataService;

class Payment extends Admin_Controller
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

    function __construct()
    {
        parent::__construct();
        $this->load->model('payment_m');
		    $this->load->model('invoice_m');
		    $this->load->model('creditmemo_m');
        $this->load->model("classes_m");
        $this->load->model("student_m");
        $this->load->model("parents_m");
        $this->load->model("section_m");
		    $this->load->model('studentgroup_m');
        $this->load->model('user_m');
        $this->load->model('weaverandfine_m');
        $this->load->model("globalpayment_m");
        $this->load->model("student_m");
		    $this->load->model("studentrelation_m");
		    $this->load->model('schoolterm_m');
        $this->load->model("quickbookssettings_m");
        $this->load->model("paymenttypes_m");
        $language = $this->session->userdata('lang');
        $this->lang->load('student', $language);
        $this->lang->load('global_payment', $language);
		    $this->lang->load('payment', $language);
    }

    protected function rules( $statusID = 0 )
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
                'field' => 'feetypeitems',
                'label' => $this->lang->line("invoice_feetypeitem"),
                'rules' => 'trim|xss_clean|required|callback_unique_feetypeitems'
            ],
            /*[
                'field' => 'statusID',
                'label' => $this->lang->line("invoice_status"),
                'rules' => 'trim|xss_clean|required|numeric|callback_unique_status'
            ],*/
            [
                'field' => 'date',
                'label' => $this->lang->line("invoice_date"),
                'rules' => 'trim|required|xss_clean|max_length[10]|callback_date_valid'
            ],
        ];

        /*if($statusID != 0) {
            $rules[] = [
                'field' => 'payment_method',
                'label' => $this->lang->line("invoice_paymentmethod"),
                'rules' => 'trim|required|xss_clean|max_length[20]|callback_unique_payment_method'
            ];
        }*/

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
        $schoolID = $this->session->userdata('schoolID');
        $usertypeID = $this->session->userdata("usertypeID");
        $schoolyearID = $this->session->userdata("defaultschoolyearID");
        $this->data['paymentmethods'] = $this->paymenttypes_m->get_order_by_paymenttypes(array('schoolID' => $schoolID));
        if($usertypeID == 4) {
			    $userID = $this->session->userdata('loginuserID');
			    $students = $this->studentrelation_m->general_get_order_by_student(['srschoolyearID' => $schoolyearID, 'parentID' => $userID]);
			    foreach ($students as $student) {
				    $allPaymentList = $this->payment_m->get_order_by_payment(['studentID' => $student->studentID]);
				    $totalPaymentAmount = $this->generateAllPaymentAmount($allPaymentList);
				    $invoices = $this->invoice_m->get_order_by_invoice(['studentID' => $student->studentID, 'deleted_at' => 1]);
				    $totalInvoiceAmount = $this->generateAllInvoiceAmount($invoices);
				    $creditmemos = $this->creditmemo_m->get_order_by_creditmemo(['studentID' => $student->studentID, 'deleted_at' => 1]);
				    $totalCreditmemoAmount = $this->generateAllCreditmemoAmount($creditmemos);
				    $balance = $totalInvoiceAmount - ($totalPaymentAmount + $totalCreditmemoAmount);
				    $student->balance = $balance;
			   }
			   $this->data['students'] = $students;
      } elseif($usertypeID == 1) {
			  $this->data['students'] = [];
      }

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

		  $this->data['classes']  = $this->classes_m->general_get_order_by_classes(array('schoolID' => $schoolID));
		  $this->data['studentgroups'] = $this->studentgroup_m->get_order_by_studentgroup(array('schoolID' => $schoolID));
		  $this->data['usertypeID']  = $usertypeID;
		  $this->data['terms'] = $this->schoolterm_m->get_order_by_schoolterm(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));

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

		  $this->data["subview"]  = "payment/index";
		  $this->load->view('_layout_main', $this->data);
    }

	public function getstudent()
  {
    $classesID = $this->input->post('classesID');
		$studentGroupID = $this->input->post('studentGroupID');
    $schoolyearID = $this->session->userdata('defaultschoolyearID');
		$usertypeID = $this->session->userdata("usertypeID");
		$userID = $this->session->userdata('loginuserID');

		echo '<option value="0">' . $this->lang->line('payment_select_student') . '</option>';

		$array = array('srschoolID' => $this->session->userdata('schoolID'), 'srschoolyearID' => $schoolyearID, 'srclassesID' => $classesID);

		if ($studentGroupID != 0)
			$array['srstudentGroupID'] = $studentGroupID;
		if ($usertypeID == 4)
			$array['parentID'] = $userID;

		$students = $this->studentrelation_m->get_order_by_student($array);

        if(customCompute($students)) {
            foreach($students as $student) {
                echo "<option value=\"$student->srstudentID\">" . $student->srname . " - " . $this->lang->line('payment_student_registerNO') . " - " . $student->srstudentID . "</option>";
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
