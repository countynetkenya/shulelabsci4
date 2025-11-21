<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Section extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('classes_m');
        $this->load->model('teacher_m');
        $this->load->model('section_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/section",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get($id = null)
    {
        $schoolID = $this->session->userdata('schoolID');
        if($this->session->userdata('usertypeID') == 3) {
            $id = $this->data['myclass'];
        }

        if((int)$id) {
            $this->retdata['classesID']     = $id;
            $this->retdata['classes']       = $this->classes_m->get_order_by_classes(array('schoolID' => $schoolID));
            $fetchClass = pluck($this->retdata['classes'], 'classesID', 'classesID');
            if(isset($fetchClass[$id])) {
                $this->retdata['teachers'] = pluck($this->teacher_m->general_get_order_by_teacher(array('schoolID' => $schoolID)), 'name', 'teacherID');
                $this->retdata['sections'] = $this->section_m->general_get_order_by_section(array('classesID' => $id, 'schoolID' => $schoolID));
            } else {
                $this->retdata['teacher']  = [];
                $this->retdata['sections'] = [];
            }
        } else {
            $this->retdata['classesID'] = 0;
            $this->retdata['classes']   = $this->classes_m->get_order_by_classes(array('schoolID' => $schoolID));
            $this->retdata['sections']  = [];
        }

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }
}
