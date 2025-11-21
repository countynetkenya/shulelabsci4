<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Visitorinfo extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("visitorinfo_m");
        $this->load->model('usertype_m');
        $this->load->model('systemadmin_m');
        $this->load->model('student_m');
        $this->load->model('parents_m');
        $this->load->model('teacher_m');
        $this->load->model('user_m');
        $this->load->model('studentrelation_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/visitorinfo",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $schoolID = $this->session->userdata('schoolID');
        $this->data['usertypes'] = $this->usertype_m->get_order_by_usertype_with_or(array('schoolID' => $schoolID));
        $schoolyearID = $this->session->userdata('defaultschoolyearID');

        $this->retdata['visitorinfos'] = $this->visitorinfo_m->get_order_by_visitorinfo(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
        $mapUsertype = pluck($this->usertype_m->get_order_by_usertype_with_or(array('schoolID' => $schoolID)), 'usertype', 'usertypeID');
        $this->retdata['users'] = [];

        $systemadmins = $this->systemadmin_m->get_order_by_systemadmin(array('schoolID' => $schoolID));
        if(customCompute($systemadmins)) {
            foreach ($systemadmins as $systemadmin) {
                $this->retdata['users'][$systemadmin->usertypeID][$systemadmin->systemadminID] = array($systemadmin->name, $mapUsertype[$systemadmin->usertypeID]);
            }
        }

        $teachers = $this->teacher_m->get_order_by_teacher(array('schoolID' => $schoolID));
        if(customCompute($teachers)) {
            foreach ($teachers as $teacher) {
                $this->retdata['users'][$teacher->usertypeID][$teacher->teacherID] = array($teacher->name, $mapUsertype[$teacher->usertypeID]);
            }
        }

        $students = $this->studentrelation_m->get_order_by_student(array('srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID));
        if(customCompute($students)) {
            foreach ($students as $student) {
                $this->retdata['users'][$student->usertypeID][$student->studentID] = array($student->name, $mapUsertype[$student->usertypeID]);
            }
        }

        $parents = $this->parents_m->get_order_by_parents(array('schoolID' => $schoolID));
        if(customCompute($parents)) {
            foreach ($parents as $parent) {
                $this->retdata['users'][$parent->usertypeID][$parent->parentsID] = array($parent->name, $mapUsertype[$parent->usertypeID]);
            }
        }

        $users = $this->user_m->get_order_by_user(array('schoolID' => $schoolID));
        if(customCompute($users)) {
            foreach ($users as $user) {
                $this->retdata['users'][$user->usertypeID][$user->userID] = array($user->name, $mapUsertype[$user->usertypeID]);
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
       *     path="/api/v10/visitorinfo/view/{visitorID}",
       *     @OA\Parameter(
       *         name="visitorID",
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
       *     @OA\Response(
       *         response="401",
       *         description="Permission denied"
       *     ),
       * )
       */
    public function view_get($id = 0)
    {
        if(permissionChecker('visitorinfo')) {
            $schoolyearID = $this->session->userdata('defaultschoolyearID');
            $this->retdata['visitorinfo'] = $this->visitorinfo_m->get_single_visitorinfo(array('visitorID' => $id, 'schoolyearID' => $schoolyearID, 'schoolID' => $this->session->userdata('schoolID')));

            if(customCompute($this->retdata['visitorinfo'])) {
                if(!empty($this->retdata['visitorinfo']->to_meet_usertypeID) && !empty($this->retdata['visitorinfo']->to_meet_personID)) {
                    $this->retdata['name'] = getNameByUsertypeIDAndUserID($this->retdata['visitorinfo']->to_meet_usertypeID, $this->retdata['visitorinfo']->to_meet_personID);
                } else {
                    $this->retdata['name'] = null;
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
                'message' => 'Permission deny',
                'data' => []
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }
    }
}
