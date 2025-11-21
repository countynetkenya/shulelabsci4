<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Asset_assignment extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_m');
        $this->load->model('teacher_m');
        $this->load->model('student_m');
        $this->load->model('parents_m');
        $this->load->model('usertype_m');
        $this->load->model('systemadmin_m');
        $this->load->model('asset_assignment_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/asset_assignment",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $this->retdata['asset_assignments'] = $this->asset_assignment_m->get_asset_assignment_with_userypeID(array('asset_assignment.schoolID' => $this->session->userdata('schoolID')));
        if(customCompute($this->retdata['asset_assignments'])) {
            foreach ($this->retdata['asset_assignments'] as $key => $assignment) {
                $getName = $this->userTableCall($assignment->usertypeID, $assignment->check_out_to);
                if(!empty($getName)) {
                    $this->retdata['asset_assignments'][$key] = (object) array_merge( (array)$assignment, array( 'assigned_to' => $getName));
                }
            }
        }

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }

    /**
       * @OA\Get(
       *     path="/api/v10/asset_assignment/view/{asset_assignmentID}",
       *     @OA\Parameter(
       *         name="asset_assignmentID",
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
    public function view_get($id = null)
    {
        if((int)$id) {
            $schoolID = $this->session->userdata('schoolID');
            $this->retdata['asset_assignment'] = $this->asset_assignment_m->get_single_asset_assignment_with_usertypeID(array('asset_assignmentID' => $id, 'schoolID' => $schoolID));
            $this->retdata['usertypes'] = pluck($this->usertype_m->get_order_by_usertype_with_or(array('schoolID' => $schoolID)), 'usertype', 'usertypeID');

            if(customCompute($this->retdata['asset_assignment'])) {
                $usertypeID = $this->retdata['asset_assignment']->usertypeID;

                if($usertypeID == 3) {
                    $student = $this->student_m->get_single_student(array('studentID' => $this->retdata['asset_assignment']->check_out_to));

                    if(customCompute($student)) {
                        $this->retdata['user'] = $this->allUsersArrayObject($usertypeID, $student->studentID, $student->classesID);
                    } else {
                        $this->retdata['user'] = [];
                    }
                } else {
                    $this->retdata['user'] = $this->allUsersArrayObject($usertypeID, $this->retdata['asset_assignment']->check_out_to);
                }

                $this->response([
                    'status'    => true,
                    'message'   => 'Success',
                    'data'      => $this->retdata
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status'    => false,
                    'message'   => 'Error 404',
                    'data'      => [],
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        } else {
            $this->response([
                'status'    => false,
                'message'   => 'Error 404',
                'data'      => [],
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    private function userTableCall($usertypeID, $userID)
    {
        $this->load->model('systemadmin_m');
        $this->load->model('teacher_m');
        $this->load->model('student_m');
        $this->load->model('parents_m');
        $this->load->model('user_m');

        $findUserName = '';
        if($usertypeID == 1) {
            $user = $this->db->get_where('systemadmin', array("usertypeID" => $usertypeID, 'systemadminID' => $userID));
            $alluserdata = $user->row();
            if(customCompute($alluserdata)) {
                $findUserName = $alluserdata->name;
            }
            return $findUserName;
        } elseif($usertypeID == 2) {
            $user = $this->db->get_where('teacher', array("usertypeID" => $usertypeID, 'teacherID' => $userID));
            $alluserdata = $user->row();
            if(customCompute($alluserdata)) {
                $findUserName = $alluserdata->name;
            }
            return $findUserName;
        } elseif($usertypeID == 3) {
            $user = $this->db->get_where('student', array("usertypeID" => $usertypeID, 'studentID' => $userID));
            $alluserdata = $user->row();
            if(customCompute($alluserdata)) {
                $findUserName = $alluserdata->name;
            }
            return $findUserName;
        } elseif($usertypeID == 4) {
            $user = $this->db->get_where('parents', array("usertypeID" => $usertypeID, 'parentsID' => $userID));
            $alluserdata = $user->row();
            if(customCompute($alluserdata)) {
                $findUserName = $alluserdata->name;
            }
            return $findUserName;
        } else {
            $user = $this->db->get_where('user', array("usertypeID" => $usertypeID, 'userID' => $userID));
            $alluserdata = $user->row();
            if(customCompute($alluserdata)) {
                $findUserName = $alluserdata->name;
            }
            return $findUserName;
        }
    }

    Private function allUsersArrayObject($usertypeID, $userID, $classesID = 0)
    {
        $returnArray = [];
        if($usertypeID == 1) {
            $returnArray = $this->systemadmin_m->get_single_systemadmin(array('systemID' => $userID));
        } elseif($usertypeID == 2) {
            $returnArray = $this->teacher_m->general_get_single_teacher(array('teacherID' => $userID));
        } elseif($usertypeID == 3) {
            $returnArray = $this->student_m->general_get_single_student(array('studentID' => $userID, 'classesID' => $classesID, 'schoolyearID' => $this->data['siteinfos']->school_year));
        } elseif($usertypeID == 4) {
            $returnArray = $this->parents_m->get_single_parents(array('parentsID' => $userID));
        } else {
            $returnArray = $this->user_m->get_single_user(array('usertypeID' => $usertypeID, 'userID' => $userID));
        }
        return $returnArray;
    }
}
