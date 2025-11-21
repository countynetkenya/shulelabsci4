<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Leaveapply extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('leaveapplication_m');
        $this->load->model('leavecategory_m');
        $this->load->model('usertype_m');
        $this->load->model('leaveassign_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/leaveapply",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $schoolID = $this->session->userdata('schoolID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $this->retdata['leaveapplications'] = $this->leaveapplication_m->get_order_by_leaveapply_with_user(array('leaveapplications.schoolyearID' => $schoolyearID, 'leaveapplications.create_usertypeID' => $this->session->userdata('usertypeID'), 'leaveapplications.create_userID' => $this->session->userdata('loginuserID')));
        $this->retdata['leavecategorys'] = pluck($this->leavecategory_m->get_order_by_leavecategory(array('schoolID' => $schoolID)), 'leavecategory', 'leavecategoryID');

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }

    /**
       * @OA\Get(
       *     path="/api/v10/leaveapply/view/{leaveapplicationID}",
       *     @OA\Parameter(
       *         name="leaveapplicationID",
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
        if ((int)$id) {
            $schoolID = $this->session->userdata('schoolID');
            $schoolyearID  = $this->session->userdata("defaultschoolyearID");
            $this->retdata['usertypes'] = pluck($this->usertype_m->get_order_by_usertype_with_or(array('schoolID' => $schoolID)),'usertype','usertypeID');
            $this->retdata['leaveapply'] = $this->leaveapplication_m->get_single_leaveapplication(array('leaveapplicationID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));

            if(customCompute($this->retdata['leaveapply'])) {
                if(($this->retdata['leaveapply']->create_userID == $this->session->userdata('loginuserID')) && ($this->retdata['leaveapply']->create_usertypeID == $this->session->userdata('usertypeID'))) {

                    $leavecategory = $this->leavecategory_m->get_single_leavecategory(array('leavecategoryID' => $this->retdata['leaveapply']->leavecategoryID));
                    if(customCompute($leavecategory)) {
                        $this->retdata['leaveapply']->category = $leavecategory->leavecategory;
                    } else {
                        $this->retdata['leaveapply']->category = '';
                    }

                    $availableleave = $this->leaveapplication_m->get_sum_of_leave_days_by_user_for_single_category($this->session->userdata('usertypeID'), $this->session->userdata('loginuserID'), $schoolyearID, $this->retdata['leaveapply']->leavecategoryID);
                    if(isset($availableleave->days) && $availableleave->days > 0) {
                        $availableleavedays = $availableleave->days;
                    } else {
                        $availableleavedays = 0;
                    }

                    $leaveassign = $this->leaveassign_m->get_single_leaveassign(array('leavecategoryID' => $this->retdata['leaveapply']->leavecategoryID, 'schoolyearID' => $schoolyearID));
                    if(customCompute($leaveassign)) {
                        $this->retdata['leaveapply']->leaveavabledays = ($leaveassign->leaveassignday - $availableleavedays);
                    } else {
                        $this->retdata['leaveapply']->leaveavabledays = $this->lang->line('leaveapply_deleted');
                    }

                    $this->retdata['applicant']= getObjectByUserTypeIDAndUserID($this->retdata['leaveapply']->create_usertypeID, $this->retdata['leaveapply']->create_userID, $schoolyearID);

                    $this->retdata['daysArray'] = $this->leavedayscustomCompute($this->retdata['leaveapply']->from_date, $this->retdata['leaveapply']->to_date);

                    $this->response([
                        'status'    => true,
                        'message'   => 'Success',
                        'data'      => $this->retdata
                    ], REST_Controller::HTTP_OK);
                } else {
                    $this->response([
                        'status'    => false,
                        'message'   => 'Error 404',
                        'data'      => []
                    ], REST_Controller::HTTP_NOT_FOUND);
                }
            } else {
                $this->response([
                    'status'    => false,
                    'message'   => 'Error 404',
                    'data'      => []
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        } else {
            $this->response([
                'status'    => false,
                'message'   => 'Error 404',
                'data'      => []
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    private function leavedayscustomCompute($fromdate, $todate)
    {
        $allholidayArray    = $this->getHolidaysSession();
        $getweekenddayArray = $this->getWeekendDaysSession();
        $leavedays = get_day_using_two_date(strtotime($fromdate), strtotime($todate));

        $holidayCount    = 0;
        $weekenddayCount = 0;
        $leavedayCount   = 0;
        $totaldayCount   = 0;
        $retArray = [];
        if(customCompute($leavedays)) {
            foreach($leavedays as $leaveday) {
                if(in_array($leaveday, $allholidayArray)) {
                    $holidayCount++;
                } elseif(in_array($leaveday, $getweekenddayArray)) {
                    $weekenddayCount++;
                } else {
                    $leavedayCount++;
                }
                $totaldayCount++;
            }
        }

        $retArray['fromdate']        = $fromdate;
        $retArray['todate']          = $todate;
        $retArray['holidayCount']    = $holidayCount;
        $retArray['weekenddayCount'] = $weekenddayCount;
        $retArray['leavedayCount']   = $leavedayCount;
        $retArray['totaldayCount']   = $totaldayCount;
        return $retArray;
    }
}
