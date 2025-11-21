<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Pages extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("pages_m");
    }

    /**
       * @OA\Get(
       *     path="/api/v10/pages",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $this->retdata['pagess'] = $this->pages_m->get_order_by_pages();

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }
}
