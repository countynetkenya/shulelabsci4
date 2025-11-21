<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Dashboard extends Api_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('systemadmin_m');
		$this->load->model("notice_m");
		$this->load->model("user_m");
		$this->load->model("student_m");
		$this->load->model("classes_m");
		$this->load->model("teacher_m");
		$this->load->model("parents_m");
		$this->load->model("subject_m");
		$this->load->model("feetypes_m");
		$this->load->model("lmember_m");
		$this->load->model("book_m");
		$this->load->model('event_m');
		$this->load->model('holiday_m');
		$this->load->model('visitorinfo_m');
		$this->load->model('maininvoice_m');
		$this->load->model('studentrelation_m');
		$this->load->model('menu_m');

    $this->lang->load('dashboard', $this->data['language']);
    $this->lang->load('topbar_menu', $this->data['language']);
	}

	/**
		 * @OA\Get(
		 *     path="/api/v10/dashboard",
		 *     @OA\Response(
		 *         response="200",
		 *         description="Success"
		 *     )
		 * )
		 */
	public function index_get()
	{
		$schoolID     = $this->session->userdata('schoolID');
		$schoolyearID = $this->session->userdata('defaultschoolyearID');
		$loginuserID  = $this->session->userdata('loginuserID');
		$students     = $this->studentrelation_m->get_order_by_student(array('srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID));

		$classes	= pluck($this->classes_m->get_order_by_classes(array('schoolID' => $schoolID)), 'obj', 'classesID');
		$teachers	= $this->teacher_m->get_order_by_teacher(array('schoolID' => $schoolID));
		$parents	= $this->parents_m->get_order_by_parents(array('schoolID' => $schoolID));
		$books		= $this->book_m->get_order_by_book(array('schoolID' => $schoolID));
		$feetypes	= $this->feetypes_m->get_order_by_feetypes(array('schoolID' => $schoolID));
		$lmembers	= $this->lmember_m->get_order_by_lmember(array('schoolID' => $schoolID));
		$events		= $this->event_m->get_order_by_event(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
		$holidays	= $this->holiday_m->get_order_by_holiday(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
		$visitors = $this->visitorinfo_m->get_order_by_visitorinfo(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
		$mainMenu = $this->menu_m->get_order_by_menu();
		$allmenu 	= pluck($mainMenu, 'icon', 'link');
		$allmenulang= pluck($mainMenu, 'menuName', 'link');

		if($this->session->userdata('usertypeID') == 3) {
			$getLoginStudent = $this->studentrelation_m->get_single_student(array('srstudentID' => $loginuserID, 'srschoolyearID' => $schoolyearID));
			if(customCompute($getLoginStudent)) {
				$subjects	 = $this->subject_m->get_order_by_subject(array('classesID' => $getLoginStudent->srclassesID));
				$invoices	 = $this->maininvoice_m->get_order_by_maininvoice(array('maininvoicestudentID' => $getLoginStudent->srstudentID, 'maininvoiceschoolyearID' => $schoolyearID, 'maininvoicedeleted_at' => 1));
				$lmember     = $this->lmember_m->get_single_lmember(array('studentID' => $getLoginStudent->srstudentID));
			} else {
				$invoices = [];
				$subjects = [];
			}
		} else {
			$invoices	= $this->maininvoice_m->get_order_by_maininvoice(array('maininvoiceschoolyearID' => $schoolyearID, 'maininvoicedeleted_at'=> 1, 'schoolID' => $schoolID));
			$subjects	= $this->subject_m->get_order_by_subject(array('schoolID' => $schoolID));
		}

		$widgetArray['dashboardWidget']['students']    = customCompute($students);
		$widgetArray['dashboardWidget']['classes']     = customCompute($classes);
		$widgetArray['dashboardWidget']['teachers']    = customCompute($teachers);
		$widgetArray['dashboardWidget']['parents'] 	   = customCompute($parents);
		$widgetArray['dashboardWidget']['subjects']    = customCompute($subjects);
		$widgetArray['dashboardWidget']['books'] 	   = customCompute($books);
		$widgetArray['dashboardWidget']['feetypes']    = customCompute($feetypes);
		$widgetArray['dashboardWidget']['lmembers']    = customCompute($lmembers);
		$widgetArray['dashboardWidget']['events'] 	   = customCompute($events);
		$widgetArray['dashboardWidget']['holidays']    = customCompute($holidays);
		$widgetArray['dashboardWidget']['invoices']    = customCompute($invoices);
		$widgetArray['dashboardWidget']['visitors']    = customCompute($visitors);
		$widgetArray['dashboardWidget']['allmenu'] 	   = $allmenu;
		$widgetArray['dashboardWidget']['allmenulang'] = $allmenulang;


		$userTypeID    = $this->session->userdata('usertypeID');
		$loginUserID   = $this->session->userdata('loginuserID');
		$this->retdata['usertype']   = $this->session->userdata('usertype');
		$this->retdata['usertypeID'] = $userTypeID;
		$this->retdata['sitename']   = $this->data['siteinfos']->sname;
		$this->retdata['sitephoto']  = $this->data['siteinfos']->photo;

		if($userTypeID == 1) {
			$this->retdata['user'] = $this->systemadmin_m->get_single_systemadmin(array('systemadminID' => $loginUserID));
		} elseif($userTypeID == 2) {
			$this->retdata['user'] = $this->teacher_m->get_single_teacher(array('teacherID' => $loginUserID));
		}  elseif($userTypeID == 3) {
			$this->retdata['user'] = $this->studentrelation_m->general_get_single_student(array('studentID' => $loginUserID));
		} elseif($userTypeID == 4) {
			$this->retdata['user'] = $this->parents_m->get_single_parents(array('parentsID' => $loginUserID));
		} else {
			$this->retdata['user'] = $this->user_m->get_single_user(array('userID' => $loginUserID));
		}

		$this->retdata['notices']  = $this->notice_m->get_order_by_notice(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
		$this->dashboard_tiles($widgetArray);
// 		$calenderArray['holidays'] = $holidays;
// 		$calenderArray['events']   = $events;
// 		$this->calender_info($calenderArray);

        $this->retdata['events']   = $events;
		$this->retdata['holidays'] = $holidays;

		$this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
	}

	private function dashboard_tiles($array) {
		extract($array);

		$arrayColor = array(
            'bg-orange-dark',
            'bg-teal-light',
            'bg-pink-light',
            'bg-purple-light'
        );
        $userArray = array(
            '1' => array(
                'student' => $dashboardWidget['students'],
                'teacher' => $dashboardWidget['teachers'],
                'parents' => $dashboardWidget['parents'],
                'subject' => $dashboardWidget['subjects']
            ),
            '2' => array(
                'student' => $dashboardWidget['students'],
                'teacher' => $dashboardWidget['teachers'],
                'classes' => $dashboardWidget['classes'],
                'subject' => $dashboardWidget['subjects'],
            ),
            '3' => array(
                'teacher' => $dashboardWidget['teachers'],
                'subject' => $dashboardWidget['subjects'],
                'holiday' => $dashboardWidget['holidays'],
                'invoice' => $dashboardWidget['invoices'],
            ),
            '4' => array(
                'teacher' => $dashboardWidget['teachers'],
                'book'    => $dashboardWidget['books'],
                'event'   => $dashboardWidget['events'],
                'holiday' => $dashboardWidget['holidays'],
            ),
            '5' => array(
                'teacher' => $dashboardWidget['teachers'],
                'parents' => $dashboardWidget['parents'],
                'feetypes'=> $dashboardWidget['feetypes'],
                'invoice' => $dashboardWidget['invoices'],
            ),
            '6' => array(
                'teacher' => $dashboardWidget['teachers'],
                'lmember' => $dashboardWidget['lmembers'],
                'book'    => $dashboardWidget['books'],
                'holiday' => $dashboardWidget['holidays'],
            ),
            '7' => array(
                'teacher'     => $dashboardWidget['teachers'],
                'event'       => $dashboardWidget['events'],
                'holiday'     => $dashboardWidget['holidays'],
                'visitorinfo' => $dashboardWidget['visitors'],
            ),
        );

        $counter = 0;
        $getActiveUserID    = $this->session->userdata('usertypeID');
        $getAllSessionDatas = $this->session->userdata('master_permission_set');
        $generateBoxArray   = array();
        if(customCompute($getAllSessionDatas)) {
	        foreach($getAllSessionDatas as $getAllSessionDataKey => $getAllSessionData) {
	            if($getAllSessionData == 'yes') {
	                if(isset($userArray[$getActiveUserID][$getAllSessionDataKey])) {
	                    if($counter == 4) {
	                      break;
	                    }

	                    $generateBoxArray[$getAllSessionDataKey] = array(
	                        'icon' => $dashboardWidget['allmenu'][$getAllSessionDataKey],
	                        'color' => $arrayColor[$counter],
	                        'link' => $getAllSessionDataKey,
	                        'count' => $userArray[$getActiveUserID][$getAllSessionDataKey],
	                        'menu' => $this->lang->line('menu_'.$dashboardWidget['allmenulang'][$getAllSessionDataKey]),
	                    );
	                    $counter++;
	                }
	            }
	        }
        }

        $icon = '';
        $menu = '';
        if($counter < 4) {
            $userArray = $this->allModuleArray($dashboardWidget, $getActiveUserID);
            if(customCompute($getAllSessionDatas)) {
	            foreach ($getAllSessionDatas as $getAllSessionDataKey => $getAllSessionData) {
	                if($getAllSessionData == 'yes') {
	                    if(isset($userArray[$getActiveUserID][$getAllSessionDataKey])) {
	                        if($counter == 4) {
	                            break;
	                        }

	                        if(!isset($generateBoxArray[$getAllSessionDataKey])) {
	                            $generateBoxArray[$getAllSessionDataKey] = array(
	                                'icon'  => $dashboardWidget['allmenu'][$getAllSessionDataKey],
	                                'color' => $arrayColor[$counter],
	                                'link'  => $getAllSessionDataKey,
	                                'count' => $userArray[$getActiveUserID][$getAllSessionDataKey],
	                        		'menu' => $this->lang->line('menu_'.$dashboardWidget['allmenulang'][$getAllSessionDataKey])
	                            );
	                            $counter++;
	                        }
	                    }
	                }
	            }
            }
        }
		$this->retdata['generateBoxs'] = $generateBoxArray;
	}

	private function calender_info($array) {
		extract($array);

		$retArray = '';
		if(customCompute($events)) {
			foreach ($events as $event) {
                $retArray .= '{';
                    $retArray .= "title: '".str_replace("'", "\'", $event->title)."', ";
                    $retArray .= "start: '".$event->fdate."T".$event->ftime."', ";
                    $retArray .= "end: '".$event->tdate."T".$event->ttime."', ";
	                $retArray .= "url:'".base_url('event/view/'.$event->eventID)."', ";
                    $retArray .= "color  : '#5C6BC0'";
                $retArray .= '},';
	        }
		}

		if(customCompute($holidays)) {
			foreach($holidays as $holiday) {
                $retArray .= '{';
                    $retArray .= "title: '".str_replace("'", "\'", $holiday->title)."', ";
                    $retArray .= "start: '".$holiday->fdate."', ";
                    $retArray .= "end: '".$holiday->tdate."', ";
	                $retArray .= "url:'".base_url('holiday/view/'.$holiday->holidayID)."', ";
                    $retArray .= "color  : '#C24984'";
                $retArray .= '},';
            }
		}
		$this->retdata['eventAndHolidays'] = $retArray;
	}

	private function allModuleArray($dashboardWidget, $usertypeID='1') {
      	$userAllModuleArray = array(
        	$usertypeID => array(
	            'student'   => $dashboardWidget['students'],
	            'classes'   => $dashboardWidget['classes'],
	            'teacher'   => $dashboardWidget['teachers'],
	            'parents'   => $dashboardWidget['parents'],
	            'subject'   => $dashboardWidget['subjects'],
	            'book'      => $dashboardWidget['books'],
	            'feetypes'  => $dashboardWidget['feetypes'],
	            'lmember'   => $dashboardWidget['lmembers'],
	            'event'     => $dashboardWidget['events'],
	            'holiday'   => $dashboardWidget['holidays'],
	            'invoice'   => $dashboardWidget['invoices'],
	        )
      	);
      	return $userAllModuleArray;
    }

}
