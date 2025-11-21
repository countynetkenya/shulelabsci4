<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Leaveassign extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('usertype_m');
        $this->load->model('leaveassign_m');
        $this->load->model('leavecategory_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/leaveassign",
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
        $this->retdata['usertypes']      = pluck($this->usertype_m->get_order_by_usertype_with_or(array('schoolID' => $schoolID)), 'usertype', 'usertypeID');
        $this->retdata['leavecategorys'] = pluck($this->leavecategory_m->get_order_by_leavecategory(array('schoolID' => $schoolID)), 'leavecategory', 'leavecategoryID');
        $this->retdata['leaveassign']    = $this->leaveassign_m->get_order_by_leaveassign(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }
}
