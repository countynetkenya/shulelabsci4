<?php

if ( !defined('BASEPATH') ) {
    exit('No direct script access allowed');
}

    class Dashboard extends Admin_Controller
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
        protected $_versionCheckingUrl = 'http://demo.inilabs.net/autoupdate/update/index';

        function __construct()
        {
            parent::__construct();
            $this->load->model('systemadmin_m');
            $this->load->model("dashboard_m");
            $this->load->model("automation_shudulu_m");
            $this->load->model("automation_rec_m");
            $this->load->model("setting_m");
            $this->load->model("notice_m");
            $this->load->model("user_m");
            $this->load->model("student_m");
            $this->load->model("classes_m");
            $this->load->model("teacher_m");
            $this->load->model("parents_m");
            $this->load->model("sattendance_m");
            $this->load->model("subjectattendance_m");
            $this->load->model("subject_m");
            $this->load->model("feetypes_m");
            $this->load->model("invoice_m");
            $this->load->model("creditmemo_m");
            $this->load->model("expense_m");
            $this->load->model("payment_m");
            $this->load->model("lmember_m");
            $this->load->model("book_m");
            $this->load->model("issue_m");
            $this->load->model('hmember_m');
            $this->load->model('tmember_m');
            $this->load->model('event_m');
            $this->load->model('holiday_m');
            $this->load->model('visitorinfo_m');
            $this->load->model('income_m');
            $this->load->model('make_payment_m');
            $this->load->model('maininvoice_m');
            $this->load->model('studentrelation_m');
            $this->load->model('divisions_m');
            $language = $this->session->userdata('lang');
            $this->lang->load('dashboard', $language);

            $this->_automation();
        }

        private function _automation()
        {
            /* Automation Start */
            if ( $this->data['siteinfos']->auto_invoice_generate == 1 ) {

                $array        = [];
                $autoRecArray = [];
                $cnt          = 0;
                $date         = date('Y-m-d');
                $day          = date('d');
                $month        = date('m');
                $year         = date('Y');
                $setting      = $this->setting_m->get_setting();
                if ( $day >= $setting->automation ) {
                    $libraryFeetype = $this->feetypes_m->get_single_feetypes([ 'feetypes' => $this->lang->line('dashboard_libraryfee') ]);
                    if ( !customCompute($libraryFeetype) ) {
                        $this->feetypes_m->insert_feetypes([
                            'feetypes' => $this->lang->line('dashboard_libraryfee'),
                            'note'     => "Don't delete it!"
                        ]);
                    }
                    $libraryFeetype = $this->feetypes_m->get_single_feetypes([ 'feetypes' => $this->lang->line('dashboard_libraryfee') ]);

                    $transportFeetype = $this->feetypes_m->get_single_feetypes([ 'feetypes' => $this->lang->line('dashboard_transportfee') ]);
                    if ( !customCompute($transportFeetype) ) {
                        $this->feetypes_m->insert_feetypes([
                            'feetypes' => $this->lang->line('dashboard_transportfee'),
                            'note'     => "Don't delete it!"
                        ]);
                    }
                    $transportFeetype = $this->feetypes_m->get_single_feetypes([ 'feetypes' => $this->lang->line('dashboard_transportfee') ]);

                    $hostelFeetype = $this->feetypes_m->get_single_feetypes([ 'feetypes' => $this->lang->line('dashboard_hostelfee') ]);
                    if ( !customCompute($hostelFeetype) ) {
                        $this->feetypes_m->insert_feetypes([
                            'feetypes' => $this->lang->line('dashboard_hostelfee'),
                            'note'     => "Don't delete it!"
                        ]);
                    }
                    $hostelFeetype = $this->feetypes_m->get_single_feetypes([ 'feetypes' => $this->lang->line('dashboard_hostelfee') ]);

                    $automation_shudulus = $this->automation_shudulu_m->get_automation_shudulu();

                    if ( customCompute($automation_shudulus) ) {
                        foreach ( $automation_shudulus as $automation_shudulu ) {
                            if ( $automation_shudulu->month == $month && $automation_shudulu->year == $year ) {
                                $cnt = 1;
                            }
                        }

                        if ( $cnt === 0 ) {
                            $automationStudents = $this->student_m->general_get_order_by_student([
                                'schoolyearID' => $this->data['siteinfos']->school_year,
                                'classesID !=' => $this->data['siteinfos']->ex_class
                            ]);
                            $automationLMember  = pluck($this->lmember_m->get_lmember(), 'lbalance', 'studentID');
                            $automationTMember  = pluck($this->tmember_m->get_tmember(), 'tbalance', 'studentID');
                            $automationHMember  = pluck($this->hmember_m->get_hmember(), 'hbalance', 'studentID');
                            $allRecord          = $this->_getAllRec($this->automation_rec_m->get_automation_rec());
                            $superAdmin         = $this->systemadmin_m->get_systemadmin(1);

                            $mainInvoiceArray = [];
                            if ( customCompute($automationStudents) ) {
                                foreach ( $automationStudents as $aTstudentkey => $aTstudent ) {
                                    if ( customCompute($automationLMember) ) {
                                        if ( isset($automationLMember[ $aTstudent->studentID ]) ) {
                                            if ( $automationLMember[ $aTstudent->studentID ] > 0 ) {
                                                if ( !isset($allRecord[5427279][ $aTstudent->studentID ][ $month ][ $year ]) ) {

                                                    $mainInvoiceArray[] = [
                                                        'maininvoiceschoolyearID' => $this->data['siteinfos']->school_year,
                                                        'maininvoiceclassesID'    => $aTstudent->classesID,
                                                        'maininvoicestudentID'    => $aTstudent->studentID,
                                                        'maininvoicestatus'       => 0,
                                                        'maininvoiceuserID'       => 1,
                                                        'maininvoiceusertypeID'   => 1,
                                                        'maininvoiceuname'        => null,
                                                        'maininvoicedate'         => date("Y-m-d"),
                                                        'maininvoicecreate_date'  => date('Y-m-d'),
                                                        'maininvoiceday'          => date('d'),
                                                        'maininvoicemonth'        => date('m'),
                                                        'maininvoiceyear'         => date('Y'),
                                                        'maininvoicedeleted_at'   => 1
                                                    ];

                                                    $array[] = [
                                                        'schoolyearID' => $this->data['siteinfos']->school_year,
                                                        'classesID'    => $aTstudent->classesID,
                                                        'studentID'    => $aTstudent->studentID,
                                                        'feetypeID'    => customCompute($libraryFeetype) ? $libraryFeetype->feetypesID : 0,
                                                        'feetype'      => customCompute($libraryFeetype) ? $libraryFeetype->feetypes : null,
                                                        'amount'       => (int) $automationLMember[ $aTstudent->studentID ],
                                                        'discount'     => 0,
                                                        'paidstatus'   => 0,
                                                        'userID'       => 1,
                                                        'usertypeID'   => 1,
                                                        'uname'        => $superAdmin->name,
                                                        'date'         => date("Y-m-d"),
                                                        'create_date'  => date('Y-m-d'),
                                                        'day'          => date('d'),
                                                        'month'        => date('m'),
                                                        'year'         => date('Y'),
                                                        'deleted_at'   => 1
                                                    ];

                                                    $autoRecArray[] = [
                                                        'studentID' => $aTstudent->studentID,
                                                        'date'      => $date,
                                                        'day'       => $day,
                                                        'month'     => $month,
                                                        'year'      => $year,
                                                        'nofmodule' => 5427279
                                                    ];
                                                }
                                            }
                                        }
                                    }

                                    if ( customCompute($automationTMember) ) {
                                        if ( isset($automationTMember[ $aTstudent->studentID ]) ) {
                                            if ( $automationTMember[ $aTstudent->studentID ] > 0 ) {
                                                if ( !isset($allRecord[872677678][ $aTstudent->studentID ][ $month ][ $year ]) ) {

                                                    $mainInvoiceArray[] = [
                                                        'maininvoiceschoolyearID' => $this->data['siteinfos']->school_year,
                                                        'maininvoiceclassesID'    => $aTstudent->classesID,
                                                        'maininvoicestudentID'    => $aTstudent->studentID,
                                                        'maininvoicestatus'       => 0,
                                                        'maininvoiceuserID'       => 1,
                                                        'maininvoiceusertypeID'   => 1,
                                                        'maininvoiceuname'        => null,
                                                        'maininvoicedate'         => date("Y-m-d"),
                                                        'maininvoicecreate_date'  => date('Y-m-d'),
                                                        'maininvoiceday'          => date('d'),
                                                        'maininvoicemonth'        => date('m'),
                                                        'maininvoiceyear'         => date('Y'),
                                                        'maininvoicedeleted_at'   => 1
                                                    ];

                                                    $array[] = [
                                                        'schoolyearID' => $this->data['siteinfos']->school_year,
                                                        'classesID'    => $aTstudent->classesID,
                                                        'studentID'    => $aTstudent->studentID,
                                                        'feetypeID'    => customCompute($transportFeetype) ? $transportFeetype->feetypesID : 0,
                                                        'feetype'      => customCompute($transportFeetype) ? $transportFeetype->feetypes : 0,
                                                        'amount'       => (int) $automationTMember[ $aTstudent->studentID ],
                                                        'discount'     => 0,
                                                        'paidstatus'   => 0,
                                                        'userID'       => 1,
                                                        'usertypeID'   => 1,
                                                        'uname'        => $superAdmin->name,
                                                        'date'         => date("Y-m-d"),
                                                        'create_date'  => date('Y-m-d'),
                                                        'day'          => date('d'),
                                                        'month'        => date('m'),
                                                        'year'         => date('Y'),
                                                        'deleted_at'   => 1
                                                    ];

                                                    $autoRecArray[] = [
                                                        'studentID' => $aTstudent->studentID,
                                                        'date'      => $date,
                                                        'day'       => $day,
                                                        'month'     => $month,
                                                        'year'      => $year,
                                                        'nofmodule' => 872677678
                                                    ];
                                                }
                                            }
                                        }
                                    }

                                    if ( customCompute($automationHMember) ) {
                                        if ( isset($automationHMember[ $aTstudent->studentID ]) ) {
                                            if ( $automationHMember[ $aTstudent->studentID ] > 0 ) {
                                                if ( !isset($allRecord[467835][ $aTstudent->studentID ][ $month ][ $year ]) ) {

                                                    $mainInvoiceArray[] = [
                                                        'maininvoiceschoolyearID' => $this->data['siteinfos']->school_year,
                                                        'maininvoiceclassesID'    => $aTstudent->classesID,
                                                        'maininvoicestudentID'    => $aTstudent->studentID,
                                                        'maininvoicestatus'       => 0,
                                                        'maininvoiceuserID'       => 1,
                                                        'maininvoiceusertypeID'   => 1,
                                                        'maininvoiceuname'        => null,
                                                        'maininvoicedate'         => date("Y-m-d"),
                                                        'maininvoicecreate_date'  => date('Y-m-d'),
                                                        'maininvoiceday'          => date('d'),
                                                        'maininvoicemonth'        => date('m'),
                                                        'maininvoiceyear'         => date('Y'),
                                                        'maininvoicedeleted_at'   => 1
                                                    ];

                                                    $array[] = [
                                                        'schoolyearID' => $this->data['siteinfos']->school_year,
                                                        'classesID'    => $aTstudent->classesID,
                                                        'studentID'    => $aTstudent->studentID,
                                                        'feetypeID'    => customCompute($hostelFeetype) ? $hostelFeetype->feetypesID : null,
                                                        'feetype'      => customCompute($hostelFeetype) ? $hostelFeetype->feetypes : null,
                                                        'feetype'      => $this->lang->line('dashboard_hostelfee'),
                                                        'amount'       => (int) $automationHMember[ $aTstudent->studentID ],
                                                        'discount'     => 0,
                                                        'paidstatus'   => 0,
                                                        'userID'       => 1,
                                                        'usertypeID'   => 1,
                                                        'uname'        => $superAdmin->name,
                                                        'date'         => date("Y-m-d"),
                                                        'create_date'  => date('Y-m-d'),
                                                        'day'          => date('d'),
                                                        'month'        => date('m'),
                                                        'year'         => date('Y'),
                                                        'deleted_at'   => 1
                                                    ];

                                                    $autoRecArray[] = [
                                                        'studentID' => $aTstudent->studentID,
                                                        'date'      => $date,
                                                        'day'       => $day,
                                                        'month'     => $month,
                                                        'year'      => $year,
                                                        'nofmodule' => 467835
                                                    ];
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            if ( customCompute($mainInvoiceArray) ) {
                                $count   = customCompute($mainInvoiceArray);
                                $firstID = $this->maininvoice_m->insert_batch_maininvoice($mainInvoiceArray);
                                $lastID  = $firstID + ( $count - 1 );

                                if ( $lastID >= $firstID ) {
                                    $j = 0;
                                    for ( $i = $firstID; $i <= $lastID; $i++ ) {
                                        $array[ $j ]['maininvoiceID'] = $i;
                                        $j++;
                                    }
                                }

                                if ( customCompute($array) ) {
                                    $this->invoice_m->insert_batch_invoice($array);
                                }

                                if ( customCompute($autoRecArray) ) {
                                    $this->automation_rec_m->insert_batch_automation_rec($autoRecArray);
                                }

                                $this->automation_shudulu_m->insert_automation_shudulu([
                                    'date'  => $date,
                                    'day'   => $day,
                                    'month' => $month,
                                    'year'  => $year
                                ]);
                            }
                        }
                    } else {
                        $this->automation_shudulu_m->insert_automation_shudulu([
                            'date'  => $date,
                            'day'   => $day,
                            'month' => $month,
                            'year'  => $year
                        ]);
                    }
                }
            }
            /* Automation Close */
        }

        private function _getAllRec( $arrays )
        {
            $returnArray = [];
            if ( customCompute($arrays) ) {
                foreach ( $arrays as $key => $array ) {
                    $returnArray[ $array->nofmodule ][ $array->studentID ][ $array->month ][ $array->year ] = 'Yes';
                }
            }
            return $returnArray;
        }

        public function index()
        {
            $this->data['headerassets'] = [
                'css' => [
                    'assets/fullcalendar/lib/cupertino/jquery-ui.min.css',
                    'assets/fullcalendar/fullcalendar.css',
                    'assets/select2/css/select2.css',
                    'assets/select2/css/select2-bootstrap.css'
                ],
                'js'  => [
                    'assets/highcharts/highcharts.js',
                    'assets/highcharts/highcharts-more.js',
                    'assets/highcharts/data.js',
                    'assets/highcharts/drilldown.js',
                    'assets/highcharts/exporting.js',
                    'assets/fullcalendar/lib/jquery-ui.min.js',
                    'assets/fullcalendar/lib/moment.min.js',
                    'assets/fullcalendar/fullcalendar.min.js',
                    'assets/select2/select2.js'
                ]
            ];

            $this->_tails($_POST['divisionID']);
            $this->_attendanceGraph();
            $this->_incomeExpenseGraph();
            $this->_visitorGraph();
            $this->_profile();

            if ( ( config_item('demo') === false ) && ( $this->data['siteinfos']->auto_update_notification == 1 ) && ( $this->session->userdata('usertypeID') == 1 ) && ( $this->session->userdata('loginuserID') == 1 ) ) {
                if ( $this->session->userdata('updatestatus') === null ) {
                    $this->data['versionChecking'] = $this->_checkUpdate();
                } else {
                    $this->data['versionChecking'] = 'none';
                }
            } else {
                $this->data['versionChecking'] = 'none';
            }

            // paid %
            $schoolID 							 = $this->session->userdata('schoolID');
            $this->data['divisions'] = $this->divisions_m->get_order_by_divisions(array('schoolID' => $schoolID));
            $usertypeID = $this->session->userdata('usertypeID');
        		if ($usertypeID == 1) {
                $schoolyearID = $this->session->userdata('defaultschoolyearID');
                $array = array();
                if(isset($_POST['divisionID']) && $_POST['divisionID'] != '') {
                    $array['divisionID'] = $_POST['divisionID'];
                    $array['schoolID']   = $schoolID;
                }
                $totalAmountAndDiscount = $this->totalAmountAndDiscustomCompute($this->invoice_m->get_all_balancefees_for_report($array));
                $totalCredit = $this->totalAmountAndDiscustomCompute($this->creditmemo_m->get_all_balancefees_for_report($array));
                $totalPayment = $this->totalPaymentAndWeaver($this->payment_m->get_all_payment_for_report($array));
                try {
                    $this->data['paid_percentage'] = round(($totalPayment+$totalCredit)/$totalAmountAndDiscount*100);
                } catch(DivisionByZeroError $e){
                    $this->data['paid_percentage'] = 0;
                }
            }

            $this->data["subview"] = "dashboard/index";
            $this->load->view('_layout_main', $this->data);
        }

        private function totalAmountAndDiscustomCompute($arrays) {
      		$totalAmountAndDiscount = 0;
      		if(customCompute($arrays)) {
      			foreach($arrays as $key => $array) {
      					$totalAmountAndDiscount += $array->amount;
      			}
      		}
      		return $totalAmountAndDiscount;
      	}

        private function totalPaymentAndWeaver($arrays) {
      		$totalPayment = 0;
      		if(customCompute($arrays)) {
      			foreach($arrays as $key => $array) {
      					$totalPayment += $array->paymentamount;
      			}
      		}
      		return $totalPayment;
      	}

        private function _tails($divisionID = NULL)
        {
            $schoolyearID = $this->session->userdata('defaultschoolyearID');
            $loginuserID  = $this->session->userdata('loginuserID');
            $schoolID 		= $this->session->userdata('schoolID');

            $studentArray = ['srschoolID' => $schoolID, 'srschoolyearID' => $schoolyearID];
            $parentArray['schoolID'] = $schoolID;
            if($divisionID) {
              $studentArray['divisionID'] = $divisionID;
              $parentArray['divisionID'] = $divisionID;
            }
            $students    = $this->studentrelation_m->get_order_by_student($studentArray);
            $classes     = pluck($this->classes_m->get_order_by_classes(array('schoolID' => $schoolID)), 'obj', 'classesID');
            $teachers    = $this->teacher_m->get_order_by_teacher(array('schoolID' => $schoolID));
            $parents     = $this->parents_m->get_parents2($parentArray);
            $books       = $this->book_m->get_order_by_book(array('schoolID' => $schoolID));
            $feetypes    = $this->feetypes_m->get_order_by_feetypes(array('schoolID' => $schoolID));
            $lmembers    = $this->lmember_m->get_order_by_lmember(array('schoolID' => $schoolID));
            $events      = $this->event_m->get_order_by_event([ 'schoolyearID' => $schoolyearID , 'schoolID' => $schoolID ]);
            $holidays    = $this->holiday_m->get_order_by_holiday([ 'schoolyearID' => $schoolyearID , 'schoolID' => $schoolID]);
            $visitors    = $this->visitorinfo_m->get_order_by_visitorinfo([ 'schoolyearID' => $schoolyearID , 'schoolID' => $schoolID]);
            $allmenu     = pluck($this->menu_m->get_order_by_menu(), 'icon', 'link');
            $allmenulang = pluck($this->menu_m->get_order_by_menu(), 'menuName', 'link');

            if ( $this->session->userdata('usertypeID') == 3 ) {
                $getLoginStudent = $this->studentrelation_m->get_single_student([
                    'srstudentID'    => $loginuserID,
                    'srschoolyearID' => $schoolyearID,
                    'srschoolID'     => $schoolID,
                ]);
                if ( customCompute($getLoginStudent) ) {
                    $subjects = $this->subject_m->get_order_by_subject([ 'classesID' => $getLoginStudent->srclassesID , 'divisionID' => $divisionID , 'schoolID' => $schoolID ]);
                    $invoices = $this->maininvoice_m->get_order_by_maininvoice([
                        'maininvoicestudentID'    => $getLoginStudent->srstudentID,
                        'maininvoiceschoolyearID' => $schoolyearID,
                        'maininvoicedeleted_at'   => 1,
                        'schoolID'                => $schoolID,
                    ]);
                    $lmember  = $this->lmember_m->get_single_lmember([ 'studentID' => $getLoginStudent->srstudentID , 'schoolID' => $schoolID ]);
                    if ( customCompute($lmember) ) {
                        $issues = $this->issue_m->get_order_by_issue([ "lID" => $lmember->lID, 'return_date' => null , 'schoolID' => $schoolID ]);
                    } else {
                        $issues = [];
                    }
                } else {
                    $invoices = [];
                    $subjects = [];
                    $issues   = [];
                }
            } else {
                $invoices = $this->maininvoice_m->get_order_by_maininvoice([
                    'maininvoiceschoolyearID' => $schoolyearID,
                    'maininvoicedeleted_at'   => 1,
                    'schoolID'                => $schoolID,
                ]);
                $subjects = $this->subject_m->get_order_by_subject([ 'classesID' => $getLoginStudent->srclassesID , 'divisionID' => $divisionID , 'schoolID' => $schoolID ]);
                $issues   = $this->issue_m->get_order_by_issue([ 'return_date' => null , 'schoolID' => $schoolID ]);
            }

            $this->data['dashboardWidget']['students']    = customCompute($students);
            $this->data['dashboardWidget']['classes']     = customCompute($classes);
            $this->data['dashboardWidget']['teachers']    = customCompute($teachers);
            $this->data['dashboardWidget']['parents']     = customCompute($parents);
            $this->data['dashboardWidget']['subjects']    = customCompute($subjects);
            $this->data['dashboardWidget']['books']       = customCompute($books);
            $this->data['dashboardWidget']['feetypes']    = customCompute($feetypes);
            $this->data['dashboardWidget']['lmembers']    = customCompute($lmembers);
            $this->data['dashboardWidget']['events']      = customCompute($events);
            $this->data['dashboardWidget']['issues']      = customCompute($issues);
            $this->data['dashboardWidget']['holidays']    = customCompute($holidays);
            $this->data['dashboardWidget']['invoices']    = customCompute($invoices);
            $this->data['dashboardWidget']['visitors']    = customCompute($visitors);
            $this->data['dashboardWidget']['allmenu']     = $allmenu;
            $this->data['dashboardWidget']['allmenulang'] = $allmenulang;

            $this->data['notices']  = $this->notice_m->get_order_by_notice([ 'schoolyearID' => $schoolyearID , 'schoolID' => $schoolID ]);
            $this->data['holidays'] = $holidays;
            $this->data['events']   = $events;
            $this->data['classes']  = $classes;
        }

        private function _attendanceGraph()
        {
            $schoolID 								      = $this->session->userdata('schoolID');
            $schoolyearID                   = $this->session->userdata('defaultschoolyearID');
            $attendanceSystem               = $this->data['siteinfos']->attendance;
            $this->data['attendanceSystem'] = $attendanceSystem;

            if ( $attendanceSystem != 'subject' ) {
                $attendances = $this->sattendance_m->get_order_by_attendance([
                    'schoolyearID' => $schoolyearID,
                    'monthyear'    => date('m-Y'),
                    'schoolID'     => $schoolID,
                ]);

                $classWiseAttendance = [];
                foreach ( $attendances as $attendance ) {
                    for ( $i = 1; $i <= 31; $i++ ) {
                        if ( $i > date('d') ) {
                            break;
                        }
                        $date = 'a' . $i;

                        if ( !isset($classWiseAttendance[ $attendance->classesID ][ $i ]['P']) ) {
                            $classWiseAttendance[ $attendance->classesID ][ $i ]['P'] = 0;
                        }

                        if ( !isset($classWiseAttendance[ $attendance->classesID ][ $i ]['A']) ) {
                            $classWiseAttendance[ $attendance->classesID ][ $i ]['A'] = 0;
                        }

                        if ( $attendance->$date == 'P' || $attendance->$date == 'L' || $attendance->$date == 'LE' ) {
                            $classWiseAttendance[ $attendance->classesID ][ $i ]['P']++;
                        } else {
                            $classWiseAttendance[ $attendance->classesID ][ $i ]['A']++;
                        }
                    }
                }

                $todaysAttendance = [];
                foreach ( $classWiseAttendance as $key => $value ) {
                    $todaysAttendance[ $key ] = $value[ (int) date('d') ];
                }

                $this->data['classWiseAttendance'] = $classWiseAttendance;
                $this->data['todaysAttendance']    = $todaysAttendance;
            } else {
                $subjectWiseAttendance = [];
                $attendances           = $this->subjectattendance_m->get_order_by_sub_attendance([
                    'schoolyearID' => $schoolyearID,
                    'monthyear'    => date('m-Y'),
                    'schoolID'     => $schoolID,
                ]);

                foreach ( $attendances as $attendance ) {
                    for ( $i = 1; $i <= 31; $i++ ) {
                        if ( $i > date('d') ) {
                            break;
                        }
                        $date = 'a' . $i;

                        if ( !isset($subjectWiseAttendance[ $attendance->classesID ][ $attendance->subjectID ][ $i ]['P']) ) {
                            $subjectWiseAttendance[ $attendance->classesID ][ $attendance->subjectID ][ $i ]['P'] = 0;
                        }

                        if ( !isset($subjectWiseAttendance[ $attendance->classesID ][ $attendance->subjectID ][ $i ]['A']) ) {
                            $subjectWiseAttendance[ $attendance->classesID ][ $attendance->subjectID ][ $i ]['A'] = 0;
                        }

                        if ( $attendance->$date == 'P' || $attendance->$date == 'L' || $attendance->$date == 'LE' ) {
                            $subjectWiseAttendance[ $attendance->classesID ][ $attendance->subjectID ][ $i ]['P']++;
                        } else {
                            $subjectWiseAttendance[ $attendance->classesID ][ $attendance->subjectID ][ $i ]['A']++;
                        }
                    }
                }

                $todaysSubjectWiseAttendance = [];
                foreach ( $subjectWiseAttendance as $class => $subject ) {
                    foreach ( $subject as $key => $value ) {
                        if ( !isset($todaysSubjectWiseAttendance[ $class ]) ) {
                            $todaysSubjectWiseAttendance[ $class ]['P'] = 0;
                            $todaysSubjectWiseAttendance[ $class ]['A'] = 0;
                        }
                        $todaysSubjectWiseAttendance[ $class ]['P'] += $value[ (int) date('d') ]['P'];
                        $todaysSubjectWiseAttendance[ $class ]['A'] += $value[ (int) date('d') ]['A'];
                    }
                }

                $this->data['subjectWiseAttendance']       = $subjectWiseAttendance;
                $this->data['todaysSubjectWiseAttendance'] = $todaysSubjectWiseAttendance;
            }
        }

        private function _incomeExpenseGraph()
        {
            $months = [
                1 => 'January',
                'February',
                'March',
                'April',
                'May',
                'June',
                'July ',
                'August',
                'September',
                'October',
                'November',
                'December',
            ];

            $monthArray   = [];
            $schoolID     = $this->session->userdata('schoolID');
            $schoolyearID = $this->session->userdata('defaultschoolyearID');
            $schoolyear   = $this->schoolyear_m->get_obj_schoolyear(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
            if ( customCompute($schoolyear) ) {
                $monthStart = abs($schoolyear->startingmonth);
                if ( $schoolyear->startingyear == $schoolyear->endingyear ) {
                    $monthLimit = ( ( $schoolyear->endingmonth - $schoolyear->startingmonth ) + 1 );
                } else {
                    $monthLimit = ( $schoolyear->startingmonth + $schoolyear->endingmonth + 1 );
                }

                $n = $monthStart;
                for ( $k = 1; $k <= $monthLimit; $k++ ) {
                    $monthArray[ $n ] = $months[ $n ];
                    $n++;
                    if ( $n > 12 ) {
                        $n = 1;
                    }
                }
                $months = $monthArray;
            }

            $incomes  = $this->income_m->get_order_by_income([ 'schoolyearID' => $schoolyearID ]);
            $payments = $this->payment_m->get_order_by_payment([
                'schoolyearID'  => $schoolyearID,
                'paymentamount' => null
            ]);

            $expenses     = $this->expense_m->get_order_by_expense([ 'schoolyearID' => $schoolyearID ]);
            $makepayments = $this->make_payment_m->get_order_by_make_payment([ 'schoolyearID' => $schoolyearID ]);


            $incomeMonthAndDay = [];
            $incomeMonthTotal  = [];
            if ( customCompute($incomes) ) {
                foreach ( $incomes as $incomeKey => $income ) {
                    if ( !isset($incomeMonthAndDay[ (int) $income->incomemonth ][ $income->incomeday ]) ) {
                        $incomeMonthAndDay[ (int) $income->incomemonth ][ (string) $income->incomeday ] = 0;
                    }

                    $incomeMonthAndDay[ (int) $income->incomemonth ][ (string) $income->incomeday ] += $income->amount;
                    if ( !isset($incomeMonthTotal[ (int) $income->incomemonth ]) ) {
                        $incomeMonthTotal[ (int) $income->incomemonth ] = 0;
                    }
                    $incomeMonthTotal[ (int) $income->incomemonth ] += $income->amount;
                }
            }

            if ( customCompute($payments) ) {
                foreach ( $payments as $paymentKey => $payment ) {
                    if ( !isset($incomeMonthAndDay[ (int) $payment->paymentmonth ][ $payment->paymentday ]) ) {
                        $incomeMonthAndDay[ (int) $payment->paymentmonth ][ (string) $payment->paymentday ] = 0;
                    }

                    $incomeMonthAndDay[ (int) $payment->paymentmonth ][ (string) $payment->paymentday ] += $payment->paymentamount;
                    if ( !isset($incomeMonthTotal[ (int) $payment->paymentmonth ]) ) {
                        $incomeMonthTotal[ (int) $payment->paymentmonth ] = 0;
                    }
                    $incomeMonthTotal[ (int) $payment->paymentmonth ] += $payment->paymentamount;
                }
            }

            $expenseMonthAndDay = [];
            $expenseMonthTotal  = [];
            if ( customCompute($expenses) ) {
                foreach ( $expenses as $expenseKey => $expense ) {
                    if ( !isset($expenseMonthAndDay[ (int) $expense->expensemonth ][ $expense->expenseday ]) ) {
                        $expenseMonthAndDay[ (int) $expense->expensemonth ][ (string) $expense->expenseday ] = 0;
                    }

                    $expenseMonthAndDay[ (int) $expense->expensemonth ][ (string) $expense->expenseday ] += $expense->amount;
                    if ( !isset($expenseMonthTotal[ (int) $expense->expensemonth ]) ) {
                        $expenseMonthTotal[ (int) $expense->expensemonth ] = 0;
                    }
                    $expenseMonthTotal[ (int) $expense->expensemonth ] += $expense->amount;
                }
            }

            if ( customCompute($makepayments) ) {
                foreach ( $makepayments as $makepaymentKey => $makepayment ) {
                    $makepaymentDay   = date('d', strtotime($makepayment->create_date));
                    $makepaymentMonth = date('m', strtotime($makepayment->create_date));
                    if ( !isset($expenseMonthAndDay[ (int) $makepaymentMonth ][ $makepaymentDay ]) ) {
                        $expenseMonthAndDay[ (int) $makepaymentMonth ][ (string) $makepaymentDay ] = 0;
                    }

                    $expenseMonthAndDay[ (int) $makepaymentMonth ][ (string) $makepaymentDay ] += $makepayment->payment_amount;
                    if ( !isset($expenseMonthTotal[ (int) $makepaymentMonth ]) ) {
                        $expenseMonthTotal[ (int) $makepaymentMonth ] = 0;
                    }
                    $expenseMonthTotal[ (int) $makepaymentMonth ] += $makepayment->payment_amount;
                }
            }

            $this->data['months']             = $months;
            $this->data['incomeMonthAndDay']  = $incomeMonthAndDay;
            $this->data['incomeMonthTotal']   = $incomeMonthTotal;
            $this->data['expenseMonthAndDay'] = $expenseMonthAndDay;
            $this->data['expenseMonthTotal']  = $expenseMonthTotal;
        }

        private function _visitorGraph()
        {
            $showChartVisitor  = [];
            $currentDate       = strtotime(date('Y-m-d H:i:s'));
            $previousSevenDate = strtotime(date('Y-m-d 00:00:00', strtotime('-7 days')));
            $visitors          = $this->loginlog_m->get_order_by_loginlog([
                'login <= ' => $currentDate,
                'login >= ' => $previousSevenDate
            ]);
            foreach ( $visitors as $visitor ) {
                $date = date('j M', $visitor->login);
                if ( !isset($showChartVisitor[ $date ]) ) {
                    $showChartVisitor[ $date ] = 0;
                }
                $showChartVisitor[ $date ]++;
            }
            $this->data['showChartVisitor'] = $showChartVisitor;
        }

        private function _profile()
        {
            $userTypeID             = $this->session->userdata('usertypeID');
            $loginUserID            = $this->session->userdata('loginuserID');
            $this->data['usertype'] = $this->session->userdata('usertype');

            if ( $userTypeID == 1 ) {
                $this->data['user'] = $this->systemadmin_m->get_single_systemadmin([ 'systemadminID' => $loginUserID ]);
            } elseif ( $userTypeID == 2 ) {
                $this->data['user'] = $this->teacher_m->get_single_teacher([ 'teacherID' => $loginUserID ]);
            } elseif ( $userTypeID == 3 ) {
                $this->data['user'] = $this->studentrelation_m->general_get_single_student([ 'studentID' => $loginUserID ]);
            } elseif ( $userTypeID == 4 ) {
                $this->data['user'] = $this->parents_m->get_single_parents([ 'parentsID' => $loginUserID ]);
            } else {
                $this->data['user'] = $this->user_m->get_single_user([ 'userID' => $loginUserID ]);
            }
        }

        private function _checkUpdate()
        {
            $version = 'none';
            if ( $this->session->userdata('usertypeID') == 1 && $this->session->userdata('loginuserID') == 1 ) {
                if ( customCompute($postDatas = @$this->_postData()) ) {
                    $versionChecking = $this->_versionChecking($postDatas);
                    if ( $versionChecking->status ) {
                        $version = $versionChecking->version;
                    }
                }
            }

            return $version;
        }

        private function _postData()
        {
            $postDatas = [];
            $this->load->model('update_m');
            $updates = $this->update_m->get_max_update();
            if ( customCompute($updates) ) {
                $postDatas = [
                    'username'       => customCompute($this->data['siteinfos']) ? $this->data['siteinfos']->purchase_username : '',
                    'purchasekey'    => customCompute($this->data['siteinfos']) ? $this->data['siteinfos']->purchase_code : '',
                    'domainname'     => base_url(),
                    'email'          => customCompute($this->data['siteinfos']) ? $this->data['siteinfos']->email : '',
                    'currentversion' => $updates->version,
                    'projectname'    => 'school',
                ];
            }

            return $postDatas;
        }

        private function _versionChecking( $postDatas )
        {

            try
			{
                $result = [
                    'status'  => false,
                    'message' => 'Error',
                    'version' => 'none'
                ];

                $postDataStrings = json_encode($postDatas);
                $ch              = curl_init($this->_versionCheckingUrl);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataStrings);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER,
                    [
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($postDataStrings)
                    ]
                );

                $serverResult = curl_exec($ch);
                curl_close($ch);
                if(customCompute($serverResult)) {
                    $result = json_decode($serverResult, true);
                }

                if($result == null){
                    $result = [
                        'status'  => true,
                        'version' => 'none'
                    ];
                }
                return (object) $result;
			}
			catch (Exception $e)
			{
                return (object) [
                    'status'  => false,
                    'message' => $e->getMessage(),
                    'version' => 'none'
                ];
			}
        }

        public function update()
        {
            if ( $this->session->userdata('usertypeID') == 1 && $this->session->userdata('loginuserID') == 1 ) {
                $this->session->set_userdata('updatestatus', true);
                redirect(base_url('update/autoupdate'));
            }
            redirect(base_url('dashboard/index'));
        }

        public function remind()
        {
            if ( $this->session->userdata('usertypeID') == 1 && $this->session->userdata('loginuserID') == 1 ) {
                $this->session->set_userdata('updatestatus', false);
            }
            redirect(base_url('dashboard/index'));
        }

        public function getDayWiseAttendance()
        {
            $showChartData = [];
            if ( $this->input->post('dayWiseAttendance') ) {
                $dayWiseAttendance = json_decode($this->input->post('dayWiseAttendance'), true);
                $type              = $this->input->post('type');
                foreach ( $dayWiseAttendance as $key => $value ) {
                    $showChartData[ $key ] = $value[ $type ];
                }
            }
            echo json_encode($showChartData);
        }

        public function dayWiseExpenseOrIncome()
        {
            $type          = $this->input->post('type');
            $monthID       = $this->input->post('monthID');
            $schoolyearID  = $this->session->userdata('defaultschoolyearID');
            $schoolID      = $this->session->userdata('schoolID');
            $showChartData = [];
            if ( $type && $monthID ) {
                $year = date('Y');

                $yearArray  = [];
                $schoolyear = $this->schoolyear_m->get_obj_schoolyear(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
                if ( customCompute($schoolyear) ) {
                    $monthStart = abs($schoolyear->startingmonth);
                    if ( $schoolyear->startingyear == $schoolyear->endingyear ) {
                        $monthLimit = ( ( $schoolyear->endingmonth - $schoolyear->startingmonth ) + 1 );
                    } else {
                        $monthLimit = ( $schoolyear->startingmonth + $schoolyear->endingmonth + 1 );
                    }

                    $n             = $monthStart;
                    $endYearStatus = false;
                    for ( $k = 1; $k <= $monthLimit; $k++ ) {
                        if ( $endYearStatus == false ) {
                            $yearArray[ $n ] = $schoolyear->startingyear;
                        }

                        if ( $endYearStatus ) {
                            $yearArray[ $n ] = $schoolyear->endingyear;
                        }

                        $n++;
                        if ( $n > 12 ) {
                            $n             = 1;
                            $endYearStatus = true;
                        }
                    }
                    $year = ( isset($yearArray[ abs($monthID) ]) ? $yearArray[ abs($monthID) ] : date('Y') );
                }

                $days        = date('t', mktime(0, 0, 0, $monthID, 1, $year));
                $dayWiseData = json_decode($this->input->post('dayWiseData'), true);
                for ( $i = 1; $i <= $days; $i++ ) {
                    if ( !isset($dayWiseData[ lzero($i) ]) ) {
                        $showChartData[ $i ] = 0;
                    } else {
                        $showChartData[ $i ] = isset($dayWiseData[ lzero($i) ]) ? $dayWiseData[ lzero($i) ] : 0;
                    }
                }
            } else {
                for ( $i = 1; $i <= 31; $i++ ) {
                    $showChartData[ $i ] = 0;
                }
            }

            echo json_encode($showChartData);
        }

        public function getSubjectWiseAttendance()
        {
            $subjectWiseAttendance = json_decode($this->input->post('subjectWiseAttendance'), true);
            $classID               = $this->input->post('classID');
            $schoolID 						 = $this->session->userdata('schoolID');
            $data['subjects']      = pluck($this->subject_m->get_order_by_subject([ 'classesID' => $classID , 'schoolID' => $schoolID ]), 'obj',
                'subjectID');
            $present               = [];
            $absent                = [];
            foreach ( $subjectWiseAttendance as $subjectID => $days ) {
                foreach ( $days as $key => $attendance ) {
                    if ( !isset($present[ $subjectID ]) ) {
                        $present[ $subjectID ] = 0;
                    }

                    if ( !isset($absent[ $subjectID ]) ) {
                        $absent[ $subjectID ] = 0;
                    }

                    $present[ $subjectID ] += $attendance['P'];
                    $absent[ $subjectID ]  += $attendance['A'];
                }
            }

            $data['present']               = $present;
            $data['absent']                = $absent;
            $data['subjectWiseAttendance'] = $subjectWiseAttendance;
            echo json_encode($data);
        }
    }
