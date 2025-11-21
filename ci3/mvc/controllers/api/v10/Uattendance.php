<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Uattendance extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("user_m");
        $this->load->model("usertype_m");
        $this->load->model('uattendance_m');
        $this->load->model('leaveapplication_m');

        $this->lang->load('uattendance', $this->data['language']);
    }

    /**
       * @OA\Get(
       *     path="/api/v10/uattendance",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $schoolID = $this->session->userdata('schoolID');
        $usertype = pluck($this->usertype_m->get_order_by_usertype_with_or(array('schoolID' => $schoolID)), 'obj', 'usertypeID');
        unset($usertype[1], $usertype[2], $usertype[3], $usertype[4]);

        $myProfile = false;
        if(isset($usertype[$this->session->userdata('usertypeID')])) {
            if(!permissionChecker('uattendance_view')) {
                $myProfile = true;
            }
        }

        if(isset($usertype[$this->session->userdata('usertypeID')]) && $myProfile) {
            $id = $this->session->userdata('loginuserID');
            $this->view_get($id);
        } else {
            $this->retdata['users'] = $this->user_m->get_user_by_usertype(array('schoolID' => $schoolID));
        }

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }

    private function dayattendance($id = null, $usertypeID = null)
    {
        $schoolyearID       = $this->session->userdata('defaultschoolyearID');
        $attendances        = $this->uattendance_m->get_order_by_uattendance(array("userID" => $id, 'schoolyearID' => $schoolyearID));
        $attendances        = pluck($attendances,'obj','monthyear');
        $schoolYearMonths   = $this->schoolYearMonth($this->data['schoolyearsessionobj']);
        $holidays           = $this->getHolidaysSession();
        $weekends           = $this->getWeekendDaysSession();
        $leaves             = $this->leaveApplicationsDateListByUser($id, $schoolyearID, $usertypeID);

        $attendacneArray = [];
        $totalDayCount   = [];
        if(customCompute($schoolYearMonths)) {
            foreach ($schoolYearMonths as $schoolYearMonth) {
                for ($i=1; $i <= 31; $i++) {
                    $d = sprintf('%02d',$i);
                    $date = $d."-".$schoolYearMonth;

                    if(!isset($totalDayCount['totalholiday'])) {
                        $totalDayCount['totalholiday'] = 0;
                    }

                    if(!isset($totalDayCount['totalweekend'])) {
                        $totalDayCount['totalweekend'] = 0;
                    }

                    if(!isset($totalDayCount['totalleave'])) {
                        $totalDayCount['totalleave'] = 0;
                    }

                    if(!isset($totalDayCount['totalpresent'])) {
                        $totalDayCount['totalpresent'] = 0;
                    }

                    if(!isset($totalDayCount['totallatewithexcuse'])) {
                        $totalDayCount['totallatewithexcuse'] = 0;
                    }

                    if(!isset($totalDayCount['totallate'])) {
                        $totalDayCount['totallate'] = 0;
                    }

                    if(!isset($totalDayCount['totalabsent'])) {
                        $totalDayCount['totalabsent'] = 0;
                    }

                    if(in_array($date, $holidays)) {
                        $attendacneArray[$schoolYearMonth][$i] = 'H';
                        $totalDayCount['totalholiday']++;
                    } elseif (in_array($date, $weekends)) {
                        $attendacneArray[$schoolYearMonth][$i] = 'W';
                        $totalDayCount['totalweekend']++;
                    } elseif(in_array($date, $leaves)) {
                        $attendacneArray[$schoolYearMonth][$i] = 'LA';
                        $totalDayCount['totalleave']++;
                    } else {
                        $a = 'a'.$i;
                        if(isset($attendances[$schoolYearMonth]) && $attendances[$schoolYearMonth]->$a != null) {
                            $attendacneArray[$schoolYearMonth][$i] = $attendances[$schoolYearMonth]->$a;

                            if($attendances[$schoolYearMonth]->$a == 'P') {
                                $totalDayCount['totalpresent']++;
                            } elseif($attendances[$schoolYearMonth]->$a == 'LE') {
                                $totalDayCount['totallatewithexcuse']++;
                            } elseif($attendances[$schoolYearMonth]->$a == 'L') {
                                $totalDayCount['totallate']++;
                            } elseif($attendances[$schoolYearMonth]->$a == 'A') {
                                $totalDayCount['totalabsent']++;
                            }
                        } else {
                            $attendacneArray[$schoolYearMonth][$i] = 'N/A';
                        }
                    };
                }
            }
        }

        $retArray = ['attendance' => $attendacneArray, 'totalcount' => $totalDayCount];
        return $retArray;
    }

    private function schoolYearMonth($schoolYear, $keyExist = false)
    {
        $dateArray = [];
        $startDate    = (new DateTime($schoolYear->startingdate))->modify('first day of this month');
        $endDate      = (new DateTime($schoolYear->endingdate))->modify('last day of this month');
        $dateInterval = DateInterval::createFromDateString('1 month');
        $datePeriods   = new DatePeriod($startDate, $dateInterval, $endDate);

        if(customCompute($datePeriods)) {
            foreach ($datePeriods as $datePeriod) {
                if($keyExist) {
                    $dateArray[] = ['monthkey' => $datePeriod->format("m").'-'.$datePeriod->format("Y"), 'monthname' => $datePeriod->format("M")];
                } else {
                    $dateArray[] = $datePeriod->format("m-Y");
                }
            }
        }
        return $dateArray;
    }

    private function leaveApplicationsDateListByUser($studentID, $schoolyearID, $usertypeID)
    {
        $leaveapplications = $this->leaveapplication_m->get_order_by_leaveapplication(array('create_userID'=>$studentID,'create_usertypeID' => $usertypeID ,'schoolyearID' => $schoolyearID, 'status' => 1));
        $retArray = [];
        if(customCompute($leaveapplications)) {
            $oneday    = 60*60*24;
            foreach($leaveapplications as $leaveapplication) {
                for($i = strtotime($leaveapplication->from_date); $i <= strtotime($leaveapplication->to_date); $i = ($i+$oneday)) {
                    $retArray[] = date('d-m-Y', $i);
                }
            }
        }
        return $retArray;
    }

    /**
       * @OA\Get(
       *     path="/api/v10/uattendance/view/{userID}",
       *     @OA\Parameter(
       *         name="userID",
       *         description="",
       *         in = "path",
       *         allowMultiple=false,
       *         required=true,
       *         @OA\Schema(
       *             default=null,
       *             type="integer"
       *         )
       *     ),
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     ),
       *     @OA\Response(
       *         response="404",
       *         description="Error 404"
       *     ),
       * )
       */
    public function view_get($id = null)
    {
        if((int)$id) {
            $schoolID       = $this->session->userdata('schoolID');
            $schoolyearID   = $this->session->userdata('defaultschoolyearID');
            $user           = $this->user_m->get_single_user(array('userID' => $id, 'schoolID' => $schoolID));
            if(customCompute($user)) {
                $this->retdata['user']              = $user;
                $this->retdata['usertypes']         = pluck($this->usertype_m->get_order_by_usertype_with_or(array('schoolID' => $schoolID)), 'usertype', 'usertypeID' );
                $this->retdata['attendancesmonths'] = $this->schoolYearMonth($this->data['schoolyearsessionobj'], true);
                $attendance                         = $this->dayattendance($user->userID, $user->usertypeID);
                $this->retdata['attendance']        = $attendance['attendance'];
                $this->retdata['totalcount']        = $attendance['totalcount'];

                $this->response([
                    'status' => true,
                    'message' => 'Success',
                    'data' => $this->retdata
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Error 404',
                    'data' => []
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        } else {
            $this->response([
                'status' => false,
                'message' => 'Error 404',
                'data' => []
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    /**
  * @OA\Post(
  *   path="/api/v10/uattendance/add",
  *   summary="User attendance",
  *   description="Add user attendance",
  *   @OA\RequestBody(
  *       required=true,
  *       @OA\MediaType(
  *           mediaType="application/json",
  *           @OA\Schema(
  *               type="object",
  *               @OA\Property(
  *                   property="date",
  *                   description="Date of attendance",
  *                   type="string",
  *                   example="22-04-2025"
  *               ),
  *           )
  *       )
  *   ),
  *  @OA\Response(
  *         response="200",
  *         description="Success"
  *     ),
  *  @OA\Response(
  *         response="400",
  *         description="Invalid details submitted"
  *     ),
  *  @OA\Response(
  *         response="404",
  *         description="404 error"
  *     ),
  * )
  */
     public function add_post() {
        if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID') || $this->session->userdata('usertypeID') == 1)) {

            $schoolyearID    = $this->session->userdata('defaultschoolyearID');
            $this->retdata['date']     = date("d-m-Y");
            $this->retdata['users']    = [];
            $this->retdata['dateinfo'] = [];
            $this->retdata['calenderdisableweekdays']   = (($this->data['siteinfos']->weekends != '') ? explode(',', $this->data['siteinfos']->weekends) : []); ;
            $this->retdata['calenderfromdate']          = date('Y-m-d', strtotime($this->data['schoolyearsessionobj']->startingdate));
            $this->retdata['calendertodate']            = date('Y-m-d', strtotime($this->data['schoolyearsessionobj']->endingdate));
            $this->retdata['calenderdisabledates']      = $this->getHolidayssession(false);

            if(inputCall()) {
                $_POST = inputCall();
                $rules = $this->rules();
                $this->form_validation->set_rules($rules);
                if ($this->form_validation->run() == FALSE) {
                    $this->retdata2['validation'] = $this->form_validation->error_array();
                    $this->response([
                        'status' => false,
                        'message' => 'Error 400',
                        'data' => $this->retdata2,
                    ], REST_Controller::HTTP_BAD_REQUEST);
                } else {
                    $date = inputCall("date");
                    $this->retdata['date'] = $date;
                    $explode_date  = explode("-", $date);
                    $monthyear     = $explode_date[1]."-".$explode_date[2];

                    $schoolID = $this->session->userdata('schoolID');
                    $users = $this->user_m->get_user_by_usertype(array('schoolID' => $schoolID));
                    if(customCompute($users)) {
                        $uattendance_monthyear = pluck($this->uattendance_m->get_order_by_uattendance(array("monthyear" => $monthyear, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID)), 'obj', 'userID');
                        $userArray = [];
                        foreach($users as $user) {
                            if(!isset($uattendance_monthyear[$user->userID])) {
                                $userArray[] = array(
                                    'schoolID'      => $schoolID,
                                    'schoolyearID'  => $schoolyearID,
                                    "userID"        => $user->userID,
                                    "usertypeID"    => $user->usertypeID,
                                    "monthyear"     => $monthyear
                                );
                            }
                        }

                        if(customCompute($userArray)) {
                            $this->uattendance_m->insert_batch_uattendance($userArray);
                        }

                        $this->retdata['dateinfo']['day']  = date('l', strtotime($date));
                        $this->retdata['dateinfo']['date'] = date('jS F Y', strtotime($date));
                        $this->retdata['uattendances']     = pluck($this->uattendance_m->get_order_by_uattendance(array('monthyear' => $monthyear, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID)), 'obj', 'userID');

                        $this->retdata['users']     = $users;
                        $this->retdata['day']       = $explode_date[0];
                        $this->retdata['monthyear'] = $monthyear;
                    }
                    $this->response([
                        'status' => true,
                        'message' => 'Success',
                        'data' => $this->retdata
                    ], REST_Controller::HTTP_OK);
                }
            } else {
                $this->response([
                    'status' => true,
                    'message' => 'Success',
                    'data' => $this->retdata
                ], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response([
                'status' => false,
                'message' => 'Error 404',
                'data' => []
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    /**
  * @OA\Post(
  *   path="/api/v10/uattendance/saveattendance",
  *   summary="Save user attendance",
  *   description="Save user attendance",
  *   @OA\RequestBody(
  *       required=true,
  *       @OA\MediaType(
  *           mediaType="application/json",
  *           @OA\Schema(
  *               type="object",
  *               @OA\Property(
  *                   property="day",
  *                   description="Day of the month",
  *                   type="string",
  *                   example="22"
  *               ),
  *               @OA\Property(
  *                   property="monthyear",
  *                   description="Month and Year",
  *                   type="string",
  *                   example="04-2025"
  *               ),
  *               @OA\Property(
  *                   property="attendance[]",
  *                   description="Present (P), Late w/ excuse (lE), late (L), and absent (A)",
  *                   type="string",
  *                   example="P"
  *               ),
  *           )
  *       )
  *   ),
  *  @OA\Response(
  *         response="200",
  *         description="Success"
  *     ),
  *  @OA\Response(
  *         response="400",
  *         description="Invalid data submitted"
  *     ),
  *  @OA\Response(
  *         response="401",
  *         description="Permission denied"
  *     ),
  *  @OA\Response(
  *         response="404",
  *         description="Attendance data is not found"
  *     ),
  *  @OA\Response(
  *         response="405",
  *         description="No data submitted"
  *     ),
  * )
  */
    public function saveattendance_post() {
        $retArray['status']  = FALSE;
        $retArray['message'] = '';
        if(permissionChecker('uattendance')) {
            if(inputCall()) {
                $day          = inputCall('day');
                $monthyear    = inputCall('monthyear');
                $attendance   = inputCall('attendance');
                $schoolyearID = $this->session->userdata('defaultschoolyearID');

                $_POST = inputCall();
                $rules = $this->attendance_rules();
                $this->form_validation->set_rules($rules);
                if ($this->form_validation->run() == FALSE) {
                    $this->retdata2['validation'] = $this->form_validation->error_array();
                    $this->response([
                        'status' => false,
                        'message' => 'Error 400',
                        'data' => $this->retdata2,
                    ], REST_Controller::HTTP_BAD_REQUEST);
                } else {
                    $updateArray = [];
                    $attendance  = json_decode($attendance, true);
                    if(is_array($attendance) && customCompute($attendance)) {
                        foreach($attendance as $key => $singleAttendance) {
                            $id = str_replace("attendance", "", $key);
                            $updateArray[] = array(
                                'uattendanceID' => $id,
                                'a'.abs($day) => $singleAttendance,
                            );
                        }
                    }

                    if($updateArray) {
                        $this->uattendance_m->update_batch_uattendance($updateArray, 'uattendanceID');
                        $this->response([
                            'status' => TRUE,
                            'message' => 'Success',
                            'data' => []
                        ], REST_Controller::HTTP_OK);
                    } else {
                        $this->response([
                            'status' => false,
                            'message' => $this->lang->line('uattendance_attendance_data'),
                            'data' => []
                        ], REST_Controller::HTTP_NOT_FOUND);
                    }
                }
            }  else {
                $this->response([
                    'status' => false,
                    'message' => $this->lang->line('uattendance_permissionmethod'),
                    'data' => []
                ], REST_Controller::HTTP_METHOD_NOT_ALLOWED);
            }
        } else {
            $this->response([
                'status' => false,
                'message' => $this->lang->line('uattendance_permission'),
                'data' => []
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

    protected function rules() {
        $rules = array(
            array(
                'field' => 'date',
                'label' => $this->lang->line("uattendance_date"),
                'rules' => 'trim|required|max_length[10]|xss_clean|callback_date_valid|callback_valid_future_date|callback_check_holiday|callback_check_weekendday|callback_check_session_year_date'
            )
        );
        return $rules;
    }

    protected function attendance_rules() {
        $rules = array(
            array(
                'field' => 'day',
                'label' => $this->lang->line("uattendance_day"),
                'rules' => 'trim|required|numeric|xss_clean|max_length[11]'
            ),
            array(
                'field' => 'monthyear',
                'label' => $this->lang->line("uattendance_monthyear"),
                'rules' => 'trim|required|max_length[10]|xss_clean'
            ),
            array(
                'field' => 'attendance[]',
                'label' => $this->lang->line("uattendance_attendance"),
                'rules' => 'trim|required|xss_clean'
            )
        );
        return $rules;
    }

    public function date_valid($date) {
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

    public function valid_future_date($date) {
        $presentdate = date('Y-m-d');
        $date = date("Y-m-d", strtotime($date));
        if($date > $presentdate) {
            $this->form_validation->set_message('valid_future_date','The %s field given invalid.');
            return FALSE;
        }
        return TRUE;
    }

    public function check_holiday($date) {
        $getHolidays = $this->getHolidaysSession();
        if(customCompute($getHolidays)) {
            if(in_array($date, $getHolidays)) {
                $this->form_validation->set_message('check_holiday','The %s field given holiday.');
                return FALSE;
            } else {
                return TRUE;
            }
        }
        return TRUE;
    }

    public function check_weekendday($date) {
        $getWeekendDays = $this->getWeekendDaysSession();
        if(customCompute($getWeekendDays)) {
            if(in_array($date, $getWeekendDays)) {
                $this->form_validation->set_message('check_weekendday','The %s field given weekenday.');
                return FALSE;
            } else {
                return TRUE;
            }
        }
        return TRUE;
    }

    public function check_session_year_date() {
        $date = strtotime(inputCall('date'));
        $startingdate = strtotime($this->data['schoolyearsessionobj']->startingdate);
        $endingdate   = strtotime($this->data['schoolyearsessionobj']->endingdate);

        if($date < $startingdate || $date > $endingdate) {
            $this->form_validation->set_message('check_session_year_date','The %s field given not exits.');
            return FALSE;
        }
        return TRUE;
    }

}
