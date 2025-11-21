<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Examschedule extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('classes_m');
        $this->load->model('section_m');
        $this->load->model('examschedule_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/examschedule",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get($id = null)
    {
        $schoolID     = $this->session->userdata('schoolID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        if($this->session->userdata('usertypeID') == 3) {
            $id = $this->data['myclass'];
        }

        $this->retdata['classes']       = $this->classes_m->get_order_by_classes(array('schoolID' => $schoolID));
        if((int)$id) {
            $this->retdata['classesID']     = $id;
            $this->retdata['examschedules'] = $this->examschedule_m->get_join_examschedule_with_exam_classes_section_subject(array('classesID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
            if(customCompute($this->retdata['examschedules'])) {
                $sections = $this->section_m->general_get_order_by_section(array("classesID" => $id, 'schoolID' => $schoolID));
                $this->retdata['sections'] = $sections;
                if(customCompute($sections)) {
                    foreach ($sections as $key => $section) {
                        $this->retdata['allsection'][$section->section] = $this->examschedule_m->get_join_examschedule_with_exam_classes_section_subject(array('classesID' => $id, 'sectionID' => $section->sectionID, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
                    }
                }
            } else {
                $this->retdata['examschedules'] = [];
            }
        } else {
            $this->retdata['classesID'] = 0;
            $this->retdata['examschedules'] = [];
        }

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }
}
