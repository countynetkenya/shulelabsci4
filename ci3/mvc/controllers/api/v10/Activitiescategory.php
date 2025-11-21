<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Activitiescategory extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('activitiescategory_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/activitiescategory",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $this->retdata['activitiescategorys'] = $this->activitiescategory_m->get_activitiescategory(array('schoolID' => $this->session->userdata('schoolID')));

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }
}
