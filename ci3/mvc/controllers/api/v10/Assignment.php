<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Assignment extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('section_m');
        $this->load->model('classes_m');
        $this->load->model('assignment_m');
        $this->load->model('assignmentanswer_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/assignment",
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

        $this->retdata['classes'] = $this->classes_m->get_order_by_classes(array('schoolID' => $schoolID));
        if((int)$id) {
            $fetchClasses = pluck($this->retdata['classes'], 'classesID', 'classesID');
            if(isset($fetchClasses[$id])) {
                $this->retdata['classesID'] = $id;
                $this->retdata['sections'] = pluck($this->section_m->general_get_order_by_section(array('classesID' => $id, 'schoolID' => $schoolID)), 'section', 'sectionID');
                $schoolyearID = $this->session->userdata('defaultschoolyearID');
                $this->retdata['assignments'] = $this->assignment_m->join_get_assignment($id, $schoolyearID);
            } else {
                $this->retdata['classesID'] = 0;
                $this->retdata['assignments'] = [];
            }
        } else {
            $this->retdata['classesID'] = 0;
            $this->retdata['assignments'] = [];
        }

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }

    /**
       * @OA\Get(
       *     path="/api/v10/assignment/view/{assignmentID}/{classID}",
       *     @OA\Parameter(
       *         name="assignmentID",
       *         description="",
       *         in = "path",
       *         allowMultiple=false,
       *         required=true,
       *         @OA\Schema(
       *             default=0,
       *             type="integer"
       *         )
       *     ),
       *     @OA\Parameter(
       *         name="classID",
       *         description="",
       *         in = "path",
       *         allowMultiple=false,
       *         required=truee,
       *         @OA\Schema(
       *             default=0,
       *             type="integer"
       *         )
       *     ),
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function view_get($id = 0, $url = 0)
    {
        $schoolID 		= $this->session->userdata('schoolID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        if((int)$id && (int)($url)) {
            $this->retdata['classesID'] = $url;
            $fetchClasses = pluck($this->classes_m->get_order_by_classes(array('schoolID' => $schoolID)), 'classesID', 'classesID');
            if(isset($fetchClasses[$url])) {
                $assignment = $this->assignment_m->get_single_assignment(array('assignmentID' => $id, 'classesID' => $url, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
                if(customCompute($assignment)) {
                    $this->retdata['assignmentanswers'] = $this->assignmentanswer_m->join_get_assignmentanswer($id, $schoolyearID);
                } else {
                    $this->retdata['assignmentanswers'] = [];
                }
            } else {
                $this->retdata['assignmentanswers'] = [];
            }
        } else {
            $this->retdata['classesID'] = $url;
            $this->retdata['assignmentanswers'] = [];
        }

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }
}
