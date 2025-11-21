<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Classes extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('classes_m');
        $this->load->model('teacher_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/classes",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $schoolID = $this->session->userdata('schoolID');
        $this->retdata['teachers'] = pluck($this->teacher_m->get_teacher(array('schoolID' => $schoolID)), 'name', 'teacherID');
        $this->retdata['classes']  = $this->classes_m->get_classes(array('schoolID' => $schoolID));

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }
}
