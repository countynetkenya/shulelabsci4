<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Holiday extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("alert_m");
        $this->load->model("holiday_m");
    }

    /**
       * @OA\Get(
       *     path="/api/v10/holiday",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $this->retdata['holidays'] = $this->holiday_m->get_order_by_holiday(array('schoolyearID' => $schoolyearID, 'schoolID' => $this->session->userdata('schoolID')));

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }

    /**
       * @OA\Get(
       *     path="/api/v10/holiday/view/{holidayID}",
       *     @OA\Parameter(
       *         name="holidayID",
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

        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        if((int)$id) {
            $this->retdata['holiday'] = $this->holiday_m->get_single_holiday(array('schoolyearID' => $schoolyearID, 'holidayID' => $id, 'schoolID' => $this->session->userdata('schoolID')));
            if(customCompute($this->retdata['holiday'])) {
                $alert = $this->alert_m->get_single_alert(array('itemID' => $id, "userID" => $this->session->userdata("loginuserID"), 'usertypeID' => $this->session->userdata('usertypeID'), 'itemname' => 'holiday'));
                if(!customCompute($alert)) {
                    $array = array(
                        "itemID" => $id,
                        "userID" => $this->session->userdata("loginuserID"),
                        "usertypeID" => $this->session->userdata("usertypeID"),
                        "itemname" => 'holiday',
                    );
                    $this->alert_m->insert_alert($array);
                }

                $this->response([
                    'status'    => true,
                    'message'   => 'Success',
                    'data'      => $this->retdata
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
}
