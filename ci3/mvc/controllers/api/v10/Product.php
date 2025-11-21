<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Product extends Api_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->methods['users_get']['limit']    = 500;
        $this->methods['users_post']['limit']   = 100;
        $this->methods['users_delete']['limit'] = 50;

        $this->load->model('product_m');
        $this->load->model('productcategory_m');

        $this->lang->load('product', $this->data['language']);
        $this->retdata['language'] = $this->lang->language;
    }

    /**
       * @OA\Get(
       *     path="/api/v10/product",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $schoolID = $this->session->userdata('schoolID');
        $this->retdata['productcategorys'] = pluck($this->productcategory_m->get_order_by_productcategory(array('schoolID' => $schoolID)), 'productcategoryname', 'productcategoryID');
        $this->retdata['products'] = $this->product_m->get_order_by_product(array('schoolID' => $schoolID));

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }
}
