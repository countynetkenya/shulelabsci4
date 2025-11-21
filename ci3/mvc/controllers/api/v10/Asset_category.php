<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Asset_category extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('asset_category_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/asset_category",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $this->retdata['asset_categorys'] = $this->asset_category_m->get_order_by_asset_category(array('schoolID' => $this->session->userdata('schoolID')));
        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }
}
