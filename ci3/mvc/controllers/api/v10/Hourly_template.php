<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Hourly_template extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('hourly_template_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/hourly_template",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $this->retdata['hourly_templates'] = $this->hourly_template_m->get_order_by_hourly_template(array('schoolID' => $this->session->userdata('schoolID')));
        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }
}
