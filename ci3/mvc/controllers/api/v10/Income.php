<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class Income extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('income_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/income",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $this->retdata['incomes'] = $this->income_m->get_income_with_user(array('income.schoolyearID' => $schoolyearID, 'schoolID' => $this->session->userdata('schoolID')));

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }
}
