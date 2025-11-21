<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Tmember extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("tmember_m");
        $this->load->model("transport_m");
        $this->load->model("student_m");
        $this->load->model("studentrelation_m");
        $this->load->model("section_m");
        $this->load->model('studentgroup_m');
        $this->load->model('subject_m');
        $this->load->model('classes_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/tmember",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get($id=null)
    {
        $myProfile = false;
        if($this->session->userdata('usertypeID') == 3) {
            $id = $this->data['myclass'];
            if(!permissionChecker('tmember_view')) {
                $myProfile = true;
            }
        }

        if($this->session->userdata('usertypeID') == 3 && $myProfile) {
            $url = $id;
            $id = $this->session->userdata('loginuserID');
            $this->view_get($id, $url);
        } else {
            $schoolID = $this->session->userdata('schoolID');
            $schoolyearID = $this->session->userdata('defaultschoolyearID');
            if((int)$id) {
                $this->data['classesID'] = $id;
                $this->retdata['classes'] = $this->classes_m->get_order_by_classes(array('schoolID' => $schoolID));
                $fetchClass = pluck($this->retdata['classes'], 'classesID', 'classesID');
                if(isset($fetchClass[$id])) {
                    $retStudentArr = [];
                    $students      = $this->studentrelation_m->get_order_by_student(array('srclassesID' => $id, 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID));
                    if(customCompute($students)) {
                        foreach ($students as $student) {
                            if((int)$student->transport) {
                                $retStudentArr[] = $student;
                            }
                        }
                    }
                    $this->retdata['students'] = $retStudentArr;
                } else {
                    $this->retdata['students'] = [];
                }

                $this->response([
                    'status'    => true,
                    'message'   => 'Success',
                    'data'      => $this->retdata
                ], REST_Controller::HTTP_OK);
            } else {
                $this->retdata['classesID'] = $id;
                $this->retdata['students'] = [];
                $this->retdata['classes'] = $this->classes_m->get_classes();

                $this->response([
                    'status'    => true,
                    'message'   => 'Success',
                    'data'      => $this->retdata
                ], REST_Controller::HTTP_OK);
            }
        }
    }

    /**
       * @OA\Get(
       *     path="/api/v10/tmember/view/{studentID}/{classID}",
       *     @OA\Parameter(
       *         name="studentID",
       *         description="",
       *         in = "path",
       *         allowMultiple=false,
       *         required=true,
       *         @OA\Schema(
       *             default=null,
       *             type="integer"
       *         )
       *     ),
       *     @OA\Parameter(
       *         name="classID",
       *         description="",
       *         in = "path",
       *         allowMultiple=false,
       *         required=true,
       *         @OA\Schema(
       *             default=null,
       *             type="integer"
       *         )
       *     ),
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     ),
       *     @OA\Response(
       *         response="404",
       *         description="Error 404"
       *     ),
       * )
       */
    public function view_get($id = null, $url = null)
    {
        $schoolID = $this->session->userdata('schoolID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        if((int)$id && (int)$url) {
            $fetchClass = pluck($this->classes_m->get_order_by_classes(array('schoolID' => $schoolID)), 'classesID', 'classesID');
            if(isset($fetchClass[$url])) {
                $this->retdata['classesID'] = $url;
                $this->retdata['student'] = $this->studentrelation_m->get_single_student(array('srstudentID' => $id, 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID), true);
                $this->retdata['usertypes'] = pluck($this->usertype_m->get_order_by_usertype_with_or(array('schoolID' => $schoolID)),'usertype','usertypeID');
                if(customCompute($this->retdata['student'])) {
                    $this->retdata["classes"] = $this->classes_m->get_classes($this->retdata['student']->srclassesID);
                    $this->retdata['tmember'] = $this->tmember_m->get_single_tmember(array('studentID' => $id));
                    $this->retdata["section"] = $this->section_m->general_get_section($this->retdata['student']->srsectionID);
                    if(customCompute($this->retdata['tmember'])) {
                        $this->retdata['transport'] = $this->transport_m->get_transport($this->retdata['tmember']->transportID);

                        $this->response([
                            'status'    => true,
                            'message'   => 'Success',
                            'data'      => $this->retdata
                        ], REST_Controller::HTTP_OK);
                    } else {
                        $this->retdata['transport'] = [];

                        $this->response([
                            'status'    => true,
                            'message'   => 'Success',
                            'data'      => $this->retdata
                        ], REST_Controller::HTTP_OK);
                    }
                } else {
                    $this->response([
                        'status' => false,
                        'message' => 'Error 404',
                        'data' => []
                    ], REST_Controller::HTTP_NOT_FOUND);
                }
            } else {
               $this->response([
                    'status' => false,
                    'message' => 'Error 404',
                    'data' => []
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        } else {
            $this->response([
                'status' => false,
                'message' => 'Error 404',
                'data' => []
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }
}
