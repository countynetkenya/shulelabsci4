<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Complain extends Api_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model("complain_m");
        $this->load->model("teacher_m");
        $this->load->model("student_m");
        $this->load->model("parents_m");
        $this->load->model("user_m");
        $this->load->model("classes_m");
        $this->load->model("section_m");
        $this->load->model("systemadmin_m");
        $this->load->model("studentrelation_m");
    }

    /**
       * @OA\Get(
       *     path="/api/v10/complain",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $usertypeID = $this->session->userdata('usertypeID');
        $userID     = $this->session->userdata('loginuserID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $schoolID = $this->session->userdata('schoolID');
        if($usertypeID == 1) {
            $this->retdata['complains'] = $this->complain_m->get_order_by_complain(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));

            $this->response([
                'status'    => true,
                'message'   => 'Success',
                'data'      => $this->retdata
            ], REST_Controller::HTTP_OK);
        } else {
            $this->retdata['complains'] = $this->complain_m->get_order_by_complain(array('schoolyearID' => $schoolyearID, 'create_userID' => $userID, 'create_usertypeID' => $usertypeID, 'schoolID' => $schoolID));

            $this->response([
                'status'    => true,
                'message'   => 'Success',
                'data'      => $this->retdata
            ], REST_Controller::HTTP_OK);
        }
    }

    /**
       * @OA\Get(
       *     path="/api/v10/complain/view/{complainID}",
       *     @OA\Parameter(
       *         name="complainID",
       *         description="",
       *         in = "path",
       *         allowMultiple=false,
       *         required=true,
       *         @OA\Schema(
       *             default=0,
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
    public function view_get($id = 0)
    {
        $schoolID = $this->session->userdata('schoolID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $loginuserID = $this->session->userdata('loginuserID');
        $loginusertypeID = $this->session->userdata('usertypeID');
        if((int)$id) {
            if($loginusertypeID == 1) {
                $this->retdata['complain'] = $this->complain_m->get_single_complain(array('complainID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
            } else {
                $this->retdata['complain'] = $this->complain_m->get_single_complain(array('complainID' => $id, 'schoolyearID' => $schoolyearID, 'create_userID' => $loginuserID, 'create_usertypeID' => $loginusertypeID, 'schoolID' => $schoolID));
            }
            if(customCompute($this->retdata['complain'])) {
                $usertypeID = $this->retdata['complain']->usertypeID;
                $userID     = $this->retdata['complain']->userID;
                $this->retdata['createinfo'] = getObjectByUserTypeIDAndUserID($this->retdata['complain']->create_usertypeID, $this->retdata['complain']->create_userID, $schoolyearID);
                if($usertypeID > 0 && $userID > 0) {
                    $this->retdata['usertypes'] = pluck($this->usertype_m->get_order_by_usertype_with_or(array('schoolID' => $schoolID)),'usertype','usertypeID');
                    if((int)$usertypeID) {
                        if($usertypeID == 1) {
                            $this->retdata['user'] = $this->systemadmin_m->get_single_systemadmin(array('systemadminID'=> $userID));
                        } elseif($usertypeID == 2) {
                            $this->retdata['user'] = $this->teacher_m->get_single_teacher(array('teacherID'=> $userID));
                        } elseif($usertypeID == 3) {
                            $this->retdata['user'] = $this->studentrelation_m->general_get_single_student(array('srstudentID'=> $userID, 'srschoolyearID' => $schoolyearID));
                            $this->retdata['classes'] = $this->classes_m->general_get_single_classes(array('classesID'=>$this->retdata['user']->srclassesID));
                            $this->retdata['section'] = $this->section_m->general_get_single_section(array('sectionID'=>$this->retdata['user']->srsectionID));
                        } elseif($usertypeID == 4) {
                            $this->retdata['user'] = $this->parents_m->get_single_parents(array('parentsID'=> $userID));
                        } else {
                            $this->retdata['user'] = $this->user_m->get_single_user(array('usertypeID' => $usertypeID, 'userID'=> $userID));
                        }
                    } else {
                        $this->retdata['user'] = [];
                        $this->retdata['classes'] = [];
                        $this->retdata['section'] = [];
                    }
                } else {
                    $this->retdata['user'] = [];
                    $this->retdata['classes'] = [];
                    $this->retdata['section'] = [];
                }

                $this->response([
                    'status'    => true,
                    'message'   => 'Success',
                    'data'      => $this->retdata
                ], REST_Controller::HTTP_OK);
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
