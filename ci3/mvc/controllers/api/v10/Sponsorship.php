<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Sponsorship extends Api_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model("sponsor_m");
        $this->load->model("sponsorship_m");
        $this->load->model("section_m");
        $this->load->model('studentrelation_m');
        $this->load->model('student_m');
        $this->load->model('candidate_m');
        $this->load->model('transaction_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/sponsorship",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {

        $this->retdata['sponsorships'] = $this->sponsorship_m->get_sponsorship_with_student(array('schoolID' => $this->session->userdata('schoolID')));
            $this->response([
                'status'    => true,
                'message'   => 'Success',
                'data'      => $this->retdata
            ], REST_Controller::HTTP_OK);
    }

}
