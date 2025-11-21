<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Posts_categories extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('posts_categories_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/posts_categories",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $this->retdata['posts_categories'] = $this->posts_categories_m->get_posts_categories();
        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }

}
