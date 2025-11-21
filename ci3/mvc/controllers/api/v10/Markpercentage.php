<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Markpercentage extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('markpercentage_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/markpercentage",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $this->retdata['markpercentage'] = $this->markpercentage_m->get_markpercentage(array('schoolID' => $this->session->userdata('schoolID')));

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }
}
