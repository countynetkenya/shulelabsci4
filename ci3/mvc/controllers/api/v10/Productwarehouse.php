<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Productwarehouse extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('productwarehouse_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/productwarehouse",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $this->retdata['productwarehouses'] = $this->productwarehouse_m->get_order_by_productwarehouse(array('schoolID' => $this->session->userdata('schoolID')));

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }
}
