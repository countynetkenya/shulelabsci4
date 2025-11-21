<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Syllabus extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('classes_m');
        $this->load->model('syllabus_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/syllabus",
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
                $schoolyearID = $this->session->userdata('defaultschoolyearID');
                $this->retdata['syllabuss'] = $this->syllabus_m->get_order_by_syllabus(array('schoolyearID' => $schoolyearID, 'classesID' => $id, 'schoolID' => $schoolID));
            } else {
                $this->retdata['classesID'] = 0;
                $this->retdata['syllabuss'] = [];
            }
        } else {
            $this->retdata['classesID'] = 0;
            $this->retdata['syllabuss'] = [];
        }

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }
}
