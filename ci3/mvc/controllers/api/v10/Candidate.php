<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Candidate extends Api_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model("candidate_m");
        $this->load->model("classes_m");
        $this->load->model("section_m");
        $this->load->model('studentrelation_m');
        $this->load->model('student_m');
        $this->load->model('sponsor_m');
        $this->load->model('subject_m');
        $this->load->model('sponsorship_m');
        $this->load->model('studentgroup_m');
        $this->load->model('transaction_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/candidate",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $schoolID = $this->session->userdata('schoolID');
        $this->retdata['classes'] = pluck($this->classes_m->general_get_order_by_classes(array('schoolID' => $schoolID)), 'classes', 'classesID');
        $this->retdata['sponsors'] = pluck($this->sponsor_m->get_order_by_sponsor(array('schoolID' => $schoolID)), 'name', 'sponsorID');
        $this->retdata['candidates'] = $this->candidate_m->get_candidate_with_student_sponsorship(array('schoolID' => $schoolID));
        $this->response([
            'status' => true,
            'message' => 'Success',
            'data' => $this->retdata
        ], REST_Controller::HTTP_OK);
    }

    /**
       * @OA\Get(
       *     path="/api/v10/candidate/view/{candidateID}",
       *     @OA\Parameter(
       *         name="candidateID",
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
        if ((int)$id) {
            $schoolID                   = $this->session->userdata('schoolID');
            $this->retdata['candidate'] = $this->candidate_m->get_single_candidate(['candidateID' => $id, 'schoolID' => $schoolID]);

            if (customCompute($this->retdata['candidate'])) {
                $this->retdata['photo'] = pluck($this->student_m->general_get_order_by_student(array('schoolID' => $schoolID)), 'photo', 'studentID');
                $this->retdata['profile'] = $this->studentrelation_m->get_single_studentrelation(['srstudentID' => $this->retdata['candidate']->studentID, 'srschoolID' => $schoolID]);
                $this->retdata['groups'] = pluck($this->studentgroup_m->get_order_by_studentgroup(array('schoolID' => $schoolID)), 'group', 'studentgroupID');
                $this->retdata['subjects'] = pluck($this->subject_m->general_get_order_by_subject(array('schoolID' => $schoolID)), 'subject', 'subjectID');
                $this->retdata['usertypes'] = pluck($this->usertype_m->get_order_by_usertype_with_or(array('schoolID' => $schoolID)), 'usertype', 'usertypeID');
                $this->retdata['classes'] = $this->classes_m->get_single_classes(['classesID' => $this->retdata['profile']->srclassesID, 'schoolID' => $schoolID]);
                $this->retdata['section'] = $this->section_m->get_single_section(['sectionID' => $this->retdata['profile']->srsectionID, 'schoolID' => $schoolID]);
                $this->retdata['sponsors'] = pluck($this->sponsor_m->get_order_by_sponsor(array('schoolID' => $schoolID)), 'name', 'sponsorID');

                $this->response([
                    'status' => true,
                    'message' => 'Success',
                    'data' => $this->retdata
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
