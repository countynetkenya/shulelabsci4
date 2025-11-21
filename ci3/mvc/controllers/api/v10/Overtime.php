<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Overtime extends Api_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('usertype_m');
        $this->load->model('user_m');
        $this->load->model('overtime_m');
        $this->load->model('manage_salary_m');
        $this->load->model('salary_template_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/overtime",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $schoolID = $this->session->userdata('schoolID');
        $this->retdata['overtimes'] = $this->overtime_m->get_order_by_overtime(array('schoolID' => $schoolID));
        $this->retdata['roles']     = pluck($this->usertype_m->get_order_by_usertype_with_or(array('schoolID' => $schoolID)), 'usertype', 'usertypeID');
        $this->retdata['allUsers']  = getAllUserObjectWithoutStudent(array('schoolID' => $schoolID));
            $this->response([
                'status'    => true,
                'message'   => 'Success',
                'data'      => $this->retdata
            ], REST_Controller::HTTP_OK);
    }

}
