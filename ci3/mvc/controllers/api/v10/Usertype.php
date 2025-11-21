<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class usertype extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('usertype_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/usertype",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $this->retdata['usertypes'] = $this->usertype_m->get_order_by_usertype_with_or(array('schoolID' => $this->session->userdata('schoolID')));
        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }
}
