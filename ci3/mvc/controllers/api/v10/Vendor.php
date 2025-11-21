<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Vendor extends Api_Controller {

    function __construct() {
        parent::__construct();
        $this->methods['users_get']['limit']    = 500;
        $this->methods['users_post']['limit']   = 100;
        $this->methods['users_delete']['limit'] = 50;

        $this->load->model('vendor_m');

        $this->lang->load('vendor', $this->data['language']);
        $this->retdata['language'] = $this->lang->language;
    }

    /**
       * @OA\Get(
       *     path="/api/v10/vendor",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get() {
        $this->retdata['vendors'] = $this->vendor_m->get_order_by_vendor(array('schoolID' => $this->session->userdata('schoolID')));

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }
}
