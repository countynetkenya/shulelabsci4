<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Question_level extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('question_level_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/question_level",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $this->retdata['question_levels'] = $this->question_level_m->get_order_by_question_level(array('schoolID' => $this->session->userdata('schoolID')));

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }
}
